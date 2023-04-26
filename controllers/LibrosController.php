<?php

namespace app\controllers;

use app\models\Libros;

class LibrosController extends \yii\web\ActiveController
{
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
    public function actionAltaLibro()
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
            
            if(!isset($datos['url']) || empty($datos['url']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"La URL del libro es obligatoria."));
            }

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

}
