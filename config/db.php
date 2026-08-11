<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=schoolpay',
    'username' => 'postgres',
    'password' => 'Travis@12',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
