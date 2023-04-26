<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comentarios".
 *
 * @property int $comet_id
 * @property string|null $comet_fecha_hora
 * @property int|null $comet_usu_id
 * @property int|null $comet_lib_id
 * @property string|null $comet_comentario
 * @property int|null $comet_referencia_id
 * @property int|null $comet_padre_id
 * @property string|null $comet_vigente
 */
class Comentarios extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comentarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['comet_fecha_hora'], 'safe'],
            [['comet_usu_id', 'comet_lib_id', 'comet_referencia_id', 'comet_padre_id'], 'integer'],
            [['comet_comentario'], 'string'],
            [['comet_vigente'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'comet_id' => 'Comet ID',
            'comet_fecha_hora' => 'Comet Fecha Hora',
            'comet_usu_id' => 'Comet Usu ID',
            'comet_lib_id' => 'Comet Lib ID',
            'comet_comentario' => 'Comet Comentario',
            'comet_referencia_id' => 'Comet Referencia ID',
            'comet_padre_id' => 'Comet Padre ID',
            'comet_vigente' => 'Comet Vigente',
        ];
    }
}
