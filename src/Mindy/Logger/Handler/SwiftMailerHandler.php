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
 * @date 12/06/14.06.2014 14:52
 */

namespace Mindy\Logger\Handler;


use Mindy\Base\Mindy;
use Monolog\Handler\SwiftMailerHandler as MonoSwiftMailerHandler;

class SwiftMailerHandler extends ProxyHandler
{
    public function getHandler()
    {
        $mail = Mindy::app()->mail;
        $mailer = $mail->getSwiftMailer();
        $message = $mail->compose();
        return new MonoSwiftMailerHandler($mailer, $message, $this->getLevel(), $this->bubble);
    }
}
