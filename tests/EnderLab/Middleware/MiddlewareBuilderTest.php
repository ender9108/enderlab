<?php

namespace Tests\EnderLab\MiddleEarth;

use DI\ContainerBuilder;
use EnderLab\MiddleEarth\Dispatcher\Dispatcher;
use EnderLab\MiddleEarth\Middleware\MiddlewareBuilder;
use EnderLab\MiddleEarth\Middleware\MiddlewareCollection;
use EnderLab\MiddleEarth\Router\Router;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareBuilderTest extends TestCase
{
    public function testBuildMiddlewareString(): void
    {
        $middlewareBuilder = new MiddlewareBuilder(
            ContainerBuilder::buildDevContainer(),
            new Router(),
            new Dispatcher(),
            new Response()
        );
        $result = $middlewareBuilder->buildMiddleware('Tests\\EnderLab\\MiddleEarth\\MiddlewareInvokable');
        $this->assertInstanceOf(MiddlewareInterface::class, $result);
    }

    public function testBuildMiddlewareArrayCallable(): void
    {
        $middlewareBuilder = new MiddlewareBuilder(
            ContainerBuilder::buildDevContainer(),
            new Router(),
            new Dispatcher(),
            new Response()
        );
        $result = $middlewareBuilder->buildMiddleware(['Tests\\EnderLab\\MiddleEarth\\MiddlewareObject', 'process']);
        $this->assertInstanceOf(MiddlewareInterface::class, $result);
    }

    public function testBuildMiddlewareArrayMiddleware(): void
    {
        $middlewareBuilder = new MiddlewareBuilder(
            ContainerBuilder::buildDevContainer(),
            new Router(),
            new Dispatcher(),
            new Response()
        );
        $result = $middlewareBuilder->buildMiddleware(['Tests\\EnderLab\\MiddleEarth\\MiddlewareObjectMiddleware', 'Tests\\EnderLab\\MiddleEarth\\MiddlewareInvokable']);
        $this->assertInstanceOf(MiddlewareInterface::class, $result);
    }

    public function testBuildMiddlewareWithInvalidArg(): void
    {
        $middlewareBuilder = new MiddlewareBuilder(
            ContainerBuilder::buildDevContainer(),
            new Router(),
            new Dispatcher(),
            new Response()
        );
        $this->expectException(\InvalidArgumentException::class);
        $middlewareBuilder->buildMiddleware(12);
    }

    public function testBuildMiddlewareCollection(): void
    {
        $middlewareBuilder = new MiddlewareBuilder(
            ContainerBuilder::buildDevContainer(),
            new Router(),
            new Dispatcher(),
            new Response()
        );
        $result = $middlewareBuilder->buildMiddleware([
            'Tests\\EnderLab\\MiddleEarth\\MiddlewareObjectMiddleware',
            new MiddlewareObjectMiddleware()
        ]);
        $this->assertInstanceOf(MiddlewareCollection::class, $result);
        $response = $result->process(
            new ServerRequest('GET', '/'),
            new Dispatcher()
        );
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testAdmissibleMiddleware(): void
    {
        $middlewareBuilder = new MiddlewareBuilder(
            ContainerBuilder::buildDevContainer(),
            new Router(),
            new Dispatcher(),
            new Response()
        );
        $result = $middlewareBuilder->isAdmissibleMiddlewares(new MiddlewareObjectMiddleware());
        $this->assertTrue($result);

        $result = $middlewareBuilder->isAdmissibleMiddlewares('Tests\\EnderLab\\MiddleEarth\\MiddlewareObjectMiddleware');
        $this->assertTrue($result);

        $result = $middlewareBuilder->isAdmissibleMiddlewares('Tests\\EnderLab\\MiddleEarth\\MiddlewareInvalid');
        $this->assertFalse($result);

        $result = $middlewareBuilder->isAdmissibleMiddlewares('Tests\\EnderLab\\MiddleEarth\\MiddlewareInvalide');
        $this->assertFalse($result);
    }

    public function testMiddlewareInstance(): void
    {
        $container = ContainerBuilder::buildDevContainer();
        $container->set('logger', new Logger(
            'test',
            [new NullHandler()]
        ));
        $middlewareBuilder = new MiddlewareBuilder(
            $container,
            new Router(),
            new Dispatcher(),
            new Response()
        );
        $middleware = $middlewareBuilder->buildMiddleware('Tests\\EnderLab\\MiddleEarth\\MiddlewareInstance');
        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }
}

class MiddlewareInstance implements MiddlewareInterface
{
    public function __construct(
        ContainerInterface $container,
        Router $router,
        Dispatcher $dispatcher,
        ResponseInterface $response
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new Response();
    }
}

class MiddlewareInvokable
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new Response();
    }
}

class MiddlewareObject
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new Response();
    }
}

class MiddlewareObjectMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new Response();
    }
}

class MiddlewareInvalid
{
    public function test(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new Response();
    }
}
