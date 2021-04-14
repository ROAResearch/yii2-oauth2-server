<?php

namespace roaresearch\yii2\oauth2server\models;

use Yii;
use yii\db\{ActiveQuery, ActiveRecord};

/**
 * This is the model class for table "oauth_authorization_codes".
 *
 * @property string $authorization_code
 * @property string $client_id
 * @property integer $user_id
 * @property string $redirect_uri
 * @property string $expires
 * @property string $scope
 *
 * @property OauthClients $client
 */
class OauthAuthorizationCodes extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%oauth_authorization_codes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [
                ['authorization_code', 'client_id', 'redirect_uri', 'expires'],
                'required',
            ],
            [['user_id'], 'integer'],
            [['expires'], 'safe'],
            [['authorization_code'], 'string', 'max' => 40],
            [['client_id'], 'string', 'max' => 32],
            [['redirect_uri'], 'string', 'max' => 1000],
            [['scope'], 'string', 'max' => 2000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'authorization_code' => 'Authorization Code',
            'client_id' => 'Client ID',
            'user_id' => 'User ID',
            'redirect_uri' => 'Redirect Uri',
            'expires' => 'Expires',
            'scope' => 'Scope',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getClient(): ActiveQuery
    {
        return $this->hasOne(OauthClients::class, ['client_id' => 'client_id']);
    }
}
