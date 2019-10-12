<?php

$appDir = dirname(__DIR__);
$testDir = dirname($appDir);
$repositoryDir = dirname($testDir);

return [
    'basePath' => $appDir,
    'language' => 'en-US',
    'aliases' => [
        '@tests' => $testDir,
        '@roaresearch/yii2/oauth2server' => "$repositoryDir/src",
    ],
    'components' => [
        'db' => require __DIR__ . '/db.php',
        'authManager' => [
             'class' => yii\rbac\DbManager::class,
        ],
    ],
];
