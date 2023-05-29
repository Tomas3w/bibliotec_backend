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
    public function actionCrear(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->request->bodyParams;
            $subcat_cat_id = $datos['subcat_cat_id'];
            $subcat_nombre = $datos['subcat_nombre'];
            $subcat_vigente = $datos['subcat_vigente'];

            if (!isset($subcat_cat_id) || empty($subcat_cat_id))
                return json_encode(array("error"=>true, "error_tipo"=>0,"mensaje"=>"No se ha enviado el 'subcat_cat_id'."));
            if (!isset($subcat_nombre) || empty($subcat_nombre))
                return json_encode(array("error"=>true, "error_tipo"=>1,"mensaje"=>"No se ha enviado el 'subcat_nombre'."));
            if (!isset($subcat_vigente) || empty($subcat_vigente))
                return json_encode(array("error"=>true, "error_tipo"=>2,"mensaje"=>"No se ha enviado el 'subcat_vigente'."));
            if ($subcat_vigente != 'S' && $subcat_vigente != 'N')
                return json_encode(array("error"=>true, "error_tipo"=>3,"mensaje"=>"Vigente puede ser 'S' o 'N'."));
            if (!Usuarios::checkIfAdmin($this->request, $this->modelClass))
                return json_encode(array("codigo"=>true, "error_tipo"=>4,"mensaje"=>"El token no corresponde a un administrador o no se ha enviado."));

            $categoria = Categorias::findOne(['cat_id' => $subcat_cat_id]);
            if ($categoria == null)
                return json_encode(array("error"=>true, "error_tipo"=>5, "mensaje"=>"La categoria con id '".$subcat_cat_id."' no esta creada.".$subcat_cat_id));
           
                $subcategoria = SubCategorias::findOne(['subcat_nombre' => $subcat_nombre, 'subcat_cat_id' => $subcat_cat_id]);
            if ($subcategoria != null)
                return json_encode(array("error"=>true, "error_tipo"=>6, "mensaje"=>"La subcategoria con nombre '".$subcat_nombre."' ya esta creada para la categoria con id '".$subcat_cat_id."'"));

            // GUARDAR NUEVA SUBCATEGORIA
            $subcategoriaNueva = SubCategorias::nuevaSubCategoria($subcat_cat_id, $subcat_nombre, $subcat_vigente);

            // CREAR LOG
            $subcategoriaNuevaJson = null;
            $nombreTabla = SubCategorias::tableName();
            $subcategoriaNuevaJson = json_encode($subcategoriaNueva->attributes);
            $usu_id_admin = Usuarios::findIdentityByAccessToken(Usuarios::getTokenFromHeaders($this->request->headers))->usu_id;
            $id_logAbm = LogAbm::nuevoLog($nombreTabla,1,NULL,$subcategoriaNuevaJson,"Nueva subcategoria ".$subcat_cat_id, $usu_id_admin);
            LogAccion::nuevoLog("Nueva subcategoria","Nueva subcategoria '".$subcat_nombre." Agregada a la categoria con id'". $subcat_cat_id."'", $id_logAbm);

            return json_encode(array("error"=>"false","mensaje"=>"Nueva subcategoria creada correctamente."));
        }
    }

}
