<?php

namespace Rougin\Slytherin\Routing\Vanilla;

/**
 * Dispatcher
 *
 * A simple implementation of a router that is based on
 * Rougin\Slytherin\Routing\RouterInterface.
 *
 * @package Slytherin
 * @author  Rougin Royce Gutib <rougingutib@gmail.com>
 */
class Router implements \Rougin\Slytherin\Routing\RouterInterface
{
    /**
     * @var array
     */
    protected $routes = array();

    /**
     * @var array
     */
    protected $validHttpMethods = array('DELETE', 'GET', 'PATCH', 'POST', 'PUT');

    /**
     * @param array $routes
     */
    public function __construct(array $routes = array())
    {
        foreach ($routes as $route) {
            list($httpMethod, $uri, $handler) = $route;

            $middlewares = (isset($route[3])) ? $route[3] : array();

            if (is_string($middlewares)) {
                $middlewares = array($middlewares);
            }

            $this->addRoute($httpMethod, $uri, $handler, $middlewares);
        }
    }

    /**
     * Adds a new route.
     *
     * @param  string|string[] $httpMethod
     * @param  string          $route
     * @param  mixed           $handler
     * @param  array           $middlewares
     * @return self
     */
    public function addRoute($httpMethod, $route, $handler, $middlewares = array())
    {
        $class = array($httpMethod, $route, $handler, $middlewares);

        array_push($this->routes, $class);

        return $this;
    }

    /**
     * Returns a route details based on the specified HTTP method and URI.
     *
     * @param  string $httpMethod
     * @param  string $uri
     * @return array|null
     */
    public function getRoute($httpMethod, $uri)
    {
        $result = null;

        foreach ($this->routes as $route) {
            if ($route[0] == $httpMethod && $route[1] == $uri) {
                $result = $route;

                break;
            }
        }

        return $result;
    }

    /**
     * Returns a listing of routes available.
     *
     * @return array
     */
    public function getRoutes()
    {
        $routes = array();

        foreach ($this->routes as $route) {
            preg_match_all('/:[a-z]*/', $route[1], $parameters);

            $pattern = str_replace($parameters[0], '(\w+)', $route[1]);
            $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';

            array_push($routes, array($route[0], $pattern, $route[2], $route[3]));
        }

        return $routes;
    }

    /**
     * Calls methods from this class in HTTP method format.
     *
     * @param  string $method
     * @param  mixed  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array(strtoupper($method), $this->validHttpMethods)) {
            array_unshift($parameters, strtoupper($method));

            return call_user_func_array(array($this, 'addRoute'), $parameters);
        }

        throw new \BadMethodCallException('"' . $method . '" is not a valid HTTP method.');
    }
}