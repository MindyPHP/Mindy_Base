<?php

use Mindy\Base\Event;
use Mindy\Base\LogRoute;
use Mindy\Base\LogRouter;
use Mindy\Base\Mindy;

class MyRoute extends LogRoute
{
    public $logCollected = false;
    public $property = 1;

    protected function processLogs($logs)
    {
        $this->logCollected = true;
        $this->property += count($logs);
    }
}

class CLogRouterTest extends CTestCase
{
    public function testRoutes()
    {
        $app = new TestApplication;
        $router = new LogRouter;

        $this->assertEquals(count($router->routes), 0);
        $router->routes = array(
            array(
                'class' => 'MyRoute',
                'property' => 2,
            ),
            array(
                'class' => 'MyRoute',
                'property' => 3,
            ),
        );
        $router->init($app);
        $this->assertEquals(count($router->routes), 2);

        $route1 = $router->routes[0];
        $this->assertFalse($route1->logCollected);
        $this->assertEquals($route1->property, 2);
        $route2 = $router->routes[1];
        $this->assertFalse($route2->logCollected);
        $this->assertEquals($route2->property, 3);

        $logger = Mindy::getLogger();
        $logger->log('message1', 'level1', 'category1');
        $logger->log('message2', 'level2', 'category2');
        $logger->log('message3', 'level3', 'category3');

        $app->onEndRequest(new Event($this));
        $this->assertTrue($route1->logCollected);
        $this->assertTrue($route1->property > 2);
        $this->assertTrue($route2->logCollected);
        $this->assertTrue($route2->property > 3);
    }
}
