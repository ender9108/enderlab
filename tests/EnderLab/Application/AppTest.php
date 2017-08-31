<?php

namespace Tests\EnderLab\Application;

use EnderLab\Application\App;
use EnderLab\Application\AppFactory;
use EnderLab\Router\Route;
use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppTest extends TestCase
{
    private function makeInstanceApp()
    {
        return AppFactory::create();
    }

    public function testCreateAppObject()
    {
        $app = $this->makeInstanceApp();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testPipeWithInvalidMiddleware()
    {
        $app = $this->makeInstanceApp();
        $this->expectException(\InvalidArgumentException::class);
        $app->pipe('InvalideMiddleware');
    }

    public function testPipeWithValidMiddlewareInterface()
    {
        $app = $this->makeInstanceApp();
        $app->pipe('App\\MyMiddleware');

        $this->assertSame(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithValidCallableMiddleware()
    {
        $app = $this->makeInstanceApp();
        $app->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('<br>Middleware callable !!!<br>');

            return $response;
        });

        $this->assertSame(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithValidInvokableMiddlewareInstance()
    {
        $app = $this->makeInstanceApp();
        $app->pipe(new \App\MyMiddlewareInvokable());

        $this->assertSame(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithValidInvokableMiddlewareCallable()
    {
        $app = $this->makeInstanceApp();
        $app->pipe('App\\MyMiddlewareInvokable');

        $this->assertSame(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testAddValidRoute()
    {
        $app = $this->makeInstanceApp();
        $route = $app->addRoute('/', function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('<br>Middleware callable !!!<br>');

            return $response;
        }, 'GET', 'route_test');
        $this->assertInstanceOf(Route::class, $route);
    }

    public function testAddInvalidRoute()
    {
        $app = $this->makeInstanceApp();
        $this->expectException(\InvalidArgumentException::class);
        $app->addRoute('/', null, 'GET', 'route_test');
    }

    public function testProcessApp()
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
