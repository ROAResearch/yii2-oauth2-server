<?php

namespace roaresearch\yii2\oauth2server;

use roaresearch\yii2\oauth2server\models\OauthAccessTokens as AccessToken;
use yii\db\ActiveQuery;

trait RevokeAccessTokenTrait
{
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken(
        $token,
        $type = null
    ): ?static {
        return static::find()->innerJoinWith([
            'activeAccessToken' => function (ActiveQuery $query) use ($token) {
                $query->andWhere(['access_token' => $token]);
            },
        ])->one();
    }

    /**
     * @return ActiveQuery
     */
    public function getAccessTokens(): ActiveQuery
    {
        return $this->hasMany(AccessToken::class, ['user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getActiveAccessToken(): ActiveQuery
    {
        $query = $this->getAccessTokens();
        $query->multiple = false;

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function getAccessTokenData(): AccessToken
    {
        return $this->activeAccessToken;
    }

    /**
     * @inheritdoc
     */
    public function revokeActiveAccessToken(): bool
    {
        return $this->getAccessTokenData()->delete();
    }

    /**
     * @inheritdoc
     */
    public function revokeAllAccessTokens(): bool
    {
        return AccessToken::deleteAll(['user_id' => $this->id]) > 0;
    }

}
