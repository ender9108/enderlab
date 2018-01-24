<?php

namespace EnderLab\MiddleEarth\Middleware;

use EnderLab\MiddleEarth\Dispatcher\Dispatcher;
use EnderLab\MiddleEarth\Loader\LazyLoading;
use EnderLab\MiddleEarth\Router\Route;
use EnderLab\MiddleEarth\Router\RouterInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;

class MiddlewareBuilder
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var null|ResponseInterface
     */
    protected $response;

    /**
     * App constructor.
     *
     * @param ContainerInterface     $container
     * @param RouterInterface|null   $router
     * @param Dispatcher             $dispatcher
     * @param ResponseInterface|null $response
     */
    public function __construct(
        ContainerInterface $container,
        ?RouterInterface $router = null,
        Dispatcher $dispatcher,
        ?ResponseInterface $response = null
    ) {
        $this->container = $container;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
        $this->response = $response;
    }

    /**
     * @param array|callable|MiddlewareInterface|string $middlewares
     *
     * @return MiddlewareInterface
     */
    public function buildMiddleware($middlewares): MiddlewareInterface
    {
        if ($middlewares instanceof MiddlewareInterface) {
            return $middlewares;
        }

        if (is_callable($middlewares) && true === $this->isAdmissibleMiddlewares($middlewares)) {
            return new CallableMiddlewareDecorator($middlewares);
        }

        if (is_string($middlewares)) {
            if (!class_exists($middlewares)) {
                throw new InvalidArgumentException(
                    sprintf('Unable to create middleware "%s"; not a valid class or service name', $middlewares)
                );
            }

            $loader = new LazyLoading($this->container);
            $instance = $loader->load($middlewares);

            if ($instance instanceof MiddlewareInterface) {
                return $instance;
            }

            if (true === $this->isAdmissibleMiddlewares($instance)) {
                return new CallableMiddlewareDecorator($instance);
            }
        }

        if (is_array($middlewares)) {
            $queue = new \SplQueue();
            $middlewares = array_reverse($middlewares);

            foreach ($middlewares as $middleware) {
                $queue->enqueue(new Route(
                    '*',
                    $this->buildMiddleware($middleware)
                ));
            }

            return new MiddlewareCollection($queue);
        }

        throw new InvalidArgumentException('Argument middleware isn\'t callable.');
    }

    /**
     * @param $middleware
     *
     * @return bool
     */
    public function isAdmissibleMiddlewares($middleware)
    {
        $reflection = null;

        if ($middleware instanceof MiddlewareInterface) {
            return true;
        }

        if (is_array($middleware)) {
            $class = array_shift($middleware);
            $method = array_shift($middleware);
            $reflection = new \ReflectionMethod($class, $method);
        } elseif (is_string($middleware)) {
            if (!class_exists($middleware)) {
                return false;
            }

            $reflection = new ReflectionClass($middleware);

            if ($reflection->implementsInterface('Interop\\Http\\Server\\MiddlewareInterface')) {
                return true;
            }

            try {
                return  $reflection->getMethod('process') || $reflection->getMethod('__invoke');
            } catch (\Exception $exception) {
                return false;
            }
        } elseif ($middleware instanceof \Closure || false === is_object($middleware)) {
            $reflection = new \ReflectionFunction($middleware);
        } else {
            $reflection = new \ReflectionMethod($middleware, '__invoke');
        }

        return 2 === $reflection->getNumberOfParameters();
    }
}
