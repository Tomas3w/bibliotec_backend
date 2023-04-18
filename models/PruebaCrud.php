<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "prueba_crud".
 *
 * @property int $id
 * @property string|null $texto1
 * @property string|null $texto2
 * @property string|null $fecha1
 */
class PruebaCrud extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'prueba_crud';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['texto1',"texto2"], 'string'],
            [['fecha1'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'texto1' => 'Texto1',
            'texto2' => 'Texto2',
            'fecha1' => 'Fecha1',
        ];
    }
}
