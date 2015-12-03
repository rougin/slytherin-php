<?php

namespace Rougin\Slytherin\Dispatching;

use Exception;
use Rougin\Slytherin\Dispatching\RouterInterface;
use Rougin\Slytherin\Dispatching\DispatcherInterface;

/**
 * Dispatcher
 *
 * A simple implementation of a route dispatcher that is based on
 * Rougin\Slytherin\Dispatching\DispatcherInterface.
 * 
 * @package Slytherin
 * @author  Rougin Royce Gutib <rougingutib@gmail.com>
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Dispatches against the provided HTTP method verb and URI.
     * 
     * @param  string $httpMethod
     * @param  string $uri
     * @return array|string
     */
    public function dispatch($httpMethod, $uri)
    {
        $method = '';
        $className = '';
        $parameters = [];

        foreach ($this->router->getRoutes() as $route) {
            $hasMatch = preg_match($route[1], $uri, $parameters);

            if ( ! $hasMatch) {
                continue;
            }

            array_shift($parameters);

            $parameters = array_values($parameters);

            if (is_object($route[2])) {
                return [$route[2], $parameters];
            }

            list($className, $method) = $route[2];

            break;
        }

        if (! $className || ! $method) {
            throw new Exception('Route "'.$uri.'" not found', 1);
        }

        return [[$className, $method], $parameters];
    }
}