<?php

return [
    'app.env'                => \DI\env('ENV', App::ENV_PROD),
    'app.enableErrorHandler' => \DI\env('ERROR', true)
];
