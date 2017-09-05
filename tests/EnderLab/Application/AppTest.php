<?php

namespace Tests\EnderLab\Application;

use EnderLab\Application\App;
use EnderLab\Application\AppFactory;
use EnderLab\Dispatcher\Dispatcher;
use EnderLab\Router\Route;
use EnderLab\Router\Router;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppTest extends TestCase
{
    private function makeInstanceApp($config = null): App
    {
        return AppFactory::create($config);
    }

    private function makeRequest()
    {
        return new ServerRequest('GET', '/');
    }

    public function testCreateAppObject(): void
    {
        $app = $this->makeInstanceApp();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testPipeWithInvalidMiddleware(): void
    {
        $app = $this->makeInstanceApp();
        $this->expectException(\InvalidArgumentException::class);
        $app->pipe('InvalideMiddleware');
    }

    public function testPipeWithValidMiddlewareInterface(): void
    {
        $app = $this->makeInstanceApp([
            'logger.name'    => 'default-logger',
            'logger.file'    => __DIR__ . '/../logs/app.log',
            'logger.handler' => [
                \DI\object(
                    StreamHandler::class
                )->constructor(\DI\get('logger.file'))
            ],
            'logger.processor' => [/*\DI\object(\Monolog\Processor\WebProcessor::class)*/],
            'logger'           => \DI\object(
                Logger::class
            )->constructor(
                \DI\get('logger.name'),
                \DI\get('logger.handler'),
                \DI\get('logger.processor')
            )
        ]);
        $app->pipe('EnderLab\\Logger\\LoggerMiddleware');

        $this->assertSame(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithValidCallableMiddleware(): void
    {
        $app = $this->makeInstanceApp();
        $app->pipe(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('<br>Middleware callable !!!<br>');

            return $response;
        });

        $this->assertSame(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testAddValidRoute(): void
    {
        $app = $this->makeInstanceApp();
        $route = $app->addRoute(
            '/',
            function (ServerRequestInterface $request, DelegateInterface $delegate) {
                $response = $delegate->process($request);
                $response->getBody()->write('<br>Middleware callable !!!<br>');

                return $response;
            },
            'GET',
            'route_test'
        );
        $this->assertInstanceOf(Route::class, $route);
    }

    public function testAddValidRouteObject(): void
    {
        $app = $this->makeInstanceApp();
        $route = $app->addRoute(new Route(
            '/',
            function (ServerRequestInterface $request, DelegateInterface $delegate) {
                $response = $delegate->process($request);
                $response->getBody()->write('<br>Middleware callable !!!<br>');

                return $response;
            },
            'GET',
            'route_test'
        ), null, 'GET', 'route_test');
        $this->assertInstanceOf(Route::class, $route);
    }

    public function testAddInvalidRoute(): void
    {
        $app = $this->makeInstanceApp();
        $this->expectException(\InvalidArgumentException::class);
        $app->addRoute('/', null, 'GET', 'route_test');
    }

    public function testProcessApp(): void
    {
        $app = $this->makeInstanceApp();
        $app->addRoute('/', function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('Test phpunit process app !');

            return $response;
        });

        $response = $app->run($this->makeRequest());
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testEnableErrorHandlerByBoolean(): void
    {
        $app = $this->makeInstanceApp();
        $app->enableErrorHandler(true);
        $errorHandler = $app->getErrorHandler();
        $this->assertInstanceOf(MiddlewareInterface::class, $errorHandler);
    }

    public function testEnableErrorHandlerByCallable(): void
    {
        $app = $this->makeInstanceApp();
        $app->enableErrorHandler(function(ServerRequestInterface $request, DelegateInterface $delegate) {
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                if (!(error_reporting() & $errno)) {
                    return;
                }
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            });

            try {
                $response = $delegate->process($request);

                if (!$response instanceof ResponseInterface) {
                    throw new \Exception('Application did not return a response', 500);
                }
            } catch (\Exception | \Throwable $e) {
                $response = $this->response->withStatus($e->getCode());
                $response->getBody()->write($e->getMessage());
            }

            restore_error_handler();

            return $response;
        });
        $errorHandler = $app->getErrorHandler();
        $this->assertInstanceOf(MiddlewareInterface::class, $errorHandler);
    }

    public function testDisableErrorHandlerByBoolean(): void
    {
        $app = $this->makeInstanceApp();
        $app->enableErrorHandler(false);
        $errorHandler = $app->getErrorHandler();
        $this->assertSame(null, $errorHandler);
    }

    public function testGetter(): void
    {
        $app = $this->makeInstanceApp();
        $router = $app->getRouter();
        $this->assertInstanceOf(Router::class, $router);

        $dispatcher = $app->getDispatcher();
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);

        $container = $app->getContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container);

        $app->enableErrorHandler(true);
        $errorHandler = $app->getErrorHandler();
        $this->assertInstanceOf(MiddlewareInterface::class, $errorHandler);
    }
}
