<?php

namespace app\controllers;

use app\models\LogAbm;
use app\models\LogAccion;
use app\models\Comentarios;
use app\models\Usuarios;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\behaviors\BlameableBehavior;
use yii\web\ForbiddenHttpException;

class ComentariosController extends \yii\rest\ActiveController
{
    public $modelClass = Comentarios::class;

    public $modeloViejo;

    public function actionVigentes()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $datos = $this->request->bodyParams;
            if (!isset($datos['lib_id']))
                return ['error' => true, 'error_tipo' => 0, 'error_mensaje' => 'Falta atributo lib_id'];
            $comentarios = null;

            $comet_id = null;
            if (isset($datos['comet_id']))
                $comet_id = $datos['comet_id'];
            $query = Comentarios::find()
                    ->where(['comet_lib_id' => $datos['lib_id'], 'comet_padre_id' => $comet_id, 'comet_vigente' => 'S']);
            if (isset($datos['page']) && isset($datos['per-page']))
            {
                $query = $query
                    ->offset(($datos['page'] - 1) * $datos['per-page'])
                    ->limit($datos['per-page']);
            }
            return $query->orderBy(['comet_fecha_hora' => SORT_DESC])->all();
        }
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action))
            return false;
        if (in_array($action->id, ['create', 'update', 'delete']))
        {
            if ($action->id == 'create' && Usuarios::checkPostAuth($this->request, $this->modelClass))
            {
                $this->modeloViejo = null;
                return true;
            }
            if (($action->id == 'update' || $action->id == 'delete') && Usuarios::checkPutDelAuth($this->request, $this->modelClass))
            {
                $nombre_id = $this->modelClass::getNombreUsuID();
                $id = $this->request->queryParams['id'];
                $this->modeloViejo = json_encode($this->modelClass::findIdentity($id));
                return true;
            }
            throw new ForbiddenHttpException("Bearer token no es valido para el usuario con esa id");
        }
        return true;
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        if ($action->id == 'create')
        {
            $nombre_id = $this->modelClass::getNombreUsuID();
            $id = $this->request->bodyParams[$nombre_id];
    
            $modeloNuevo = json_encode($this->modelClass::findIdentity($id)->attributes);
            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 1, json_encode($this->modeloViejo->attributes), $modeloNuevo, "Creado ".$this->modelClass, Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id);
            LogAccion::nuevoLog("Creado " . $this->modelClass, $this->modelClass." creado con id: ".$id, $logAbm);
        }
        elseif ($action->id == 'update')
        {
            $id = $this->request->queryParams['id'];
    
            $modeloNuevo = json_encode($this->modelClass::findIdentity($id)->attributes);
            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 2, json_encode($this->modeloViejo->attributes), $modeloNuevo, "Actualizado ".$this->modelClass, Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id);
            LogAccion::nuevoLog("Actualizado " . $this->modelClass, $this->modelClass." actualizado con id: ".$id, $logAbm);
        }
        elseif ($action->id == 'delete')
        {
            $id = $this->request->queryParams['id'];
            $modeloNuevo = json_encode([]);

            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 3, json_encode($this->modeloViejo->attributes), $modeloNuevo, "Eliminado ".$this->modelClass, Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id);
            LogAccion::nuevoLog("Eliminado " . $this->modelClass, $this->modelClass." eliminado con id: ".$id, $logAbm);
        }
        return $result;
    }

}

?>