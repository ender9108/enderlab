<?php

namespace EnderLab\MiddleEarth\Router;

interface RouteInterface
{
    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return callable|MiddlewareInterface
     */
    public function getMiddlewares();

    /**
     * @return array
     */
    public function getMethod(): array;

    /**
     * @return string
     */
    public function getName(): ?string;

    /**
     * @return array
     */
    public function getParams(): array;
}
