<?php

namespace roaresearch\yii2\oauth2server\controllers;

use roaresearch\yii2\oauth2server\filters\ErrorToExceptionFilter;
use Yii;
use yii\{helpers\ArrayHelper, rest\OptionsAction};

/**
 * @property roaresearch\yii2\oauth2server\Module $module
 */
class RestController extends \yii\rest\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::class,
                'oauth2Module' => $this->module,
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function verbs(): array
    {
        return [
            'token' => ['POST'],
            'options' => ['OPTIONS'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions(): array
    {
        return [
            'options' => [
                'class' => OptionsAction::class,
                'collectionOptions' => ['POST', 'OPTIONS'],
                'resourceOptions' => ['OPTIONS'],
            ],
        ];
    }

    /**
     * Action to generate oauth2 tokens.
     */
    public function actionToken()
    {
        return $this->module->getServer()->handleTokenRequest()
            ->getParameters();
    }

    public function actionAuthorize()
    {
        return $this->module->getServer()->handleAuthorizeRequest()
            ->getParameters();
    }
}
