<?php

use Mindy\Base\Behavior;

class NewBehavior extends Behavior
{
    public function test()
    {
        $this->owner->behaviorCalled = true;
        return 2;
    }
}
