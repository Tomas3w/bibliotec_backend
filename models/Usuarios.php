<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "usuarios".
 *
 * @property int $usu_id
 * @property string|null $usu_documento
 * @property string|null $usu_nombre
 * @property string|null $usu_apellido
 * @property string|null $usu_mail
 * @property string|null $usu_clave
 * @property string|null $usu_telefono
 * @property string|null $usu_activo
 * @property int|null $usu_tipo_usuario
 * @property string|null $usu_habilitado
 * @property string|null $usu_token
 */
class Usuarios extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usuarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['usu_documento', 'usu_nombre', 'usu_apellido', 'usu_mail', 'usu_clave', 'usu_telefono'], 'required'],
            // Crea un token seguro por defecto automaticamente cuando se crea
            [['usu_token'], 'default', 'value' => Yii::$app->security->generateRandomString()],
            [['usu_habilitado'], 'default', 'value' => 'N'],
            [['usu_tipo_usuario'], 'default', 'value' => 0],

            ['usu_mail', 'email'],

            [['usu_tipo_usuario'], 'integer'],
            [['usu_token'], 'string'],
            [['usu_documento', 'usu_nombre', 'usu_apellido', 'usu_mail', 'usu_clave', 'usu_telefono'], 'string', 'max' => 255],
            [['usu_activo', 'usu_habilitado'], 'string', 'max' => 1],
            [['usu_token', 'usu_documento', 'usu_mail', 'usu_telefono'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'usu_id' => 'Usu ID',
            'usu_documento' => 'Usu Documento',
            'usu_nombre' => 'Usu Nombre',
            'usu_apellido' => 'Usu Apellido',
            'usu_mail' => 'Usu Mail',
            'usu_clave' => 'Usu Clave',
            'usu_telefono' => 'Usu Telefono',
            'usu_activo' => 'Usu Activo',
            'usu_tipo_usuario' => 'Usu Tipo Usuario',
            'usu_habilitado' => 'Usu Habilitado',
            'usu_token' => 'Usu Token',
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['usu_id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['usu_token' => $token]);
    }

    public function getId()
    {
        return $this->usu_id;
    }

    public function getAuthKey()
    {
        return $this->usu_token;
    }

    public function validateAuthKey($authKey)
    {
        return $this->usu_token === $authKey;
    }

    // Esta funcion no esta hecha; Retorna los tokens de los admins
    public static function getAdminTokens()
    {
        return array_map(function ($usu){ return $usu->usu_token; }, static::findAll(['usu_tipo_usuario' => 1, 'usu_activo' => 'S']));
    }

    public static function checkIfAdmin($request, $modelClass)
    {
        return in_array($request->headers['Authorization'], array_map(function ($token){ return 'Bearer ' . $token; }, Usuarios::getAdminTokens()));
    }

    // Retorna true si la POST request tiene un token valido
    public static function checkPostAuth($request, $modelClass)
    {
        $nombre_id = $modelClass::getNombreUsuID();
        $id = $request->bodyParams[$nombre_id];
        $user = Usuarios::findIdentity($id);
        if (!isset($user))
            return false;
        if ($user->usu_activo == 'N')
            return false;
        if ($request->headers['Authorization'] !== 'Bearer ' . $user->getAuthKey() && !in_array($request->headers['Authorization'], array_map(function ($token){ return 'Bearer ' . $token; }, Usuarios::getAdminTokens())))
            return false;
        return true;
    }

    // Retorna true si la PUT (o DELETE) request tiene un token valido
    public static function checkPutDelAuth($request, $modelClass)
    {
        $nombre_id = $modelClass::getNombreUsuID();
        $id_model_class = $request->queryParams['id'];
        $identity = $modelClass::findIdentity($id_model_class);
        if (!isset($identity))
        {
            throw new \OutOfBoundsException("No se pudo encontrar el modelo ".$modelClass." con id=".$id_model_class);
            return false;
        }

        $user = Usuarios::findIdentity($identity->$nombre_id);
        if (!isset($user))
            return false;
        if ($user->usu_activo == 'N')
            return false;
        if ($request->headers['Authorization'] !== 'Bearer ' . $user->getAuthKey() && !in_array($request->headers['Authorization'], array_map(function ($token){ return 'Bearer' . $token; }, Usuarios::getAdminTokens())))
            return false;
        return true;
    }

    public static function bajaUsuario($usu_id, $motivoBaja)
    {
        $modeloViejo = null;
        $modeloNuevo = null;
        $nombreTabla = Usuarios::tableName();
        
        //$modelUsuario = Usuarios::findIdentity($usu_id);
        $modelUsuario = Usuarios::find()->where(['usu_id'=>$usu_id])->one();
        $modeloViejo = json_encode($modelUsuario->attributes);


        // if(isset($modelUsuario->attributes) && !empty($modelUsuario->attributes)){
        //     return false;
        // }else{
        
        $modelUsuario->usu_habilitado = "N"; // modifico el atributo usu_habilitado en la base de datos
        $modelUsuario->save(); // update

        $modeloNuevo = json_encode($modelUsuario->attributes);

 
        $id_logAbm = LogAbm::nuevoLog($nombreTabla,2,$modeloViejo,$modeloNuevo,$motivoBaja);
        LogAccion::nuevoLog("Baja usuario","ID Usuario: $usu_id \nMotivo baja:".$motivoBaja, $id_logAbm);
        return true; 

        // }
    }

    public static function obtenerUsuarioshabilitados(){
        $sql = "SELECT usu_id, usu_documento, usu_nombre, usu_apellido, usu_mail, usu_telefono FROM usuarios WHERE usu_habilitado = 'S'";
        $usuarios = Yii::$app->db->createCommand($sql)->queryAll();
        return $usuarios;
    }

    public static function getNombreUsuID()
    {
        return "id";
    }


}
