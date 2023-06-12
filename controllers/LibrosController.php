<?php

namespace app\controllers;

use app\models\Libros;
use app\models\LibrosCategorias;
use app\models\Reservas;
use app\models\Tokens;
use app\models\Usuarios;

class LibrosController extends \yii\web\Controller
{
    public $modelClass = 'app\models\Libros';
    public $enableCsrfValidation = false;
    /** VER https://www.yiiframework.com/doc/guide/2.0/es/rest-quick-start PARA IMPLEMENTAR */

    public function actionIndex()
    {
        echo 'hola!';
    }

    /**
     * endpoint: /libros/alta-libro
     *  form-data:
     *   
     * metodo: POST
     * 
     * Libro: {
     *   titulo oblig
     *   descrip oblig
     *   autores 
     *   stock:A oblig int
     * }
     * 
     */

    public function actions()
    {
        $actions = parent::actions();
    
        // disable the "delete" and "create" actions
        unset($actions['delete'], $actions['create']);
    
        // customize the data provider preparation with the "prepareDataProvider()" method
        // $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
    
        return $actions;
    }

    public function actionCreate()
    {
        
        if(isset($_POST['Libro']) && !empty($_POST['Libro']))
        {
            $datos = $_POST['Libro'];

            if(!isset($datos['isbn']) || empty($datos['isbn']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"El ISBN del libro es obligatorio."));
            }else if(Libros::existeISBNVigente($datos['isbn']) == "S"){
                $modelo = Libros::obtenerModeloLibro($datos['isbn']);
                $datos = $modelo->attributes;
                return json_encode(array("codigo"=>101, "mensaje"=>"El ISBN ingresado ya existe en la base de datos.","datos"=>$datos));
            }

            if(!isset($datos['titulo']) || empty($datos['titulo']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"El titulo del libro es obligatorio."));
            }

            if(!isset($datos['descripcion']) || empty($datos['descripcion']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"La descripcion del libro es obligatorio."));
            }

            if(!isset($datos['imagen']) || empty($datos['imagen']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"La imagen del libro es obligatoria."));
            }

            if(!isset($datos['categoria']) || empty($datos['categoria']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"La categoria del libro es obligatoria."));
            }else if(!is_numeric($datos['categoria'])){
                return json_encode(array("codigo"=>102, "mensaje"=>"La categoria enviada debe ser un numero."));
            }

            if(!isset($datos['subcategoria']) || empty($datos['subcategoria']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"La sub-categoria del libro es obligatoria."));
            }else if(!is_numeric($datos['subcategoria'])){
                return json_encode(array("codigo"=>102, "mensaje"=>"La sub-categoria enviada debe ser un numero."));
            }
            
            /* if(!isset($datos['url']) || empty($datos['url']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"La URL del libro es obligatoria."));
            } */

            if(!isset($datos['fecha_lanzamiento']) || empty($datos['fecha_lanzamiento']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"La fecha de lanzamiento del libro es obligatoria."));
            }else{
                $fecha = date("Y-m-d",strtotime($datos['fecha_lanzamiento']));
                $datos['fecha_lanzamiento'] = $fecha;
            }

            if(!isset($datos['stock']) || empty($datos['stock']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"La URL del libro es obligatoria."));
            }else{
                if(!is_numeric($datos['stock']))
                {
                    return json_encode(array("codigo"=>102, "mensaje"=>"El stock tiene que ser un numero."));
                }else{
                    if($datos['stock'] < 0)
                    {
                        return json_encode(array("codigo"=>101, "mensaje"=>"El stock no puede ser un valor negativo. Solamente se admiten igual o mayor a cero."));
                    }
                }
            }

            if(!isset($datos['novedad']) || empty($datos['novedad']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"Se tiene que indicar si se quiere informar como una novedad."));
            }else if(!is_string($datos['novedad']) || strlen($datos['novedad']) != 1){
                return json_encode(array("codigo"=>101, "mensaje"=>"El campo es de tipo String con un largo de 1 caracter."));
            }else if($datos['novedad'] != "S" && $datos['novedad'] != "N"){
                return json_encode(array("codigo"=>101, "mensaje"=>"El campo solo se tiene que ingresar una S o N."));
            }
            
            $nuevoLibro = Libros::nuevoLibro($datos);
            
            return json_encode($nuevoLibro);
        }else{
            return json_encode(array("codigo"=>100, "mensaje"=>"No se envio la estructura adecuada, consulte la guia de la API."));
        }
        
    }

    public function actionObtenerLibros()
    {

        $query = "";
        $categoria = "";
        $subcategoria = "";

        if(isset($_GET['q']) && !empty($_GET['q']))
        {
            $query = $_GET['q'];
        }

        if(isset($_GET['categoria']) && !empty($_GET['categoria']))
        {
            $categoria = $_GET['categoria'];    
        }

        if(isset($_GET['subcategoria']) && !empty($_GET['subcategoria']))
        {
            $subcategoria = $_GET['subcategoria'];
        }

        $datos = array("query" => $query, "categoria" => $categoria, "subcategoria"=>$subcategoria);

        $listadoLibros = Libros::obtenerLibros($datos);
        $listadoLibros = LibrosController::generarEstrucutraLibros($listadoLibros);

        return json_encode(array("codigo" => 0, "mensaje" => "", "data" => $listadoLibros));
    }

    public function generarEstrucutraLibros($libros)
    {
        $array = array();
        foreach($libros as $libro)
        {
            $index = null;
            $index['isbn'] = $libro['lib_isbn'];
            $index['titulo'] = $libro['lib_titulo'];
            $index['imagen'] = $libro['lib_imagen'];
            $index['descripcion'] = $libro['lib_descripcion'];
            $index['autores'] = $libro['lib_autores'];
            $index['edicion'] = $libro['lib_edicion'];
            $index['novedades'] = $libro['lib_novedades'];
            
            $fechaLanzamiento = ""; 
            if(!empty($libro['lib_fecha_lanzamiento']))
            {
                $fechaLanzamiento = date("d/m/Y", strtotime($libro['lib_fecha_lanzamiento']));
            }
            $index['fechaLanzamiento'] = $fechaLanzamiento;

            $index['idioma'] = $libro['lib_idioma'];
            $index['puntuacion'] = $libro['lib_puntuacion'];
            $vigente = "Si";
            if($libro['lib_vigente']=="N")
            {
                $vigente = "No";
            }
            $index['vigencia'] = $vigente;
            $categoriasLibros = LibrosCategorias::obtenerCategoriasSubCategorias($libro['lib_id']);
            $arrayCategorias = array();
            foreach($categoriasLibros as $cl)
            {
                $indexCat = null;
                $indexCat['categoria'] = $cl['cat_nombre'];
                $indexCat['subCategoria'] = $cl['subcat_nombre']; 
                array_push($arrayCategorias,$indexCat);
            }
            $index['categorias']  = $arrayCategorias;
            array_push($array,$index);
        }
        return $array;
    }

    public function actionObtenerTodosLibros()
    {
       
        $token = $_GET['token'];
        $verificacionToken = Tokens::verificarToken($token);
        if(is_numeric($verificacionToken))
        {
            $idUsuario = $verificacionToken;
            $modeloUsuario = Usuarios::find()->where(['usu_id'=>$idUsuario,'usu_tipo_usuario' => 1, 'usu_activo' => 'S'])->one();
            if(!empty($modeloUsuario))
            {

                $listadoLibros = Libros::obtenerLibros([],"M");
                $listadoLibros = LibrosController::generarEstrucutraLibros($listadoLibros);
        
                return json_encode(array("codigo" => 0, "mensaje" => "", "data" => $listadoLibros));
            }else{
                $respuesta = array("code"=>101,"msg"=>"El usuario no esta autorizado para la accion.");
            }
        }else{
            switch($verificacionToken)
            {
                case "NE":
                    $respuesta = array("code"=>100,"msg"=>"No existe o es incorrecto el token enviado.");
                break;
                case "EX":
                    $respuesta = array("code"=>101,"msg"=>"El token ya fue expirado.");
                break;
            }
        }
        return $respuesta;
    }

    public function actionObtenerLibro()
    {
        if(!isset($_GET['isbn']))
            return json_encode(['error' => true, 'error_tipo' => 1, 'error_mensaje' => 'no se envio isbn de libro']);

        $isbn = $_GET['isbn'];

        $sql = "SELECT *
                FROM libros
                WHERE lib_isbn = $isbn";
        $libro = \Yii::$app->db->createCommand($sql)->queryOne();  
        if ($libro == null)
            return json_encode(['error' => true, 'error_tipo' => 2, 'error_mensaje' => 'no existe libro con el isbn especificado']);
        
        return json_encode(["error" => false, "libro" => $libro]);
    }


}
