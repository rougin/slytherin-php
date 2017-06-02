<?php

namespace Rougin\Slytherin\Routing;

use Phroute\Phroute\HandlerResolverInterface;

/**
 * Phroute Dispatcher
 *
 * A simple implementation of dispatcher that is built on top of Phroute.
 *
 * https://github.com/mrjgreen/phroute
 *
 * @package Slytherin
 * @author  Rougin Royce Gutib <rougingutib@gmail.com>
 */
class PhrouteDispatcher extends \Phroute\Phroute\Dispatcher implements DispatcherInterface
{
    /**
     * @var \Phroute\Phroute\Dispatcher
     */
    protected $dispatcher;

    /**
     * @var \Phroute\Phroute\HandlerResolverInterface|null
     */
    protected $resolver;

    /**
     * @var \Rougin\Slytherin\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param \Rougin\Slytherin\Routing\RouterInterface|null $router
     * @param \Phroute\Phroute\HandlerResolverInterface|null $resolver
     */
    public function __construct(RouterInterface $router = null, HandlerResolverInterface $resolver = null)
    {
        $resolver === null || $this->resolver = $resolver;

        $router === null || $this->router($router);
    }

    /**
     * Dispatches against the provided HTTP method verb and URI.
     *
     * @param  string $httpMethod
     * @param  string $uri
     * @return array|mixed
     */
    public function dispatch($httpMethod, $uri)
    {
        $result = array();

        try {
            $response = $this->resolve($httpMethod, $uri);

            $route = $this->router->retrieve($httpMethod, $uri);

            $middlewares = ($response && isset($info[3])) ? $info[3] : array();

            $result = array($response, $middlewares);
        } catch (\Exception $e) {
            $this->exceptions($exception, $uri);
        }

        return $result;
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

        $routes = $router instanceof PhrouteRouter ? $router->routes(true) : $this->collect();

        parent::__construct($routes, $this->resolver);

        return $this;
    }

    /**
     * Collects the specified routes and generates a data for it.
     *
     * @return \Phroute\Phroute\RouteDataArray
     */
    protected function collect()
    {
        $collector = new \Phroute\Phroute\RouteCollector;

        foreach ($this->router->routes() as $route) {
            $collector->addRoute($route[0], $route[1], $route[2]);
        }

        return $collector->getData();
    }

    /**
     * Returns exceptions based on catched error.
     *
     * @throws \UnexpectedValueException
     *
     * @param \Exception $exception
     * @param string     $uri
     */
    protected function exceptions(\Exception $exception, $uri)
    {
        $interface = 'Phroute\Phroute\Exception\HttpRouteNotFoundException';

        $message = $exception->getMessage();

        if (is_a($exception, $interface)) {
            $message = 'Route "' . $uri . '" not found';
        }

        throw new \UnexpectedValueException($message);
    }

    /**
     * Runs the \Phroute\Phroute\Dispatcher::dispatch method.
     *
     * @param  string $httpMethod
     * @param  string $uri
     * @return mixed|null
     */
    protected function resolve($httpMethod, $uri)
    {
        list($handler, $filters, $parameters) = $this->dispatchRoute($httpMethod, trim($uri, '/'));

        list($before, $after) = $this->parseFilters($filters);

        if (($response = $this->dispatchFilters($before)) === null) {
            $resolved = $this->handlerResolver->resolve($handler);

            if (is_array($callback) && ! is_object($callback)) {
                $response = call_user_func_array($resolved, $parameters);
            } else {
                $response = call_user_func($resolved, $parameters);
            }

            $response = $this->dispatchFilters($after, $response);
        }

        return $response;
    }
}
