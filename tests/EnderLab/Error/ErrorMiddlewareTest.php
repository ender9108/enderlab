<?php

namespace Tests\EnderLab\MiddleEarth;

use EnderLab\MiddleEarth\Dispatcher\Dispatcher;
use EnderLab\MiddleEarth\Error\ErrorMiddleware;
use EnderLab\MiddleEarth\Middleware\CallableMiddlewareDecorator;
use EnderLab\MiddleEarth\Router\Route;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorMiddlewareTest extends TestCase
{
    public function testProcessWithError()
    {
        $middleware = new ErrorMiddleware(new Response());
        $request = new ServerRequest('GET', '/');
        $dispatcher = new Dispatcher();
        $dispatcher->pipe(
            new Route(
                '*',
                new CallableMiddlewareDecorator(function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                    return 'Bad response';
                })
            )
        );
        $dispatcher->pipe(
            new Route(
                '*',
                $middleware,
                true
            ),
            true
        );
        $this->expectException(\RuntimeException::class);
        $response = $dispatcher->handle($request);
        //$this->assertSame(500, $response->getStatusCode());
    }
}
