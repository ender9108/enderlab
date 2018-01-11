# MIDDLE-EARTH-FRAMEWORK
It's a micro framework that implements [middleware PSR-15 convention](https://github.com/php-fig/fig-standards/blob/master/proposed/http-middleware/middleware.md)

I created it just for fun and understand the use of PSR15 middlewares.

[![Build Status](https://travis-ci.org/ender9108/middle-earth-framework.svg?branch=master)](https://travis-ci.org/ender9108/middle-earth-framework)
[![Coverage Status](https://coveralls.io/repos/github/ender9108/middle-earth-framework/badge.svg?branch=master)](https://coveralls.io/github/ender9108/middle-earth-framework?branch=master)
[![Latest Stable Version](https://poser.pugx.org/enderlab/middle-earth-framework/v/stable)](https://packagist.org/packages/enderlab/middle-earth-framework)
[![Total Downloads](https://poser.pugx.org/enderlab/middle-earth-framework/downloads)](https://packagist.org/packages/enderlab/middle-earth-framework)
[![License](https://poser.pugx.org/enderlab/middle-earth-framework/license)](https://packagist.org/packages/enderlab/middle-earth-framework)


# Installation
```
composer require enderlab/middle-earth-framework
```

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


## Documentation
You can see the documentation [here !](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/index.md)


## Author
Alexandre Berthelot <alexandreberthelot9108@gmail.com>