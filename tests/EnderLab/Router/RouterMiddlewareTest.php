<?php

namespace Tests\EnderLab\MiddleEarth;

use EnderLab\MiddleEarth\Dispatcher\Dispatcher;
use EnderLab\MiddleEarth\Middleware\CallableMiddlewareDecorator;
use EnderLab\MiddleEarth\Router\Route;
use EnderLab\MiddleEarth\Router\Router;
use EnderLab\MiddleEarth\Router\RouterMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RouterMiddlewareTest extends TestCase
{
    public function testRouteNotFound()
    {
        $request = new ServerRequest('GET', '/');
        $dispatcher = new Dispatcher();
        $response = new Response();
        $router = new Router();

        $router->addRoute(new Route(
            '/toto',
            function (ServerRequestInterface $request, DelegateInterface $delegate) {
                $response = $delegate->handler($request);
                $response->getBody()->write('Test phpunit process app !');

                return $response;
            },
            'GET',
            'test_route'
        ));

        $middleware = new RouterMiddleware($router, $response);
        $response = $middleware->process($request, $dispatcher);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRouteWithInvalidMethod()
    {
        $request = new ServerRequest('GET', '/');
        $dispatcher = new Dispatcher();
        $response = new Response();
        $router = new Router();

        $router->addRoute(new Route(
            '/',
            function (ServerRequestInterface $request, DelegateInterface $delegate) {
                $response = $delegate->handle($request);
                $response->getBody()->write('Test phpunit process app !');

                return $response;
            },
            'POST',
            'test_route'
        ));

        $middleware = new RouterMiddleware($router, $response);
        $response = $middleware->process($request, $dispatcher);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRouteFoundWithAttributes()
    {
        $request = new ServerRequest('GET', '/test/1');
        $dispatcher = new Dispatcher();
        $response = new Response();
        $router = new Router();
        $callable = new CallableMiddlewareDecorator(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->handle($request);
            $response->getBody()->write('Test phpunit process app !');

            return $response;
        });

        $router->addRoute(new Route(
            '/test/{id:\d+}',
            $callable,
            'GET',
            'test_route'
        ));

        $middleware = new RouterMiddleware($router, $response);
        $response = $middleware->process($request, $dispatcher);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
