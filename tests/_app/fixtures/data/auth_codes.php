<?php

use yii\db\Expression as DbExpression;

return [
    [
        'authorization_code' => 'abcd',
        'client_id' => 'testclient',
        'user_id' => 1,
        'expires' => new DbExpression('NOW() + INTERVAL 1 WEEK'),
        'redirect_uri' => 'http://127.0.0.1:8000/index.php',
    ],
];
