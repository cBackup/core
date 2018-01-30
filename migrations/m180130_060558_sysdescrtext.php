<?php

use yii\db\Migration;

class m180130_060558_sysdescrtext extends Migration
{

    public function up()
    {
        $this->dropIndex('ix_snmp', '{{%device_attributes}}');
        $this->dropIndex('ix_snmp', '{{%device_attributes_unknown}}');
        $this->alterColumn('{{%device_attributes}}', 'sys_description', 'VARCHAR(1024) NULL DEFAULT NULL');
        $this->alterColumn('{{%device_attributes_unknown}}', 'sys_description', 'VARCHAR(1024) NULL DEFAULT NULL');
        $this->createIndex('ix_snmp', '{{%device_attributes}}', ['sysobject_id', 'hw']);
        $this->createIndex('ix_snmp', '{{%device_attributes_unknown}}', ['sysobject_id', 'hw']);
        $this->createIndex('ix_descr', '{{%device_attributes}}', ['sys_description']);
        $this->createIndex('ix_descr', '{{%device_attributes_unknown}}', ['sys_description']);
    }

    public function down()
    {
        $this->dropIndex('ix_snmp', '{{%device_attributes}}');
        $this->dropIndex('ix_snmp', '{{%device_attributes_unknown}}');
        $this->dropIndex('ix_descr', '{{%device_attributes}}');
        $this->dropIndex('ix_descr', '{{%device_attributes_unknown}}');
        $this->alterColumn('{{%device_attributes}}', 'sys_description', 'VARCHAR(255) NULL DEFAULT NULL');
        $this->alterColumn('{{%device_attributes_unknown}}', 'sys_description', 'VARCHAR(255) NULL DEFAULT NULL');
        $this->createIndex('ix_snmp', '{{%device_attributes}}', ['sysobject_id', 'hw', 'sys_description'], true);
        $this->createIndex('ix_snmp', '{{%device_attributes_unknown}}', ['sysobject_id', 'hw', 'sys_description'], true);
    }

}
