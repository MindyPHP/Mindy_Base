<?php

namespace Mindy\Middleware;
use Exception;
use Mindy\Base\ApplicationComponent;
use Mindy\Helper\Creator;
use Mindy\Http\Request;

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
 * @date 11/04/14.04.2014 16:47
 */
class MiddlewareManager extends ApplicationComponent implements IMiddleware
{
    /**
     * @var Middleware[]
     */
    public $middleware = [];

    /**
     * @var Middleware[]
     */
    private $_middleware = [];

    public function init()
    {
        foreach ($this->middleware as $middleware) {
            $this->_middleware[] = Creator::createObject($middleware);
        }
    }

    public function processView($output)
    {
        foreach ($this->_middleware as $middleware) {
            $output = $middleware->processView($output);
        }
        return $output;
    }

    public function processRequest(Request $request)
    {
        foreach ($this->_middleware as $middleware) {
            $middleware->processRequest($request);
        }
    }

    /**
     * @param Exception $exception
     * @void
     */
    public function processException(Exception $exception)
    {
        foreach ($this->_middleware as $middleware) {
            $middleware->processException($exception);
        }
    }

    public function processResponse()
    {
        foreach ($this->_middleware as $middleware) {
            $middleware->processResponse();
        }
    }
}
