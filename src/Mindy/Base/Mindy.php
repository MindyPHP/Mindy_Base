<?php

namespace Mindy\Base;

use Mindy\Helper\Alias;

if (!function_exists('d')) {
    require_once __DIR__ . '/m.php';
}

Alias::set('mindy', __DIR__);

/**
 * Class Mindy
 * @package Mindy\Base
 */
class Mindy extends MindyBase
{
    /**
     * @return string the version of Mindy
     */
    public static function getVersion()
    {
        return '0.9';
    }
}
