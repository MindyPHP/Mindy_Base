<?php

use Mindy\Base\BaseList;
use Mindy\Base\Component;
use Mindy\Base\TypedList;

class CTypedListTest extends CTestCase
{
    public function testClassType()
    {
        $list = new TypedList('\Mindy\Base\Component');
        $list[] = new Component;
        $this->setExpectedException('\Mindy\Exception\Exception');
        $list[] = new stdClass;
    }

    public function testInterfaceType()
    {
        $list = new TypedList('Traversable');
        $list[] = new BaseList;
        $this->setExpectedException('\Mindy\Exception\Exception');
        $list[] = new Component;
    }
}
