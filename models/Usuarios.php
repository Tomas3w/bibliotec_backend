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
class Usuarios extends \yii\db\ActiveRecord
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
}
