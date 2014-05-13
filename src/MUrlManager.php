<?php

use Aura\Router\DefinitionFactory;
use Aura\Router\Map;
use Aura\Router\RouteFactory;
use Mindy\Router\Patterns;

class MUrlManager extends Map
{
    public $urlsAlias = 'application.config.urls';

    /**
     * @var array of routes for backward compatability
     */
    public $rulesCsrfExcluded = [];

    public function __construct()
    {
        $patterns = new Patterns($this->urlsAlias);

        parent::__construct(new DefinitionFactory, new RouteFactory, $patterns->getRoutes());
    }

    public function init()
    {

    }

    public function createUrl($name, $data = null)
    {
        if(is_array($name)) {
            $data = $name;
            $name = $name[0];
            unset($data[0]);
        }
        return $this->generate($name, $data);
    }

    public function parseUrl($request)
    {
        $route = $this->match($request->getRequestUri(), $_SERVER);
        if($route) {
            foreach($route->values as $key => $value) {
                if(in_array($key, ['controller', 'action'])) {
                    continue;
                }
                if($route->wildcard == $key) {
                    $value = implode('/', $value);
                }
                $_GET[$key] = $value;
            }
        }
        return $route;
    }
}
