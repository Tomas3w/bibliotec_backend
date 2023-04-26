<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "reservas".
 *
 * @property int $resv_id
 * @property int|null $resv_usu_id
 * @property string|null $resv_fecha_hora
 * @property int|null $resv_lib_id
 * @property string|null $resv_fecha_desde
 * @property string|null $resv_fecha_hasta
 * @property string|null $resv_estado
 */
class Reservas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reservas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['resv_usu_id', 'resv_lib_id'], 'integer'],
            [['resv_fecha_hora', 'resv_fecha_desde', 'resv_fecha_hasta'], 'safe'],
            [['resv_estado'], 'string', 'max' => 2],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'resv_id' => 'Resv ID',
            'resv_usu_id' => 'Resv Usu ID',
            'resv_fecha_hora' => 'Resv Fecha Hora',
            'resv_lib_id' => 'Resv Lib ID',
            'resv_fecha_desde' => 'Resv Fecha Desde',
            'resv_fecha_hasta' => 'Resv Fecha Hasta',
            'resv_estado' => 'Resv Estado',
        ];
    }
}
