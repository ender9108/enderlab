<?php

namespace EnderLab\Router;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

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
        $this->path = rtrim($path, '/');
        $this->method = (is_string($method) ? [$method] : (null === $method ? [] : $method));
        $this->middlewares = $middlewares;
        $this->name = $name;
        $this->params = $params;
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
}
