<?php

use Mindy\Base\AttributeCollection;

class CAttributeCollectionTest extends CTestCase
{
    public function testCanGetProperty()
    {
        $collection = new AttributeCollection();
        $collection->Property = 'value';
        $this->assertEquals('value', $collection->Property);
        $this->assertTrue($collection->canGetProperty('Property'));
    }

    public function testCanNotGetUndefinedProperty()
    {
        $collection = new AttributeCollection(array(), true);
        $this->assertFalse($collection->canGetProperty('Property'));
        $this->setExpectedException('\Mindy\Exception\Exception');
        $value = $collection->Property;
    }

    public function testCanSetProperty()
    {
        $collection = new AttributeCollection();
        $collection->Property = 'value';
        $this->assertEquals('value', $collection->itemAt('Property'));
        $this->assertTrue($collection->canSetProperty('Property'));
    }

    public function testCanNotSetPropertyIfReadOnly()
    {
        $collection = new AttributeCollection(array(), true);
        $this->setExpectedException('\Mindy\Exception\Exception');
        $collection->Property = 'value';
    }

    public function testGetCaseSensitive()
    {
        $collection = new AttributeCollection();
        $collection->caseSensitive = false;
        $this->assertFalse($collection->caseSensitive);
        $collection->caseSensitive = true;
        $this->assertTrue($collection->caseSensitive);
    }

    public function testSetCaseSensitive()
    {
        $collection = new AttributeCollection();
        $collection->Property = 'value';
        $collection->caseSensitive = false;
        $this->assertEquals('value', $collection->itemAt('property'));
    }

    public function testItemAt()
    {
        $collection = new AttributeCollection();
        $collection->Property = 'value';
        $this->assertEquals('value', $collection->itemAt('Property'));
    }

    public function testAdd()
    {
        $collection = new AttributeCollection();
        $collection->add('Property', 'value');
        $this->assertEquals('value', $collection->itemAt('Property'));
    }

    public function testRemove()
    {
        $collection = new AttributeCollection();
        $collection->add('Property', 'value');
        $collection->remove('Property');
        $this->assertEquals(0, count($collection));
    }

    public function testUnset()
    {
        $collection = new AttributeCollection();
        $collection->add('Property', 'value');
        unset($collection->Property);
        $this->assertEquals(0, count($collection));
    }

    public function testIsset()
    {
        $collection = new AttributeCollection();
        $this->assertFalse(isset($collection->Property));
        $collection->Property = 'value';
        $this->assertTrue(isset($collection->Property));
    }

    public function testContains()
    {
        $collection = new AttributeCollection();
        $this->assertFalse($collection->contains('Property'));
        $collection->Property = 'value';
        $this->assertTrue($collection->contains('Property'));
    }

    public function testHasProperty()
    {
        $collection = new AttributeCollection();
        $this->assertFalse($collection->hasProperty('Property'));
        $collection->Property = 'value';
        $this->assertTrue($collection->hasProperty('Property'));
    }

    public function testMergeWithCaseSensitive()
    {
        $collection = new AttributeCollection();
        $item = array('Test' => 'Uppercase');
        $collection->mergeWith($item);
        $this->assertEquals('Uppercase', $collection->itemAt('test'));
    }

    public function testMergeWithCaseInSensitive()
    {
        $collection = new AttributeCollection();
        $collection->caseSensitive = true;
        $collection->add('k1', 'item');

        $item = array('K1' => 'ITEM');
        $collection->mergeWith($item);
        $this->assertEquals('item', $collection->itemAt('k1'));
        $this->assertEquals('ITEM', $collection->itemAt('K1'));
    }
}
