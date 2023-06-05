<?php

use yii\db\Migration;

/**
 * Class m230605_221005_delete_token_from_usuario
 */
class m230605_221005_delete_token_from_usuario extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('usuarios', 'usu_token');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230605_221005_delete_token_from_usuario cannot be reverted.\n";

        return false;
    }

}
