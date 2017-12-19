<?php

namespace EnderLab\Middleware;

use EnderLab\Dispatcher\Dispatcher;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareCollection implements MiddlewareInterface
{
    private $middlewares;

    /**
     * MiddlewareCollection constructor.
     *
     * @param \SplQueue $middlewares
     */
    public function __construct(\SplQueue $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $requestHandler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        $dispatcher = new Dispatcher($this->middlewares, $requestHandler);

        return $dispatcher->handle($request);
    }
}
