<?php

namespace Tests\EnderLab;

use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Logger\LoggerMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class LoggerMiddlewareTest extends TestCase
{
    public function testProcessLog(): void
    {
        $logger = new Logger(
            'test',
            [new StreamHandler(__DIR__ . '/test.log')]
        );
        $middleware = new LoggerMiddleware($logger);
        $request = new ServerRequest('GET', '/');
        $dispatcher = new Dispatcher();
        $response = $middleware->process($request, $dispatcher);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertFileExists(__DIR__ . '/test.log');

        if (file_exists(__DIR__ . '/test.log')) {
            unlink(__DIR__ . '/test.log');
        }
    }
}
