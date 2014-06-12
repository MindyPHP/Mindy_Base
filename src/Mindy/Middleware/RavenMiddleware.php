<?php
use Mindy\Base\Mindy;

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
 * @date 11/04/14.04.2014 16:49
 */
class RavenMiddleware extends Middleware
{
    public $loggerName = 'raven';

    public function processException(Exception $exception)
    {
        Mindy::app()->logger->error($exception, $this->loggerName);
    }
}
