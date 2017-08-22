# enderlab
Micro framework middleware PSR-15

## Basic Usage
```php
<?php
require dirname(__FILE__).'/../vendor/autoload.php';

use EnderLab\Application\AppFactory;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;

$app = AppFactory::create('../config/config.php');
$app->pipe(new \Middlewares\Whoops());
$app->pipe(function(ServerRequestInterface $request, DelegateInterface $delegate) {
    $response = $delegate->process($request);
    $response->getBody()->write('<br>Middleware callable !!!<br>');

    return $response;
});

\Http\Response\send($app->run());
```

## Requirements

- psr/http-message
- guzzlehttp/psr7
- http-interop/response-sender
- http-interop/http-middleware
- php-di/php-di
- psr/container
- monolog/monolog

## Author

Alexandre Berthelot <alexandreberthelot9108@gmail.com>
