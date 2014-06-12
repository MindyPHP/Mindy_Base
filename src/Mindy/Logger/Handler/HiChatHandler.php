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
 * @date 11/06/14.06.2014 19:06
 */

namespace Mindy\Logger\Handler;
use Monolog\Handler\HipChatHandler as MonoHiChatHandler;

class HiChatHandler extends ProxyHandler
{
    public $token;
    public $room;
    public $notify = false;

    public function getHandler()
    {
        return new MonoHiChatHandler($this->token, $this->room, $this->name, $this->notify, $this->getLevel(), $this->bubble);
    }
}
