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
use Monolog\Handler\NullHandler;
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

    private function makeMiddleware(): callable
    {
        return function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('Welcome !!!<br>');

            return $response;
        };
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
            'logger.name'      => 'default-logger',
            'logger.handler'   => [\DI\object(NullHandler::class)],
            'logger.processor' => [],
            'logger'           => \DI\object(Logger::class)->constructor(
                \DI\get('logger.name'),
                \DI\get('logger.handler'),
                \DI\get('logger.processor')
            )
        ]);
        $app->pipe('EnderLab\\Logger\\LoggerMiddleware');

        $this->assertSame(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithInvalidEnv(): void
    {
        $app = $this->makeInstanceApp(['global.env' => 'prod']);
        $result = $app->pipe('EnderLab\\Logger\\LoggerMiddleware', null, false, 'dev');
        $this->assertInstanceOf(App::class, $result);
        $this->assertSame(0, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithPathAnMiddleware(): void
    {
        $app = $this->makeInstanceApp(['global.env' => 'prod']);
        $result = $app->pipe(
            '/',
            function (ServerRequestInterface $request, DelegateInterface $delegate) {
                $response = $delegate->process($request);
                $response->getBody()->write('<br>Middleware callable !!!<br>');

                return $response;
            }
        );
        $this->assertInstanceOf(App::class, $result);
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

    public function testAddRouteByMagicMethod(): void
    {
        $app = $this->makeInstanceApp();
        $route = $app->get('/', $this->makeMiddleware());
        $this->assertInstanceOf(Route::class, $route);
        $route = $app->post('/', $this->makeMiddleware());
        $this->assertInstanceOf(Route::class, $route);
        $route = $app->put('/', $this->makeMiddleware());
        $this->assertInstanceOf(Route::class, $route);
        $route = $app->delete('/', $this->makeMiddleware());
        $this->assertInstanceOf(Route::class, $route);
        $route = $app->head('/', $this->makeMiddleware());
        $this->assertInstanceOf(Route::class, $route);
        $route = $app->options('/', $this->makeMiddleware());
        $this->assertInstanceOf(Route::class, $route);
        $this->expectException(\BadMethodCallException::class);
        $app->pouette('/', $this->makeMiddleware());
    }

    public function testAddInvalidRoute(): void
    {
        $app = $this->makeInstanceApp();
        $this->expectException(\InvalidArgumentException::class);
        $app->addRoute('/', null, 'GET', 'route_test');
    }

    public function testRunApp(): void
    {
        $app = $this->makeInstanceApp();
        $app->addRoute('/', function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('Test phpunit process app !');

            return $response;
        }, Router::HTTP_GET);

        $response = $app->run($this->makeRequest(), true);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRunWithErrorHandlerApp(): void
    {
        $app = $this->makeInstanceApp();
        $app->enableErrorHandler(true);
        $app->addRoute('/', function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('Test phpunit process app !');

            throw new \Exception('test error handler', 500);

            return $response;
        });

        $response = $app->run($this->makeRequest(), true);
        var_dump($response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
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
        $app->enableErrorHandler(function (ServerRequestInterface $request, DelegateInterface $delegate) {
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
        $this->assertSame(false, $errorHandler);
    }

    public function testEnableRouterHandlerByBoolean(): void
    {
        $app = $this->makeInstanceApp();
        $result = $app->enableRouterHandler(true);
        $this->assertInstanceOf(App::class, $result);
    }

    public function testDisableRouterHandlerByBoolean(): void
    {
        $app = $this->makeInstanceApp();
        $result = $app->enableRouterHandler(false);
        $this->assertInstanceOf(App::class, $result);
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
