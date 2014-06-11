<?php
use Mindy\Base\Controller;
use Mindy\Base\Event;

/**
 * Used in CBehaviorTest
 */
class BehaviorTestController extends Controller
{
    public $behaviorEventHandled = 0;

    public function onTestEvent()
    {
        $this->raiseEvent("onTestEvent", new Event());
    }
}
