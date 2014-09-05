<?php

namespace Mindy\Base;

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
     * @date 09/06/14.06.2014 17:32
     */
use Mindy\Helper\Traits\BehaviorAccessors;
use Mindy\Helper\Traits\Configurator;

/**
 * CEvent is the base class for all event classes.
 *
 * It encapsulates the parameters associated with an event.
 * The {@link sender} property describes who raises the event.
 * And the {@link handled} property indicates if the event is handled.
 * If an event handler sets {@link handled} to true, those handlers
 * that are not invoked yet will not be invoked anymore.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */
class Event
{
    use Configurator, BehaviorAccessors;

    /**
     * @var object the sender of this event
     */
    public $sender;
    /**
     * @var boolean whether the event is handled. Defaults to false.
     * When a handler sets this true, the rest of the uninvoked event handlers will not be invoked anymore.
     */
    public $handled = false;
    /**
     * @var mixed additional event parameters.
     * @since 1.1.7
     */
    public $params;

    /**
     * Constructor.
     * @param mixed $sender sender of the event
     * @param mixed $params additional parameters for the event
     */
    public function __construct($sender = null, $params = null)
    {
        $this->sender = $sender;
        $this->params = $params;
    }
}
