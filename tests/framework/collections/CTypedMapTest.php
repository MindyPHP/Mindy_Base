<?php

use Mindy\Base\TypedMap;

class CTypedMapTestFoo
{
}

class CTypedMapTestBar
{
}

/**
 * CTypedMapTest
 */
class CTypedMapTest extends CTestCase
{
    public function testAddRightType()
    {
        $typedMap = new TypedMap('CTypedMapTestFoo');
        $typedMap->add(0, new CTypedMapTestFoo());
    }

    public function testAddWrongType()
    {
        $this->setExpectedException('\Mindy\Exception\Exception');

        $typedMap = new TypedMap('CTypedMapTestFoo');
        $typedMap->add(0, new CTypedMapTestBar());
    }
}
