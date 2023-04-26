<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "libros_categorias".
 *
 * @property int $libcat_id
 * @property int|null $libcat_lib_id
 * @property int|null $libcat_cat_id
 * @property int|null $libcat_subcat_id
 */
class LibrosCategorias extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'libros_categorias';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['libcat_lib_id', 'libcat_cat_id', 'libcat_subcat_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'libcat_id' => 'Libcat ID',
            'libcat_lib_id' => 'Libcat Lib ID',
            'libcat_cat_id' => 'Libcat Cat ID',
            'libcat_subcat_id' => 'Libcat Subcat ID',
        ];
    }
}
