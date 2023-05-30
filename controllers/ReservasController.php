<?php

namespace app\controllers;

use app\models\Reservas;
use app\models\Usuarios;
use app\models\LogAbm;
use app\models\LogAccion;
use app\models\Tokens;
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
            throw new ForbiddenHttpException("Bearer token no es valido o no existe administrador con ese token [puede ser que no se haya especificado el id de ".$this->modelClass.']');
        }
        return true;
    }

    public function actionMisReservas()
    {
        $token = $_GET['token'];
        $verificacionToken = Tokens::verificarToken($token);
        
        $respuesta = array("code"=>102,"msg"=>"Error a la hora de obtener las reservas");

        if(is_numeric($verificacionToken))
        {
            $idUsuario = $verificacionToken;
            $misReservas = Reservas::obtenerReservas($idUsuario);
            $misReservas = ReservasController::generarEstructuraReservas($misReservas);
            $respuesta = array("code"=>0,"msg"=>"Reservas obtenidas correctamente","datos"=>array('reservas'=>$misReservas));
        }else{
            switch($verificacionToken)
            {
                case "NE":
                    $respuesta = array("code"=>100,"msg"=>"No existe o es incorrecto el token enviado.");
                break;
                case "EX":
                    $respuesta = array("code"=>101,"msg"=>"El token ya fue expirado.");
                break;
            }
        }
        
        return $respuesta;
    }

    public function generarEstructuraReservas($datos)
    {
        $array = array();
        foreach($datos as $dato)
        {
            $index = null;
            $index['nroReserva'] = $dato['resv_id'];
            $index['tituloLibro'] = $dato['lib_titulo'];
            $index['fechaDesde'] = date("d/m/Y",strtotime($dato['resv_fecha_desde']));
            $index['fechaHasta'] = date("d/m/Y",strtotime($dato['resv_fecha_hasta']));
            $index['fechaRealizado'] = date("d/m/Y",strtotime($dato['resv_fecha_hora']));
            $estadoReserva = "Error";
            switch($dato['resv_estado'])
            {
                case "X":
                    $estadoReserva = "Cancelado";
                break;
                case "P":
                    $estadoReserva = "Pendiente";
                break;
                case "C":
                    $estadoReserva = "Confirmado";
                break;
                case "L":
                    $estadoReserva = "Levantado";
                break;
                case "D":
                    $estadoReserva = "Devuelto";
                break;
                case "N":
                    $estadoReserva = "No devuelto";
                break;
            }   
            $index['estadoLetra'] = $dato['resv_estado'];
            $index['estado'] = $estadoReserva;

            array_push($array,$index);
        }
        return $array;
    }

      /**
     * Para poder cancelar la reserva se tiene que enviar el id de la reserva y el motivo por el cual se quiere cancelar la reserva.
     * 
     * Se envia por metodo DELETE, pero se toma los datos por metodo GET, es decir por la URL
     * 
     */
    public function actionCancelarReserva()
    {
        if(!isset($_GET['idReserva']) || empty($_GET['idReserva']))
        {
            return json_encode(array("codigo"=>100,"mensaje"=>"El id de la reserva es un dato obligatorio."));
        }

        if(!isset($_GET['motivoCancelacion']) || empty($_GET['motivoCancelacion']))
        {
            return json_encode(array("codigo"=>101,"mensaje"=>"El motivo de la cancelacion no puede ser vacio."));
        }      
        $idReserva = $_GET['idReserva'];
        $motivoCancelacion = $_GET['motivoCancelacion'];
    
        $estadoReserva = Reservas::obtenerEstadoReserva($idReserva);

        if($estadoReserva == "P" || $estadoReserva == "C")
        {
            Reservas::cancelarReserva($idReserva, $motivoCancelacion);
            return json_encode(array("codigo"=>0,"mensaje"=>"Se cancelo correctamente la reserva"));
        }else{
            return json_encode(array("codigo"=>102,"mensaje"=>"No se puede cancelar la reserva, solamente se puede cancelar si esta en pediente o ya confirmada la reserva."));
        }
    }

}

?>