<?php

namespace Tests\EnderLab\MiddleEarth\Application;

use DI\ContainerBuilder;
use EnderLab\MiddleEarth\Dispatcher\Dispatcher;
use EnderLab\MiddleEarth\Middleware\MiddlewareBuilder;
use EnderLab\MiddleEarth\Router\Route;
use EnderLab\MiddleEarth\Router\Router;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DispatcherTest extends TestCase
{
    /**
     * @param null|\SplQueue               $queue
     * @param RequestHandlerInterface|null $delegate
     *
     * @return Dispatcher
     */
    private function makeDispatcher(?\SplQueue $queue = null, ?RequestHandlerInterface $delegate = null): Dispatcher
    {
        return new Dispatcher($queue, $delegate);
    }

    /**
     * @return MiddlewareBuilder
     */
    private function makeMiddlewareBuilder(): MiddlewareBuilder
    {
        $containerBuilder = new ContainerBuilder();

        return new MiddlewareBuilder(
            $containerBuilder->build(),
            new Router(),
            $this->makeDispatcher(),
            new Response()
        );
    }

    private function makeRequest()
    {
        return ServerRequest::fromGlobals();
    }

    public function testPipeWithValidMiddleware(): void
    {
        $dispatcher = $this->makeDispatcher();
        $middlewareBuilder = $this->makeMiddlewareBuilder();
        $return = $dispatcher->pipe($middlewareBuilder->buildMiddleware(function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
            $response = $delegate->handle($request);
            $response->getBody()->write('<br>Middleware callable !!!<br>');

            return $response;
        }));

        $this->assertInstanceOf(Dispatcher::class, $return);
        $this->assertSame(1, $dispatcher->countMiddlewares());
        $this->assertSame(1, $dispatcher->getQueue()->count());
    }

    public function testPipeWithInvalidMiddleware(): void
    {
        $dispatcher = $this->makeDispatcher();
        $this->expectException(\InvalidArgumentException::class);
        $dispatcher->pipe(function ($invalidArg, $invalidDelegate) {
            return 'Error';
        });
    }

    public function testProcess(): void
    {
        $dispatcher = $this->makeDispatcher();
        $middlewareBuilder = $this->makeMiddlewareBuilder();
        $dispatcher->pipe(
            new Route(
                '*',
                $middlewareBuilder->buildMiddleware(function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                    $response = $delegate->handle($request);
                    $response->getBody()->write('<br>Middleware callable !!!<br>');

                    return $response;
                })
            )
        );
        $response = $dispatcher->handle($this->makeRequest());
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessWithMiddlewareRoute(): void
    {
        $dispatcher = $this->makeDispatcher();
        $middlewareBuilder = $this->makeMiddlewareBuilder();
        $dispatcher->pipe(
            new Route(
                '/admin',
                $middlewareBuilder->buildMiddleware(function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                    $response = $delegate->handle($request);
                    $response->getBody()->write('<br>Middleware callable !!!<br>');

                    return $response;
                })
            )
        );
        $request = new ServerRequest('GET', '/admin');
        $response = $dispatcher->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessWithInvalidMiddlewareRoute(): void
    {
        $dispatcher = $this->makeDispatcher();
        $middlewareBuilder = $this->makeMiddlewareBuilder();
        $dispatcher->pipe(
            new Route(
                '/toto',
                $middlewareBuilder->buildMiddleware(function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                    $response = $delegate->handle($request);
                    $response->getBody()->write('<br>Middleware callable !!!<br>');

                    return $response;
                })
            )
        );
        $request = new ServerRequest('GET', '/admin');
        $response = $dispatcher->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessWithMiddlewareRouteAndInvalidResponse(): void
    {
        $dispatcher = $this->makeDispatcher();
        $middlewareBuilder = $this->makeMiddlewareBuilder();
        $dispatcher->pipe(
            new Route(
                '/admin',
                $middlewareBuilder->buildMiddleware(function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                    return 'Bad response';
                })
            )
        );
        $request = new ServerRequest('GET', '/admin');
        $this->expectException(\Exception::class);
        $response = $dispatcher->handle($request);
    }

    public function testProcessWithDoubleDispatcher(): void
    {
        $middlewareBuilder = $this->makeMiddlewareBuilder();
        $dispatcherA = $this->makeDispatcher();
        $dispatcherA->pipe(
            new Route(
                '*',
                $middlewareBuilder->buildMiddleware(function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                    $response = $delegate->handle($request);
                    $response->getBody()->write('<br>Middleware callable !!!<br>');

                    return $response;
                })
            )
        );

        $dispatcherB = $this->makeDispatcher(null, $dispatcherA);
        $dispatcherB->pipe(
            new Route(
                '*',
                $middlewareBuilder->buildMiddleware(function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                    $response = $delegate->handle($request);
                    $response->getBody()->write('<br>Middleware callable !!!<br>');

                    return $response;
                })
            )
        );

        $response = $dispatcherB->handle($this->makeRequest());
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
