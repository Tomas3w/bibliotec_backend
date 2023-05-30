<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tokens".
 *
 * @property int $tk_id
 * @property int|null $tk_usu_id
 * @property string $tk_fecha_creado
 * @property string|null $tk_token
 * @property string|null $tk_fecha_expiracion
 */
class Tokens extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tokens';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tk_usu_id'], 'integer'],
            [['tk_fecha_creado', 'tk_fecha_expiracion'], 'safe'],
            [['tk_token'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'tk_id' => 'Tk ID',
            'tk_usu_id' => 'Tk Usu ID',
            'tk_fecha_creado' => 'Tk Fecha Creado',
            'tk_token' => 'Tk Token',
            'tk_fecha_expiracion' => 'Tk Fecha Expiracion',
        ];
    }


    public static function generarToken($idUsuario)
    {
        $token = uniqid();
        $model = new Tokens();
        $model->tk_usu_id = $idUsuario;
        $model->tk_token = $token;
        $fechaHoy = date("Y-m-d H:i:s");
        $fechaExpiracion = date("Y-m-d H:i:s", strtotime($fechaHoy. " + 1 days"));
        $model->tk_fecha_expiracion = $fechaExpiracion;
        $model->save();
        return $token;
    }

    /**
     * Verificar token
     * 
     *  Si retorna NE --> Significa que no exite el token o que es incorrecto.
     *  Si retorna EX --> Significa que el token ya fue expirado.
     *  Si retorna un numero --> Significa que autentico correctamente y retorna el numero de usuario.
     * 
     */
    public static function verificarToken($token) 
    {
        $modeloToken = Tokens::find()->where(['tk_token'=>$token])->one();
        if(!empty($modeloToken))//Si existe el token entonces, voy a verificar que aun no haya expirado
        {
            $fechaHoy = strtotime(date("Y-m-d H:i:s"));
            $fechaExpiracion = strtotime(date("Y-m-d H:i:s",strtotime($modeloToken->tk_fecha_expiracion)));
            if($fechaHoy <= $fechaExpiracion)
            {
                return $modeloToken->tk_usu_id;
            }else{
                return "EX";
            }
        }else{
            return "NE";
        }
    }

}
