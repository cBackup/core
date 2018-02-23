<?php

use yii\db\Migration;

class m180223_074249_nonuniquemac extends Migration
{

    public function up()
    {
        $this->dropIndex('mac_UNIQUE', '{{%node}}');
    }

    public function down()
    {
        $this->createIndex('mac_UNIQUE', '{{%node}}', true);
    }

}
