<?php

namespace Mindy\Base;

require_once('m.php');

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
