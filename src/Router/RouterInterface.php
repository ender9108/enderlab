<?php

namespace EnderLab\Router;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    /**
     * Add route on router.
     *
     * @param Route $route
     *
     * @return Router
     */
    public function addRoute(Route $route): Router;

    /**
     * Add routes collection on router.
     *
     * @param array $routes
     *
     * @return Router
     */
    public function addRoutes(array $routes = []): Router;

    /**
     * Compare uri with route collection.
     *
     * @param ServerRequestInterface $request
     *
     * @return mixed
     */
    public function match(ServerRequestInterface $request);

    /**
     * Return formatted url by route name.
     *
     * @param string $name
     * @param array  $params
     *
     * @return string
     */
    public function generateUri(string $name, array $params = []): string;

    /**
     * Return allowed http methods.
     *
     * @return array
     */
    public function getAllowedMethods(): array;

    /**
     * @return array
     */
    public function getRoutes(): array;
}
