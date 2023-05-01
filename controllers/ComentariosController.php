<?php

namespace app\controllers;

use app\models\Comentarios;
use app\models\Usuarios;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\behaviors\BlameableBehavior;
use yii\web\ForbiddenHttpException;

class ComentariosController extends \yii\rest\ActiveController
{
    public $modelClass = Comentarios::class;

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action))
            return false;
        if (in_array($action->id, ['create', 'update', 'delete']))
        {
            if ($action->id == 'create' && Usuarios::checkPostAuth($this->request, $this->modelClass))
                return true;
            if (($action->id == 'update' || $action->id == 'delete') && Usuarios::checkPutDelAuth($this->request, $this->modelClass))
                return true;
            throw new ForbiddenHttpException("Bearer token no es valido para el usuario con esa id");
        }
        return true;
    }

}

?>