<?php

namespace EnderLab\Application;

use DI\ContainerBuilder;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Dispatcher\DispatcherInterface;
use EnderLab\Router\Router;
use EnderLab\Router\RouterInterface;
use Psr\Container\ContainerInterface;

final class AppFactory
{
    /**
     * Build App object and load config.
     *
     * @param string|ContainerInterface|null $containerConfig
     * @param DispatcherInterface|null       $dispatcher
     * @param RouterInterface|null           $router
     *
     * @return App
     */
    public static function create(
        $containerConfig = null,
        DispatcherInterface $dispatcher = null,
        RouterInterface $router = null
    ): App {
        $containerBuilder = new ContainerBuilder();

        if (null !== $containerConfig && !$containerConfig instanceof ContainerInterface) {
            $containerBuilder->addDefinitions($containerConfig);
        }

        $container = $containerBuilder->build();
        $dispatcher = $dispatcher ?: new Dispatcher();
        $router = $router ?: new Router();

        $container->set(Dispatcher::class, $dispatcher);
        $container->set(Router::class, $router);

        if ($container->has('routes')) {
            $router->addRoutes($container->get('routes'));
        }

        return new App(
            $container,
            $router,
            $dispatcher
        );
    }

    private function __construct()
    {
    }
}
