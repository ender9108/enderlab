# Get started
```php
<?php
use EnderLab\Application\AppFactory;
use EnderLab\Logger\LoggerMiddleware;
use EnderLab\Router\TrailingSlashMiddleware;

/**
 * Create App object
 */
$app = AppFactory::create(__DIR__.'/../config/');

/** 
 * Use default error handler
 * If you want to use a different handler
 * Use $app->pipe(MyCustomErrorHandler)  
 */
$app->enableErrorHandler();

/**
 * Add new middleware in pipe
 */
$app->pipe(new LoggerMiddleware($app->getContainer()->get('logger')));
$app->pipe(new TrailingSlashMiddleware());

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