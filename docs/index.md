# PSR15-MIDDLE-EARTH-FRAMEWORK

[![Build Status](https://travis-ci.org/ender9108/psr15-middle-earth-framework.svg?branch=master)](https://travis-ci.org/ender9108/psr15-middle-earth-framework)
[![Coverage Status](https://coveralls.io/repos/github/ender9108/psr15-middle-earth-framework/badge.svg?branch=master)](https://coveralls.io/github/ender9108/psr15-middle-earth-framework?branch=master)
[![Latest Stable Version](https://poser.pugx.org/enderlab/psr15-middle-earth-framework/v/stable)](https://packagist.org/packages/enderlab/psr15-middle-earth-framework)
[![License](https://poser.pugx.org/enderlab/psr15-middle-earth-framework/license)](https://packagist.org/packages/enderlab/psr15-middle-earth-framework)
[![Total Downloads](https://poser.pugx.org/enderlab/psr15-middle-earth-framework/downloads)](https://packagist.org/packages/enderlab/psr15-middle-earth-framework)

## Description
It's a micro framework that implements [middleware PSR-15 convention](https://github.com/php-fig/fig-standards/blob/master/proposed/http-middleware/middleware.md)

I created it just for fun and understand the use of PSR15 middlewares.

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
* [Get started](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/get_started.md)
* [Configuration](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/configuration.md)
* [Routes](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/routes.md)
* [Middlewares](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md)
    * [How to create middleware](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md#how-to-create-middleware)
    * [Use internal middleware](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md#use-internal-middleware)
        * [Error handler middleware](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md#error-handler-middleware)
        * [Trailing slash middleware](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md#trailing-slash-middleware)
        * [Logger middleware](https://github.com/ender9108/psr15-middle-earth-framework/tree/master/docs/middlewares.md#logger-middleware)

## Author
Alexandre Berthelot <alexandreberthelot9108@gmail.com>