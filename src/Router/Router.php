<?php

namespace EnderLab\Router;

use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route as ZendRoute;

class Router implements RouterInterface
{
    const HTTP_GET = RequestMethodInterface::METHOD_GET;
    const HTTP_POST = RequestMethodInterface::METHOD_POST;
    const HTTP_PUT = RequestMethodInterface::METHOD_PUT;
    const HTTP_DELETE = RequestMethodInterface::METHOD_DELETE;
    const HTTP_HEAD = RequestMethodInterface::METHOD_HEAD;
    const HTTP_OPTION = RequestMethodInterface::METHOD_OPTIONS;
    const HTTP_PATCH = RequestMethodInterface::METHOD_PATCH;
    const HTTP_TRACE = RequestMethodInterface::METHOD_TRACE;
    const HTTP_ANY = ZendRoute::HTTP_METHOD_ANY;

    private $router;
    private $routes = [];
    private $count = 0;

    /**
     * Router constructor.
     *
     * @param array $routes
     * @param array $config
     */
    public function __construct(array $routes = [], array $config = [])
    {
        $this->router = new FastRouteRouter(null, null, $config);
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
     * @throws RouterException
     *
     * @return Router
     */
    public function addRoutes(array $routes = []): self
    {
        foreach ($routes as $key => $routesDetails) {
            if ($routesDetails instanceof Route) {
                $this->addRoute($routesDetails);
            } else {
                $this->addRoute(
                    new Route(
                        $routesDetails[0],
                        $routesDetails[1],
                        (isset($routesDetails[2]) ? $routesDetails[2] : null),
                        (isset($routesDetails[3]) ? $routesDetails[3] : null)
                    )
                );
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
    public function addRoute(Route $route): self
    {
        foreach ($route->getMethod() as $method) {
            if (!in_array($method, $this->getAllowedMethods(), true) &&
                ZendRoute::HTTP_METHOD_ANY !== $method
            ) {
                throw new RouterException('Invalid method "' . $method . '"');
            }
        }

        $this->routes[] = $route;
        $this->router->addRoute(
            new ZendRoute(
                $route->getPath(),
                $route->getMiddlewares(),
                (0 === count($route->getMethod()) ? ZendRoute::HTTP_METHOD_ANY : $route->getMethod()),
                $route->getName()
            )
        );
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
        $result = $this->router->match($request);

        if ($result->isSuccess()) {
            return new Route(
                $result->getMatchedRoute()->getPath(),
                $result->getMatchedMiddleware(),
                $result->getMatchedRoute()->getAllowedMethods(),
                $result->getMatchedRouteName(),
                $result->getMatchedParams()
            );
        }

        return null;
    }

    /**
     * @param string $name
     * @param array  $params
     * @param array  $options
     *
     * @return string
     */
    public function generateUri(string $name, array $params = [], array $options = []): string
    {
        return $this->router->generateUri($name, $params, $options);
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return $this->router::HTTP_METHODS_STANDARD;
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
