<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=schoolpay',
    'username' => getenv('DB_USERNAME') ?: 'postgres',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
