<?php

namespace EnderLab\Application;

use DI\ContainerBuilder;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Dispatcher\DispatcherInterface;
use EnderLab\Router\Router;
use EnderLab\Router\RouterInterface;
use FilesystemIterator;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

final class AppFactory
{
    /**
     * Build App object and load config.
     *
     * @param string|ContainerInterface|null $containerConfig
     * @param RouterInterface|null           $router
     * @param DispatcherInterface|null       $dispatcher
     *
     * @return App
     */
    public static function create(
        $containerConfig = null,
        ?RouterInterface $router = null,
        ?DispatcherInterface $dispatcher = null
    ): App {
        $container = self::buildContainer($containerConfig);
        $dispatcher = $dispatcher ?: new Dispatcher();
        $router = $router ?: new Router();

        if ($container->has('app.routes') && is_array($container->get('app.routes'))) {
            $router->addRoutes($container->get('app.routes'));
        }

        $container->set(Dispatcher::class, $dispatcher);
        $container->set(Router::class, $router);

        $app = new App($container, $router, $dispatcher);

        if ($container->has('app.env') && is_array($container->get('app.env'))) {
            $app->setEnv($container->get('app.env'));
        }

        if ($container->has('app.enableErrorHandler') && is_array($container->get('app.enableErrorHandler'))) {
            $app->enableErrorHandler();
        }

        return $app;
    }

    /**
     * @param null $containerConfig
     *
     * @return ContainerInterface
     */
    private static function buildContainer($containerConfig = null): ContainerInterface
    {
        if (is_string($containerConfig)) {
            $containerBuilder = new ContainerBuilder();

            if (is_dir($containerConfig)) {
                $iterator = new FilesystemIterator($containerConfig, FilesystemIterator::SKIP_DOTS);

                foreach ($iterator as $file) {
                    $containerBuilder->addDefinitions($file->getPathname());
                }
            } elseif (file_exists($containerConfig)) {
                $containerBuilder->addDefinitions($containerConfig);
            } else {
                throw new InvalidArgumentException('Config file "' . $containerConfig . '" doesn\'t exists.');
            }

            $container = $containerBuilder->build();
        } elseif (is_array($containerConfig)) {
            $containerBuilder = new ContainerBuilder();
            $containerBuilder->addDefinitions($containerConfig);
            $container = $containerBuilder->build();
        } elseif (null === $containerConfig || !$containerConfig instanceof ContainerInterface) {
            $containerBuilder = new ContainerBuilder();
            $container = $containerBuilder->build();
        } else {
            $container = $containerConfig;
        }

        return $container;
    }
}
