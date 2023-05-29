<?php

namespace app\controllers;
use Yii;
use app\models\Usuarios;
use app\models\LogAbm;
use app\models\LogAccion;
use yii\rest\ActiveController;


class UsuariosController extends \yii\web\Controller
{   
    public $modelClass = 'app\models\Usuarios';
    public $enableCsrfValidation = false;
    
    public function actionDelete(){

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $datos = $this->request->bodyParams;
            $usu_id = $datos['usu_id'];
            $motivoBaja = $datos['motivoBaja'];


            if (!isset($usu_id) || empty($usu_id))
                return json_encode(array("error"=>true, "error_tipo"=>0, "mensaje"=>"No se ha enviado el 'usu_id'."));
            if (!isset($motivoBaja) || empty($motivoBaja))
                return json_encode(array("error"=>true, "error_tipo"=>1, "mensaje"=>"No se ha enviado el 'motivoBaja'."));
            
            // COMPROBAR SI EL TOKEN ES DE UN USUARIO ADMIN
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("error"=>true, "error_tipo"=>2, "mensaje"=>"El token no corresponde a un administrador o no se a enviado."));
            
            // COMPROBAR SI EL USUARIO QUE SE ENVIO EN EL CAMPO usu_id EXISTE
            //$usuario = Usuarios::findIdentity($usu_id);
            $usuario = Usuarios::findOne(['usu_id' => $usu_id]);
            if ($usuario == null)
                return json_encode(array("error"=>true, "error_tipo"=>3, "mensaje"=>"El usu_id proporcionado no corresponde a ningun usuario"));
            
            if ($usuario->usu_habilitado == "N")
                return json_encode(array("error"=>true, "error_tipo"=>4, "mensaje"=>"El usu_id=".$usu_id." ya se encuentra dado de baja."));
            
            
            // DAR BAJA
            $usuarioViejo = null;
            $usuarioNuevo = null;
            $nombreTabla = Usuarios::tableName();
            $usuarioViejo = json_encode($usuario->attributes);
            $usuario->usu_habilitado = "N"; // Se modifica el atributo usu_habilitado.
            $usuario->save(); // Se guardan los nuevos cambios.
            $usuarioNuevo = json_encode($usuario->attributes);
            
            $usu_id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id; // Obtener el id del admin para luego guardar quien hizo la baja

            $id_logAbm = LogAbm::nuevoLog($nombreTabla,2,$usuarioViejo,$usuarioNuevo,$motivoBaja, $usu_id_admin);
            LogAccion::nuevoLog("Baja usuario","ID Usuario:$usu_id \nMotivo baja:".$motivoBaja, $id_logAbm);

            return json_encode(array("error"=>false,"mensaje"=>"Usuario dado de baja"));       
        }
    }

    public function actionListadoUsuarioshabilitados(){
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            
            // COMPROBAR SI EL TOKEN ES DE UN USUARIO ADMIN
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("error"=>true, "error_tipo"=>0, "mensaje"=>"El token no corresponde a un administrador o no se a enviado"));
           
                //usuario = Usuarios::findOne(['usu_id' => $usu_id]);
            $usuarioshabilitados = Usuarios::findAll(['usu_habilitado' => 'S']);

            $arrayUsuarios = UsuariosController::generarEstructuraUsuarioshabilitados($usuarioshabilitados);
            return json_encode(array("error"=>false, "mensaje" => "Todos los usuarios habilitados", "data" => $arrayUsuarios));
        }
    }

    public function generarEstructuraUsuarioshabilitados($usuarios){
        
        $array = array();
        foreach($usuarios as $usuario)
        {
            $index = null;
            $index['usu_id'] = $usuario['usu_id'];
            $index['usu_documento'] = $usuario['usu_documento'];
            $index['usu_nombre'] = $usuario['usu_nombre'];
            $index['usu_apellido'] = $usuario['usu_apellido'];
            $index['usu_mail'] = $usuario['usu_mail'];
            $index['usu_telefono'] = $usuario['usu_telefono'];
            $index['usu_tipo_usuario'] = $usuario['usu_tipo_usuario'];

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
