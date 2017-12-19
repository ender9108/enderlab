<?php

namespace EnderLab\Dispatcher;

use EnderLab\Router\RouteInterface;
use GuzzleHttp\Psr7\Response;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\Server\MiddlewareInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SplQueue;

class Dispatcher implements DispatcherInterface
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

    /**
     * @param $middleware
     *
     * @return Dispatcher
     */
    public function pipe($middleware): Dispatcher
    {
        if (!$middleware instanceof MiddlewareInterface &&
            !$middleware instanceof RouteInterface
        ) {
            throw new InvalidArgumentException('Middleware must be implement "MiddlewareInterface" or "RouteInterface"');
        }

        $this->middlewares->enqueue($middleware);

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request)/*: ResponseInterface*/
    {
        if ($this->middlewares->isEmpty()) {
            if (null !== $this->delegate) {
                return $this->delegate->process($request);
            }

            return $this->response;
        }

        $middleware = $this->middlewares->dequeue();

        if ('*' !== $middleware->getPath()) {
            $uri = $request->getUri()->getPath();
            $regex = '#^' . $middleware->getPath() . '#';

            if (!preg_match($regex, $uri, $matches)) {
                return $this->process($request);
            }
        }

        ++$this->index;
        $middleware = $middleware->getMiddlewares();
        $response = $middleware->process($request, $this);

        /*if (!$response instanceof ResponseInterface) {
            throw new \RuntimeException('No valid response sending.');
        }*/

        return $response;
    }

    public function countMiddlewares()
    {
        return $this->middlewares->count();
    }

    public function getQueue()
    {
        return $this->middlewares;
    }
}
