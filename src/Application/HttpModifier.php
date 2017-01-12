<?php

namespace Rougin\Slytherin\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Rougin\Slytherin\Middleware\MiddlewareInterface;

/**
 * HTTP Modifier
 *
 * Modifies the HTTP by updating the HTTP response with middleware (if included).
 *
 * @package Slytherin
 * @author  Rougin Royce Gutib <rougingutib@gmail.com>
 */
class HttpModifier
{
    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Sets the HTTP response and return it to the user.
     *
     * @param  \Psr\Http\Message\ResponseInterface|string $result
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function setHttpResponse($result)
    {
        $response = $this->response;

        if ($result instanceof \Psr\Http\Message\ResponseInterface) {
            $response = $result;
        } else {
            $response->getBody()->write($result);
        }

        foreach ($response->getHeaders() as $name => $value) {
            header($name . ': ' . implode(',', $value));
        }

        http_response_code($response->getStatusCode());

        return $response;
    }

    /**
     * Sets the defined middlewares.
     *
     * @param  array $middlewares
     * @return self
     */
    public function setMiddlewares(array $middlewares = [])
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * Sets the defined middlewares to the HTTP response.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface         $request
     * @param  \Rougin\Slytherin\Middleware\MiddlewareInterface $middleware
     * @param  array                                            $middlewares
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function invokeMiddleware(ServerRequestInterface $request, MiddlewareInterface $middleware = null)
    {
        $result = null;

        if ($middleware && ! empty($this->middlewares)) {
            $result = $middleware($request, $this->response, $this->middlewares);
        }

        return ($result) ? $this->setHttpResponse($result) : null;
    }
}
