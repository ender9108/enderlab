<?php

namespace Tests\EnderLab\Application;

use DI\ContainerBuilder;
use EnderLab\Application\App;
use EnderLab\Application\AppFactory;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Router\Route;
use EnderLab\Router\Router;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
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
                'global.env'       => \DI\env('global_env', 'dev'),
                'logger.name'      => 'default-logger',
                'logger.handler'   => [\DI\object(NullHandler::class)],
                'logger.processor' => [],
                'logger'           => \DI\object(Logger::class)->constructor(
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

    public function testCreateAppWithArrayConfig(): void
    {
        $app = AppFactory::create(
            [
                'logger.name'      => 'default-logger',
                'logger.handler'   => [\DI\object(NullHandler::class)],
                'logger.processor' => [],
                'logger'           => \DI\object(Logger::class)->constructor(
                    \DI\get('logger.name'),
                    \DI\get('logger.handler'),
                    \DI\get('logger.processor')
                )
            ]
        );
        $this->assertInstanceOf(App::class, $app);
        $this->assertInstanceOf(Logger::class, $app->getContainer()->get('logger'));
    }

    public function testCreateAppWithFileConfig(): void
    {
        file_put_contents('config.php', '<?php return [\'test\' => \'truc\'] ?>');
        $app = AppFactory::create('config.php');
        $this->assertInstanceOf(App::class, $app);
        $this->assertSame('truc', $app->getContainer()->get('test'));

        if (file_exists('config.php')) {
            unlink('config.php');
        }
    }

    public function testCreateAppWithDirConfig(): void
    {
        mkdir(__DIR__ . '/config/', 0777);
        file_put_contents(__DIR__ . '/config/config.php', '<?php return [\'test\' => \'truc\'] ?>');

        $app = AppFactory::create(__DIR__ . '/config/');
        $this->assertInstanceOf(App::class, $app);
        $this->assertSame('truc', $app->getContainer()->get('test'));

        if (file_exists(__DIR__ . '/config/config.php')) {
            unlink(__DIR__ . '/config/config.php');
            rmdir(__DIR__ . '/config/');
        }
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

    public function testCreateAppWithRouteConfig(): void
    {
        $app = AppFactory::create([
            'routes' => [
                \DI\object(Route::class)->constructor(
                    '/blog/:id/:pouette',
                    function (ServerRequestInterface $request, DelegateInterface $delegate) {
                        $response = $delegate->process($request);
                        $response->getBody()->write('<br>Middleware callable !!!<br>');

                        return $response;
                    },
                    'GET',
                    'first_route_test',
                    ['id' => '\\d+', 'pouette' => '\\w+']
                )
            ]
        ]);
        $this->assertInstanceOf(App::class, $app);
    }
}
