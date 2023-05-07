<?php

namespace app\controllers;

use app\models\Reservas;
use app\models\Usuarios;
use Yii;
use yii\web\ForbiddenHttpException;

class ReservasController extends \yii\rest\ActiveController
{
    public $modelClass = Reservas::class;

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action))
            return false;
        if ($action->id == 'delete') // Temporalmente bloqueando los delete
            throw new ForbiddenHttpException("No se puede eliminar reserva");
        if (in_array($action->id, ['create', 'update', 'delete']))
        {
            if (isset($this->request->bodyParams['resv_estado']) and !Usuarios::checkIfAdmin($this->request, $this->modelClass))
                throw new ForbiddenHttpException("Solo un administrador puede cambiar el estado de una reserva");
            if ($action->id == 'create' && Usuarios::checkPostAuth($this->request, $this->modelClass))
            {
                return true;
            }
            if (($action->id == 'update' || $action->id == 'delete') && Usuarios::checkPutDelAuth($this->request, $this->modelClass))
            {
                return true;
            }
            throw new ForbiddenHttpException("Bearer token no es valido o no existe administrador con ese token");
        }
        return true;
    }

}

?>