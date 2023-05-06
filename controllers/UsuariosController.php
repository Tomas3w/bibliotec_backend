<?php

namespace app\controllers;

use app\models\Usuarios;

class UsuariosController extends \yii\web\Controller
{   
    /**
     * .../web/usuarios/baja-usuario
     * usuario = UsuarioController
     * baja-usuario = actionBajaUsuario
     */

    public $modelClass = 'app\models\Usuarios';
    public $enableCsrfValidation = false;
    
    public function actionIndex()
    {
        //echo 'hola!';
    }

    public function actionBajaUsuario(){
        /**
         * Tiene que ser un administrador para poder realizar esta accion 
         * Entonces hay que recibir algun tipo de autenticación de que es un administrador pero no se como.
         * 
         * Y el debo colocar un motivo por el que se da de baja pero no esta en la base de datos.
         */
         
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $datos = json_decode(file_get_contents('php://input'));

            if(Usuarios::bajaUsuario($datos->usu_id, $datos->causa)){
                return json_encode(array("codigo"=>0,"mensaje"=>"Usuario dado de baja"));
            }else{
                return json_encode(array("codigo"=>100,"mensaje"=>"No"));
            }
        }
    }

    /**
     * Listar los usuario habilitados para que el administrador los vea y de de baja el que quiere.
     */
    public function actionObtenerUsuariosHabilitados(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //$datos = json_decode(file_get_contents('php://input'));
            
            $listaUsuarios = Usuarios::obtenerUsuariosHabilitados();
            $listaUsuarios = UsuariosController::generarEstructuaUsuariosHabilitados($listaUsuarios);
            return json_encode(array("codigo" => 0, "mensaje" => "", "data" => $listaUsuarios));
        }

    }

    public function generarEstructuaUsuariosHabilitados($usuarios){
        
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
