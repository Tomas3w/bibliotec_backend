<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "categorias".
 *
 * @property int $cat_id
 * @property string|null $cat_nombre
 * @property string|null $cat_vigente
 */
class Categorias extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categorias';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cat_nombre'], 'string', 'max' => 255],
            [['cat_vigente'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cat_id' => 'Cat ID',
            'cat_nombre' => 'Cat Nombre',
            'cat_vigente' => 'Cat Vigente',
        ];
    }

    public static function nuevaCategoria($cat_nombre, $cat_vigente = 'S'){
        $model = new Categorias();

        $model->cat_nombre = $cat_nombre;
        $model->cat_vigente = $cat_vigente;
        $model->save();

        return $model;
    }
}
