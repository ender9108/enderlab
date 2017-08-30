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
     * @param string      $path
     * @param null        $middlewares
     * @param string|null $method
     * @param string|null $name
     * @param array       $params
     *
     * @throws \Exception
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
            throw new \Exception('');
        }

        if ($path instanceof Route) {
            $route = $path;
        }

        //TODO check duplicate route

        if (false === isset($route)) {
            $middlewares = $this->buildMiddleware($middlewares);
            $route = new Route($path, $middlewares, $method, $name, $params);
        }

        $this->router->addRoute($route);

        return $route;
    }

    /**
     * @param $path
     * @param null        $middlewares
     * @param string|null $env
     *
     * @throws \Exception
     *
     * @return App
     */
    public function pipe($path, $middlewares = null, string $env = null): App
    {
        if (null === $middlewares) {
            $middlewares = $this->buildMiddleware($path);
            $path = '*';
        }

        if (!$middlewares instanceof MiddlewareInterface) {
            $middlewares = $this->buildMiddleware($middlewares);
        }

        if (!$middlewares instanceof MiddlewareInterface) {
            throw new \Exception('Invalid middleware');
        }

        $this->dispatcher->pipe(new Route($path, $middlewares));

        return $this;
    }

    /**
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
     * @return Emitter|null
     */
    public function getEmitter(): ?Emitter
    {
        return $this->emitter;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }
}
