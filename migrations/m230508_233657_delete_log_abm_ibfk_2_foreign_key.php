<?php

use yii\db\Migration;

/**
 * Class m230508_233657_delete_log_abm_ibfk_2_foreign_key
 */
class m230508_233657_delete_log_abm_ibfk_2_foreign_key extends Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('log_abm_ibfk_2', 'log_abm');
    }

    public function safeDown()
    {
        echo "m230508_233657_delete_log_abm_ibfk_2_foreign_key cannot be reverted.\n";

        return false;
    }

}
