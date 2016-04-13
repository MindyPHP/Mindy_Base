<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 16/10/14.10.2014 18:57
 */

namespace Mindy\Base;

use Exception;
use Mindy\Base\Interfaces\IModule;
use Mindy\Di\ServiceLocator;
use Mindy\Helper\Alias;
use Mindy\Helper\Collection;
use Mindy\Helper\Creator;
use Mindy\Helper\Params;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
use ReflectionClass;

/**
 * BaseModule is the base class for module and application classes.
 *
 * BaseModule mainly manages application components and sub-modules.
 *
 * @property string $id The module ID.
 * @property string $basePath The root directory of the module. Defaults to the directory containing the module class.
 * @property Collection $params The list of user-defined parameters.
 * @property string $modulePath The directory that contains the application modules. Defaults to the 'modules' subdirectory of {@link basePath}.
 * @property BaseModule $parentModule The parent module. Null if this module does not have a parent.
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
 * @package Mindy\Base
 */
abstract class BaseModule implements IModule
{
    use Configurator, Accessors;

    /**
     * @var array the IDs of the application components that should be preloaded.
     */
    public $preload = [];
    /**
     * @var string
     */
    private $_id;
    /**
     * @var Module
     */
    private $_parentModule;
    /**
     * @var string
     */
    private $_basePath;
    /**
     * @var array
     */
    private $_componentConfig = [];
    /**
     * @var \Mindy\Di\ServiceLocator
     */
    private $_locator;

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

        Alias::set($id, $this->getBasePath());

        $this->preinit();

        $this->configure($config);
        $this->preloadComponents();

        $this->init();
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

    public static function preConfigure()
    {

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
            throw new Exception(Mindy::t('base', 'Base path "{path}" is not a valid directory.', ['{path}' => $path]));
        }
    }

    public function getLocator()
    {
        if ($this->_locator === null) {
            $this->_locator = new ServiceLocator();
        }
        return $this->_locator;
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
        return $this->getLocator()->has($id);
    }

    /**
     * Retrieves the named application component.
     * @param string $id application component ID (case-sensitive)
     * @param boolean $createIfNull whether to create the component if it doesn't exist yet.
     * @return object the application component instance, null if the application component is disabled or does not exist.
     * @see hasComponent
     */
    public function getComponent($id, $createIfNull = true)
    {
        if ($this->hasComponent($id)) {
            return $this->getLocator()->get($id);
        } elseif (isset($this->_componentConfig[$id]) && $createIfNull) {
            $config = $this->_componentConfig[$id];
            if (!isset($config['enabled']) || $config['enabled']) {
                Mindy::app()->logger->debug("Loading \"$id\" application component", [], 'system.CModule');
                unset($config['enabled']);
                $component = Creator::createObject($config);
                $this->getLocator()->set($id, $component);
                return $component;
            }
        }
    }

    /**
     * Puts a component under the management of the module.
     * The component will be initialized by calling its {@link CApplicationComponent::init() init()}
     * method if it has not done so.
     * @param string $id component ID
     * @param array|object $component application component
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
            $this->getLocator()->clear($id);
            return;
        } elseif (is_object($component)) {
            $this->getLocator()->set($id, $component);
            return;
        } elseif ($this->getLocator()->has($id)) {
            if (isset($component['class']) && get_class($this->getLocator()->get($id)) !== $component['class']) {
                $this->getLocator()->clear($id);
                $this->_componentConfig[$id] = $component; //we should ignore merge here
                return;
            }

            Creator::configure($this->getLocator()->get($id), $component);
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
        return $this->getLocator()->getComponents(!$loadedOnly);
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
     *         'enabled'=>!MINDY_DEBUG,  // enable caching in non-debug mode
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
            $components = array_merge($this->getLocator()->getComponents(), $components);
        }
        foreach ($components as $id => $obj) {
            $this->getLocator()->set($id, is_object($obj) ? $obj : Creator::createObject($obj));
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

    public function getDescription()
    {
        return '';
    }

    public static function t($str, $params = [], $dic = 'main')
    {
        return Mindy::t(get_called_class() . "." . $dic, $str, $params);
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
     * @return mixed
     */
    public function getParams()
    {
        return Params::get($this->getId());
    }
}
