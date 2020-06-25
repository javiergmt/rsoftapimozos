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
  
    $nmesa = $data->{'nmesa'};
    $iddet = $data->{'iddet'};
    $idcombina = $data->{'idcombina'};
    $descrip = $data->{'descrip'};
    $pcio = $data->{'pcio'};
    
    
    $sql = "Insert EN_MESADET_COMBINA (NroMesa, idDetalle, idPlatoCombina, Descripcion, Precio) ";
    $sql .= "Values( :nmesa, :iddet, :idcombina, :descrip, :pcio)";
    
    try {

      $resultado = $cid->prepare($sql);
      $resultado->execute( array(":nmesa"=>$nmesa, ":iddet"=>$iddet, ":idcombina"=>$idcombina, ":descrip"=>$descrip, ":pcio"=>$pcio) );
      $resultado->closeCursor();
      header("HTTP/1.1 200 OK");
      header('Access-Control-Allow-Origin: *');
      header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
      header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
      echo json_encode( "ok " );

    } catch(Exception $e) {
      header("HTTP/1.1 200 OK");
      header('Access-Control-Allow-Origin: *');
      header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
      header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
      echo json_encode( "error grabadet ".$sql." - Error: ".$e->getLine() );
    }
    exit();
	
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
header("HTTP/1.1 400 Bad Request");

?>