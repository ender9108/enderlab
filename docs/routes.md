# Routes

## Activation du router
```php
<?php
use EnderLab\Application\AppFactory;

/**
 * Create App object
 */
$app = AppFactory::create(__DIR__.'/../config/');

/**
 * Start router middleware
 */
$app->enableRouterHandler();

/**
 * Start dispatcher middleware
 */
$app->enableDispatcherHandler();

$app->run();
```

## Add route
```php
<?php
use EnderLab\Application\AppFactory;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Create App object
 */
$app = AppFactory::create(__DIR__.'/../config/');

$app->addRoute(
    '/',
    function(ServerRequestInterface $request, DelegateInterface $delegate) {
        return $delegate->process($request);
    },
    'GET',
    'route.name'
);

$app->addGroup(
    '/admin',
        /* add route in router */
        function(App $app) {
            $app->addRoute('/', new \MyTest(), 'GET');
            $app->addRoute('/test', new \MyTest(), 'GET');
        },
        /* add global middleware for group */
        function(ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('Middleware group !!!<br>');
            return $response;
        }
);

/**
 * Start router middleware
 */
$app->enableRouterHandler();

/**
 * Start dispatcher middleware
 */
$app->enableDispatcherHandler();

$app->run();
```

## Router configuration

### With config file
```php
<?php
return [
    'router.options' => [
        'cache_enabled' => true, /* true or false */
        'cache_file' => 'tmp/routes.cache' /* cache file path */
    ],
    'router.routes' => [
        /* string $path, mixed $middlewares, string $method, string $name */
        ['/', 'MyMiddleware', 'GET', 'route.name'],
        ['/test', 'MyMiddleware2', 'GET', 'route.name2'] 
    ]
];
```

### With constructor parameters
```php
<?php
$router = new \EnderLab\Router\Router(
    [
        /* string $path, mixed $middlewares, string $method, string $name */
        ['/', 'MyMiddleware1', 'GET', 'route.name1'],
        ['/test', 'MyMiddleware2', 'GET', 'route.name2']
    ],
    [
        'cache_enabled' => true, /* true or false */
        'cache_file' => 'tmp/routes.cache' /* cache file path */
    ]
);
```