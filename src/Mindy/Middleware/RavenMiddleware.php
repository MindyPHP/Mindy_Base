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
 * @date 11/04/14.04.2014 16:49
 */
class RavenMiddleware extends Middleware
{
    public $dsn;

    private $_client;

    public function processException(Exception $exception)
    {
        if (!$this->dsn) {
            return;
        }

        if (!$this->_client) {
            // Instantiate a new client with a compatible DSN
            $this->_client = new Raven_Client($this->dsn);
        }

        // Capture exception
        $this->_client->captureException($exception, [
            'extra' => [
                'php_version' => phpversion(),
                'mindy_version' => Mindy::getVersion()
            ],
        ]);

        // Install error handlers and shutdown function to catch fatal errors
        $error_handler = new Raven_ErrorHandler($this->_client);
        $error_handler->registerExceptionHandler();
        $error_handler->registerErrorHandler();
        $error_handler->registerShutdownFunction();
    }
}
