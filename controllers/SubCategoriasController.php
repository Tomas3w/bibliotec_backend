<?php

namespace app\controllers;
use app\models\Categorias;
use app\models\SubCategorias;
use app\models\Usuarios;
use app\models\LogAbm;
use app\models\LogAccion;

class SubCategoriasController extends \yii\web\Controller
{
    public $modelClass = 'app\models\SubCategorias';
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

            if (!isset($datos['id_categoria']) || empty($datos['id_categoria']) || !isset($datos['nombre']) || empty($datos['nombre']) || !isset($datos['vigente']) || empty($datos['vigente']) )
                return json_encode(array("codigo"=>2));
            if (($datos['vigente'] != 'S' && $datos['vigente'] != 'N'))
                return json_encode(array("codigo"=>2));

            $id_categoria = $datos['id_categoria'];
            $nombre = $datos['nombre'];
            $vigente = $datos['vigente'];

            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>3));

            $categoria = Categorias::findOne(['cat_id' => $id_categoria]);
            if ($categoria == null)
                return json_encode(array("codigo"=>4));

            if ($categoria->cat_vigente == "N")
                return json_encode(array("codigo"=>12));
           
            $subcategoria = SubCategorias::findOne(['subcat_nombre' => $nombre, 'subcat_cat_id' => $id_categoria]);
            if ($subcategoria != null)
                return json_encode(array("codigo"=>9));

            // GUARDAR NUEVA SUBCATEGORIA
            $subcategoriaNueva = SubCategorias::nuevaSubCategoria($id_categoria, $nombre, $vigente);

            // CREAR LOG
            $subcategoriaNuevaJson = null;
            $nombreTabla = SubCategorias::tableName();
            $subcategoriaNuevaJson = json_encode($subcategoriaNueva->attributes);
            $usu_id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id;
            $id_logAbm = LogAbm::nuevoLog($nombreTabla,1,NULL,$subcategoriaNuevaJson,"Crear subcategoria ".$nombre, $usu_id_admin);
            LogAccion::nuevoLog("Crear subcategoria","Creada subcategoria con nombre='".$nombre, $id_logAbm);

            return json_encode(array("codigo"=>1));
        }else{
            return json_encode(array("codigo"=>5));
        }
    }

    public function actionListado(){
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>3));
            
            $subcategorias = SubCategorias::find()->all();

            $arraySubCategorias = SubCategoriasController::generarEstructuraListado($subcategorias);
            return json_encode(array("codigo"=>0, "data"=>$arraySubCategorias));
        }else{
            return json_encode(array("codigo"=>5));
        }
    }

    public function generarEstructuraListado($subcategorias){
        
        $array = array();
        foreach($subcategorias as $subcategoria)
        {
            $index = null;
            $index['id'] = $subcategoria['subcat_id'];
            $index['id_categoria'] = $subcategoria['subcat_cat_id'];
            $index['nombre'] = $subcategoria['subcat_nombre'];
            $index['vigente'] = $subcategoria['subcat_vigente'];
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

            $subcategoria = SubCategorias::findOne(['subcat_id' => $id]);
            if ($subcategoria == null)
                return json_encode(array("codigo"=>4));

            $subcategorias = SubCategorias::findAll(['subcat_cat_id' => $subcategoria->subcat_cat_id]);
            foreach($subcategorias as $subcategoria_aux){
                if ($subcategoria_aux->subcat_nombre == $nuevo_nombre)
                    return json_encode(array("codigo"=>9)); 
            }
        
            $subcategoriaViejo = null;
            $subcategoriaNuevo = null;
            $nombreTabla = SubCategorias::tableName();
            $subcategoriaViejo = json_encode($subcategoria->attributes);
            $subcategoria->subcat_nombre = $nuevo_nombre;
            $subcategoria->save();
            $subcategoriaNuevo = json_encode($subcategoria->attributes);
            
            $usu_id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id; // Obtener el id del admin para luego guardar quien hizo la baja

            $id_logAbm = LogAbm::nuevoLog($nombreTabla,2,$subcategoriaViejo,$subcategoriaNuevo,"Modificar subcategoria", $usu_id_admin);
            LogAccion::nuevoLog("Modificar subcategoria","Modificada subcategoria con id=".$id, $id_logAbm);

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
            
            $subcategoria = SubCategorias::findOne(['subcat_id' => $id]);
            if ($subcategoria == null)
                return json_encode(array("codigo"=>4));
            if ($subcategoria->subcat_vigente == "N")
                return json_encode(array("codigo"=>9));
            
        
            $subcategoriaViejo = null;
            $subcategoriaNuevo = null;
            $nombreTabla = SubCategorias::tableName();
            $subcategoriaViejo = json_encode($subcategoria->attributes);
            $subcategoria->subcat_vigente = "N";
            $subcategoria->save();
            $subcategoriaNuevo = json_encode($subcategoria->attributes);
            
            $id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id; // Obtener el id del admin para luego guardar quien hizo la baja

            $id_logAbm = LogAbm::nuevoLog($nombreTabla,3,$subcategoriaViejo,$subcategoriaNuevo,"Baja subcategoria", $id_admin);
            LogAccion::nuevoLog("Baja subcategoria","Baja subcategoria con id=".$id, $id_logAbm);

            return json_encode(array("codigo"=>1));       
        }else{
            return json_encode(array("codigo"=>5));
        }
    }

}
