<?php

namespace roaresearch\yii2\oauth2server\models;

use Yii;
use yii\db\{ActiveQuery, ActiveRecord};

/**
 * This is the model class for table "oauth_scopes".
 *
 * @property string $scope
 * @property integer $is_default
 */
class OauthScopes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%oauth_scopes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['scope', 'is_default'], 'required'],
            [['is_default'], 'integer'],
            [['scope'], 'string', 'max' => 2000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'scope' => 'Scope',
            'is_default' => 'Is Default',
        ];
    }
}
