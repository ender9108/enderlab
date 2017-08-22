<?php
require dirname(__FILE__).'/../vendor/autoload.php';

use EnderLab\Application\AppFactory;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;

$app = AppFactory::create('../config/config.php');
$app->pipe(new \Middlewares\Whoops());
$app->pipe(function(ServerRequestInterface $request, DelegateInterface $delegate) {
    //$a = 3/ 0;
    $response = $delegate->process($request);
    $response->getBody()->write('<br>Middleware callable !!!<br>');

    return $response;
});

$app->addRoute('/blog', [
    'App\\MyMiddlewareInvokable',
    new \App\MyMiddleware()
], 'GET', 'first_route_test');

\Http\Response\send($app->run());