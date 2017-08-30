<?php

namespace EnderLab\Dispatcher;

use GuzzleHttp\Psr7\Response;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use SplQueue;

class Dispatcher implements DelegateInterface
{
    private $middlewares;
    private $index = 0;
    private $response;
    private $delegate;

    /**
     * Dispatcher constructor.
     *
     * @param SplQueue|null          $middlewares
     * @param DelegateInterface|null $delegate
     */
    public function __construct(SplQueue $middlewares = null, DelegateInterface $delegate = null)
    {
        $this->middlewares = $middlewares ?: new SplQueue();
        $this->delegate = $delegate;
        $this->response = new Response();
    }

    public function pipe($middleware): Dispatcher
    {
        $this->middlewares->enqueue($middleware);

        return $this;
    }

    public function process(ServerRequestInterface $request)
    {
        if ($this->middlewares->isEmpty()) {
            if (false === (null === $this->delegate)) {
                return $this->delegate->process($request);
            }

            return $this->response;
        }

        $middleware = $this->middlewares->dequeue();
        ++$this->index;
        $middleware = $middleware->getMiddlewares();

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }
    }
}
