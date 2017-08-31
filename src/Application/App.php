<?php

namespace EnderLab\Application;

use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Dispatcher\DispatcherMiddleware;
use EnderLab\Event\Emitter;
use EnderLab\Middleware\MiddlewareBuilder;
use EnderLab\Router\Route;
use EnderLab\Router\Router;
use EnderLab\Router\RouterMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class App extends MiddlewareBuilder
{
    const ENV_DEV = 'dev';
    const ENV_TEST = 'test';
    const ENV_PROD = 'prod';

    /**
     * App constructor.
     *
     * @param ContainerInterface $container
     * @param Router             $router
     * @param Dispatcher         $dispatcher
     * @param Emitter|null       $emitter
     */
    public function __construct(
        ContainerInterface $container,
        Router $router,
        Dispatcher $dispatcher,
        ?Emitter $emitter
    ) {
        parent::__construct($container, $router, $dispatcher, $emitter);
    }

    /**
     * Add route on router.
     *
     * @param string      $path
     * @param null        $middlewares
     * @param string|null $method
     * @param string|null $name
     * @param array       $params
     *
     * @throws \InvalidArgumentException
     *
     * @return Route
     */
    public function addRoute(
        string $path,
        $middlewares = null,
        string $method = null,
        string $name = null,
        array $params = []
    ): Route {
        if (!$path instanceof Route && null === $middlewares) {
            throw new \InvalidArgumentException('Invalid route config');
        }

        if ($path instanceof Route) {
            $route = $path;
        }

        if (false === isset($route)) {
            $middlewares = $this->buildMiddleware($middlewares);
            $route = new Route($path, $middlewares, $method, $name, $params);
        }

        $this->router->addRoute($route);

        return $route;
    }

    /**
     * Add middleware on pipe.
     *
     * @param $path
     * @param null        $middlewares
     * @param string|null $env
     *
     * @return App
     */
    public function pipe($path, $middlewares = null, string $env = null): App
    {
        if (null !== $env && $this->container->get('global.env') !== $env) {
            return $this;
        }

        if (null === $middlewares) {
            $middlewares = $this->buildMiddleware($path);
            $path = '*';
        }

        if (!$middlewares instanceof MiddlewareInterface) {
            $middlewares = $this->buildMiddleware($middlewares);
        }

        $this->dispatcher->pipe(new Route($path, $middlewares));

        return $this;
    }

    /**
     * Start process dispatcher.
     *
     * @return ResponseInterface
     */
    public function run(): ResponseInterface
    {
        $request = ServerRequest::fromGlobals();
        $response = new Response();
        $request = $request->withAttribute('originalResponse', $response);

        $this->pipe(new RouterMiddleware($this->router, $response));
        $this->pipe(new DispatcherMiddleware($this->container, $this->router, $this->emitter));

        $response = $this->dispatcher->process($request);

        return $response;
    }

    /**
     * Return Emitter object.
     *
     * @return Emitter|null
     */
    public function getEmitter(): ?Emitter
    {
        return $this->emitter;
    }

    /**
     * Return Container object.
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Return Router object.
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Return Dispatcher object.
     *
     * @return Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }
}
