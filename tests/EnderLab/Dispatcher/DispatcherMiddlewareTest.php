<?php

namespace Tests\EnderLab\Application;

use DI\ContainerBuilder;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Dispatcher\DispatcherMiddleware;
use EnderLab\Router\Route;
use EnderLab\Router\Router;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DispatcherMiddlewareTest extends TestCase
{
    public function testProcessWithRoute()
    {
        $request = new ServerRequest('GET', '/');
        $dispatcher = new Dispatcher();
        $container = ContainerBuilder::buildDevContainer();
        $router = new Router();
        $route = new Route(
            '/',
            function (ServerRequestInterface $request, DelegateInterface $delegate) {
                $response = $delegate->process($request);
                $response->getBody()->write('Test phpunit process app !');

                return $response;
            },
            'GET',
            'test_route'
        );
        $request = $request->withAttribute(Route::class, $route);
        $middleware = new DispatcherMiddleware($container, $router);

        $response = $middleware->process($request, $dispatcher);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessWithoutRoute()
    {
        $request = new ServerRequest('GET', '/');
        $dispatcher = new Dispatcher();
        $container = ContainerBuilder::buildDevContainer();
        $router = new Router();
        $middleware = new DispatcherMiddleware($container, $router);

        $response = $middleware->process($request, $dispatcher);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
