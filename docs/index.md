# PSR15-MIDDLE-EARTH-FRAMEWORK

## Description
It's a micro framework that implements [middleware PSR-15 convention](https://github.com/php-fig/fig-standards/blob/master/proposed/http-middleware/middleware.md) (beta version)

## Requirements
- psr/http-message
- psr/container
- psr/log
- guzzlehttp/psr7
- http-interop/response-sender
- http-interop/http-middleware
- php-di/php-di
- zendframework/zend-expressive-fastroute

## Summary
* [Basic usage](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/basic_usage.md)
* [Configuration](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/configuration.md)
* [Middlewares](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md)
    * [How to create middleware](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md#how-to-create-middleware)
    * [Use internal middleware](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md#use-internal-middleware)
        * [Error handler middleware](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md#error-handler-middleware)
        * [Trailing slash middleware](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md#trailing-slash-middleware)
        * [Logger middleware](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md#logger-middleware)

## Author
Alexandre Berthelot <alexandreberthelot9108@gmail.com>