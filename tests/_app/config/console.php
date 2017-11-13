<?php

return [
    'id' => 'yii2-test-console',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@tests' => dirname(dirname(__DIR__)),
        '@tecnocen/oauth2server' => dirname(dirname(dirname(__DIR__))) . '/src',
    ],
    'components' => [
        'log' => null,
        'cache' => null,
        'db' => require __DIR__ . '/db.php',
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => yii\console\controllers\MigrateController::class,
            'migrationPath' => null,
            'migrationNamespaces' => [
            ],
        ],
    ],
];
