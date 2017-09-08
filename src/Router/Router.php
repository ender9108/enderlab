<?php

namespace EnderLab\Router;

use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route as ZendRoute;

class Router implements RouterInterface
{
    const HTTP_GET      = RequestMethodInterface::METHOD_GET;
    const HTTP_POST     = RequestMethodInterface::METHOD_POST;
    const HTTP_PUT      = RequestMethodInterface::METHOD_PUT;
    const HTTP_DELETE   = RequestMethodInterface::METHOD_DELETE;
    const HTTP_HEAD     = RequestMethodInterface::METHOD_HEAD;
    const HTTP_OPTION   = RequestMethodInterface::METHOD_OPTIONS;
    const HTTP_PATCH    = RequestMethodInterface::METHOD_PATCH;
    const HTTP_TRACE    = RequestMethodInterface::METHOD_TRACE;
    const HTTP_ANY      = ZendRoute::HTTP_METHOD_ANY;

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
     * @return Router
     * @throws RouterException
     */
    public function addRoutes(array $routes = []): Router
    {
        foreach ($routes as $key => $route) {
            if (in_array($key, $this->getAllowedMethods(), true) || $key === ZendRoute::HTTP_METHOD_ANY) {
                foreach ($route as $routeDetails) {
                    $this->addRoute(
                        new Route(
                            $routeDetails[0],
                            $routeDetails[1],
                            $key,
                            (isset($routeDetails[2]) ? $routeDetails[2] : null)
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
                                (isset($routeDetails[2]) ? $routeDetails[2] : null)
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
        foreach( $route->getMethod() as $method ) {
            if(
                !in_array($method, $this->getAllowedMethods()) &&
                $method !== ZendRoute::HTTP_METHOD_ANY
            ) {
                throw new RouterException('Invalid method "'.$method.'"');
            }
        }

        $this->routes[] = $route;
        $this->router->addRoute(
            new ZendRoute(
                $route->getPath(),
                $route->getMiddlewares(),
                ( count($route->getMethod()) == 0 ? ZendRoute::HTTP_METHOD_ANY : $route->getMethod() ),
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

        if( $result->isSuccess() ) {
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
     * @param array $params
     * @param array $options
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
