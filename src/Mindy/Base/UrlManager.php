<?php

namespace Mindy\Base;

use Aura\Router\DefinitionFactory;
use Aura\Router\Map as AuraMap;
use Aura\Router\RouteFactory;
use Mindy\Router\Patterns;

class UrlManager extends AuraMap
{
    public $urlsAlias = 'application.config.urls';

    public $trailingSlash = true;

    public function __construct()
    {
        $patterns = new Patterns($this->urlsAlias);
        $patterns->setTrailingSlash($this->trailingSlash);

        parent::__construct(new DefinitionFactory, new RouteFactory, $patterns->getRoutes());
        $this->init();
    }

    public function init()
    {
    }

    /**
     *
     * Gets a route that matches a given path and other server conditions.
     *
     * @param string $path The path to match against.
     *
     * @param array $server An array copy of $_SERVER.
     *
     * @return Route|false Returns a Route object when it finds a match, or
     * boolean false if there is no match.
     *
     */
    public function match($path, array $server)
    {
        $path = strtok($path, '?');

        // reset the log
        $this->log = [];

        // look through existing route objects
        foreach ($this->routes as $route) {
            $this->logRoute($route);
            if ($route->isMatch($path, $server)) {
                return $route;
            } else {
                if($this->trailingSlash) {
                    if(substr($path, -1) === '/' && $route->isMatch(rtrim($path, '/'), $server)) {
                        $this->trailingSlashCallback(rtrim($path, '/'));
                    } else if($route->isMatch($path . '/', $server)) {
                        $this->trailingSlashCallback($path . '/');
                    }
                }
            }
        }

        // convert remaining definitions as needed
        while ($this->attach_routes || $this->definitions) {
            $route = $this->createNextRoute();
            $this->logRoute($route);
            if ($route->isMatch($path, $server)) {
                return $route;
            }
        }

        // no joy
        return false;
    }

    public function trailingSlashCallback($path)
    {
        return Yii::app()->request->redirect($path);
    }

    public function createUrl($name, $data = null)
    {
        if (is_array($name)) {
            $data = $name;
            $name = $name[0];
            unset($data[0]);
        }
        return $this->generate($name, $data);
    }

    public function parseUrl($request)
    {
        $uri = $request->getRequestUri();
        if ($route = $this->match($uri, $_SERVER)) {
            foreach ($route->values as $key => $value) {
                if (in_array($key, ['controller', 'action'])) {
                    continue;
                }
                if ($route->wildcard == $key) {
                    $value = implode('/', $value);
                }
                $_GET[$key] = $value;
            }
        }
        return $route;
    }
}
