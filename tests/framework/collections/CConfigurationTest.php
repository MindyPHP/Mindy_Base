<?php

use Mindy\Base\Component;
use Mindy\Base\Configuration;
use Mindy\Helper\Creator;

class MyClass extends Component
{
    public $param1;
    private $_param2;
    public $param3;
    private $_object;
    public $backquote;

    public function getParam2()
    {
        return $this->_param2;
    }

    public function setParam2($value)
    {
        $this->_param2 = $value;
    }

    public function getObject()
    {
        if ($this->_object === null) {
            $this->_object = new MyClass;
        }
        return $this->_object;
    }
}

class CConfigurationTest extends CTestCase
{
    public $configFile;

    public function setUp()
    {
        $this->configFile = dirname(__FILE__) . '/data/config.php';
    }

    public function tearDown()
    {
    }

    public function testLoadFromFile()
    {
        $config = new Configuration;
        $this->assertTrue($config->toArray() === array());
        $config->loadFromFile($this->configFile);
        $data = include($this->configFile);
        $this->assertTrue($config->toArray() === $data);
    }

    public function testSaveAsString()
    {
        $config = new Configuration($this->configFile);
        $str = $config->saveAsString();
        eval("\$data=$str;");
        $this->assertTrue($config->toArray() === $data);
    }

    public function testApplyTo()
    {
        $config = new Configuration($this->configFile);
        $object = new MyClass;
        $config->applyTo($object);
        $this->assertTrue($object->param1 === 'value1');
        $this->assertTrue($object->param2 === false);
        $this->assertTrue($object->param3 === 123);
        $this->assertTrue($object->backquote === "\\back'quote'");
        /*
        $this->assertTrue($object->object->param1===null);
        $this->assertTrue($object->object->param2==='123');
        $this->assertTrue($object->object->param3===array('param1'=>'kkk','ddd',''));
        */
    }

    public function testException()
    {
        $config = new Configuration(array('invalid' => 'value'));
        $object = new MyClass;
        $this->setExpectedException('\Mindy\Exception\Exception');
        $config->applyTo($object);
    }

    public function testCreateComponent()
    {
        $obj = Creator::createObject(array('class' => 'MyClass', 'param2' => 3));
        $this->assertInstanceOf('MyClass', $obj);
        $this->assertEquals($obj->param2, 3);
    }
}
