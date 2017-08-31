<?php
namespace EnderLab\Test;

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
            dirname(__FILE__).'/../../../config.config.php',
            (new ContainerBuilder())->build(),
            new Dispatcher(),
            new Router(),
            Emitter::getInstance()
        );
        $this->assertInstanceOf(App::class, $app);
    }
}