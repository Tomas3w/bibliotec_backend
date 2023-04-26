<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "favoritos".
 *
 * @property int $fav_id
 * @property int|null $fav_usu_id
 * @property int|null $fav_lib_id
 */
class Favoritos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'favoritos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fav_usu_id', 'fav_lib_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fav_id' => 'Fav ID',
            'fav_usu_id' => 'Fav Usu ID',
            'fav_lib_id' => 'Fav Lib ID',
        ];
    }
}
