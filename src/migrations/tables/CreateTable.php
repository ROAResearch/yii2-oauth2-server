<?php

namespace roaresearch\yii2\oauth2server\migrations\tables;

abstract class CreateTable extends \roaresearch\yii2\migrate\CreateTableMigration
{
    /**
     * @inheritdoc
     */
    public function primaryKey($length = self::DEFAULT_KEY_LENGTH)
    {
        return $this->string($length)->notNull()->append('PRIMARY KEY');
    }
}
