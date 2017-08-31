<?php
require dirname(__FILE__).'/../vendor/autoload.php';

use EnderLab\Application\AppFactory;

$app = AppFactory::create('../config/config.php');
$app->pipe(new \Middlewares\Whoops());
$app->pipe('EnderLab\\Logger\\LoggerMiddleware');

\Http\Response\send($app->run());