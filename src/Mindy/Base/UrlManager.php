<?php

namespace Mindy\Base;

use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
use Mindy\Router\Dispatcher;
use Mindy\Router\Patterns;

class UrlManager extends Dispatcher
{
    use Accessors, Configurator;

    public $urlsAlias = 'App.config.urls';
    public $patterns = null;
    public $trailingSlash = true;

    public function __construct($config = [])
    {
        $this->configure($config);

        $patterns = new Patterns(empty($this->patterns) ? $this->urlsAlias : $this->patterns);
        $patterns->setTrailingSlash($this->trailingSlash);

        parent::__construct($patterns->getRouteCollector());

        $this->init();
    }

    public function init()
    {
    }

    public function addPattern($prefix, Patterns $patterns)
    {
        $patterns->setTrailingSlash($this->trailingSlash);
        $patterns->parse($this->collector, $patterns->getPatterns(), $prefix);
    }

    public function getResponse($handler)
    {
        return $handler;
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
    public function createUrl($name, $data = null)
    {
        if(is_null($data)) {
            $data = [];
        }
        return $this->reverse($name, $data);
    }

    public function reverse($name, $args = [])
    {
        if (is_array($name)) {
            $args = $name;
            $name = $name[0];
            unset($args[0]);
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
        if ($this->trailingSlash === true && substr($url, -1) !== '/') {
            $newUri = $url . '/' . str_replace($url, '', $uri);
            $route = $this->dispatch($request->getRequestType(), $newUri);
            if($route) {
                $this->trailingSlashCallback($newUri);
            }
        }

        return $route;
    }
}
