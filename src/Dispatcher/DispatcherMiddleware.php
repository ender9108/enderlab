<?php

namespace EnderLab\Dispatcher;

use EnderLab\Middleware\MiddlewareBuilder;
use EnderLab\Router\Route;
use EnderLab\Router\RouterInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DispatcherMiddleware implements MiddlewareInterface
{
    private $container;
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
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $route = $request->getAttribute(Route::class, false);

        if (!$route) {
            return $delegate->process($request);
        }

        $middleware = $route->getMiddlewares();
        $middlewareBuilder = new MiddlewareBuilder(
            $this->container,
            $this->router,
            $delegate
        );

        if (!$middleware instanceof MiddlewareInterface) {
            $middleware = $middlewareBuilder->buildMiddleware($middleware);
        }

        return $middleware->process($request, $delegate);
    }
}
