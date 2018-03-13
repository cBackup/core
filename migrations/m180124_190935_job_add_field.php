<?php

use yii\db\Migration;

class m180124_190935_job_add_field extends Migration
{

    public function up()
    {
        $this->addColumn('{{%job}}', 'cli_custom_prompt', $this->string(255)->null()->defaultValue(null)->after('command_var'));
    }

    public function down()
    {
        $this->dropColumn('{{%job}}', 'cli_custom_prompt');
    }
}
