<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "log_abm".
 *
 * @property int $logabm_id
 * @property string|null $logabm_fecha_hora
 * @property int|null $logabm_usu_id
 * @property string|null $logabm_tabla
 * @property int|null $logabm_accion_id
 * @property string|null $logabm_nombre_accion
 * @property string|null $logabm_modelo_viejo
 * @property string|null $logabm_modelo_nuevo
 * @property string|null $logabm_descripcion
 */
class LogAbm extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log_abm';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['logabm_fecha_hora'], 'safe'],
            [['logabm_usu_id', 'logabm_accion_id'], 'integer'],
            [['logabm_modelo_viejo', 'logabm_modelo_nuevo', 'logabm_descripcion'], 'string'],
            [['logabm_tabla', 'logabm_nombre_accion'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'logabm_id' => 'Logabm ID',
            'logabm_fecha_hora' => 'Logabm Fecha Hora',
            'logabm_usu_id' => 'Logabm Usu ID',
            'logabm_tabla' => 'Logabm Tabla',
            'logabm_accion_id' => 'Logabm Accion ID',
            'logabm_nombre_accion' => 'Logabm Nombre Accion',
            'logabm_modelo_viejo' => 'Logabm Modelo Viejo',
            'logabm_modelo_nuevo' => 'Logabm Modelo Nuevo',
            'logabm_descripcion' => 'Logabm Descripcion',
        ];
    }
}
