<?php
namespace EnderLab\Middleware;

use EnderLab\Dispatcher\Dispatcher;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareCollection implements MiddlewareInterface
{
    private $middlewares;

    /**
     * MiddlewareCollection constructor.
     * @param \SplQueue $middlewares
     */
    public function __construct(\SplQueue $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $dispatcher = new Dispatcher($this->middlewares, $delegate);
        return $dispatcher->process($request);
    }
}