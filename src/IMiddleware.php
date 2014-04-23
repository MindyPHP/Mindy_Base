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
 * @date 13/04/14.04.2014 17:12
 */
interface IMiddleware
{
    public function processRequest();

    /**
     * Event owner RenderTrait
     * @param $output string
     * @return string
     */
    public function processView($output);

    /**
     * @param Exception $exception
     * @void
     */
    public function processException(Exception $exception);

    public function processResponse();
}
