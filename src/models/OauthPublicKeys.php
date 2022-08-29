<?php

namespace roaresearch\yii2\oauth2server\models;

use Yii;
use yii\db\{ActiveQuery, ActiveRecord};

/**
 * This is the model class for table "oauth_public_keys".
 *
 * @property string $client_id
 * @property string $public_key
 * @property string $private_key
 * @property string $encription_algorithm
 *
 * @property OauthClients $client
 */
class OauthPublicKeys extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%oauth_public_keys}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['client_id', 'public_key', 'private_key'], 'required'],
            [
                [
                    'client_id',
                    'public_key',
                    'private_key',
                    'encription_algorithm',
                ],
                'string',
            ],
            [
                'public_key',
                'unique',
                'targetAttribute' => ['client_id', 'public_key'],
                'message' => 'Public key already in use',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'client_id' => 'Client ID',
            'public_key' => 'Public Key',
            'private_key' => 'Private Key',
            'encription_algorithm' => 'Encription Algorithm',
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
