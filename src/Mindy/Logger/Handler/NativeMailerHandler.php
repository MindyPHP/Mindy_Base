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
 * @date 12/06/14.06.2014 14:58
 */

namespace Mindy\Logger\Handler;

use Mindy\Base\Mindy;
use Monolog\Handler\NativeMailerHandler as MonoNativeMailerHandler;


class NativeMailerHandler extends ProxyHandler
{
    public $subject = "Logging";

    public $maxColumnWidth = 70;

    public function getHandler()
    {
        $mail = Mindy::app()->mail;
        return new MonoNativeMailerHandler($mail->admins, $this->subject, $mail->defaultFrom, $this->getLevel(), $this->bubble, $this->maxColumnWidth);
    }
}
