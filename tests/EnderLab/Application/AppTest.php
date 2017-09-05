<?php

namespace Tests\EnderLab\Application;

use EnderLab\Application\App;
use EnderLab\Application\AppFactory;
use EnderLab\Router\Route;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppTest extends TestCase
{
    private function makeInstanceApp($config = null): App
    {
        return AppFactory::create($config);
    }

    public function testCreateAppObject(): void
    {
        $app = $this->makeInstanceApp();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testPipeWithInvalidMiddleware(): void
    {
        $app = $this->makeInstanceApp();
        $this->expectException(\InvalidArgumentException::class);
        $app->pipe('InvalideMiddleware');
    }

    public function testPipeWithValidMiddlewareInterface(): void
    {
        $app = $this->makeInstanceApp([
            'logger.name'    => 'default-logger',
            'logger.file'    => __DIR__ . '/../logs/app.log',
            'logger.handler' => [
                \DI\object(
                    StreamHandler::class
                )->constructor(\DI\get('logger.file'))
            ],
            'logger.processor' => [/*\DI\object(\Monolog\Processor\WebProcessor::class)*/],
            'logger'           => \DI\object(
                Logger::class
            )->constructor(
                \DI\get('logger.name'),
                \DI\get('logger.handler'),
                \DI\get('logger.processor')
            )
        ]);
        $app->pipe('EnderLab\\Logger\\LoggerMiddleware');

        $this->assertSame(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithValidCallableMiddleware(): void
    {
        $app = $this->makeInstanceApp();
        $app->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('<br>Middleware callable !!!<br>');

            return $response;
        });

        $this->assertSame(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testAddValidRoute(): void
    {
        $app = $this->makeInstanceApp();
        $route = $app->addRoute('/', function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('<br>Middleware callable !!!<br>');

            return $response;
        }, 'GET', 'route_test');
        $this->assertInstanceOf(Route::class, $route);
    }

    public function testAddValidRouteObject(): void
    {
        $app = $this->makeInstanceApp();
        $route = $app->addRoute(new Route(
            '/',
            function (ServerRequestInterface $request, DelegateInterface $delegate) {
                $response = $delegate->process($request);
                $response->getBody()->write('<br>Middleware callable !!!<br>');

                return $response;
            },
            'GET',
            'route_test'
        ), null, 'GET', 'route_test');
        $this->assertInstanceOf(Route::class, $route);
    }

    public function testAddInvalidRoute(): void
    {
        $app = $this->makeInstanceApp();
        $this->expectException(\InvalidArgumentException::class);
        $app->addRoute('/', null, 'GET', 'route_test');
    }

    public function testProcessApp(): void
    {
        $app = $this->makeInstanceApp();
        $app->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('Test phpunit process app !');

            return $response;
        });
        $response = $app->run();
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
