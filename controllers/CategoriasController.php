<?php

namespace app\controllers;
use app\models\Categorias;
use app\models\Usuarios;
use app\models\LogAbm;
use app\models\LogAccion;

class CategoriasController extends \yii\web\Controller
{
    public $modelClass = 'app\models\Categorias';
    public $enableCsrfValidation = false;
    /*
    public function actionIndex()
    {
        return $this->render('index');
    }
    */

    public function actionCreate(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->request->bodyParams;
            $cat_nombre = $datos['cat_nombre'];
            $cat_vigente = $datos['cat_vigente'];

            if (!isset($cat_nombre) || empty($cat_nombre))
                return json_encode(array("error"=>true, "error_tipo"=>0,"mensaje"=>"No se ha enviado el 'cat_nombre'."));
            if (!isset($cat_vigente) || empty($cat_vigente))
                return json_encode(array("error"=>true, "error_tipo"=>1,"mensaje"=>"No se ha enviado el 'cat_vigente'."));
            if ($cat_vigente != 'S' && $cat_vigente != 'N')
                return json_encode(array("error"=>true, "error_tipo"=>2,"mensaje"=>"Vigente puede ser 'S' o 'N'."));
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>true, "error_tipo"=>3,"mensaje"=>"El token no corresponde a un administrador o no se ha enviado."));

            $categoria = Categorias::findOne(['cat_nombre' => $cat_nombre]);
            if ($categoria != null)
                return json_encode(array("error"=>true, "error_tipo"=>4, "mensaje"=>"La categoria con nombre '".$cat_nombre."' ya esta creada."));

            // GUARDAR NUEVA CATEGORIA
            $categoriaNueva = Categorias::nuevaCategoria($cat_nombre, $cat_vigente);

            // CREAR LOG
            $categoriaNuevaJson = null;
            $nombreTabla = Categorias::tableName();
            $categoriaNuevaJson = json_encode($categoriaNueva->attributes);
            $usu_id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id;
            $id_logAbm = LogAbm::nuevoLog($nombreTabla,1,NULL,$categoriaNuevaJson,"Nueva categoria ".$cat_nombre, $usu_id_admin);
            LogAccion::nuevoLog("Nueva categoria","Nueva categoria '".$cat_nombre."' agregada", $id_logAbm);

            return json_encode(array("error"=>"false","mensaje"=>"Nueva categoria creada correctamente."));
        }
    }

    public function actionMostrarCategorias(){
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            // COMPROBAR SI EL TOKEN ES DE UN USUARIO?
           // if (!Usuarios::checkIfUser($this->request, $this->modelClass))
               // return json_encode(array("error"=>true, "error_tipo"=>0, "mensaje"=>"El token no corresponde a un usuario o no se a enviado"));
            
            $categorias = Categorias::findAll(['cat_vigente' => 'S']);

            $arrayCategorias = CategoriasController::generarEstructuraCategorias($categorias);
            return json_encode(array("error"=>false, "mensaje" => "Todos las categorias vigentes", "data" => $arrayCategorias));
        }
    }

    public function generarEstructuraCategorias($categorias){
        
        $array = array();
        foreach($categorias as $categoria)
        {
            $index = null;
            $index['usu_id'] = $categoria['cat_id'];
            $index['usu_nombre'] = $categoria['cat_nombre'];
            array_push($array,$index);
        }
        return $array;
    }

    public function actionUpdate(){

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $datos = $this->request->bodyParams;
            $cat_id = $datos['cat_id'];
            $cat_nombre = $datos['cat_nombre'];
            $cat_vigente = $datos['cat_vigente'];


            if (!isset($cat_id) || empty($cat_id))
                return json_encode(array("error"=>true, "error_tipo"=>0, "mensaje"=>"No se ha enviado el 'cat_id'."));
            if (!isset($cat_nombre) || empty($cat_nombre))
                return json_encode(array("error"=>true, "error_tipo"=>1, "mensaje"=>"No se ha enviado el 'cat_nombre'."));
            if (!isset($cat_vigente) || empty($cat_vigente))
                return json_encode(array("error"=>true, "error_tipo"=>2, "mensaje"=>"No se ha enviado el 'cat_vigente'."));
            if ($cat_vigente != 'S' && $cat_vigente != 'N')
                return json_encode(array("error"=>true, "error_tipo"=>3,"mensaje"=>"Vigente puede ser 'S' o 'N'."));
            
            // COMPROBAR SI EL TOKEN ES DE UN USUARIO ADMIN
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("error"=>true, "error_tipo"=>4, "mensaje"=>"El token no corresponde a un administrador o no se a enviado."));
            
            // COMPROBAR SI EL id QUE SE ENVIO EN EL CAMPO cat_id EXISTE
            $categoria = Categorias::findOne(['cat_id' => $cat_id]);
            if ($categoria == null)
                return json_encode(array("error"=>true, "error_tipo"=>5, "mensaje"=>"El cat_id proporcionado no corresponde a ninguna categoria"));
            
        
            // Actualizar
            $categoriaViejo = null;
            $categoriaNuevo = null;
            $nombreTabla = Categorias::tableName();
            $categoriaViejo = json_encode($categoria->attributes);
            $categoria->cat_nombre = $cat_nombre;
            $categoria->cat_vigente = $cat_vigente;
            $categoria->save();
            $categoriaNuevo = json_encode($categoria->attributes);
            
            $usu_id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id; // Obtener el id del admin para luego guardar quien hizo la baja

            $id_logAbm = LogAbm::nuevoLog($nombreTabla,2,$categoriaViejo,$categoriaNuevo,"Update categoria", $usu_id_admin);
            LogAccion::nuevoLog("Update categoria","Update categoria con id: ".$cat_id, $id_logAbm);

            return json_encode(array("error"=>false,"mensaje"=>"Categoria actualizada."));       
        }
    }

}
