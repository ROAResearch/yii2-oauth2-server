<?php

use yii\db\Migration;

class m140501_075317_alter_oauth_authorization_codes extends Migration
{
    public function up()
    {
        $this->addColumn(
            '{{%oauth_authorization_codes}}',
            'id_token',
            $this->string(100)->defaultValue(null)
        );
    }

    public function down()
    {
        $this->dropColumn('{{%oauth_authorization_codes}}', 'id_token');
    }
}
