<?php

namespace Tests\EnderLab\Application;

use DI\ContainerBuilder;
use EnderLab\Application\App;
use EnderLab\Application\AppFactory;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Event\Emitter;
use EnderLab\Router\Router;
use PHPUnit\Framework\TestCase;

class AppFactoryTest extends TestCase
{
    public function testCreateAppWithoutArg()
    {
        $app = AppFactory::create();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testCreateAppWithArg()
    {
        $app = AppFactory::create(
            __DIR__ . '/../../../config.config.php',
            new Dispatcher(),
            new Router(),
            Emitter::getInstance()
        );
        $this->assertInstanceOf(App::class, $app);
    }
}
