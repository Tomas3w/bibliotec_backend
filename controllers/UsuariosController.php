<?php

namespace app\controllers;

use app\models\Usuarios;
use app\models\LogAccion;
use app\models\LogAbm;
use Yii;
use yii\web\Response;
use yii\helpers\Json;

class UsuariosController extends \yii\web\Controller
{   
    public $modelClass = 'app\models\Usuarios';
    public $enableCsrfValidation = false;

    public function actionAltaUsuario(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = \Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            $body = file_get_contents('php://input');
            $datos = Json::decode($body, true);

            $obj = new Usuarios();
            $obj->attributes = $datos;
            if (!$obj->validate())
                return ['error' => true, 'error_tipo' => 1, 'error_mensaje' => $obj->getErrors()];
            if ((isset($datos['usu_token']) || isset($datos['usu_activo']) || isset($datos['usu_habilitado']) || isset($datos['usu_tipo_usuario']) || isset($datos['usu_token'])) && !Usuarios::checkIfAdmin($this->request, $this->modelClass))
            {
                return ['error' => true, 'error_tipo' => 2, 'error_mensaje' => 'Solo administradores puden crear usuarios con los atributos: usu_token, usu_activo, usu_habilitado, usu_tipo_usuario o usu_token'];
            }
            $obj->save();

            $logAbm = LogAbm::nuevoLog("Usuarios", 1, null, $obj, "Se creo un nuevo usuario");
            LogAccion::nuevoLog("Crear usuario", "Esperando confirmacion por correo");
            return ['error' => false];
        }
    }
    
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
}
