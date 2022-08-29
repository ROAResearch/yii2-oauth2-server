<?php

namespace app\fixtures;

use roaresearch\yii2\oauth2server\models\OauthPublicKeys;

class OauthPublicKeysFixture extends \yii\test\ActiveFixture
{
    public $modelClass = OauthPublicKeys::class;
    public $dataFile = __DIR__ . '/data/public_keys.php';
}
