<?php

namespace EnderLab\Application;

use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Dispatcher\DispatcherInterface;
use EnderLab\Dispatcher\DispatcherMiddleware;
use EnderLab\Error\ErrorMiddleware;
use EnderLab\Middleware\MiddlewareBuilder;
use EnderLab\Router\Route;
use EnderLab\Router\RouterInterface;
use EnderLab\Router\RouterMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class App extends MiddlewareBuilder
{
    const ENV_DEV = 'dev';
    const ENV_TEST = 'test';
    const ENV_PROD = 'prod';

    private $env = self::ENV_PROD;

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
     * @throws \BadMethodCallException
     *
     * @return Route
     */
    public function __call($name, $arguments): Route
    {
        switch ($name) {
            case 'get':
            case 'post':
            case 'put':
            case 'patch':
            case 'delete':
            case 'head':
            case 'options':
            case 'any':
                $args = [
                    $arguments[0],
                    (count($arguments) > 1 ? $arguments[1] : null),
                    ($name === 'any' ? null : mb_strtoupper($name)),
                    (count($arguments) > 2 ? $arguments[2] : null)
                ];
                break;
            default:
                throw new \BadMethodCallException('Invalid method name "' . $name . '"');
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
     *
     * @throws \InvalidArgumentException
     *
     * @return Route
     */
    public function addRoute(
        $path,
        $middlewares = null,
        string $method = null,
        string $name = null
    ): Route {
        if (!$path instanceof Route && null === $middlewares) {
            throw new \InvalidArgumentException('Invalid route config');
        }

        if ($path instanceof Route) {
            $route = $path;
        }

        if (!isset($route)) {
            $middlewares = $this->buildMiddleware($middlewares);
            $route = new Route($path, $middlewares, $method, $name);
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
    public function pipe($path, $middlewares = null, string $env = null): App
    {
        if (null !== $env && $this->env !== $env) {
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
     * @param null|ServerRequestInterface $request
     * @param bool                        $returnResponse
     *
     * @return void|\Psr\Http\Message\ResponseInterface
     */
    public function run(?ServerRequestInterface $request = null, bool $returnResponse = false)
    {
        $request = (null !== $request) ? $request : ServerRequest::fromGlobals();
        $request = $request->withAttribute('originalResponse', $this->response);
        $response = $this->dispatcher->process($request);

        if (true === $returnResponse) {
            return $response;
        }

        if (PHP_SAPI === 'cli') {
            echo (string) $response->getBody();
        } else {
            \Http\Response\send($response);
        }
    }

    public function setEnv($env)
    {
        if (!in_array($env, [self::ENV_PROD, self::ENV_DEV, self::ENV_TEST], true)) {
            throw new \InvalidArgumentException('Environment must be "' . implode(', ', [self::ENV_PROD, self::ENV_DEV, self::ENV_TEST]) . '".');
        }

        $this->env = $env;
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
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
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
     * @return App
     */
    public function enableErrorHandler(): App
    {
        $this->pipe(new ErrorMiddleware($this->response));

        return $this;
    }

    /**
     * @return App
     */
    public function enableRouterHandler(): App
    {
        $this->pipe(new RouterMiddleware($this->router, $this->response));

        return $this;
    }

    /**
     * @return App
     */
    public function enableDispatcherHandler(): App
    {
        $this->pipe(new DispatcherMiddleware($this->container, $this->router));

        return $this;
    }
}
