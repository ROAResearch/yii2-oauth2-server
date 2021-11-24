<?php

namespace app\fixtures;

use roaresearch\yii2\oauth2server\models\OauthAuthorizationCodes;

class OauthAuthorizationCodesFixture extends \yii\test\ActiveFixture
{
    public $modelClass = OauthAuthorizationCodes::class;
    public $dataFile = __DIR__ . '/data/auth_codes.php';
}
