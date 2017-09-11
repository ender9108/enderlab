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

    public function testAddValidRoutesCollectionByArrayConfig(): void
    {
        $router = new Router();
        $router->addRoutes([
             ['test/{id:\d+}', function () {
             }, Router::HTTP_GET, 'test_route']
        ]);
        $this->assertSame(1, $router->count());
    }

    public function testAddValidRoutesCollectionByArrayConfigAndGroup(): void
    {
        $router = new Router();
        $router->addRoutes([
            ['/', function () {
            }, Router::HTTP_GET, 'get_all_users'],
            ['/{id:\d+}', function () {
            }, Router::HTTP_GET, 'get_user_by_id'],
            ['/', function () {
            }, Router::HTTP_POST, 'create_user'],
            ['/{id:\d+}', function () {
            }, Router::HTTP_PUT, 'update_user'],
            ['/{id:\d+}', function () {
            }, Router::HTTP_DELETE, 'delete_user']
        ]);
        $this->assertSame(5, $router->count());
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
        }, 'GET'));
        $request = $this->makeRequest('GET', '/');
        //$this->expectException(RouterException::class);
        $route = $router->match($request);
        $this->assertInstanceOf(Route::class, $route);
    }

    public function testInvalidMatch(): void
    {
        $router = new Router();
        $router->addRoute(new Route('/', function () {
        }, 'GET'));
        $request = $this->makeRequest('GET', '/test');
        $this->assertSame(null, $router->match($request));
    }

    public function testGetAllowedMethods(): void
    {
        $router = new Router();
        $this->assertSame(8, count($router->getAllowedMethods()));
    }

    public function testGenerateUri()
    {
        $router = new Router();
        $router->addRoute(new Route('/test/{id:\d+}/pouette', function () {
        }, 'GET', 'route_test'));
        $uri = $router->generateUri('route_test', ['id' => 2]);
        $this->assertSame('/test/2/pouette', $uri);
    }

    public function testGetRoutes()
    {
        $router = new Router();
        $router->addRoute(new Route('/test/{id:\d+}/pouette', function () {
        }, 'GET', 'route_test'));
        $routes = $router->getRoutes();
        $this->assertSame(1, count($routes));
    }
}
