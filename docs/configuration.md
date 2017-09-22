# Configuration

You can  create a directory **config/** in root dir.

Class AppFactory load all files in directory.

```php
<?php
use EnderLab\Application\AppFactory;

/**
 * Parameters can be an array or directory or file path
 */
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