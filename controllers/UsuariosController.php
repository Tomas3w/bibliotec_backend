<?php

namespace app\controllers;
use Yii;
use app\models\Usuarios;
use app\models\Tokens;
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

            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>3));

            if (!isset($datos['id']) || empty($datos['id']) || !isset($datos['motivo']) || empty($datos['motivo']))
                return json_encode(array("codigo"=>2));

            $id = $datos['id'];
            $motivo = $datos['motivo'];
        
            $usuario = Usuarios::findOne(['usu_id' => $id]);
            if ($usuario == null)
                return json_encode(array("codigo"=>4));
            
            if ($usuario->usu_habilitado == "N")
                return json_encode(array("codigo"=>9));
            
            // DAR BAJA
            $usuarioViejo = null;
            $usuarioNuevo = null;
            $nombreTabla = Usuarios::tableName();
            $usuarioViejo = json_encode($usuario->attributes);
            $usuario->usu_habilitado = "N"; // Se modifica el atributo usu_habilitado.
            $usuario->save(); // Se guardan los nuevos cambios.
            $usuarioNuevo = json_encode($usuario->attributes);
            
            $usu_id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id; // Obtener el id del admin para luego guardar quien hizo la baja

            $id_logAbm = LogAbm::nuevoLog($nombreTabla,3,$usuarioViejo,$usuarioNuevo,$motivo, $usu_id_admin);
            LogAccion::nuevoLog("Baja usuario","ID Usuario:$id \nMotivo baja:".$motivo, $id_logAbm);

            return json_encode(array("codigo"=>1));       
        }else{
            return json_encode(array("codigo"=>5));
        }
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

    public function actionListado(){
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>3));
            $usuarios = Usuarios::find()->all();
            $usuarioshabilitados = Usuarios::findAll(['usu_habilitado' => 'S']);

            $arrayUsuarios = UsuariosController::generarEstructuraListado($usuarios);
            return json_encode(array("codigo"=>0, "data"=>$arrayUsuarios));
        }else{
            return json_encode(array("codigo"=>5));
        }
    }

    public function generarEstructuraListado($usuarios){
        
        $array = array();
        foreach($usuarios as $usuario)
        {
            $index = null;
            $index['id'] = $usuario['usu_id'];
            $index['documento'] = $usuario['usu_documento'];
            $index['nombre'] = $usuario['usu_nombre'];
            $index['apellido'] = $usuario['usu_apellido'];
            $index['mail'] = $usuario['usu_mail'];
            $index['clave'] = $usuario['usu_clave'];
            $index['telefono'] = $usuario['usu_telefono'];
            $index['activo'] = $usuario['usu_activo'];
            $index['tipo_usuario'] = $usuario['usu_tipo_usuario'];
            $index['habilitado'] = $usuario['usu_habilitado'];
            // $index['token'] = $usuario['usu_token'];

            array_push($array,$index);
        }
        return $array;
    }

    public function actionFind(){
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $documento = $this->request->queryParams['doc'];
                       
            /*
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>3));
            */
            if (!isset($documento) || empty($documento) )
                return json_encode(array("codigo"=>2));
        
            $usuario = Usuarios::findOne(['usu_documento' => $documento]);
            if ($usuario == null)
                return json_encode(array("codigo"=>4));
            
            $array = array();
            $index = null;
            $index['id'] = $usuario['usu_id'];
            $index['documento'] = $usuario['usu_documento'];
            $index['nombre'] = $usuario['usu_nombre'];
            $index['apellido'] = $usuario['usu_apellido'];
            $index['mail'] = $usuario['usu_mail'];
            $index['clave'] = $usuario['usu_clave'];
            $index['telefono'] = $usuario['usu_telefono'];
            $index['activo'] = $usuario['usu_activo'];
            $index['tipo_usuario'] = $usuario['usu_tipo_usuario'];
            $index['habilitado'] = $usuario['usu_habilitado'];
            array_push($array,$index);

            return json_encode(array("codigo"=>0, "data"=>$array));
        }else{
            return json_encode(array("codigo"=>5));
        }
    }
    
    public function actionTokenSigueValido()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($this->request->queryParams['token']))
                return json_encode(['error' => true, 'error_tipo' => 1, 'error_mensaje' => 'Debe especificarse un token']);
            $token_v = $this->request->queryParams['token'];
            $token = Tokens::findOne(['tk_token' => $token_v]);
            if ($token == null)
                return json_encode(['error' => true, 'error_tipo' => 2, 'error_mensaje' => 'Token no existe']);
            return json_encode(['error' => false, 'ha_expirado' => (Tokens::verificarToken($token) == 'EX')]);
        }
        else {
            return json_encode(['error' => true, 'error_tipo' => 3, 'error_mensaje' => 'El metodo HTTP debe ser GET.']);
        }
    }

    public function actionUpdate(){

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $datos = $this->request->bodyParams;

            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>2, 'mensaje' => 'Debe ser un usuario administrador'));


            if (!isset($datos['id']) || empty($datos['id']))
                return json_encode(array("codigo"=>3, 'mensaje' => 'El id es obligatorio'));

            $id = $datos['id'];
            $usuario = Usuarios::findOne(['usu_id' => $id]);

            if ($usuario == null)
                return json_encode(array("codigo"=>4, 'mensaje' => 'Usuario no encontrado'));

            $usuarioModeloViejo = json_encode($usuario->attributes);
            $bandera = false;

            if (isset($datos['nombre'])){
                if(empty($datos['nombre'])){
                    return json_encode(array("codigo"=>5, 'mensaje' => 'El atributo nombre esta vacio'));
                }else{
                    $usuario->usu_nombre = $datos['nombre'];
                    $bandera = true;
                }
            }   

            if (isset($datos['apellido'])){
                if(empty($datos['apellido'])){
                    return json_encode(array("codigo"=>6, 'mensaje' => 'El atributo apellido esta vacio'));
                }else{
                    $usuario->usu_apellido = $datos['apellido'];
                    $bandera = true;
                }
            }

            if (isset($datos['mail'])){
                if(empty($datos['mail'])){
                    return json_encode(array("codigo"=>7, 'mensaje' => 'El atributo mail esta vacio'));
                }else{
                    if (!filter_var($datos['mail'], FILTER_VALIDATE_EMAIL)){
                        return json_encode(array("codigo"=> 71, 'mensaje' => 'El mail es incorrecto'));
                        // El mail es incorrecto
                    }else{
                        $usuario->usu_mail = $datos['mail'];
                        $bandera = true;
                    }
 
                }
            } 
            
            if (isset($datos['clave'])){
                if(empty($datos['clave'])){
                    return json_encode(array("codigo"=>8, 'mensaje' => 'El atributo clave esta vacio'));
                }else{
                    $usuario->usu_clave = Yii::$app->getSecurity()->generatePasswordHash($datos['clave']);
                    $bandera = true;
                }
            } 
            
            if (isset($datos['telefono'])){
                if(empty($datos['telefono'])){
                    return json_encode(array("codigo"=>9, 'mensaje' => 'El atributo telefono esta vacio'));
                }else{
                    $usuario->usu_telefono = $datos['telefono'];
                    $bandera = true;
                }
            } 
            
            if (isset($datos['activo'])){
                if(empty($datos['activo'])){
                    return json_encode(array("codigo"=>10, 'mensaje' => 'El atributo activo esta vacio'));
                }else{
                    if (($datos['activo'] != 'S' && $datos['activo'] != 'N')){
                        return json_encode(array("codigo"=>101, 'mensaje' => 'El atributo activo no cumple la especificacion'));
                    }else{
                        $usuario->usu_activo = $datos['activo'];
                        $bandera = true;
                    }
                          
                }
            }

            if (isset($datos['tipo'])){
                if(empty($datos['tipo'])){
                    return json_encode(array("codigo"=>11, 'mensaje' => 'El atributo tipo esta vacio'));
                }else{
                    if (($datos['tipo'] != 1 && $datos['tipo'] != 0)){
                        return json_encode(array("codigo"=>111, 'mensaje' => 'El atributo tipo no cumple la especificacion'));
                    }else{
                        $usuario->usu_tipo_usuario = $datos['tipo'];
                        $bandera = true;
                    }
                          
                }
            } 

            if ($bandera){
                $usuario->save();
            }else{
                return json_encode(array("codigo"=>12, 'mensaje' => 'Debe pasar algun atributo para modificarlo'));
            }

            $usuarioModeloNuevo = json_encode($usuario->attributes);
            $nombreTabla = Usuarios::tableName();
                  
            $usu_id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id; // Obtener el id del admin para luego guardar quien hizo la baja

            $id_logAbm = LogAbm::nuevoLog($nombreTabla,2,$usuarioModeloViejo,$usuarioModeloNuevo,"Modificar usuario", $usu_id_admin);
            LogAccion::nuevoLog("Modificar usuario","Modificado usuarios con id=".$id, $id_logAbm);

            return json_encode(array("codigo"=>13, 'mensaje' => 'Se a modificado con exito'));      
        }else{
            return json_encode(array("codigo"=>14, 'mensaje' => 'El metodo no es el correcto'));   
        }
    }

    public function actionActivar(){

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $id = $this->request->queryParams['id'];

            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>2, 'mensaje' => 'Debe ser un usuario administrador'));


            if (!isset($id) || empty($id))
                return json_encode(array("codigo"=>3, 'mensaje' => 'El id es obligatorio'));

            $usuario = Usuarios::findOne(['usu_id' => $id]);

            if ($usuario == null)
                return json_encode(array("codigo"=>4, 'mensaje' => 'Usuario no encontrado'));
            if ($usuario->usu_habilitado == "S")
                return json_encode(array("codigo"=>5, 'mensaje' => 'La el usuario ya esta activado'));

            $usuarioModeloViejo = json_encode($usuario->attributes);
            $usuario->usu_habilitado = "S";
            $usuario->save();
            $usuarioModeloNuevo = json_encode($usuario->attributes);             
            $usu_id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id;
            $id_logAbm = LogAbm::nuevoLog(Usuarios::tableName(),4,$usuarioModeloViejo,$usuarioModeloNuevo,"Activar usuario", $usu_id_admin);
            LogAccion::nuevoLog("Activar usuario","Activar usuario con id=".$id, $id_logAbm);

            return json_encode(array("codigo"=>6, 'mensaje' => 'Se a activado con exito'));      
        }else{
            return json_encode(array("codigo"=>7, 'mensaje' => 'El metodo no es el correcto'));   
        }
    }
}
