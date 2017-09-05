<?php

namespace Tests\EnderLab;

use EnderLab\Router\Route;
use EnderLab\Router\Router;
use EnderLab\Router\RouterException;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /**
     * @param $method
     * @param $uri
     *
     * @return ServerRequest
     */
    private function makeRequest($method, $uri): ServerRequest
    {
        return new ServerRequest($method, $uri);
    }

    public function testCreateRouterWithoutArg(): void
    {
        $router = new Router();
        $this->assertInstanceOf(Router::class, $router);
    }

    public function testCreateRouterWithArg(): void
    {
        $router = new Router([new Route('/', function () {
        }, 'GET')]);
        $this->assertInstanceOf(Router::class, $router);
        $this->assertSame(1, $router->count());
    }

    public function testAddValidRoutesCollection(): void
    {
        $router = new Router();
        $router->addRoutes([new Route('/', function () {
        }, 'GET')]);
        $this->assertSame(1, $router->count());
    }

    public function testAddInvalidRoutesCollection(): void
    {
        $router = new Router();
        $this->expectException(RouterException::class);
        $router->addRoutes([new Route('/', function () {
        }, 'GETPOST')]);
    }

    public function testAddValidRoute(): void
    {
        $router = new Router();
        $router->addRoute(new Route('/', function () {
        }, 'GET'));
        $this->assertSame(1, $router->count());
    }

    public function testAddInvalidRoute(): void
    {
        $router = new Router();
        $this->expectException(RouterException::class);
        $router->addRoute(new Route('/', function () {
        }, 'GETPOST'));
    }

    public function testValidMatch(): void
    {
        $router = new Router();
        $router->addRoute(new Route('/', function () {
        }, 'GET'));
        $request = $this->makeRequest('GET', '/');
        $this->assertInstanceOf(Route::class, $router->match($request));
    }

    public function testValidMethodMatch(): void
    {
        $router = new Router();
        $router->addRoute(new Route('/', function () {
        }, 'POST'));
        $request = $this->makeRequest('GET', '/');
        $this->expectException(RouterException::class);
        $router->match($request);
    }

    public function testInvalidMatch(): void
    {
        $router = new Router();
        $router->addRoute(new Route('/', function () {
        }, 'GET'));
        $request = $this->makeRequest('GET', '/test');
        $this->assertSame(null, $router->match($request));
    }

    public function testGetValidNamedUrl(): void
    {
        $router = new Router();
        $router->addRoutes([new Route('/', function () {
        }, 'GET', 'route_test')]);
        $this->assertEmpty($router->getNamedUrl('route_test'));
    }

    public function testGetInvalidNamedUrl(): void
    {
        $router = new Router();
        $router->addRoutes([new Route('/', function () {
        }, 'GET', 'route_test')]);
        $this->expectException(RouterException::class);
        $router->getNamedUrl('route_tests');
    }

    public function testGetAllowedMethods(): void
    {
        $router = new Router();
        $this->assertSame(6, count($router->getAllowedMethods()));
    }
}
