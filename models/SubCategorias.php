<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sub_categorias".
 *
 * @property int $subcat_id
 * @property int|null $subcat_cat_id
 * @property string|null $subcat_nombre
 * @property string|null $subcat_vigente
 */
class SubCategorias extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sub_categorias';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subcat_cat_id'], 'integer'],
            [['subcat_nombre'], 'string', 'max' => 255],
            [['subcat_vigente'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'subcat_id' => 'Subcat ID',
            'subcat_cat_id' => 'Subcat Cat ID',
            'subcat_nombre' => 'Subcat Nombre',
            'subcat_vigente' => 'Subcat Vigente',
        ];
    }
}
