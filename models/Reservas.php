<?php

namespace app\models;

use yii\util\CDateTimeParser;
use yii\web\ForbiddenHttpException;

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
            [['resv_fecha_hora', 'resv_fecha_desde', 'resv_fecha_hasta', 'resv_lib_id', 'resv_usu_id'], 'required'],
            [['resv_fecha_hora'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],
            [['resv_fecha_desde', 'resv_fecha_hasta'], 'datetime', 'format' => 'yyyy-MM-dd'],

            [['resv_usu_id', 'resv_lib_id'], 'integer'],
            [['resv_fecha_hora', 'resv_fecha_desde', 'resv_fecha_hasta'], 'safe'],

            ['resv_usu_id', 'usuarioYaConReserva'],

            ['resv_fecha_hasta', 'compare', 'compareAttribute' => 'resv_fecha_desde', 'operator' => '>='],

            [['resv_fecha_desde', 'resv_fecha_hasta'], 'reservaIntervalo'],
            [['resv_fecha_desde', 'resv_fecha_hasta'], 'reservaIntervaloMax'],

            [['resv_estado'], 'string', 'max' => 2],
            ['resv_estado', 'default', 'value' => 'P'],
            ['resv_estado', 'in', 'range' => [
                'X', // reserva cancelada
                'P', // reserva pendiente de confirmacion
                'C', // reserva confirmada
                'L', // libro levantado
                'D', // reserva completada (libro devuelto)
                'N', // reserva no devuelta
            ]],
        ];
    }

    public function reservaIntervaloMax($attribute, $params)
    {
        $fechaDesde = new \DateTime($this->resv_fecha_desde);
        $fechaHasta = new \DateTime($this->resv_fecha_hasta);
        $diferencia = $fechaDesde->diff($fechaHasta)->days;

        if ($diferencia > 10) {
            $this->addError($attribute, "La diferencia entre las fechas no puede ser mayor a 10 días.");
        }
    }

    public function usuarioYaConReserva($attribute, $params)
    {
        // echo $this->resv_id;
        if (count(static::find()
            ->where(['resv_usu_id' => $this->resv_usu_id])
            ->andWhere(['!=', 'resv_id', $this->resv_id])
            ->andWhere(['!=', 'resv_estado', 'X'])
            ->andWhere(['!=', 'resv_estado', 'D'])
            // ->andWhere([
            //     'or',
            //     ['and', ['>=', 'resv_fecha_hasta', $this->resv_fecha_desde], ['<=', 'resv_fecha_hasta', $this->resv_fecha_hasta]],
            //     ['and', ['>=', 'resv_fecha_desde', $this->resv_fecha_desde], ['<=', 'resv_fecha_desde', $this->resv_fecha_hasta]],
            // ])
            ->all()) > 3)
            $this->addError($attribute, "El usuario especificado tiene 3 reservas ya");
    }

    public function reservaIntervalo($attribute, $params)
    {
        if (count(static::find()
            ->where(['resv_lib_id' => $this->resv_lib_id])
            ->andWhere(['!=', 'resv_id', $this->resv_id])
            ->andWhere(['!=', 'resv_estado', 'X'])
            ->andWhere(['!=', 'resv_estado', 'D'])
            ->andWhere([
                'or',
                ['and', ['>=', 'resv_fecha_hasta', $this->resv_fecha_desde], ['<=', 'resv_fecha_hasta', $this->resv_fecha_hasta]],
                ['and', ['>=', 'resv_fecha_desde', $this->resv_fecha_desde], ['<=', 'resv_fecha_desde', $this->resv_fecha_hasta]],
            ])->all()) > Libros::findOne(['lib_id' => $this->resv_lib_id])->lib_stock - 1)
            $this->addError($attribute, "La reserva entra en conflicto de fechas con otra reserva");
        //andFilterWhere
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

    public static function obtenerEstadoReserva($idReserva)
    {
        $modelReserva = Reservas::find()->where(['resv_id'=>$idReserva])->one();
        return $modelReserva->resv_estado;
    }

    public static function obtenerReservas($idUsuarios)
    {
        $sql = "SELECT *
                FROM reservas, libros
                WHERE resv_lib_id = lib_id AND 
                      resv_usu_id = $idUsuarios";
        
        $misReservas = Yii::$app->db->createCommand($sql)->queryAll();  
        return $misReservas;
    }

    public static function cancelarReserva($idReserva, $motivoCancelacion)
    {
        $modeloViejo = null;
        $modeloNuevo = null;
        $nombreTabla = Reservas::tableName();
        
        $modelReserva = Reservas::find()->where(['resv_id'=>$idReserva])->one();
        $modeloViejo = json_encode($modelReserva->attributes);
        
        $modelReserva->resv_estado = "X";
        $modelReserva->save();

        $modeloNuevo = json_encode($modelReserva->attributes);

        $logAbm = LogAbm::nuevoLog($nombreTabla,2,$modeloViejo,$modeloNuevo,$motivoCancelacion);
        LogAccion::nuevoLog("Cancelar Reserva","Nro Reserva: $idReserva \nMotivo cancelacion:".$motivoCancelacion,$logAbm);

    }

    public static function getNombreUsuID()
    {
        return 'resv_usu_id';
    }

    public static function findIdentity($id)
    {
        return static::findOne(['resv_id' => $id]);
    }

}
