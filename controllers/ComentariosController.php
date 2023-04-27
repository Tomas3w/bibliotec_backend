<?php

namespace app\controllers;

use app\models\Comentarios;
use Yii;
use yii\filters\auth\HttpBearerAuth;

class ComentariosController extends \yii\rest\ActiveController
{
    public $modelClass = Comentarios::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // $behaviors['authenticator']['authMethods'] = [
        //     HttpBearerAuth::class
        // ];

        return $behaviors;
    }
}

?>