<?php

namespace Mindy\Base\Compatability;

use Mindy\Base\Exception\Exception;
use Mindy\Base\Mindy;
use Mindy\Helper\Alias;
use Mindy\Helper\Creator;
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
}
