# Middlewares

## How to create middleware

Your custom middleware must be implement **Interop\Http\ServerMiddleware\MiddlewareInterface**

```php
<?php
namespace App\MyTest

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoggerMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        /* ... My treatment ... */
        /* Return ResponseInterface */
    }
}
```

## Use internal middleware
### Error handler middleware

#### Enable with internal error handler
```php
<?php
require dirname(__FILE__).'/../vendor/autoload.php';

use EnderLab\Application\AppFactory;

$app = AppFactory::create();

$app->enableErrorHandler(true);
```

#### Enable with custom class middleware
```php
<?php
require dirname(__FILE__).'/../vendor/autoload.php';

use EnderLab\Application\AppFactory;
use \Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$app = AppFactory::create();
$app->enableErrorHandler(new MyCustomErrorHandler());

class MyCustomErrorHandler implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        /* ... My treatment ... */
        /* Return ResponseInterface */
    }
}
```

#### Enable with custom closure
```php
<?php
require dirname(__FILE__).'/../vendor/autoload.php';

use EnderLab\Application\AppFactory;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$app = AppFactory::create();
$app->enableErrorHandler(new MyCustomErrorHandler());

$app->enableErrorHandler(function(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface {
    /* ... My treatment ... */
    /* Return ResponseInterface */
});
```

### Trailing slash middleware

```php
<?php
require dirname(__FILE__).'/../vendor/autoload.php';

use EnderLab\Application\AppFactory;

$app = AppFactory::create();
$app->enableTrailingSlash(true);

```

### Logger middleware

```php
<?php
require dirname(__FILE__).'/../vendor/autoload.php';

use EnderLab\Application\AppFactory;

$app = AppFactory::create();
$app->pipe(new \EnderLab\Logger\LoggerMiddleware(
    /* My class logger implementing Psr\Log\LoggerInterface */
));

```