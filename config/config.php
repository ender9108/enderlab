<?php
return [
    'global.env' => 'dev',
    'routes' => [

    ],
    'logger' => [
        'logger.name' => 'default-logger',
        'logger.file' => __DIR__.'/../logs/app.log',
        'logger.handler' => [
            \DI\object(
                \Monolog\Handler\StreamHandler::class
            )->constructor(\DI\get('logger.file'))
        ],
        'logger.processor' => [
            \DI\object(\Monolog\Processor\WebProcessor::class)
        ],
        'logger' => \DI\object(
            \Monolog\Logger::class
        )->constructor(
            \DI\get('logger.name'),
            \DI\get('logger.handler'),
            \DI\get('logger.processor')
        )
    ]
];