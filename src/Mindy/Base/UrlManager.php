<?php

namespace Mindy\Base;

use Mindy\Router\Dispatcher;
use Mindy\Router\Patterns;

class UrlManager extends Dispatcher
{
    public $urlsAlias = 'application.config.urls';
    public $trailingSlash = true;

    public function __construct()
    {
        $patterns = new Patterns($this->urlsAlias);
        $patterns->setTrailingSlash($this->trailingSlash);
        parent::__construct($patterns->getRouteCollector());
        $this->init();
    }

    public function init()
    {
    }

    public function getResponse($handler)
    {
        return $handler;
    }

    /**
     *
     * Gets a route that matches a given path and other server conditions.
     *
     * @param string $path The path to match against.
     *
     * @param array $server An array copy of $_SERVER.
     *
     * @return \Aura\Router\Route|false Returns a Route object when it finds a match, or
     * boolean false if there is no match.
     *
     */
    public function match($path, array $server)
    {
        $url = strtok($path, '?');

        // reset the log
        $this->log = [];
        // look through existing route objects
        foreach ($this->getRoutes() as $route) {
            $this->logRoute($route);
//            echo $route->name . '<br/>';

            if ($route->name == 'page.index' && $url == '/') {
                return $route;
            }

            if ($route->isMatch($url, $server)) {
                return $route;
            } else {
                if ($this->trailingSlash === true && substr($url, -1) !== '/') {
                    $newUrl = $url . '/' . str_replace($url, '', $path);
                    $route = $this->match($newUrl, $server);
                    if ($route && substr($route->path, -1) === '/') {
                        $this->trailingSlashCallback($newUrl);
                    }
                }
            }
        }

        // convert remaining definitions as needed
        while ($this->attach_routes || $this->definitions) {
            $route = $this->createNextRoute();
            $this->logRoute($route);
            if ($route->isMatch($url, $server)) {
                return $route;
            }
        }

        // no joy
        return false;
    }

    /**
     * @param $path
     * @void
     */
    public function trailingSlashCallback($path)
    {
        Mindy::app()->request->redirect($path);
    }

    /**
     * @deprecated
     * @param $name
     * @param array $data
     * @return mixed
     */
    public function createUrl($name, array $data = [])
    {
        return $this->reverse($name, $data);
    }

    public function reverse($name, $args = [])
    {
        if (is_array($name)) {
            $data = $name;
            $name = $name[0];
            unset($data[0]);
        }
        return parent::reverse($name, $args);
    }

    /**
     * @param $request \Mindy\Base\HttpRequest
     * @return false
     */
    public function parseUrl($request)
    {
        $uri = $request->getRequestUri();
        $url = strtok($uri, "?");

        $route = $this->dispatch($request->getRequestType(), $uri);
        if (!$route && $this->trailingSlash === true && substr($url, -1) !== '/') {
            $newUri = $url . '/' . str_replace($url, '', $uri);
            $route = $this->dispatch($request->getRequestType(), $newUri);
            if($route) {
                $this->trailingSlashCallback($newUri);
            }
        }

        return $route;
    }
}
