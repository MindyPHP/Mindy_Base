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
use Mindy\Di\ServiceLocator;
use Mindy\Helper\Collection;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
use ReflectionClass;

/**
 * BaseModule is the base class for module and application classes.
 * @property string $id The module ID.
 * @property string $basePath The root directory of the module. Defaults to the directory containing the module class.
 * @property Collection $params The list of user-defined parameters.
 * @property string $modulePath The directory that contains the application modules. Defaults to the 'modules' subdirectory of {@link basePath}.
 * @property array $modules The configuration of the currently installed modules (module ID => configuration).
 * @property array $components The application components (indexed by their IDs).
 * @property array $import List of aliases to be imported.
 * @property array $aliases List of aliases to be defined. The array keys are root aliases,
 */
abstract class BaseModule
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
     * @var string
     */
    private $_basePath;
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
    public function __construct($id, $config = null)
    {
        $this->_id = $id;
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
     * @return string
     */
    public function getName()
    {
        return self::t(ucfirst($this->getId()));
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
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
     * @return ServiceLocator
     */
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
     * @param $id
     * @return null|object
     * @throws Exception
     */
    public function getComponent($id)
    {
        return $this->getLocator()->get($id);
    }

    /**
     * @param $id
     * @param $component
     * @throws Exception
     */
    public function setComponent($id, $component)
    {
        $this->getLocator()->set($id, $component);
    }

    /**
     * @param bool $definitions
     * @return array
     */
    public function getComponents($definitions = true)
    {
        return $this->getLocator()->getComponents($definitions);
    }

    /**
     * @param $components
     */
    public function setComponents($components)
    {
        $this->getLocator()->setComponents($components);
    }

    /**
     * Loads static application components.
     */
    protected function preloadComponents()
    {
        foreach ($this->preload as $id) {
            $this->getLocator()->get($id);
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

    /**
     * @param $str
     * @param array $params
     * @param string $dic
     * @return string
     */
    public static function t($str, $params = [], $dic = 'main')
    {
        return Mindy::app()->translate->t(get_called_class() . "." . $dic, $str, $params);
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return Mindy::app()->getParam($this->getId());
    }
}
