<?php

namespace Rougin\Slytherin\Routing;

/**
 * FastRoute Dispatcher
 *
 * A simple implementation of dispatcher that is built on top of FastRoute.
 *
 * https://github.com/nikic/FastRoute
 *
 * @package Slytherin
 * @author  Rougin Royce Gutib <rougingutib@gmail.com>
 */
class FastRouteDispatcher implements DispatcherInterface
{
    /**
     * @var \FastRoute\Dispatcher
     */
    protected $dispatcher;

    /**
     * @var \Rougin\Slytherin\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param \Rougin\Slytherin\Routing\RouterInterface|null $router
     */
    public function __construct(RouterInterface $router = null)
    {
        $router == null || $this->router($router);
    }

    /**
     * Dispatches against the provided HTTP method verb and URI.
     *
     * @param  string $httpMethod
     * @param  string $uri
     * @return array
     */
    public function dispatch($httpMethod, $uri)
    {
        $result = $this->dispatcher->dispatch($httpMethod, $uri);

        $route = $this->router->retrieve($httpMethod, $uri);

        $this->throwException($result[0], $uri);

        $middlewares = ($route[2] == $route[1] && isset($route[3])) ? $route[3] : array();

        return array(array($result[1], $result[2]), $middlewares);
    }

    /**
     * Sets the router and parse its available routes if needed.
     *
     * @param  \Rougin\Slytherin\Routing\RouterInterface $router
     * @return self
     */
    public function router(RouterInterface $router)
    {
        $this->router = $router;

        $routes = $router->routes(true);

        if (! $router instanceof FastRouteRouter) {
            $routes = function (\FastRoute\RouteCollector $collector) use ($router) {
                foreach (array_filter($router->routes()) as $route) {
                    $collector->addRoute($route[0], $route[1], $route[2]);
                }
            };
        }

        $this->dispatcher = \FastRoute\simpleDispatcher($routes);

        return $this;
    }

    /**
     * Throws an exception if it matches to the following result.
     *
     * @param  integer $result
     * @param  string  $uri
     * @return void
     */
    protected function throwException($result, $uri)
    {
        if ($result == \FastRoute\Dispatcher::NOT_FOUND) {
            throw new \UnexpectedValueException('Route "' . $uri . '" not found');
        }

        if ($result == \FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            throw new \UnexpectedValueException('Used method is not allowed');
        }
    }
}