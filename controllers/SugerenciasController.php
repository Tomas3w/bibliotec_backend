<?php

namespace app\controllers;

use app\models\Sugerencias;
use app\models\Libros;
use app\models\Usuarios;
use app\models\LogAbm;
use app\models\LogAccion;
use Yii;
use yii\web\ForbiddenHttpException;

class SugerenciasController extends \yii\rest\ActiveController
{
    public $modelClass = Sugerencias::class;
    public $modeloViejo;

    public function actionModificarEstado()
    {
        $this->modeloViejo = null;
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $datos = $this->request->bodyParams;

            if (!isset($datos['sug_id']))
                return ['error' => true, 'error_tipo' => 0, 'error_mensaje' => 'No se paso sug_id'];
            if (!isset($datos['sug_vigente']))
                return ['error' => true, 'error_tipo' => 1, 'error_mensaje' => 'No se paso sug_vigente con el estado nuevo'];
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return ['error' => true, 'error_tipo' => 2, 'error_mensaje' => 'Solo administradores pueden modificar sugerencias'];

            $sugerencia = Sugerencias::findOne(['sug_id' => $datos['sug_id']]);
            if ($sugerencia == null)
                return ['error' => true, 'error_tipo' => 3, 'error_mensaje' => 'No existe la sugerencia a modificar'];
            $sugerencia->sug_vigente = $datos['sug_vigente'];
            $sugerencia->save();
            $this->modeloViejo = $sugerencia;

            return ['error' => false];
        }
        else
            return ['error' => true, 'error_tipo' => 4, 'error_mensaje' => 'Este endpoint funciona solo con el metodo PUT'];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action))
            return false;
        if ($action->id == 'modificar-estado')
        {
            if (isset($this->request->bodyParams['sug_id']))
            {
                $id = $this->request->bodyParams['sug_id'];
                $this->modeloViejo = json_encode($this->modelClass::findIdentity($id));
            }
            else
                $this->modeloViejo = null;
            return true;
        }
        if (in_array($action->id, ['create', 'view', 'index']))
        {
            if (isset($this->request->bodyParams['sug_vigente']))
                throw new ForbiddenHttpException("sug_vigente deberia ser cambiado (o creado) con el endpoint sugerencias-estado");
            if ($action->id == 'view' || $action->id == 'index')
                return true;
            if ($action->id == 'create' && Usuarios::checkPostAuth($this->request, $this->modelClass))
                return true;
            throw new ForbiddenHttpException("Bearer token no es valido o no existe administrador con ese token [puede ser que no se haya especificado ".$this->modelClass::getNombreUsuID()."]");
        }
        else
            return false;
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
        if ($action->id == 'modificar-estado' && $this->modeloViejo != null)
        {
            $nombre_id = $this->modelClass::getNombreID();
            $id = $this->request->bodyParams[$nombre_id];
            if ($this->modeloViejo != null)
                $json_atributos = json_encode($this->modeloViejo->attributes);
            else
                $json_atributos = "";
    
            $modeloNuevo = json_encode($this->modelClass::findIdentity($id)->attributes);
            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 2, $json_atributos, $modeloNuevo, "Actualizado ".$this->modelClass, Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id);
            LogAccion::nuevoLog("Actualizado " . $this->modelClass, $this->modelClass." actualizado con id: ".$id, $logAbm);
        }
        return $result;
    }

}

?>