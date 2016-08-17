<?php

namespace Mindy\Base\Tests;

use Mindy\Base\Mindy;
use PHPUnit_Framework_TestCase;

class ModuleTest extends PHPUnit_Framework_TestCase
{
    protected $parent;
    protected $mod;
    protected $d;

    public function setUp()
    {
        $this->parent = new NewModule('root', NULL);
        $this->mod = new NewModule('foo', $this->parent);
        $this->d = dirname(__FILE__);
    }

    public function tearDown()
    {
        unset($this->parent);
        unset($this->mod);
    }

    public function testGetId()
    {
        $this->assertEquals('foo', $this->mod->getId());
    }

    public function testSetId()
    {
        $this->mod->setId('bar');
        $this->assertEquals('bar', $this->mod->getId());
    }

    public function testGetBasePath()
    {
        $this->assertEquals($this->d, $this->mod->getBasePath());
    }

    public function testSetComponentsViaConfig()
    {
        $this->mod = new NewModule('foo', [
            'components' => [
                'bar' => [
                    'class' => NewApplicationComponent::class
                ],
            ]
        ]);
        $this->assertEquals('hello world', $this->mod->bar->getText('hello world'));
        $this->mod->setComponents(array(
            'bar' => array(
                'class' => NewApplicationComponent::class,
                'text' => 'test'
            ),
        ));
        $this->assertEquals('test', $this->mod->bar->getText());

        $this->mod->setComponent('bar', null);
        $this->assertFalse($this->mod->hasComponent('bar'));

        $this->mod->setComponents(array(
            'bar' => array(
                'class' => NewApplicationComponent::class,
                'text' => 'test'
            ),
        ));
        $this->assertEquals('test', $this->mod->bar->getText());
        $this->mod->setComponents(array(
            'bar' => array(
                'class' => NewApplicationComponent::class
            ),
        ));
        $this->assertNull($this->mod->bar->getText());
        $this->mod->setComponent('bar', null);
        $this->assertFalse($this->mod->hasComponent('bar'));

        $this->mod->setComponents(array(
            'bar' => array(
                'class' => NewApplicationComponent::class,
                'text' => 'test',
            ),
        ));
        $this->mod->setComponents(array(
            'bar' => array('class' => NewApplicationComponent::class),
        ));
        $this->assertNull($this->mod->bar->getText());
    }
}
