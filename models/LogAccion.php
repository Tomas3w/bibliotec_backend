<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "log_accion".
 *
 * @property int $loga_id
 * @property string|null $loga_endpoint
 * @property string|null $loga_nombre_accoin
 * @property string|null $loga_descripcion
 * @property int|null $loga_usu_id
 * @property string|null $loga_fecha_hora
 * @property int|null $loga_logabm_id
 */
class LogAccion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log_accion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['loga_id', 'loga_usu_id', 'loga_logabm_id'], 'integer'],
            [['loga_descripcion'], 'string'],
            [['loga_fecha_hora'], 'safe'],
            [['loga_endpoint', 'loga_nombre_accoin'], 'string', 'max' => 255],
            [['loga_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'loga_id' => 'Loga ID',
            'loga_endpoint' => 'Loga Endpoint',
            'loga_nombre_accoin' => 'Loga Nombre Accoin',
            'loga_descripcion' => 'Loga Descripcion',
            'loga_usu_id' => 'Loga Usu ID',
            'loga_fecha_hora' => 'Loga Fecha Hora',
            'loga_logabm_id' => 'Loga Logabm ID',
        ];
    }

    public static function nuevoLog($nombreAccion, $descripcion, $logAbm = null)
    {
        $uri = $_SERVER['REDIRECT_URL'];
        if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1')
            $endPoint = explode("/web/",$uri)[1];
        else
            $endPoint = Yii::$app->request->baseUrl;
        echo Yii::$app->request->baseUrl;
        echo '
        ';
        echo Yii::$app->request->absoluteUrl;
        echo '
        ';
        echo Yii::$app->request->hostName;
        echo '
        ';
        $model = new LogAccion();
        $model->loga_endpoint = $endPoint;
        $model->loga_nombre_accoin = $nombreAccion;
        $model->loga_descripcion = $descripcion;
        $model->loga_logabm_id = $logAbm;
        $model->loga_usu_id = 1; // TODO: deberia ser cambiado a otra cosa
        if(!$model->save())
        {
            var_dump($model->errors);exit;
        }
        return $model->loga_id;
    }

}
