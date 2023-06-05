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

            if (!isset($datos['nombre']) || empty($datos['nombre']) || !isset($datos['vigente']) || empty($datos['vigente']))
                return json_encode(array("codigo"=>2));

            $nombre = $datos['nombre'];
            $vigente = $datos['vigente'];
                    
            if ($vigente != 'S' && $vigente != 'N')
                return json_encode(array("codigo"=>2));
                
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>3));
                

            $categoria = Categorias::findOne(['cat_nombre' => $nombre]);
            if ($categoria != null)
                return json_encode(array("codigo"=>9));
                
            // GUARDAR NUEVA CATEGORIA
            $categoriaNueva = Categorias::nuevaCategoria($nombre, $vigente);

            // CREAR LOG
            $categoriaNuevaJson = null;
            $nombreTabla = Categorias::tableName();
            $categoriaNuevaJson = json_encode($categoriaNueva->attributes);
            $usu_id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id;
            $id_logAbm = LogAbm::nuevoLog($nombreTabla,1,NULL,$categoriaNuevaJson,"Crear categoria ".$nombre, $usu_id_admin);
            LogAccion::nuevoLog("Crear categoria","Creada categoria con nombre=".$nombre, $id_logAbm);

            return json_encode(array("codigo"=>1));
        }else{
            return json_encode(array("codigo"=>5));
        }
    }

    public function actionListado(){
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
            /*if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>3));
            */
            $categorias = Categorias::find()->all();

            $arrayCategorias = CategoriasController::generarEstructuraCategorias($categorias);
            return json_encode(array("codigo"=>0, "data"=>$arrayCategorias));
        }else{
            return json_encode(array("codigo"=>5));
        }
    }

    public function generarEstructuraCategorias($categorias){
        
        $array = array();
        foreach($categorias as $categoria)
        {
            $index = null;
            $index['id'] = $categoria['cat_id'];
            $index['nombre'] = $categoria['cat_nombre'];
            $index['vigente'] = $categoria['cat_vigente'];
            array_push($array,$index);
        }
        return $array;
    }

    public function actionUpdate(){

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $datos = $this->request->bodyParams;

            if (!isset($datos['id']) || empty($datos['id']) || !isset($datos['nombre']) || empty($datos['nombre']) )
                return json_encode(array("codigo"=>2));

            $id = $datos['id'];
            $nuevo_nombre = $datos['nombre'];
            
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>3));

            $categoria = Categorias::findOne(['cat_id' => $id]);
            if ($categoria == null)
                return json_encode(array("codigo"=>4));

            if ($categoria->cat_nombre == $nuevo_nombre)
                return json_encode(array("codigo"=>9));
            
        
            $categoriaViejo = null;
            $categoriaNuevo = null;
            $nombreTabla = Categorias::tableName();
            $categoriaViejo = json_encode($categoria->attributes);
            $categoria->cat_nombre = $nuevo_nombre;
            $categoria->save();
            $categoriaNuevo = json_encode($categoria->attributes);
            
            $usu_id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id; // Obtener el id del admin para luego guardar quien hizo la baja

            $id_logAbm = LogAbm::nuevoLog($nombreTabla,2,$categoriaViejo,$categoriaNuevo,"Modificar categoria", $usu_id_admin);
            LogAccion::nuevoLog("Modificar categoria","Modificada categoria con id=".$id, $id_logAbm);

            return json_encode(array("codigo"=>1));       
        }else{
            return json_encode(array("codigo"=>5));
        }
    }

    public function actionDelete(){

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $id = $this->request->queryParams['id'];

            if (!isset($id) || empty($id))
                return json_encode(array("codigo"=>2));
            
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>3));
            
            $categoria = Categorias::findOne(['cat_id' => $id]);
            if ($categoria == null)
                return json_encode(array("codigo"=>4));
            if ($categoria->cat_vigente == "N")
                return json_encode(array("codigo"=>9));
            
        
            $categoriaViejo = null;
            $categoriaNuevo = null;
            $nombreTabla = Categorias::tableName();
            $categoriaViejo = json_encode($categoria->attributes);
            $categoria->cat_vigente = "N";
            $categoria->save();
            $categoriaNuevo = json_encode($categoria->attributes);
            
            $id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id; // Obtener el id del admin para luego guardar quien hizo la baja

            $id_logAbm = LogAbm::nuevoLog($nombreTabla,3,$categoriaViejo,$categoriaNuevo,"Baja categoria", $id_admin);
            LogAccion::nuevoLog("Baja categoria","Baja categoria con id=".$id, $id_logAbm);

            return json_encode(array("codigo"=>1));       
        }else{
            return json_encode(array("codigo"=>5));
        }
    }

}
