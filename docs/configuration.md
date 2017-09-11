# Configuration

You can  create a directory **config/** in the root.
Class AppFactory load all file in directory.

```php
<?php
require dirname(__FILE__).'/../vendor/autoload.php';

use EnderLab\Application\AppFactory;

$app = AppFactory::create(__DIR__.'/../config/');
```