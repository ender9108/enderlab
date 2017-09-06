<?php

namespace Tests\EnderLab;

use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Error\ErrorMiddleware;
use EnderLab\Middleware\CallableMiddlewareDecorator;
use EnderLab\Router\Route;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
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
                new CallableMiddlewareDecorator(function (ServerRequestInterface $request, DelegateInterface $delegate) {
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
        //$this->expectException(\InvalidArgumentException::class);
        $response = $dispatcher->process($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(500, $response->getStatusCode());
    }
}
