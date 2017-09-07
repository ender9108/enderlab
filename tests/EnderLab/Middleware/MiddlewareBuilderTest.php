<?php
namespace Tests\EnderLab;

use DI\ContainerBuilder;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Middleware\MiddlewareBuilder;
use EnderLab\Router\Router;
use GuzzleHttp\Psr7\Response;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;


class MiddlewareBuilderTest extends TestCase
{
    public function testBuildMiddleware()
    {
        $middlewareBuilder = new MiddlewareBuilder(
            ContainerBuilder::buildDevContainer(),
            new Router(),
            new Dispatcher(),
            new Response()
        );
        $result = $middlewareBuilder->buildMiddleware(['Tests\\EnderLab\\Middleware'], '__invoke');
        $this->assertInstanceOf(MiddlewareInterface::class, $result);
    }
}

class Middleware
{
    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        // TODO: Implement __invoke() method.
    }
}