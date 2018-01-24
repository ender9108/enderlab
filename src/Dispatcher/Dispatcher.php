<?php

namespace EnderLab\MiddleEarth\Dispatcher;

use EnderLab\MiddleEarth\Router\RouteInterface;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;

class Dispatcher implements DispatcherInterface
{
    /**
     * @var SplQueue
     */
    private $middlewares;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var RequestHandlerInterface|null
     */
    private $requestHandler;

    /**
     * Dispatcher constructor.
     *
     * @param SplQueue|null                $middlewares
     * @param RequestHandlerInterface|null $requestHandler
     */
    public function __construct(SplQueue $middlewares = null, RequestHandlerInterface $requestHandler = null)
    {
        $this->middlewares = $middlewares ?: new SplQueue();
        $this->requestHandler = $requestHandler;
        $this->response = new Response();
    }

    /**
     * @param $middleware
     *
     * @return Dispatcher
     */
    public function pipe($middleware): self
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->middlewares->isEmpty()) {
            if (null !== $this->requestHandler) {
                return $this->requestHandler->handle($request);
            }

            return $this->response;
        }

        $middleware = $this->middlewares->dequeue();

        if ('*' !== $middleware->getPath()) {
            $uri = $request->getUri()->getPath();
            $regex = '#^' . $middleware->getPath() . '#';

            if (!preg_match($regex, $uri, $matches)) {
                return $this->handle($request);
            }
        }

        $middleware = $middleware->getMiddlewares();
        $response = $middleware->process($request, $this);

        if (!$response instanceof ResponseInterface) {
            throw new \RuntimeException('No valid response sending.');
        }

        return $response;
    }

    /**
     * @return int
     */
    public function countMiddlewares()
    {
        return $this->middlewares->count();
    }

    /**
     * @return SplQueue
     */
    public function getQueue()
    {
        return $this->middlewares;
    }
}
