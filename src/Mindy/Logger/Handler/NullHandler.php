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
 * @date 11/06/14.06.2014 19:14
 */

namespace Mindy\Logger\Handler;
use Monolog\Handler\NullHandler as MonoNullHandler;

class NullHandler extends ProxyHandler
{
    public function getHandler()
    {
        return new MonoNullHandler($this->getLevel());
    }
}
