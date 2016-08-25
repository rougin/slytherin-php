<?php

namespace Rougin\Slytherin\Dispatching\Phroute;

use Phroute\Phroute\Dispatcher as PhrouteDispatcher;

use Rougin\Slytherin\Dispatching\RouterInterface;
use Rougin\Slytherin\Dispatching\DispatcherInterface;

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
class Dispatcher implements DispatcherInterface
{
    /**
     * @var \Phroute\Phroute\Dispatcher
     */
    protected $dispatcher;

    /**
     * @param \Rougin\Slytherin\Dispatching\RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->dispatcher = new PhrouteDispatcher($router->getRoutes());
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
        return $this->dispatcher->dispatch($httpMethod, $uri);
    }
}
