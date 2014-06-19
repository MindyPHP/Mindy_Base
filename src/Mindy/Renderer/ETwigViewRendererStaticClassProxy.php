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
 * @date 12/06/14.06.2014 17:12
 */

namespace Mindy\Renderer;
use ReflectionClass;


/**
 * Class-proxy for static classes
 * Needed because you can't pass static class to Twig other way
 *
 * @author Leonid Svyatov <leonid@svyatov.ru>
 * @version 1.0.0
 */
class ETwigViewRendererStaticClassProxy
{
    private $_staticClassName;

    public function __construct($staticClassName)
    {
        $this->_staticClassName = $staticClassName;
    }

    public function __get($property)
    {
        $class = new ReflectionClass($this->_staticClassName);
        return $class->getStaticPropertyValue($property);
    }

    public function __set($property, $value)
    {
        $class = new ReflectionClass($this->_staticClassName);
        $class->setStaticPropertyValue($property, $value);
        return $value;
    }

    public function __call($method, $arguments)
    {
        if(!is_callable($this->_staticClassName)) {
            return null;
        } else {
            return call_user_func_array(array($this->_staticClassName, $method), $arguments);
        }
    }
}
