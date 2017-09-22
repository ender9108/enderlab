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