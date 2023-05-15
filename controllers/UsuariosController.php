<?php

namespace app\controllers;
use Yii;
use app\models\Usuarios;

class UsuariosController extends \yii\web\Controller
{   
    public $modelClass = 'app\models\Usuarios';
    public $enableCsrfValidation = false;
    
    public function actionBajaUsuario(){
        /**
         * Se da de baja al usuario con el usu_id que reciba esta funcion
         * Falta:
         *      Token: Falta un token para saber si es un administrador que requiere esta acción.
         */
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $datos = json_decode(file_get_contents('php://input'));
            
            if(Usuarios::bajaUsuario($datos->usu_id, $datos->motivoBaja)){
                return json_encode(array("codigo"=>0,"mensaje"=>"Usuario dado de baja"));
            }else{
                return json_encode(array("codigo"=>100,"mensaje"=>"No"));
            }
        }
    }


    public function actionObtenerUsuarioshabilitados(){
            /**
            * Listar los usuario habilitados para que el administrador los vea y de baja el que requiera.
            * Falta:
            *      Token: Falta un token para saber si es un administrador que requiere esta acción.
            */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $listaUsuarios = Usuarios::obtenerUsuarioshabilitados();
            $listaUsuarios = UsuariosController::generarEstructuaUsuarioshabilitados($listaUsuarios);
            return json_encode(array("codigo" => 0, "mensaje" => "", "data" => $listaUsuarios));
        }
    }

    public function generarEstructuaUsuarioshabilitados($usuarios){
        
        $array = array();
        foreach($usuarios as $usuario)
        {
            $index = null;
            $index['id'] = $usuario['usu_id'];
            $index['documento'] = $usuario['usu_documento'];
            $index['nombre'] = $usuario['usu_nombre'];
            $index['apellido'] = $usuario['usu_apellido'];
            $index['mail'] = $usuario['usu_mail'];
            $index['telefono'] = $usuario['usu_telefono'];

            array_push($array,$index);
        }
        return $array;
    }  

    public function actionLogin()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST') //Solo se envia por POST el login
        {
            if(!isset($_POST['documento']) || empty($_POST['documento']))
            {
                return json_encode(array("codigo"=>101, "mensaje"=>"El documento es obligatorio para el inicio de sesion"));
            }

            if(!isset($_POST['clave']) || empty($_POST['clave']))
            {
                return json_encode(array("codigo"=> 102, "mensaje"=>"La clave es obligatoria para el inicio de sesion"));
            }

            $login = Usuarios::login($_POST['documento'],$_POST['clave']);

            return $login;

        }else{
            return json_encode(array("codigo"=> 100, "mensaje"=> "Solamente se puede realizar la peticion por metodo POST."));
        }
    }

    public function actionRegistro()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST') //Solo se envia por POST el login
        {

            if(!isset($_POST['documento']) || empty($_POST['documento']))
            {
                return json_encode(array("codigo"=> 101, "mensaje"=> "El documento es obligatorio"));
            }else if(!Usuarios::getvalidarCedula($_POST['documento'])){
                return json_encode(array("codigo"=> 104, "mensaje"=> "El documento es no es valido"));
            }
            
            if(!isset($_POST['nombre']) || empty($_POST['nombre']))
            {
                return json_encode(array("codigo"=> 102, "mensaje"=> "El nombre es obligatorio"));
            }
            
            if(!isset($_POST['apellido']) || empty($_POST['apellido']))
            {
                return json_encode(array("codigo"=> 103, "mensaje"=> "El apellido es obligatorio"));
            }
            
            
            if(!isset($_POST['mail']) || empty($_POST['mail']))
            {
                return json_encode(array("codigo"=> 105, "mensaje"=> "El mail es obligatorio"));
            }else if(!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL))
            {
                return json_encode(array("codigo"=> 106, "mensaje"=> "El mail es incorrecto"));
            }

            if(!isset($_POST['clave']) || empty($_POST['clave']))
            {
                return json_encode(array("codigo"=> 107, "mensaje"=> "La clave es obligatoria"));
            }
            
            $registro = Usuarios::registro($_POST);
            
            return $registro;
        }else{
            return json_encode(array("codigo"=> 100, "mensaje"=> "Solamente se puede realizar la peticion por metodo POST."));
        }
    }

    public function actionPrueba()
    {
        $a = $_GET['a'];
        echo uniqid();
        echo "<br>";
        echo "<br>";
        echo date("Y-m-d H:i:s");
        echo "<br>";
        echo "<br>";
        echo Yii::$app->getSecurity()->generatePasswordHash("admin");
    }
    
}
