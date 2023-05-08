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

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action))
            return false;
        if (in_array($action->id, ['create', 'update', 'delete']))
        {
            if ($action->id == 'create' && Usuarios::checkPostAuth($this->request, $this->modelClass))
            {
                $modeloViejo = null;
                return true;
            }
            if (($action->id == 'update' || $action->id == 'delete') && Usuarios::checkPutDelAuth($this->request, $this->modelClass))
            {
                $nombre_id = $this->modelClass::getNombreUsuID();
                $id = $this->request->queryParams['id'];
                $modeloViejo = json_encode($this->modelClass::findIdentity($id));
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
    
            $modeloNuevo = json_encode($this->modelClass::findIdentity($id));
            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 1, $this->modeloViejo, $modeloNuevo, "Creado ".$this->modelClass);
            LogAccion::nuevoLog("Creado " . $this->modelClass, $this->modelClass." creado con id: ".$id, $logAbm);
        }
        elseif ($action->id == 'update')
        {
            $nombre_id = $this->modelClass::getNombreUsuID();
            $id = $this->request->queryParams['id'];
    
            $modeloNuevo = json_encode($this->modelClass::findIdentity($id));
            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 1, $this->modeloViejo, $modeloNuevo, "Actualizado ".$this->modelClass);
            LogAccion::nuevoLog("Actualizado " . $this->modelClass, $this->modelClass." actualizado con id: ".$id, $logAbm);
        }
        elseif ($action->id == 'delete')
        {
            $nombre_id = $this->modelClass::getNombreUsuID();
            $id = $this->request->queryParams[$nombre_id];
    
            $modeloNuevo = json_encode($this->modelClass::findIdentity($id));
            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 1, $this->modeloViejo, $modeloNuevo, "Eliminado ".$this->modelClass);
            LogAccion::nuevoLog("Eliminado " . $this->modelClass, $this->modelClass." eliminado con id: ".$id, $logAbm);
        }
        return $result;
    }

}

?>