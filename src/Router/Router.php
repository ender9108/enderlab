<?php

namespace EnderLab\Router;

use Psr\Http\Message\ServerRequestInterface;

class Router implements RouterInterface
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';
    const HTTP_HEAD = 'HEAD';
    const HTTP_OPTION = 'OPTION';
    const HTTP_ANY = 'ANY';

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var array
     */
    private $namedRoutes = [];

    /**
     * @var array
     */
    private $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTION'];

    /**
     * @var int
     */
    private $count = 0;

    /**
     * Router constructor.
     *
     * @param array $routes
     */
    public function __construct(array $routes = [])
    {
        $this->addRoutes($routes);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @param array $routes
     *
     * @return Router
     */
    public function addRoutes(array $routes = []): Router
    {
        foreach ($routes as $key => $route) {
            if (in_array($key, $this->getAllowedMethods(), true) || $key === self::HTTP_ANY) {
                foreach ($route as $routeDetails) {
                    $this->addRoute(
                        new Route(
                            $routeDetails[0],
                            $routeDetails[1],
                            $key,
                            (isset($routeDetails[2]) ? $routeDetails[2] : null),
                            (isset($routeDetails[3]) ? $routeDetails[3] : [])
                        )
                    );
                } // endforeach
            } elseif ($route instanceof Route) {
                $this->addRoute($route);
            } else {
                foreach ($route as $httpVerb => $routesList) {
                    foreach ($routesList as $routeDetails) {
                        $this->addRoute(
                            new Route(
                                '/' . trim($key, '/') . '/' . trim($routeDetails[0], '/'),
                                $routeDetails[1],
                                $httpVerb,
                                (isset($routeDetails[2]) ? $routeDetails[2] : null),
                                (isset($routeDetails[3]) ? $routeDetails[3] : [])
                            )
                        );
                    } // endforeach
                } // endforeach
            }
        } // endforeach

        return $this;
    }

    /**
     * @param Route $route
     *
     * @throws RouterException
     *
     * @return Router
     */
    public function addRoute(Route $route): Router
    {
        if (count($route->getMethod()) === 0) {
            foreach ($this->allowedMethods as $allowedMethod) {
                $this->routes[$allowedMethod][] = $route;
            }
        } else {
            $found = false;

            foreach ($route->getMethod() as $method) {
                if (in_array($method, $this->allowedMethods, true)) {
                    $found = true;
                }
            }

            if (false === $found) {
                throw new RouterException('Method ' . implode(',', $route->getMethod()) . ' not allow.', 405);
            }

            foreach ($route->getMethod() as $method) {
                $this->routes[$method][] = $route;
            }
        }

        $this->namedRoutes[$route->getName()] = $route;
        ++$this->count;

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws RouterException
     *
     * @return Route|null
     */
    public function match(ServerRequestInterface $request): ?Route
    {
        if (!isset($this->routes[$request->getMethod()])) {
            throw new RouterException('Method ' . $request->getMethod() . ' not allow.', 405);
        }

        foreach ($this->routes[$request->getMethod()] as $route) {
            if ($route->match($request->getUri()->getPath())) {
                return $route;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @param array  $params
     *
     * @throws RouterException
     *
     * @return string
     */
    public function getNamedUrl(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new RouterException('No route matches this name');
        }

        return $this->namedRoutes[$name]->getUrl($params);
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
