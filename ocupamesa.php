<?php
include_once("data/dbparams.inc");
include_once("data/lib_data.inc");

$cid = conectar($db,$host,$usr,$pwd);

$data = array();


// POST para actualizar 

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $input = $_POST;
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    $nro = $data->{'nro'};
    $mozo = $data->{'mozo'};
    $pers = $data->{'pers'};

    $now=time();
    $fechahora = strftime("%d/%m/%Y %H:%M",$now);
    
    // $SQL = "Insert EN_MESA (NroMesa, IdMozo, Fecha, Cerrada, CantPersonas)";
    // $SQL .= " Values ($nro, $mozo, '$fechahora', 0, $pers)";
      
    $sql = "Update EN_MESA Set IdMozo = :mozo , Cerrada = :cerrada, CantPersonas = :pers ,Fecha = :fechahora ";
    $sql .= " Where NroMesa = :nro";

    // echo $SQL;
 
    try {
    
      $resultado = $cid->prepare($sql);
      $resultado->execute( array(":mozo"=>$mozo, ":cerrada"=>0, ":pers"=>$pers, ":fechahora"=>$fechahora, ":nro"=>$nro) );
      $resultado->closeCursor();
      header("HTTP/1.1 200 OK");
      header('Access-Control-Allow-Origin: *');
      header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
      header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
      echo json_encode( "Mesa Nueva ".$nro." Bloqueada" );


    } catch(Exception $e) {

      header("HTTP/1.1 200 OK");
      header('Access-Control-Allow-Origin: *');
      header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
      header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
      echo json_encode( "Error al ocupar mesa".$nro." - Error: ".$e->getLine() );
    }
    exit();
	
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
header("HTTP/1.1 400 Bad Request");

?>