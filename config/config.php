<?php
return [
    'global.env' => \DI\env('global_env', 'dev'),
    'routes' => [
        \DI\object(\EnderLab\Router\Route::class)->constructor(
            '/blog/:id/:pouette',
            ['App\\MyMiddlewareInvokable', new \App\MyMiddleware()],
            'GET',
            'first_route_test',
            array('id' => '\\d+', 'pouette' => '\\w+')
        )
    ],
    'logger.name' => 'default-logger',
    'logger.file' => __DIR__.'/../logs/app.log',
    'logger.handler' => [
        \DI\object(
            \Monolog\Handler\StreamHandler::class
        )->constructor(\DI\get('logger.file'))
    ],
    'logger.processor' => [/*\DI\object(\Monolog\Processor\WebProcessor::class)*/],
    'logger' => \DI\object(
        \Monolog\Logger::class
    )->constructor(
        \DI\get('logger.name'),
        \DI\get('logger.handler'),
        \DI\get('logger.processor')
    )
];