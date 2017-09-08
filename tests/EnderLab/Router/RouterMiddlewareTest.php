<?php

namespace Tests\EnderLab;

use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Middleware\CallableMiddlewareDecorator;
use EnderLab\Router\Route;
use EnderLab\Router\Router;
use EnderLab\Router\RouterException;
use EnderLab\Router\RouterMiddleware;
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
                $response = $delegate->process($request);
                $response->getBody()->write('Test phpunit process app !');

                return $response;
            },
            'GET',
            'test_route'
        ));

        $middleware = new RouterMiddleware($router, $response);
        $this->expectException(RouterException::class);
        $middleware->process($request, $dispatcher);
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
                $response = $delegate->process($request);
                $response->getBody()->write('Test phpunit process app !');

                return $response;
            },
            'POST',
            'test_route'
        ));

        $middleware = new RouterMiddleware($router, $response);
        $this->expectException(RouterException::class);
        $middleware->process($request, $dispatcher);
    }

    public function testRouteFoundWithAttributes()
    {
        $request = new ServerRequest('GET', '/test/1');
        $dispatcher = new Dispatcher();
        $response = new Response();
        $router = new Router();
        $callable = new CallableMiddlewareDecorator(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
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
