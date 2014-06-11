<?php

namespace Mindy\Base\Compatability;

use Mindy\Base\Exception\Exception;
use Mindy\Base\Mindy;
use Mindy\Helper\Alias;
use ReflectionClass;

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
 * @date 09/06/14.06.2014 17:56
 */
trait CompatabilityLayer
{
    public static function getPathOfAlias($alias)
    {
        return Alias::get($alias);
    }

    public static function setPathOfAlias($name, $path)
    {
        Alias::set($name, $path);
    }

    /**
     * Creates an object and initializes it based on the given configuration.
     *
     * The specified configuration can be either a string or an array.
     * If the former, the string is treated as the object type which can
     * be either the class name or {@link YiiBase::getPathOfAlias class path alias}.
     * If the latter, the 'class' element is treated as the object type,
     * and the rest of the name-value pairs in the array are used to initialize
     * the corresponding object properties.
     *
     * Any additional parameters passed to this method will be
     * passed to the constructor of the object being created.
     *
     * @param mixed $config the configuration. It can be either a string or an array.
     * @return mixed the created object
     * @throws Exception if the configuration does not have a 'class' element.
     */
    public static function createComponent($config)
    {
        if (is_string($config)) {
            $type = $config;
            $config = array();
        } elseif (isset($config['class'])) {
            $type = $config['class'];
            unset($config['class']);
        } else
            throw new Exception(Mindy::t('yii', 'Object configuration must be an array containing a "class" element.'));

        if (($n = func_num_args()) > 1) {
            $args = func_get_args();
            if ($n === 2)
                $object = new $type($args[1]);
            elseif ($n === 3)
                $object = new $type($args[1], $args[2]);
            elseif ($n === 4)
                $object = new $type($args[1], $args[2], $args[3]);
            else {
                unset($args[0]);
                $class = new ReflectionClass($type);
                // Note: ReflectionClass::newInstanceArgs() is available for PHP 5.1.3+
                // $object=$class->newInstanceArgs($args);
                $object = call_user_func_array(array($class, 'newInstance'), $args);
            }
        } else
            $object = new $type;

        foreach ($config as $key => $value)
            $object->$key = $value;

        return $object;
    }
}
