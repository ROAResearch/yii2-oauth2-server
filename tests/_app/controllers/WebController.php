<?php

namespace app\controllers;

use app\models\User;
use roaresearch\yii2\oauth2server\actions\AuthorizeAction;
use Yii;

class WebController extends \yii\web\Controller
{
    public function actions()
    {
        return [
            'authorize' => [
                'class' => AuthorizeAction::class,
                'loginUri' => ['login'],
            ],
        ];
    }

    public function actionLogin()
    {
        Yii::$app->user->login(User::findOne(1));

        return $this->goBack();
    }
}
