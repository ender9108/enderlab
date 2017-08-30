<?php

namespace EnderLab\Application;

use DI\ContainerBuilder;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Event\Emitter;
use EnderLab\Router\Router;
use Psr\Container\ContainerInterface;

final class AppFactory
{
    /**
     * @param string|null             $configPath
     * @param ContainerInterface|null $container
     * @param Dispatcher|null         $dispatcher
     * @param Router|null             $router
     * @param Emitter|null            $emitter
     *
     * @return App
     */
    public static function create(
        string $configPath = null,
        ContainerInterface $container = null,
        Dispatcher $dispatcher = null,
        Router $router = null,
        Emitter $emitter = null
    ): App {
        $container = $container ?: (new ContainerBuilder())->build();
        $dispatcher = $dispatcher ?: new Dispatcher();
        $router = $router ?: new Router();
        $emitter = $emitter ?: Emitter::getInstance();

        return new App(
            $container,
            $router,
            $dispatcher,
            $emitter
        );
    }

    private function __construct() {}
}
