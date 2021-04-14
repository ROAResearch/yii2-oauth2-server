<?php

namespace roaresearch\yii2\oauth2server\models;

use Yii;
use yii\db\{ActiveQuery, ActiveRecord};

/**
 * This is the model class for table "oauth_clients".
 *
 * @property string $client_id
 * @property string $client_secret
 * @property string $redirect_uri
 * @property string $grant_types
 * @property string $scope
 * @property integer $user_id
 *
 * @property OauthAccessTokens[] $oauthAccessTokens
 * @property OauthAuthorizationCodes[] $oauthAuthorizationCodes
 * @property OauthRefreshTokens[] $oauthRefreshTokens
 */
class OauthClients extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%oauth_clients}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [
                ['client_id', 'client_secret', 'redirect_uri', 'grant_types'],
                'required',
            ],
            [['user_id'], 'integer'],
            [['client_id', 'client_secret'], 'string', 'max' => 32],
            [['redirect_uri'], 'string', 'max' => 1000],
            [['grant_types'], 'string', 'max' => 100],
            [['scope'], 'string', 'max' => 2000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'client_id' => 'Client ID',
            'client_secret' => 'Client Secret',
            'redirect_uri' => 'Redirect Uri',
            'grant_types' => 'Grant Types',
            'scope' => 'Scope',
            'user_id' => 'User ID',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getOauthAccessTokens(): ActiveQuery
    {
        return $this->hasMany(
            OauthAccessTokens::class,
            ['client_id' => 'client_id']
        )->inverseOf('client');
    }

    /**
     * @return ActiveQuery
     */
    public function getOauthAuthorizationCodes(): ActiveQuery
    {
        return $this->hasMany(
            OauthAuthorizationCodes::class,
            ['client_id' => 'client_id']
        )->inverseOf('client');
    }

    /**
     * @return ActiveQuery
     */
    public function getOauthRefreshTokens(): ActiveQuery
    {
        return $this->hasMany(
            OauthRefreshTokens::class,
            ['client_id' => 'client_id']
        )->inverseOf('client');
    }
}
