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

    // public function behaviors()
    // {
    //     $behaviors = parent::behaviors();
    //     return $behaviors;
    // }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action))
            return false;
        echo "algo";
        if (in_array($action, ['post']))
        {
            if (Usuarios::checkAuth($this->request, $this->modelClass))
                return true;
            throw new ForbiddenHttpException("Bearer token no es valido para el usuario con esa id");
        }
        return true;
    }

}

?>