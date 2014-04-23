<?php

use Mindy\Helper\Console;
use Mindy\Helper\Dumper;
use Mindy\Helper\Params;

class m
{
    public static function isCli()
    {
        return Console::isCli();
    }

    public static function d($var, $highlight = true, $die = true, $depth = 10)
    {
        Dumper::dump($var, $depth, $highlight);
    }

    public static function param($key)
    {
        return Params::get($key);
    }

    public static function dump($var, $depth = 10, $highlight = false, $die = true)
    {
        throw new Exception('TODO использовать Dumper из Mindy2');
    }

    public static function email($emailto, $subject, $message, $from = null)
    {
        throw new Exception("TODO обновить EmailHelper");

        $email = new EmailHelper();

        $email->to = $emailto;
        if ($from === null)
            $email->from = m::param('core.email_system');
        else
            $email->from = $from;
        $email->returnPath = m::param('core.email_system');
        $email->subject = $subject;
        $email->message = $message;

        $email->send();
    }
}
