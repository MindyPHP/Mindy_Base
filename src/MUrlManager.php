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
        return $this->generate($name, $data);
    }

    public function parseUrl($request)
    {
        return $this->match($request->getRequestUri(), $_SERVER);
    }
}
