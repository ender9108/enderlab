<?php
namespace EnderLab\Test;

use EnderLab\Application\App;
use EnderLab\Application\AppFactory;
use EnderLab\Router\Route;
use PHPUnit\Framework\TestCase;

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
        $app->pipe('CoucouMiddleware');
    }

    public function testPipeWithValidMiddlewareInterface()
    {
        $app = $this->makeInstanceApp();
        $app->pipe('App\\MyMiddleware');

        $this->assertEquals(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithValidCallableMiddleware()
    {
        $app = $this->makeInstanceApp();
        $app->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('<br>Middleware callable !!!<br>');

            return $response;
        });

        $this->assertEquals(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithValidInvokableMiddlewareInstance()
    {
        $app = $this->makeInstanceApp();
        $app->pipe(new \App\MyMiddlewareInvokable());

        $this->assertEquals(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithValidInvokableMiddlewareCallable()
    {
        $app = $this->makeInstanceApp();
        $app->pipe('App\\MyMiddlewareInvokable');

        $this->assertEquals(1, $app->getDispatcher()->countMiddlewares());
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
}
