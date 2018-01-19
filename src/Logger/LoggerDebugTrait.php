<?php

namespace EnderLab\MiddleEarth\Logger;

use EnderLab\MiddleEarth\Application\App;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

trait LoggerDebugTrait
{
    public function log(ContainerInterface $container, $message, array $context = [])
    {
        if (App::ENV_DEBUG === $container->get('app.env')) {
            if (true === $container->has('logger.engine') &&
                $container->get('logger.engine') instanceof LoggerInterface
            ) {
                $container->get('logger.engine')->debug($message, $context);
            }
        }
    }
}
