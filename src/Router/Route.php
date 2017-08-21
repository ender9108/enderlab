<?php
namespace EnderLab\Router;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

class Route
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
     * @var null|string
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
     * Constructeur de la classe
     *
     * @param string $path
     * @param callable|MiddlewareInterface $middlewares
     * @param null|string $method
     * @param null|string $name
     */
    public function __construct(string $path, $middlewares, string $method = null, string $name = null)
    {
        $this->path = trim($path, '/');
        $this->method = $method;
        $this->middlewares = $middlewares;
        $this->name = $name ?: $this->buildDefaultRouteName();
    }

    /**
     * Permet de capturer l'url avec les paramètre
     *
     * @param string $url
     * @return bool
     */
    public function match(string $url): bool
    {
        $url = trim($url, '/');
        $path = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $this->path);
        $regex = "#^$path$#i";

        if( !preg_match($regex, $url, $matches) )
        {
            return false;
        }

        array_shift($matches);
        $this->matches = $matches;

        return true;
    }

    /**
     * Ajoute des paramètres dans l'url
     *
     * @param string $param
     * @param string $regex
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
     * @return callable|MiddlewareInterface
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * @return null|string
     */
    public function getMethod(): ?string
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
     * Retourne une url formattée
     *
     * @param array $params
     * @return string
     */
    public function getUrl(array $params): string
    {
        $path = $this->path;

        foreach( $params as $k => $v )
        {
            $path = str_replace(":$k", $v, $path);
        }

        return $path;
    }

    /**
     * Return a param regex
     *
     * @param array $match
     * @return string
     */
    private function paramMatch(array $match): string
    {
        if( isset($this->params[$match[1]]) )
        {
            return '('.$this->params[$match[1]].')';
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