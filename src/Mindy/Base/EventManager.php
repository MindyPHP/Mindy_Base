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
 * @date 28/07/14.07.2014 13:40
 */

namespace Mindy\Base;


use Aura\Signal\HandlerFactory;
use Aura\Signal\Manager as AuraSignalManager;
use Aura\Signal\ResultCollection;
use Aura\Signal\ResultFactory;


class EventManager extends AuraSignalManager
{
    public function __construct()
    {
        $tmp = func_get_args();
        $args = isset($tmp[0]) ? $tmp[0] : null;
        $events = isset($args['events']) ? $args['events'] : null;
        if (is_string($events)) {
            $handlers = require_once($events);
        } else {
            $handlers = [];
        }
        parent::__construct(new HandlerFactory, new ResultFactory, new ResultCollection, $handlers);
    }
}

