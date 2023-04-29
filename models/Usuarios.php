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
            // Crea un token seguro por defecto automaticamente cuando se crea
            [['usu_token'], 'default', 'value' => Yii::$app->security->generateRandomString()],

            [['usu_tipo_usuario'], 'integer'],
            [['usu_token'], 'string'],
            [['usu_documento', 'usu_nombre', 'usu_apellido', 'usu_mail', 'usu_clave', 'usu_telefono'], 'string', 'max' => 255],
            [['usu_activo', 'usu_habilitado'], 'string', 'max' => 1],
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

    public static function checkAuth($request, $modelClass)
    {
        $user = Usuarios::findIdentity($request->bodyParams[$modelClass::getNombreUsuID()]);
        if (!isset($user))
            return false;
        if ($request->headers['Authorization'] !== 'Bearer ' . $user->getAuthKey())
            return false;
        return true;
    }
}
