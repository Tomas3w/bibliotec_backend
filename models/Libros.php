<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "libros".
 *
 * @property int $lib_id
 * @property string|null $lib_fecha_creado
 * @property string|null $lib_titulo
 * @property string|null $lib_isbn
 * @property string|null $lib_imagen
 * @property string|null $lib_posicion
 * @property string|null $lib_descripcion
 * @property int|null $lib_stock
 * @property string|null $lib_autores
 * @property string|null $lib_edicion
 * @property string|null $lib_fecha_lanzamiento
 * @property string|null $lib_novedades
 * @property string|null $lib_idioma
 * @property string|null $lib_disponible
 * @property string|null $lib_vigente
 * @property float|null $lib_puntuacion
 */
class Libros extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'libros';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lib_fecha_creado', 'lib_fecha_lanzamiento'], 'safe'],
            [['lib_imagen', 'lib_descripcion', 'lib_autores'], 'string'],
            [['lib_stock'], 'integer'],
            [['lib_puntuacion'], 'number'],
            [['lib_titulo', 'lib_isbn', 'lib_edicion', 'lib_idioma'], 'string', 'max' => 255],
            [['lib_posicion'], 'string', 'max' => 25],
            [['lib_novedades', 'lib_disponible', 'lib_vigente'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'lib_id' => 'Lib ID',
            'lib_fecha_creado' => 'Lib Fecha Creado',
            'lib_titulo' => 'Lib Titulo',
            'lib_isbn' => 'Lib Isbn',
            'lib_imagen' => 'Lib Imagen',
            'lib_posicion' => 'Lib Posicion',
            'lib_descripcion' => 'Lib Descripcion',
            'lib_stock' => 'Lib Stock',
            'lib_autores' => 'Lib Autores',
            'lib_edicion' => 'Lib Edicion',
            'lib_fecha_lanzamiento' => 'Lib Fecha Lanzamiento',
            'lib_novedades' => 'Lib Novedades',
            'lib_idioma' => 'Lib Idioma',
            'lib_disponible' => 'Lib Disponible',
            'lib_vigente' => 'Lib Vigente',
            'lib_puntuacion' => 'Lib Puntuacion',
        ];
    }
}
