<?php

use yii\db\Migration;

/**
 * Class m180224_074418_gloval_var_alter
 */
class m180224_074418_gloval_var_alter extends Migration
{
    public function up()
    {
        $this->addColumn('{{%job_global_variable}}', 'protected', $this->boolean()->notNull()->defaultValue(0)->after('var_value'));

        $this->batchInsert('{{%job_global_variable}}', ['var_name', 'var_value', 'protected', 'description'], [
            ['%%SEQ(CTRLY)%%', '', 1, 'Emulates CTRL+Y key press'],
            ['%%SEQ(CTRLC)%%', '', 1, 'Emulates CTRL+C key press'],
            ['%%SEQ(CTRLZ)%%', '', 1, 'Emulates CTRL+Z key press'],
            ['%%SEQ(ESC)%%',   '', 1, 'Emulates ESC key press'],
            ['%%SEQ(SPACE)%%', '', 1, 'Emulates SPACE key press'],
            ['%%SEQ(ENTER)%%', '', 1, 'Emulates ENTER key press'],
        ]);
    }

    public function down()
    {
        $this->dropColumn('{{%job_global_variable}}', 'protected');

        $this->delete('{{%job_global_variable}}', ['var_name' => '%%SEQ(CTRLY)%%']);
        $this->delete('{{%job_global_variable}}', ['var_name' => '%%SEQ(CTRLC)%%']);
        $this->delete('{{%job_global_variable}}', ['var_name' => '%%SEQ(CTRLZ)%%']);
        $this->delete('{{%job_global_variable}}', ['var_name' => '%%SEQ(ESC)%%']);
        $this->delete('{{%job_global_variable}}', ['var_name' => '%%SEQ(SPACE)%%']);
        $this->delete('{{%job_global_variable}}', ['var_name' => '%%SEQ(ENTER)%%']);
    }
}
