<?php

namespace EnderLab\Application;

use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Dispatcher\DispatcherInterface;
use EnderLab\Dispatcher\DispatcherMiddleware;
use EnderLab\Error\ErrorMiddleware;
use EnderLab\Middleware\MiddlewareBuilder;
use EnderLab\Router\Route;
use EnderLab\Router\Router;
use EnderLab\Router\RouterInterface;
use EnderLab\Router\RouterMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class App extends MiddlewareBuilder
{
    const ENV_DEV = 'dev';
    const ENV_TEST = 'test';
    const ENV_PROD = 'prod';

    /**
     * @var MiddlewareInterface|callable
     */
    private $errorHandler;

    /**
     * App constructor.
     *
     * @param ContainerInterface  $container
     * @param RouterInterface     $router
     * @param DispatcherInterface $dispatcher
     */
    public function __construct(
        ContainerInterface $container,
        RouterInterface $router,
        DispatcherInterface $dispatcher
    ) {
        parent::__construct($container, $router, $dispatcher, new Response());
    }

    /**
     * Add route on router by request type.
     *
     * @param $name
     * @param $arguments
     *
     * @return Route
     */
    public function __call($name, $arguments): Route
    {
        $args = [];

        switch ($name) {
            case 'get':
            case 'post':
            case 'put':
            case 'delete':
            case 'head':
            case 'option':
            case 'any':
                $args = [
                    $arguments[0],
                    (count($arguments) > 1 ? $arguments[1] : null),
                    ($name === 'any' ? null : mb_strtoupper($name)),
                    (count($arguments) > 2 ? $arguments[2] : null),
                    (count($arguments) > 3 ? (!is_array($arguments[3]) ? [$arguments[3]] : $arguments[3]) : []),
                ];
                break;
            default:
                throw new \InvalidArgumentException('');
                break;
        }

        return $this->addRoute(...$args);
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
        $path,
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
     * @param bool        $first
     * @param string|null $env
     *
     * @return App
     */
    public function pipe($path, $middlewares = null, bool $first = false, string $env = null): App
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

        $this->dispatcher->pipe(new Route($path, $middlewares), $first);

        return $this;
    }

    /**
     * Start process dispatcher.
     *
     * @param null|ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function run(?ServerRequestInterface $request = null): ResponseInterface
    {
        $request = (null !== $request) ? $request : ServerRequest::fromGlobals();
        $request = $request->withAttribute('originalResponse', $this->response);

        $this->pipe(new DispatcherMiddleware($this->container, $this->router), null, true);
        $this->pipe(new RouterMiddleware($this->router, $this->response), null, true);

        if (null !== $this->errorHandler && $this->errorHandler instanceof MiddlewareInterface) {
            $this->pipe($this->errorHandler, null, true);
        }

        $response = $this->dispatcher->process($request);

        return $response;
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

    /**
     * @return MiddlewareInterface|null
     */
    public function getErrorHandler()
    {
        return $this->errorHandler;
    }

    /**
     * @param MiddlewareInterface|callable|bool $errorHandler
     *
     * @return MiddlewareInterface|null
     */
    public function enableErrorHandler($errorHandler)
    {
        if (is_bool($errorHandler)) {
            if (true === $errorHandler) {
                $errorHandler = new ErrorMiddleware($this->response);
            } else {
                $errorHandler = null;
            }
        } else {
            $errorHandler = $this->buildMiddleware($errorHandler);
        }

        $this->errorHandler = $errorHandler;
    }
}
