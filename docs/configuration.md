# Configuration

You can  create a directory **config/** in root dir.

Class AppFactory load all files in directory.

```php
<?php
require dirname(__FILE__).'/../vendor/autoload.php';

use EnderLab\Application\AppFactory;

$app = AppFactory::create(__DIR__.'/../config/');
```

```php
<?php
return [
    'app.env' => '{MY ENV}',            /* accept 'dev', 'prod', 'test' */
    'app.routes' => [],                 /* define all routes load by AppFactory */
    'app.enableErrorHandler' => true,   /* accept true, false */
    'my.custom.key' => 'hello'          /* define your custom key and custom value */
];
```