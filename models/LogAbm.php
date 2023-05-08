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

    public static function nuevoLog($tbl, $accion, $modeloViejo, $modeloNuevo, $descripcion)
    {
        $model = new LogAbm();

        $model->logabm_tabla = $tbl;
        $model->logabm_accion_id = $accion;
        $nombreAccion = LogAbm::obtenerNombreAccion($accion);
        $model->logabm_nombre_accion = $nombreAccion;
        $model->logabm_modelo_viejo = $modeloViejo;
        $model->logabm_modelo_nuevo = $modeloNuevo;
        $model->logabm_descripcion = $descripcion;
        $model->logabm_usu_id = 1; // TODO: deberia ser cambiado a otra cosa
        $model->save();
        return $model->logabm_id;
    }

    public static function obtenerNombreAccion($accion)
    {
        switch($accion)
        {
            case 1:
                return "Crear";
            break;
            case 2:
                return "Modificar";
            break;
            case 3:
                return "Eliminar";
            break;
        }
    }
}
