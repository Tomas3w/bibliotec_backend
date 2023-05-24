<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Sugerencias".
 *
 * @property int $sug_id
 * @property string $sug_sugerencia
 * @property string $sug_fecha_hora
 * @property string $sug_vigente
 * @property string $sug_nombre_libro
 * @property string|null $sug_link
 * @property string|null $sug_isbn
 * @property int|null $sug_usu_id
 */
class Sugerencias extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Sugerencias';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sug_sugerencia', 'sug_nombre_libro', 'sug_usu_id'], 'required'],
            [['sug_sugerencia'], 'string'],
            [['sug_fecha_hora'], 'safe'],
            // ['sug_fecha_hora', 'default', 'value' => date('Y-m-d')],
            [['sug_usu_id'], 'integer'],
            [['sug_vigente'], 'string', 'max' => 1],
            [['sug_vigente'], 'default', 'value' => 'S'],
            [['sug_nombre_libro', 'sug_link', 'sug_isbn'], 'string', 'max' => 255],
            [['sug_usu_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::class, 'targetAttribute' => ['sug_usu_id' => 'usu_id']],
            [['sug_fecha_hora'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'sug_id' => 'Sug ID',
            'sug_sugerencia' => 'Sug Sugerencia',
            'sug_fecha_hora' => 'Sug Fecha Hora',
            'sug_vigente' => 'Sug Vigente',
            'sug_nombre_libro' => 'Sug Nombre Libro',
            'sug_link' => 'Sug Link',
            'sug_isbn' => 'Sug Isbn',
            'sug_usu_id' => 'Sug Usu ID',
        ];
    }

    public static function getNombreUsuID()
    {
        return 'sug_usu_id';
    }

    public static function getNombreID()
    {
        return 'sug_id';
    }
    
    public static function findIdentity($id)
    {
        return static::findOne(['sug_id' => $id]);
    }
}
