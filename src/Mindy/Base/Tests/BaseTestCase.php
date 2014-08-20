<?php
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
 * @date 19/08/14.08.2014 13:20
 */

namespace Mindy\Base\Tests;

use Mindy\Orm\Sync;
use Mindy\Query\ConnectionManager;
use PHPUnit_Framework_TestCase;

abstract class BaseTestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->conn = new ConnectionManager([
            'databases' => $this->getDatabases()
        ]);
        $this->initModels($this->getModels());
    }

    protected function getDatabases()
    {
        return [
            'default' => [
                'class' => '\Mindy\Query\Connection',
                'dsn' => 'sqlite::memory:',
            ]
        ];
    }

    protected function getModels()
    {
        return [];
    }

    public function tearDown()
    {
        $this->dropModels($this->getModels());
    }

    public function initModels(array $models)
    {
        $sync = new Sync($models);
        $sync->delete();
        $sync->create();
    }

    public function dropModels(array $models)
    {
        $sync = new Sync($models);
        $sync->delete();
    }

    /**
     * Тестирование контроллера. Пример использования:
     *
     * >> $module = Mindy::app()->getModule('Pages');
     * >> $c = new PageController('page', $module);
     * >> $url = '/123';
     * >> $this->assertAction($c, function($c) use ($url) {
     * >>     $c->actionView($url);
     * >> }, '<h1>123</h1>');
     *
     * @param $controller
     * @param $callback
     * @param $result
     * @param bool $trim
     */
    public function assertAction($controller, $callback, $result, $trim = true)
    {
        ob_start();
        $callback->__invoke($controller);
        $this->assertEquals($result, $trim ? trim(ob_get_clean()) : ob_get_clean());
    }
}
