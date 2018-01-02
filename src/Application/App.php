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
use Interop\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class App.
 *
 * @todo remove container
 */
class App extends MiddlewareBuilder
{
    const ENV_DEV = 'dev';
    const ENV_TEST = 'test';
    const ENV_PROD = 'prod';

    /**
     * @var string
     */
    private $env = self::ENV_PROD;

    /**
     * @var string
     */
    private $groupPath = '';

    /**
     * @var array
     */
    private $groupMiddleware = [];

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
                    ('any' === $name ? null : mb_strtoupper($name)),
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
            $path = $this->groupPath . $path;

            if (count($this->groupMiddleware) > 0) {
                $temp = $middlewares;
                $middlewares = $this->groupMiddleware;

                array_push($middlewares, $temp);
            }

            $middlewares = $this->buildMiddleware($middlewares);
            $route = new Route($path, $middlewares, $method, $name);
        }

        $this->router->addRoute($route);

        return $route;
    }

    /**
     * @param string   $path
     * @param callable $callable
     * @param null     $middleware
     *
     * @return App
     */
    public function addGroup(string $path, callable $callable, $middleware = null): self
    {
        $reflection = new \ReflectionFunction($callable);
        $params = $reflection->getParameters();

        if (1 !== count($params)) {
            throw new \InvalidArgumentException('Invalid number argument');
        }

        $arg = $params[0];

        if (!$arg->getClass()->isInstance($this)) {
            throw new \InvalidArgumentException('Callback argument must be implement ' . get_class($this));
        }

        $previousGroupPath = $this->groupPath;
        $this->groupPath = $previousGroupPath . $path;

        if (null !== $middleware) {
            $previousGroupMiddleware = $this->groupMiddleware;
            $this->groupMiddleware[] = $middleware;
        }

        $callable($this);

        $this->groupPath = $previousGroupPath;

        if (null !== $middleware) {
            $this->groupMiddleware = $previousGroupMiddleware;
        }

        return $this;
    }

    /**
     * Add middleware on pipe.
     *
     * @param $path
     * @param null        $middlewares
     * @param string|null $env
     *
     * @return App
     *
     * @internal param bool $first
     */
    public function pipe($path, $middlewares = null, string $env = null): self
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
        $response = $this->dispatcher->handle($request);

        if (true === $returnResponse) {
            return $response;
        }

        if (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') {
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
     * Enable error handler.
     *
     * @return App
     */
    public function enableErrorHandler(): self
    {
        $this->pipe(new ErrorMiddleware($this->response));

        return $this;
    }

    /**
     * @return App
     */
    public function enableRouterHandler(): self
    {
        $this->pipe(new RouterMiddleware($this->router, $this->response));

        return $this;
    }

    /**
     * @return App
     */
    public function enableDispatcherHandler(): self
    {
        $this->pipe(new DispatcherMiddleware($this->container, $this->router));

        return $this;
    }
}
