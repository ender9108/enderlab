<?php

namespace EnderLab\Router;

interface RouteInterface
{
    /**
     * @param string $url
     *
     * @return bool
     */
    public function match(string $url): bool;

    /**
     * @param string $param
     * @param string $regex
     *
     * @return Route
     */
    public function with(string $param, string $regex): Route;

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @return string
     */
    public function getEvaluatedPath(): string;
}
