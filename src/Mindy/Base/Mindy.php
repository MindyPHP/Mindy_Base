<?php

namespace Mindy\Base;

use Mindy\Helper\Alias;
use Mindy\Helper\Console;
use Mindy\Helper\Dumper;

Alias::set('mindy', __DIR__);

function d()
{
    $debug = debug_backtrace();
    $args = func_get_args();
    $data = array(
        'data' => $args,
        'debug' => array(
            'file' => $debug[0]['file'],
            'line' => $debug[0]['line'],
        )
    );
    Dumper::dump($data);
    die();
}

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
