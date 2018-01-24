<?php

namespace EnderLab\MiddleEarth\Dispatcher;

use EnderLab\MiddleEarth\Middleware\MiddlewareBuilder;
use EnderLab\MiddleEarth\Router\Route;
use EnderLab\MiddleEarth\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatcherMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RouterInterface|null
     */
    private $router;

    /**
     * DispatcherMiddleware constructor.
     *
     * @param ContainerInterface   $container
     * @param RouterInterface|null $router
     */
    public function __construct(
        ContainerInterface $container,
        ?RouterInterface $router = null
    ) {
        $this->container = $container;
        $this->router = $router;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $requestHandler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        $route = $request->getAttribute(Route::class, false);

        if (!$route) {
            return $requestHandler->handle($request);
        }

        $middleware = $route->getMiddlewares();
        $middlewareBuilder = new MiddlewareBuilder(
            $this->container,
            $this->router,
            $requestHandler
        );

        if (!$middleware instanceof MiddlewareInterface) {
            $middleware = $middlewareBuilder->buildMiddleware($middleware);
        }

        return $middleware->process($request, $requestHandler);
    }
}
