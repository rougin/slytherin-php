<?php

namespace Rougin\Slytherin\Routing;

use Phroute\Phroute\RouteCollector;

/**
 * Phroute Router
 *
 * A simple implementation of router that is built on top of Phroute.
 *
 * https://github.com/mrjgreen/phroute
 *
 * @package Slytherin
 * @author  Rougin Royce Gutib <rougingutib@gmail.com>
 */
class PhrouteRouter extends Router
{
    /**
     * @var \Phroute\Phroute\RouteCollector
     */
    protected $collector;

    /**
     * @var array
     */
    protected $routes = array();

    /**
     * @param array $routes
     */
    public function __construct(array $routes = array())
    {
        $this->collector = new RouteCollector;

        foreach ($routes as $route) {
            $this->collector->addRoute($route[0], $route[1], $route[2]);
        }

        $this->routes = $routes;
    }

    /**
     * Adds a new raw route.
     *
     * @param  string|string[] $httpMethod
     * @param  string          $route
     * @param  mixed           $handler
     * @param  array           $middlewares
     * @return self
     */
    public function add($httpMethod, $route, $handler, $middlewares = array())
    {
        $route = $this->parseRoute(array($httpMethod, $route, $handler, $middlewares));

        $this->collector->addRoute($httpMethod, $route[1], $handler);

        array_push($this->routes, $route);

        return $this;
    }

    /**
     * Returns a listing of available routes.
     *
     * @param  boolean $parsed
     * @return \Phroute\Phroute\RouteDataArray
     */
    public function routes($parsed = false)
    {
        $routes = $this->routes;

        return ($parsed) ? $this->collector->getData() : $routes;
    }

    /**
     * Sets the collector of routes.
     *
     * @param \Phroute\Phroute\RouteCollector $collector
     */
    public function setCollector(RouteCollector $collector)
    {
        $this->collector = $collector;

        return $this;
    }
}