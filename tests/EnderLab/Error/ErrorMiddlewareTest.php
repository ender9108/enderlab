<?php

namespace Tests\EnderLab;

use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Error\ErrorMiddleware;
use EnderLab\Middleware\CallableMiddlewareDecorator;
use EnderLab\Router\Route;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

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
        //$this->expectException(\RuntimeException::class);
        $response = $dispatcher->handle($request);
        $this->assertSame(500, $response->getStatusCode());
    }
}
