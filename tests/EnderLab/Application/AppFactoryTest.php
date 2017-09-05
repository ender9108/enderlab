<?php

namespace Tests\EnderLab\Application;

use DI\ContainerBuilder;
use EnderLab\Application\App;
use EnderLab\Application\AppFactory;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Router\Router;
use PHPUnit\Framework\TestCase;

class AppFactoryTest extends TestCase
{
    public function testCreateAppWithoutArg(): void
    {
        $app = AppFactory::create();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testCreateAppWithArg(): void
    {
        $app = AppFactory::create(
            [
                'global.env'     => \DI\env('global_env', 'dev'),
                'logger.name'    => 'default-logger',
                'logger.file'    => __DIR__ . '/../logs/app.log',
                'logger.handler' => [
                    \DI\object(
                        \Monolog\Handler\StreamHandler::class
                    )->constructor(\DI\get('logger.file'))
                ],
                'logger.processor' => [/*\DI\object(\Monolog\Processor\WebProcessor::class)*/],
                'logger'           => \DI\object(
                    \Monolog\Logger::class
                )->constructor(
                    \DI\get('logger.name'),
                    \DI\get('logger.handler'),
                    \DI\get('logger.processor')
                )
            ],
            new Dispatcher(),
            new Router()
        );
        $this->assertInstanceOf(App::class, $app);
    }

    public function testCreateAppWithValidContainerObject(): void
    {
        $containerBuilder = new ContainerBuilder();
        $app = AppFactory::create($containerBuilder->build());
        $this->assertInstanceOf(App::class, $app);
    }

    public function testCreateAppWithInvalidContainer(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $app = AppFactory::create('myConfigFileInvalid.php');
    }
}
