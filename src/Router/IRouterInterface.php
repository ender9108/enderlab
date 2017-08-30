<?php

namespace EnderLab\Router;

use GuzzleHttp\Psr7\ServerRequest;

interface IRouterInterface
{
    /**
     * Add route to collection.
     *
     * @param Route $route
     *
     * @return Router
     */
    public function addRoute(Route $route): Router;

    /**
     * Compare uri with route collection.
     *
     * @param ServerRequest $request
     *
     * @return mixed
     */
    public function match(ServerRequest $request);

    /**
     * Return formatted url by route name.
     *
     * @param string $name
     * @param array  $params
     *
     * @return string
     */
    public function getNamedUrl(string $name, array $params = []): string;
}
