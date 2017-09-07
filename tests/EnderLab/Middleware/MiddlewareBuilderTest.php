<?php

namespace Tests\EnderLab;

use DI\ContainerBuilder;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Middleware\MiddlewareBuilder;
use EnderLab\Router\Router;
use GuzzleHttp\Psr7\Response;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareBuilderTest extends TestCase
{
    public function testBuildMiddlewareString()
    {
        $middlewareBuilder = new MiddlewareBuilder(
            ContainerBuilder::buildDevContainer(),
            new Router(),
            new Dispatcher(),
            new Response()
        );
        $result = $middlewareBuilder->buildMiddleware('Tests\\EnderLab\\MiddlewareInvokable');
        $this->assertInstanceOf(MiddlewareInterface::class, $result);
    }

    public function testBuildMiddlewareArrayCallable()
    {
        $middlewareBuilder = new MiddlewareBuilder(
            ContainerBuilder::buildDevContainer(),
            new Router(),
            new Dispatcher(),
            new Response()
        );
        $result = $middlewareBuilder->buildMiddleware(['Tests\\EnderLab\\MiddlewareObject', 'process']);
        $this->assertInstanceOf(MiddlewareInterface::class, $result);
    }

    public function testBuildMiddlewareArrayMiddleware()
    {
        $middlewareBuilder = new MiddlewareBuilder(
            ContainerBuilder::buildDevContainer(),
            new Router(),
            new Dispatcher(),
            new Response()
        );
        $result = $middlewareBuilder->buildMiddleware(['Tests\\EnderLab\\MiddlewareObjectMiddleware', 'Tests\\EnderLab\\MiddlewareInvokable']);
        $this->assertInstanceOf(MiddlewareInterface::class, $result);
    }
}

class MiddlewareInvokable
{
    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        // TODO: Implement __invoke() method.
    }
}

class MiddlewareObject
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        // TODO: Implement __invoke() method.
    }
}

class MiddlewareObjectMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        // TODO: Implement __invoke() method.
    }
}
