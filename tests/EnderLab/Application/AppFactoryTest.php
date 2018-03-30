<?php

namespace Tests\EnderLab\MiddleEarth\Application;

use function DI\create;
use function DI\env;
use function DI\get;
use EnderLab\MiddleEarth\Application\App;
use EnderLab\MiddleEarth\Application\AppFactory;
use EnderLab\MiddleEarth\Dispatcher\Dispatcher;
use EnderLab\MiddleEarth\Middleware\CallableMiddlewareDecorator;
use EnderLab\MiddleEarth\Router\Route;
use EnderLab\MiddleEarth\Router\Router;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AppFactoryTest extends TestCase
{
    public function testCreateAppWithoutArg(): void
    {
        $app = AppFactory::create();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testCreateAppWithArrayConfig(): void
    {
        $app = AppFactory::create(
            [
                'app.env'                => env('global_env', 'dev'),
                'app.error.handler'      => env('ERROR', true),
                'logger.name'            => 'default-logger',
                'logger.handler'         => [create(NullHandler::class)],
                'logger.processor'       => [],
                'logger'                 => create(Logger::class)->constructor(
                    get('logger.name'),
                    get('logger.handler'),
                    get('logger.processor')
                )
            ],
            new Router(),
            new Dispatcher()
        );
        $this->assertInstanceOf(App::class, $app);
        $this->assertInstanceOf(Logger::class, $app->getContainer()->get('logger'));
        $this->assertSame('dev', $app->getContainer()->get('app.env'));
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
        file_put_contents(__DIR__ . '/config/otherConfig.php', '<?php return [\'bidule\' => \'chouette\'] ?>');

        $app = AppFactory::create(__DIR__ . '/config/');
        $this->assertInstanceOf(App::class, $app);
        $this->assertSame('truc', $app->getContainer()->get('test'));
        $this->assertSame('chouette', $app->getContainer()->get('bidule'));

        if (is_dir(__DIR__ . '/config/')) {
            @unlink(__DIR__ . '/config/config.php');
            @unlink(__DIR__ . '/config/otherConfig.php');
            @rmdir(__DIR__ . '/config/');
        }
    }

    public function testCreateAppWithValidContainerObject(): void
    {
        $app = AppFactory::create(['app.name' => 'MyAppTest']);
        $this->assertInstanceOf(App::class, $app);
        $this->assertSame('MyAppTest', $app->getContainer()->get('app.name'));
    }

    public function testCreateAppWithInvalidContainer(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AppFactory::create('myConfigFileInvalid.php');
    }

    public function testCreateAppWithRouteConfig(): void
    {
        $app = AppFactory::create([
            'router.routes' => [
                create(Route::class)->constructor(
                    '/blog/:id/:pouette',
                    new CallableMiddlewareDecorator(function (ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface {
                        $response = $requestHandler->handle($request);
                        $response->getBody()->write('<br>Middleware callable !!!<br>');

                        return $response;
                    }),
                    'GET',
                    'first_route_test'
                )
            ]
        ]);
        $this->assertInstanceOf(App::class, $app);
        $this->assertSame(1, count($app->getRouter()->getRoutes()));
    }
}
