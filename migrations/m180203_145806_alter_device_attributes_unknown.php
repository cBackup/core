<?php

use yii\db\Migration;

/**
 * Class m180203_145806_alter_device_attributes_unknown
 */
class m180203_145806_alter_device_attributes_unknown extends Migration
{

    public function up()
    {
        $this->addColumn('{{%device_attributes_unknown}}', 'ip', $this->string(15)->null()->defaultValue(null)->after('id'));
    }

    public function down()
    {
        $this->dropColumn('{{%device_attributes_unknown}}', 'ip');
    }

}
