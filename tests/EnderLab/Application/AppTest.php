<?php

namespace Tests\EnderLab\MiddleEarth\Application;

use EnderLab\MiddleEarth\Application\App;
use EnderLab\MiddleEarth\Application\AppFactory;
use EnderLab\MiddleEarth\Dispatcher\Dispatcher;
use EnderLab\MiddleEarth\Router\Route;
use EnderLab\MiddleEarth\Router\Router;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\Server\RequestHandlerInterface;
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

    private function makeRequest($method = 'GET', $path = '/')
    {
        return new ServerRequest($method, $path);
    }

    private function makeMiddleware(): callable
    {
        return function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
            $response = $delegate->handle($request);
            $response->getBody()->write('Welcome !!!<br>');

            return $response;
        };
    }

    public function testCreateApp(): void
    {
        $app = $this->makeInstanceApp();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testPipeWithInvalidMiddleware(): void
    {
        $app = $this->makeInstanceApp();
        $this->expectException(\InvalidArgumentException::class);
        $app->pipe('InvalidMiddleware');
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
        $app->pipe('EnderLab\MiddleEarth\\Error\\ErrorMiddleware');
        $this->assertSame(1, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithInvalidEnv(): void
    {
        $app = $this->makeInstanceApp(['global.env' => 'prod']);
        $result = $app->pipe('EnderLab\MiddleEarth\\Error\\ErrorMiddleware', null, false, 'dev');
        $this->assertInstanceOf(App::class, $result);
        $this->assertSame(0, $app->getDispatcher()->countMiddlewares());
    }

    public function testPipeWithPathAnMiddleware(): void
    {
        $app = $this->makeInstanceApp(['global.env' => 'prod']);
        $result = $app->pipe(
            '/',
            function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                $response = $delegate->handle($request);
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
        $app->pipe(function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
            $response = $delegate->handle($request);
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
            function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                $response = $delegate->handle($request);
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
            function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                $response = $delegate->handle($request);
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
        $app->addRoute('/', function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
            $response = $delegate->handle($request);
            $response->getBody()->write('Test phpunit process app !');

            return $response;
        }, Router::HTTP_GET);

        $response = $app->run($this->makeRequest(), true);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRunAppCli(): void
    {
        $app = $this->makeInstanceApp();
        $app->enableRouterHandler();
        $app->enableDispatcherHandler();
        $app->addRoute('/', function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
            $response = $delegate->handle($request);
            $response->getBody()->write('Test phpunit process app !');

            return $response;
        }, Router::HTTP_GET);

        ob_start();
        $app->run($this->makeRequest());
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertSame('Test phpunit process app !', $result);
    }

    public function testRunAppWeb(): void
    {
        $app = $this->makeInstanceApp();
        $app->enableRouterHandler();
        $app->enableDispatcherHandler();
        $app->addRoute('/', function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
            $response = $delegate->handle($request);
            $response->getBody()->write('Test phpunit process app !');

            return $response;
        }, Router::HTTP_GET);

        ob_start();
        $app->run($this->makeRequest());
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertSame('Test phpunit process app !', $result);
    }

    public function testRunAppWithGroupRoute(): void
    {
        $app = $this->makeInstanceApp();
        $app->enableRouterHandler();
        $app->enableDispatcherHandler();

        $app->addGroup(
            '/admin',
            function (App $app) {
                $app->addRoute('/', function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                    $response = $delegate->handle($request);
                    $response->getBody()->write('Test phpunit process app !');

                    return $response;
                }, 'GET');
            },
            function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
                $response = $delegate->handle($request);
                $response->getBody()->write('Middleware group !!!<br>');

                return $response;
            }
        );

        ob_start();
        $app->run($this->makeRequest('GET', '/admin'));
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertSame('Middleware group !!!<br>Test phpunit process app !', $result);
    }

    public function testRunWithErrorHandlerApp(): void
    {
        $app = $this->makeInstanceApp();
        $app->enableErrorHandler();
        $app->enableRouterHandler();
        $app->enableDispatcherHandler();
        $app->addRoute('/', function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
            $response = $delegate->handle($request);
            $response->getBody()->write('Test phpunit process app !');

            $a = 3 / 0;

            return $response;
        });

        $response = $app->run($this->makeRequest(), true);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(500, $response->getStatusCode());
    }

    public function testRunWithErrorHandlerAndDisableError(): void
    {
        $errorLevel = error_reporting(0);
        $app = $this->makeInstanceApp();
        $app->enableErrorHandler();
        $app->addRoute('/', function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
            $response = $delegate->handle($request);
            $response->getBody()->write('Test phpunit process app !');

            $a = 3 / 0;

            return $response;
        });

        $response = $app->run($this->makeRequest(), true);
        error_reporting($errorLevel);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testEnableErrorHandler(): void
    {
        $app = $this->makeInstanceApp();
        $app->enableErrorHandler();
        $app->pipe(function (ServerRequestInterface $request, RequestHandlerInterface $delegate) {
            $response = $delegate->handle($request);
            $response->getBody()->write('Attention une exception va être lancée.');
            throw new \Exception('Test error handler', 500);
        });
        $response = $app->run($this->makeRequest(), true);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(500, $response->getStatusCode());
    }

    public function testEnableRouterHandlerByBoolean(): void
    {
        $app = $this->makeInstanceApp();
        $result = $app->enableRouterHandler();
        $this->assertInstanceOf(App::class, $result);
    }

    public function testDisableRouterHandlerByBoolean(): void
    {
        $app = $this->makeInstanceApp();
        $result = $app->enableRouterHandler();
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
    }
}
