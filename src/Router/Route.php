<?php

namespace EnderLab\MiddleEarth\Router;

use EnderLab\MiddleEarth\Middleware\CallableMiddlewareDecorator;
use Psr\Http\Server\MiddlewareInterface;

class Route implements RouteInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var callable|MiddlewareInterface
     */
    private $middlewares;

    /**
     * @var array
     */
    private $method;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $params = [];

    /**
     * Constructeur de la classe.
     *
     * @param string                       $path
     * @param callable|MiddlewareInterface $middlewares
     * @param array                        $method
     * @param null|string                  $name
     * @param array                        $params
     */
    public function __construct(
        string $path,
        $middlewares,
        $method = null,
        string $name = null,
        array $params = []
    ) {
        $this->path = ('/' !== trim($path)) ? rtrim($path, '/') : $path;
        $this->method = (is_string($method) ? [$method] : (null === $method ? [] : $method));
        //$this->middlewares = $middlewares;
        $this->name = $name;
        $this->params = $params;

        $this->setMiddleware($middlewares);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return callable|MiddlewareInterface
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * @return array
     */
    public function getMethod(): array
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    private function setMiddleware($middlewares)
    {
        if ($middlewares instanceof \Closure) {
            $middlewares = new CallableMiddlewareDecorator($middlewares);
        }

        if (is_array($middlewares)) {
            $temp = [];

            foreach ($middlewares as $middleware) {
                if ($middleware instanceof \Closure) {
                    $middleware = new CallableMiddlewareDecorator($middlewares);
                }

                $temp[] = $middleware;
            }

            $middlewares = $temp;
        }

        $this->middlewares = $middlewares;
    }
}
