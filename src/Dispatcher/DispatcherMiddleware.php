<?php

namespace EnderLab\Dispatcher;

use EnderLab\Event\Emitter;
use EnderLab\Middleware\MiddlewareBuilder;
use EnderLab\Router\Route;
use EnderLab\Router\Router;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DispatcherMiddleware implements MiddlewareInterface
{
    private $container;
    private $router;
    private $emitter;

    /**
     * DispatcherMiddleware constructor.
     *
     * @param ContainerInterface $container
     * @param Router|null        $router
     * @param Emitter|null       $emitter
     */
    public function __construct(
        ContainerInterface $container,
        ?Router $router,
        ?Emitter $emitter
    ) {
        $this->container = $container;
        $this->router = $router;
        $this->emitter = $emitter;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
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
            $delegate,
            $this->emitter
        );

        if (!$middleware instanceof MiddlewareInterface) {
            $middleware = $middlewareBuilder->buildMiddleware(
                $middleware,
                $this->container,
                $this->router,
                $delegate,
                $this->emitter
            );
        }

        return $middleware->process($request, $delegate);
    }
}
