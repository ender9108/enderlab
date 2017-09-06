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
    private $matches = [];

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var array
     */
    private $attributes = [];

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
        $this->path = trim($path, '/');
        $this->method = (is_string($method) ? [$method] : (null === $method ? [] : $method));
        $this->middlewares = $middlewares;
        $this->name = $name ?: $this->buildDefaultRouteName();

        foreach ($params as $param => $regex) {
            $this->with($param, $regex);
        }
    }

    /**
     * Permet de capturer l'url avec les paramètre.
     *
     * @param string $url
     *
     * @return bool
     */
    public function match(string $url): bool
    {
        $url = trim($url, '/');
        $path = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $this->getPath());
        $regex = "#^$path$#i";

        if (!preg_match($regex, $url, $matches)) {
            return false;
        }

        array_shift($matches);
        $count = 0;

        foreach ($this->getParams() as $key => $param) {
            $this->attributes[$key] = $matches[$count];
            ++$count;
        }

        $this->matches = $matches;

        return true;
    }

    /**
     * Ajoute des paramètres dans l'url.
     *
     * @param string $param
     * @param string $regex
     *
     * @return Route
     */
    public function with(string $param, string $regex): Route
    {
        $this->params[$param] = str_replace('(', '(?:', $regex);

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getEvaluatedPath(): string
    {
        return preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $this->getPath());
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
    public function getName(): string
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

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Retourne une url formattée.
     *
     * @param array $params
     *
     * @return string
     */
    public function getUrl(array $params): string
    {
        $path = $this->path;

        foreach ($params as $k => $v) {
            $path = str_replace(":$k", $v, $path);
        }

        return $path;
    }

    /**
     * Return a param regex.
     *
     * @param array $match
     *
     * @return string
     */
    private function paramMatch(array $match): string
    {
        if (isset($this->params[$match[1]])) {
            return '(' . $this->params[$match[1]] . ')';
        }

        return '([^/]+)';
    }

    /**
     * @return string
     */
    private function buildDefaultRouteName(): string
    {
        return str_replace('/', '_', $this->path);
    }
}
