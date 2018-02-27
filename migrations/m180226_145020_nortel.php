<?php

use yii\db\Migration;

/**
 * Class m180226_145020_nortel
 */
class m180226_145020_nortel extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('{{%vendor}}', ['name' => 'Nortel']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('{{%vendor}}', ['name' => 'Nortel']);
    }

}
