<?php

use roaresearch\yii2\oauth2server\migrations\tables\CreateTable;

class m140501_075316_oauth_public_keys extends CreateTable
{
    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'oauth_public_keys';
    }

    /**
     * @inheritdoc
     */
    public function columns(): array
    {
        return [
            'client_id' => $this->string(32)->notNull(),
            'public_key' => $this->string(2000)->notNUll(),
            'private_key' => $this->string(2000)->notNUll(),
            'encription_algorithm' => $this->string(100)->notNull()
                ->defaultValue('RS256'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function foreignKeys(): array
    {
        return [
            'client_id' => [
                'table' => 'oauth_clients',
                'columns' => ['client_id' => 'client_id'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function compositePrimaryKeys(): array
    {
        return ['client_id', 'public_key'];
    }
}
