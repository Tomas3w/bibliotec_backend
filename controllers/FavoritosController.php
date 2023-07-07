<?php

namespace app\controllers;

use app\models\Favoritos;
use app\models\Libros;
use app\models\Usuarios;
use app\models\LogAbm;
use app\models\LogAccion;
use app\models\Tokens;
use Yii;
use yii\web\ForbiddenHttpException;

class FavoritosController extends \yii\rest\ActiveController
{
    public $modelClass = Favoritos::class;

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action))
            return false;
        if (in_array($action->id, ['create', 'view', 'index', 'delete']))
        {
            if ($action->id == 'view' || $action->id == 'index')
                return true;
            if ($action->id == 'create' && Usuarios::checkPostAuth($this->request, $this->modelClass))
                return true;
            if ($action->id == 'delete' && Usuarios::checkPutDelAuth($this->request, $this->modelClass))
                return true;
            throw new ForbiddenHttpException("Bearer token no es valido o no existe administrador con ese token [puede ser que no se haya especificado el id de ".$this->modelClass."]");
        }
        return true;
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        if ($action->id == 'create')
        {
            // $nombre_id = $this->modelClass::getNombreUsuID();
            // $id = $this->request->bodyParams[$nombre_id];
            $id = $result[$this->modelClass::getNombreID()];
    
            $modeloNuevo = json_encode($this->modelClass::findIdentity($id)->attributes);
            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 1, null, $modeloNuevo, "Creado ".$this->modelClass, Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id);
            LogAccion::nuevoLog("Creado " . $this->modelClass, $this->modelClass." creado con id: ".$id, $logAbm);
        }
        elseif ($action->id == 'delete')
        {
            $id = $this->request->queryParams['id'];
            $modeloNuevo = json_encode([]);

            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 1, null, $modeloNuevo, "Eliminado ".$this->modelClass, Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id);
            LogAccion::nuevoLog("Eliminado " . $this->modelClass, $this->modelClass." eliminado con id: ".$id, $logAbm);
        }
        return $result;
    }

    public function actionObtenerFavoritos()
    {
        //header('Access-Control-Allow-Origin: *');
        //header("Content-type: application/json; charset=utf-8");
        if(isset($_GET['token']) && !empty($_GET['token']))
        {
            $tokenUsuario = $_GET['token'];
            $verificacionToken = Tokens::verificarToken($tokenUsuario);
            if(is_numeric($verificacionToken))
            {
                /** $verificacionToken cuanod es numerico es el id del usuario */
                $favoritos = Favoritos::obtenerLibrosFavoritos($verificacionToken);
                $favoritos = LibrosController::generarEstrucutraLibros($favoritos, "S");
                $respuesta = array("code"=>0,"msg"=>"Favoritos obtenidos con exito", "data"=>$favoritos);
            }else{
                switch($verificacionToken)
                {
                    case "NE":
                        $respuesta = array("code"=>100,"msg"=>"No existe o es incorrecto el token enviado.");
                    break;
                    case "EX":
                        $respuesta = array("code"=>101,"msg"=>"El token ya fue expirado.");
                    break;//a
                }
            }
            return $respuesta;
        }else{
            return array("code"=>100,"msg"=>"El token es obligatorio");
        }
    }

    public function actionQuitar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            
            $usu_id = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id;

            $lib_id = $this->request->queryParams['id'];
        
            if (!isset($lib_id) || empty($lib_id))
                return array("codigo" => 2, 'mensaje' => 'id es obligatorio');
                       
            $favorito = Favoritos::findOne(['fav_usu_id' => $usu_id, 'fav_lib_id' => $lib_id]);
            if ($favorito == null)
                return array("codigo" => 3, 'mensaje' => 'No se encontró el favorito');
        
            $favoritoViejo = null;
            $favoritoViejo = json_encode($favorito->attributes);
            $id_aux = $favorito->fav_id;
            $favorito->delete();
        
            $id_logAbm = LogAbm::nuevoLog(Favoritos::tableName(), 3, $favoritoViejo, null, "Eliminado favorito", $usu_id);
            LogAccion::nuevoLog("Eliminado favorito", "Eliminado favorito con id=" . $id_aux, $id_logAbm);
            return array("codigo" => 4, 'mensaje' => 'Se eliminó con éxito');
        }else{
            return array("codigo" => 5, 'mensaje' => 'Metodo http incorrecto');
        }
    }
    
    


}   

?>