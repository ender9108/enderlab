<?php

namespace Tests\EnderLab\Application;

use DI\ContainerBuilder;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Middleware\MiddlewareBuilder;
use EnderLab\Router\Router;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DispatcherTest extends TestCase
{
    /**
     * @param null|\SplQueue         $queue
     * @param DelegateInterface|null $delegate
     *
     * @return Dispatcher
     */
    private function makeDispatcher(?\SplQueue $queue = null, ?DelegateInterface $delegate = null): Dispatcher
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
        $return = $dispatcher->pipe($middlewareBuilder->buildMiddleware(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('<br>Middleware callable !!!<br>');

            return $response;
        }));

        $this->assertInstanceOf(Dispatcher::class, $return);
        $this->assertSame(1, $dispatcher->countMiddlewares());
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
        $dispatcher->pipe($middlewareBuilder->buildMiddleware(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('<br>Middleware callable !!!<br>');

            return $response;
        }));
        $response = $dispatcher->process($this->makeRequest());
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}