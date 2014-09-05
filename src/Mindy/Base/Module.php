<?php

namespace Mindy\Base;

/**
 * CModule class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
use Mindy\Base\Exception\Exception;
use Mindy\Base\Interfaces\IApplicationComponent;
use Mindy\Base\Interfaces\IModule;
use Mindy\Di\ServiceLocator;
use Mindy\Helper\Alias;
use Mindy\Helper\Collection;
use Mindy\Helper\Creator;
use Mindy\Helper\Map;
use Mindy\Helper\Traits\BehaviorAccessors;
use Mindy\Helper\Traits\Configurator;
use ReflectionClass;

/**
 * CModule is the base class for module and application classes.
 *
 * CModule mainly manages application components and sub-modules.
 *
 * @property string $id The module ID.
 * @property string $basePath The root directory of the module. Defaults to the directory containing the module class.
 * @property CAttributeCollection $params The list of user-defined parameters.
 * @property string $modulePath The directory that contains the application modules. Defaults to the 'modules' subdirectory of {@link basePath}.
 * @property CModule $parentModule The parent module. Null if this module does not have a parent.
 * @property array $modules The configuration of the currently installed modules (module ID => configuration).
 * @property array $components The application components (indexed by their IDs).
 * @property array $import List of aliases to be imported.
 * @property array $aliases List of aliases to be defined. The array keys are root aliases,
 * while the array values are paths or aliases corresponding to the root aliases.
 * For example,
 * <pre>
 * array(
 *    'models'=>'application.models',              // an existing alias
 *    'extensions'=>'application.extensions',      // an existing alias
 *    'backend'=>dirname(__FILE__).'/../backend',  // a directory
 * )
 * </pre>.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 */
abstract class Module implements IModule
{
    use Configurator, BehaviorAccessors;

    /**
     * @var array the IDs of the application components that should be preloaded.
     */
    public $preload = [];
    /**
     * @var array the behaviors that should be attached to the module.
     * The behaviors will be attached to the module when {@link init} is called.
     * Please refer to {@link CModel::behaviors} on how to specify the value of this property.
     */
    public $behaviors = [];

    private $_id;
    private $_parentModule;
    private $_basePath;
    private $_modulePath;
    private $_params;
    private $_modules = [];
    private $_moduleConfig = [];
    private $_componentConfig = [];

    /**
     * @var \Mindy\Di\ServiceLocator
     */
    public $locator;

    /**
     * Constructor.
     * @param string $id the ID of this module
     * @param Module $parent the parent module (if any)
     * @param mixed $config the module configuration. It can be either an array or
     * the path of a PHP file returning the configuration array.
     */
    public function __construct($id, $parent, $config = null)
    {
        $this->_id = $id;
        $this->_parentModule = $parent;

        // set basePath at early as possible to avoid trouble
        if (is_string($config)) {
            $config = require($config);
        }

        if (isset($config['basePath'])) {
            $this->setBasePath($config['basePath']);
            unset($config['basePath']);
        }

        Mindy::setPathOfAlias($id, $this->getBasePath());

        $this->preinit();

        $this->locator = new ServiceLocator();

        $this->configure($config);
        $this->attachBehaviors($this->behaviors);
        $this->preloadComponents();

        $this->init();
    }

    public function getVersion()
    {
        return '1.0';
    }

    /**
     * Getter magic method.
     * This method is overridden to support accessing application components
     * like reading module properties.
     * @param string $name application component or property name
     * @return mixed the named property value
     */
    public function __get($name)
    {
        if ($this->hasComponent($name)) {
            return $this->getComponent($name);
        } else {
            return $this->__getInternal($name);
        }
    }

    /**
     * Checks if a property value is null.
     * This method overrides the parent implementation by checking
     * if the named application component is loaded.
     * @param string $name the property name or the event name
     * @return boolean whether the property value is null
     */
    public function __isset($name)
    {
        if ($this->hasComponent($name)) {
            return $this->getComponent($name) !== null;
        } else {
            return $this->__issetInternal($name);
        }
    }

    /**
     * Returns the module ID.
     * @return string the module ID.
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Sets the module ID.
     * @param string $id the module ID
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Returns the root directory of the module.
     * @return string the root directory of the module. Defaults to the directory containing the module class.
     */
    public function getBasePath()
    {
        if ($this->_basePath === null) {
            $class = new ReflectionClass(get_class($this));
            $this->_basePath = dirname($class->getFileName());
        }
        return $this->_basePath;
    }

    /**
     * Sets the root directory of the module.
     * This method can only be invoked at the beginning of the constructor.
     * @param string $path the root directory of the module.
     * @throws Exception if the directory does not exist.
     */
    public function setBasePath($path)
    {
        if (($this->_basePath = realpath($path)) === false || !is_dir($this->_basePath)) {
            throw new Exception(Mindy::t('yii', 'Base path "{path}" is not a valid directory.', ['{path}' => $path]));
        }
    }

    /**
     * Returns user-defined parameters.
     * @return AttributeCollection the list of user-defined parameters
     */
    public function getParams()
    {
        if ($this->_params !== null) {
            return $this->_params;
        } else {
            $this->_params = new AttributeCollection;
            $this->_params->caseSensitive = true;
            return $this->_params;
        }
    }

    /**
     * Sets user-defined parameters.
     * @param array $value user-defined parameters. This should be in name-value pairs.
     */
    public function setParams($value)
    {
        $params = $this->getParams();
        foreach ($value as $k => $v) {
            $params->add($k, $v);
        }
    }

    /**
     * Returns the directory that contains the application modules.
     * @return string the directory that contains the application modules. Defaults to the 'modules' subdirectory of {@link basePath}.
     */
    public function getModulePath()
    {
        if ($this->_modulePath !== null) {
            return $this->_modulePath;
        } else {
            return $this->_modulePath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'Modules';
        }
    }

    /**
     * Sets the directory that contains the application modules.
     * @param string $value the directory that contains the application modules.
     * @throws Exception if the directory is invalid
     */
    public function setModulePath($value)
    {
        if (($this->_modulePath = realpath($value)) === false || !is_dir($this->_modulePath)) {
            throw new Exception(Mindy::t('yii', 'The module path "{path}" is not a valid directory.', ['{path}' => $value]));
        }
    }

    /**
     * Defines the root aliases.
     * @param array $mappings list of aliases to be defined. The array keys are root aliases,
     * while the array values are paths or aliases corresponding to the root aliases.
     * For example,
     * <pre>
     * array(
     *    'models'=>'application.models',              // an existing alias
     *    'extensions'=>'application.extensions',      // an existing alias
     *    'backend'=>dirname(__FILE__).'/../backend',  // a directory
     * )
     * </pre>
     */
    public function setAliases($mappings)
    {
        foreach ($mappings as $name => $alias) {
            if (($path = Alias::get($alias)) !== false) {
                Alias::set($name, $path);
            } else {
                Alias::set($name, $alias);
            }
        }
    }

    /**
     * Returns the parent module.
     * @return Module the parent module. Null if this module does not have a parent.
     */
    public function getParentModule()
    {
        return $this->_parentModule;
    }

    /**
     * Retrieves the named application module.
     * The module has to be declared in {@link modules}. A new instance will be created
     * when calling this method with the given ID for the first time.
     * @param string $id application module ID (case-sensitive)
     * @return Module the module instance, null if the module is disabled or does not exist.
     */
    public function getModule($id)
    {
        $id = ucfirst($id);
        if (isset($this->_modules[$id]) || array_key_exists($id, $this->_modules)) {
            return $this->_modules[$id];
        } elseif (isset($this->_moduleConfig[$id])) {
            $config = $this->_moduleConfig[$id];
            if (!isset($config['enabled']) || $config['enabled']) {
                Mindy::app()->logger->info("Loading \"$id\" module", 'system.base.CModule');
                $class = $config['class'];
                unset($config['class'], $config['enabled']);
                if ($this === Mindy::app()) {
                    $module = Creator::createObject($class, $id, null, $config);
                } else {
                    $module = Creator::createObject($class, $this->getId() . '/' . $id, $this, $config);
                }
                return $this->_modules[$id] = $module;
            }
        }
    }

    /**
     * Returns a value indicating whether the specified module is installed.
     * @param string $id the module ID
     * @return boolean whether the specified module is installed.
     * @since 1.1.2
     */
    public function hasModule($id)
    {
        return isset($this->_moduleConfig[$id]) || isset($this->_modules[$id]);
    }

    /**
     * Returns the configuration of the currently installed modules.
     * @return array the configuration of the currently installed modules (module ID => configuration)
     */
    public function getModules()
    {
        return $this->_moduleConfig;
    }

    /**
     * Configures the sub-modules of this module.
     *
     * Call this method to declare sub-modules and configure them with their initial property values.
     * The parameter should be an array of module configurations. Each array element represents a single module,
     * which can be either a string representing the module ID or an ID-configuration pair representing
     * a module with the specified ID and the initial property values.
     *
     * For example, the following array declares two modules:
     * <pre>
     * array(
     *     'admin',                // a single module ID
     *     'payment'=>array(       // ID-configuration pair
     *         'server'=>'paymentserver.com',
     *     ),
     * )
     * </pre>
     *
     * By default, the module class is determined using the expression <code>ucfirst($moduleID).'Module'</code>.
     * And the class file is located under <code>modules/$moduleID</code>.
     * You may override this default by explicitly specifying the 'class' option in the configuration.
     *
     * You may also enable or disable a module by specifying the 'enabled' option in the configuration.
     *
     * @param array $modules module configurations.
     */
    public function setModules($modules)
    {
        foreach ($modules as $id => $module) {
            if (is_int($id)) {
                $id = $module;
                $module = [];
            }
            if (!isset($module['class'])) {
                Alias::set($id, $this->getModulePath() . DIRECTORY_SEPARATOR . $id);
                $module['class'] = '\\Modules\\' . ucfirst($id) . '\\' . ucfirst($id) . 'Module';
            }

            if (isset($this->_moduleConfig[$id])) {
                $this->_moduleConfig[$id] = Collection::mergeArray($this->_moduleConfig[$id], $module);
            } else {
                $this->_moduleConfig[$id] = $module;
            }
        }
    }

    /**
     * Checks whether the named component exists.
     * @param string $id application component ID
     * @return boolean whether the named application component exists (including both loaded and disabled.)
     */
    public function hasComponent($id)
    {
        if (!is_string($id)) {
            $id = array_shift($id);
        }
        return $this->locator->has($id);
    }

    /**
     * Retrieves the named application component.
     * @param string $id application component ID (case-sensitive)
     * @param boolean $createIfNull whether to create the component if it doesn't exist yet.
     * @return IApplicationComponent the application component instance, null if the application component is disabled or does not exist.
     * @see hasComponent
     */
    public function getComponent($id, $createIfNull = true)
    {
        if ($this->hasComponent($id)) {
            return $this->locator->get($id);
        } elseif (isset($this->_componentConfig[$id]) && $createIfNull) {
            $config = $this->_componentConfig[$id];
            if (!isset($config['enabled']) || $config['enabled']) {
                Mindy::trace("Loading \"$id\" application component", 'system.CModule');
                unset($config['enabled']);
                $component = Creator::createObject($config);
                // TODO перенесено в __construct, что вроде бы логично $component->init();
                $this->locator->set($id, $component);
                return $component;
            }
        }
    }

    /**
     * Puts a component under the management of the module.
     * The component will be initialized by calling its {@link CApplicationComponent::init() init()}
     * method if it has not done so.
     * @param string $id component ID
     * @param array|IApplicationComponent $component application component
     * (either configuration array or instance). If this parameter is null,
     * component will be unloaded from the module.
     * @param boolean $merge whether to merge the new component configuration
     * with the existing one. Defaults to true, meaning the previously registered
     * component configuration with the same ID will be merged with the new configuration.
     * If set to false, the existing configuration will be replaced completely.
     * This parameter is available since 1.1.13.
     */
    public function setComponent($id, $component, $merge = true)
    {
        if ($component === null) {
            $this->locator->clear($id);
            return;
        } elseif ($component instanceof IApplicationComponent) {
            $this->locator->set($id, $component);
            return;
        } elseif ($this->locator->has($id)) {
            if (isset($component['class']) && get_class($this->locator->get($id)) !== $component['class']) {
                $this->locator->clear($id);
                $this->_componentConfig[$id] = $component; //we should ignore merge here
                return;
            }

            Creator::configure($this->locator->get($id), $component);
        } elseif (isset($this->_componentConfig[$id]['class'], $component['class']) && $this->_componentConfig[$id]['class'] !== $component['class']) {
            $this->_componentConfig[$id] = $component; //we should ignore merge here
            return;
        }

        if (isset($this->_componentConfig[$id]) && $merge) {
            $this->_componentConfig[$id] = Collection::mergeArray($this->_componentConfig[$id], $component);
        } else {
            $this->_componentConfig[$id] = $component;
        }
    }

    /**
     * Returns the application components.
     * @param boolean $loadedOnly whether to return the loaded components only. If this is set false,
     * then all components specified in the configuration will be returned, whether they are loaded or not.
     * Loaded components will be returned as objects, while unloaded components as configuration arrays.
     * This parameter has been available since version 1.1.3.
     * @return array the application components (indexed by their IDs)
     */
    public function getComponents($loadedOnly = true)
    {
        return $this->locator->getComponents(!$loadedOnly);
    }

    /**
     * Sets the application components.
     *
     * When a configuration is used to specify a component, it should consist of
     * the component's initial property values (name-value pairs). Additionally,
     * a component can be enabled (default) or disabled by specifying the 'enabled' value
     * in the configuration.
     *
     * If a configuration is specified with an ID that is the same as an existing
     * component or configuration, the existing one will be replaced silently.
     *
     * The following is the configuration for two components:
     * <pre>
     * array(
     *     'db'=>array(
     *         'class'=>'CDbConnection',
     *         'connectionString'=>'sqlite:path/to/file.db',
     *     ),
     *     'cache'=>array(
     *         'class'=>'CDbCache',
     *         'connectionID'=>'db',
     *         'enabled'=>!YII_DEBUG,  // enable caching in non-debug mode
     *     ),
     * )
     * </pre>
     *
     * @param array $components application components(id=>component configuration or instances)
     * @param boolean $merge whether to merge the new component configuration with the existing one.
     * Defaults to true, meaning the previously registered component configuration of the same ID
     * will be merged with the new configuration. If false, the existing configuration will be replaced completely.
     */
    public function setComponents($components, $merge = true)
    {
        if ($merge) {
            $components = array_merge($this->locator->getComponents(), $components);
        }
        foreach ($components as $id => $obj) {
            $this->locator->set($id, is_object($obj) ? $obj : Creator::createObject($obj));
        }
    }

    /**
     * Loads static application components.
     */
    protected function preloadComponents()
    {
        foreach ($this->preload as $id) {
            $this->getComponent($id);
        }
    }

    /**
     * Preinitializes the module.
     * This method is called at the beginning of the module constructor.
     * You may override this method to do some customized preinitialization work.
     * Note that at this moment, the module is not configured yet.
     * @see init
     */
    protected function preinit()
    {
    }

    public function getName()
    {
        return self::t(ucfirst($this->getId()));
    }

    public static function t($str, $params = [], $dic = 'main')
    {
        return Mindy::t(get_called_class() . "." . $dic, $str, $params);
    }

    /**
     * Return array of mail templates and his variables
     * @return array
     */
    public function getMailTemplates()
    {
        return [];
    }

    /**
     * Return array for MMenu {$see: MMenu} widget
     * @abstract
     * @return array
     */
    public function getMenu()
    {
        return [];
    }

    /**
     * Install sql
     */
    public function install()
    {

    }

    /**
     * Run migrations, update sql
     */
    public function update()
    {

    }

    /**
     * Delete tables from database
     */
    public function delete()
    {

    }
}
