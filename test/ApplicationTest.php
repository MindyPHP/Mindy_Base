<?php

namespace Mindy\Base\Tests;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $params = [
            'foo' => 'bar',
            'one' => ['two' => ['three' => 'yea']]
        ];
        $app = new TestApplication([
            'params' => $params,
        ]);

        // Unique id test
        $this->assertNotNull($app->getId());

        // Base paths test
        $this->assertEquals(realpath(__DIR__ . '/app'), $app->getBasePath());
        $this->assertEquals(realpath(__DIR__ . '/app/Modules'), $app->getModulePath());
        $this->assertEquals(realpath(__DIR__ . '/app/runtime'), $app->getRuntimePath());

        // Params test
        $this->assertEquals($params, $app->getParams());
        $this->assertEquals('bar', $app->getParam('foo'));
        $this->assertEquals('yea', $app->getParam('one.two.three'));
        $this->assertEquals(false, $app->getParam('one.two.example', false));

        // Timezones test
        $this->assertEquals(date_default_timezone_get(), $app->getTimeZone());
        $app->setTimeZone('UTC');
        $this->assertEquals('UTC', $app->getTimeZone());

        // Test Translate component
        $this->assertInstanceOf('\Mindy\Locale\Locale', $app->getTranslate());
    }
}
