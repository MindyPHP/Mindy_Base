<?php
use Mindy\Base\Behavior;
use Mindy\Base\Event;
use Mindy\Exception\Exception;

/**
 * Used in CBehaviorTest
 */
class TestBehavior extends Behavior
{
    public function events()
    {
        return array(
            'onTestEvent' => 'handleTest',
        );
    }

    public function handleTest($event)
    {
        if (!($event instanceof Event)) {
            throw new Exception('event has to be instance of CEvent');
        }
        $this->owner->behaviorEventHandled++;
    }
}
