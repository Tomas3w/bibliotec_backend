<?php

use yii\db\Migration;

/**
 * Class m230523_200009_agregar_isbn_link_y_nombre_a_sugerencia
 */
class m230523_200009_agregar_isbn_link_id_usu_y_nombre_a_sugerencia extends Migration
{
    public function safeUp()
    {
        $this->addColumn('sugerencias', 'sug_nombre_libro', $this->string(255)->notNull());
        $this->addColumn('sugerencias', 'sug_link', $this->string(255));
        $this->addColumn('sugerencias', 'sug_isbn', $this->string(255));
        $this->addColumn('sugerencias', 'sug_usu_id', $this->integer());

        // Add foreign key constraint
        $this->addForeignKey(
            'fk-sugerencias-sug_usu_id',
            'sugerencias',
            'sug_usu_id',
            'usuarios',
            'usu_id'
        );
    }


    public function safeDown()
    {
        echo "m230523_200009_agregar_isbn_link_id_usu_y_nombre_a_sugerencia cannot be reverted.\n";
        return false;
    }

}
