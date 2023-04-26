<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sugerencias".
 *
 * @property int $sug_id
 * @property string|null $sug_sugerencia
 * @property string|null $sug_fecha_hora
 * @property string|null $sug_vigente
 */
class Sugerencias extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sugerencias';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sug_sugerencia'], 'string'],
            [['sug_fecha_hora'], 'safe'],
            [['sug_vigente'], 'string', 'max' => 1],
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
        ];
    }
}
