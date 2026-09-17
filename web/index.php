<?php

declare(strict_types=1);

defined('YII_DEBUG') or define('YII_DEBUG', getenv('YII_DEBUG') !== false ? filter_var(getenv('YII_DEBUG'), FILTER_VALIDATE_BOOLEAN) : true);
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV') ?: 'dev');
defined('YII_ENV_DEV') or define('YII_ENV_DEV', YII_ENV === 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
