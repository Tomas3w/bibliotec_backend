<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tokens}}`.
 */
class m230605_214001_create_tokens_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tokens}}', [
            'tk_id' => $this->primaryKey(),
            'tk_usu_id' => $this->integer(),
            'tk_fecha_creado' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'tk_token' => $this->text(),
            'tk_fecha_expiracion' => $this->datetime(),
        ]);

        $this->addForeignKey(
            'fk-tokens-tk_usu_id',
            'tokens',
            'tk_usu_id',
            'usuarios',
            'usu_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-tokens-tk_usu_id', 'tokens');
        $this->dropTable('{{%tokens}}');
    }
}
