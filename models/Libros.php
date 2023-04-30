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


    public static function obtenerModeloLibro($valor, $atributo = "lib_isbn")
    {
        $model = Libros::find()->where(["$atributo" => $valor, "lib_vigente"=>"S"])->one();
        return $model;
    }

    public static function existeISBNVigente($isbn)
    {
        $modelo = Libros::obtenerModeloLibro($isbn);
        $existe = "N";
        if(!empty($modelo))
        {
            $existe = "S";
        }
        return $existe;
    }

    public static function nuevoLibro($datos)
    {
        $model = new Libros();

        $model->lib_isbn = $datos['isbn'];
        $model->lib_titulo = $datos['titulo'];
        $model->lib_descripcion = $datos['descripcion'];
        $model->lib_imagen = $datos['imagen'];
        $model->lib_categoria = $datos['categoria'];
        $model->lib_sub_categoria = $datos['subcategoria'];
        $model->lib_url = $datos['url'];
        $model->lib_stock = $datos['stock'];
        $model->lib_fecha_lanzamiento = $datos['fecha_lanzamiento'];
        $model->lib_novedades = $datos['novedad'];
        $model->lib_disponible = "S";
        $model->lib_vigente = "S";

        if(isset($datos['autores']))
        {
            $model->lib_autores =  $datos['autores'];
        }

        if(isset($datos['edicion']))
        {
            $model->lib_edicion =  $datos['edicion'];
        }

        if(isset($datos['idioma']))
        {
            $model->lib_idioma =  $datos['idioma'];
        }

        if($model->save())
        {
            return array("codigo"=>0,"mensaje"=>"Agregado correctamente");
        }else{
            return array("codigo"=>105,"mensaje"=>"Error a la hora de ingresar los datos.","data"=>$model->errors);
        }
    }

    public static function obtenerLibros($datos)
    {
        $subWhere = "";

        if(isset($datos['query']) && !empty($datos['query']))
        {
            $subWhere = " AND UPPER(lib_titulo) LIKE UPPER('%".$datos['query']."%') ";
        }

        if(isset($datos['categoria']) && !empty($datos['categoria']))
        {
            $subWhere = " AND EXISTS(SELECT 1
                                     FROM libros_categorias
                                     WHERE libcat_lib_id = lib_id
                                           AND libcat_cat_id IN(".$datos['categoria'].")) ";  

            if(isset($datos['subcategoria']) && !empty($datos['subcategoria']))
            {
                $subWhere = " AND EXISTS(SELECT 1
                                        FROM libros_categorias, sub_categorias
                                        WHERE libcat_lib_id = lib_id
                                            AND libcat_subcat_id IN(".$datos['subcategoria'].")
                                            AND libcat_subcat_id = subcat_id
                                            AND subcat_cat_id IN(".$datos['categoria'].")) ";  
            }
        }

        

        $sql = "SELECT *
                FROM libros
                WHERE lib_vigente = 'S'
                      $subWhere";
        
        $libros = Yii::$app->db->createCommand($sql)->queryAll();  
        return $libros;
    }
}
