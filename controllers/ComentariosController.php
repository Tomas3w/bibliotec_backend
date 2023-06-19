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

    public static function respuestas_de_comentario($comentario_id)
    {
        $respuestas = Comentarios::findAll(['comet_padre_id' => $comentario_id, 'comet_vigente' => 'S']);
        for ($i = 0; $i < count($respuestas); $i++)
        {
            $respuestas[$i] = $respuestas[$i]->attributes;
            $respuestas[$i]['respuestas'] = static::respuestas_de_comentario($respuestas[$i]['comet_id']);
        }
        return $respuestas;
    }

    // toma comentarios y un booleano, si $es_arbol = true entonces retorna la version de arbol de comentarios
    // y sino, retorna los comentarios sin cambios
    public static function hacer_arbol($comentarios, $es_arbol)
    {
        if ($es_arbol)
        {
            for ($i = 0; $i < count($comentarios); $i++)
            {
                $comentarios[$i] = $comentarios[$i]->attributes;
                $comentarios[$i]['respuestas'] = static::respuestas_de_comentario($comentarios[$i]['comet_id']);
            }
        }
        return $comentarios;
    }

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
            $arbol = null;
            if (isset($datos['arbol']))
                $arbol = $datos['arbol'];
            return static::hacer_arbol($query->orderBy(['comet_fecha_hora' => SORT_DESC])->all(), $arbol);
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
                $this->modeloViejo = $this->modelClass::findIdentity($id);
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
            //$nombre_id = $this->modelClass::getNombreUsuID();
            //$id = $this->request->bodyParams[$nombre_id];
            $id = $result[$this->modelClass::getNombreID()];
            if ($this->modeloViejo != null)
                $json_atributos = json_encode($this->modeloViejo->attributes);
            else
                $json_atributos = "";
    
            $modeloNuevo = json_encode($this->modelClass::findIdentity($id)->attributes);
            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 1, $json_atributos, $modeloNuevo, "Creado ".$this->modelClass, Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id);
            LogAccion::nuevoLog("Creado " . $this->modelClass, $this->modelClass." creado con id: ".$id, $logAbm);
        }
        elseif ($action->id == 'update')
        {
            $id = $this->request->queryParams['id'];
            if ($this->modeloViejo != null)
                $json_atributos = json_encode($this->modeloViejo->attributes);
            else
                $json_atributos = "";
    
            $modeloNuevo = json_encode($this->modelClass::findIdentity($id)->attributes);
            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 2, $json_atributos, $modeloNuevo, "Actualizado ".$this->modelClass, Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id);
            LogAccion::nuevoLog("Actualizado " . $this->modelClass, $this->modelClass." actualizado con id: ".$id, $logAbm);
        }
        elseif ($action->id == 'delete')
        {
            $id = $this->request->queryParams['id'];
            if ($this->modeloViejo != null)
                $json_atributos = json_encode($this->modeloViejo->attributes);
            else
                $json_atributos = "";
            $modeloNuevo = json_encode([]);

            $logAbm = LogAbm::nuevoLog($this->modelClass::getTableSchema()->name, 3, $json_atributos, $modeloNuevo, "Eliminado ".$this->modelClass, Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id);
            LogAccion::nuevoLog("Eliminado " . $this->modelClass, $this->modelClass." eliminado con id: ".$id, $logAbm);
        }
        return $result;
    }

}

?>