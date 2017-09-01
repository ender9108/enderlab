<?php

namespace EnderLab\Application;

use DI\ContainerBuilder;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Event\Emitter;
use EnderLab\Router\Router;

final class AppFactory
{
    /**
     * Build App object and load config.
     *
     * @param string|array|DefinitionSource|null     $configPath
     * @param Dispatcher|null $dispatcher
     * @param Router|null     $router
     * @param Emitter|null    $emitter
     *
     * @return App
     */
    public static function create(
        $configPath = null,
        Dispatcher $dispatcher = null,
        Router $router = null,
        Emitter $emitter = null
    ): App {
        $containerBuilder = new ContainerBuilder();

        if (null !== $configPath) {
            $containerBuilder->addDefinitions($configPath);
        }

        $container = $containerBuilder->build();
        $dispatcher = $dispatcher ?: new Dispatcher();
        $router = $router ?: new Router();
        $emitter = $emitter ?: Emitter::getInstance();

        $container->set(Dispatcher::class, $dispatcher);
        $container->set(Router::class, $router);
        $container->set(Emitter::class, $emitter);

        if ($container->has('routes')) {
            $router->addRoutes($container->get('routes'));
        }

        return new App(
            $container,
            $router,
            $dispatcher,
            $emitter
        );
    }

    private function __construct()
    {
    }
}
