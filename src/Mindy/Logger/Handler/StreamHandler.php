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
 * @date 11/06/14.06.2014 19:15
 */

namespace Mindy\Logger\Handler;

use Mindy\Helper\Alias;
use Monolog\Handler\StreamHandler as MonoStreamHandler;

class StreamHandler extends ProxyHandler
{
    /**
     * @var string path to file or proxy to stdout: php://stdout
     */
    public $stream;

    public $filePermission;

    public function init()
    {
        $this->stream = Alias::get('application.runtime.application') . '.log';
        parent::init();
    }

    public function getHandler()
    {
        return new MonoStreamHandler($this->stream, $this->getLevel(), $this->bubble, $this->filePermission);
    }
}

