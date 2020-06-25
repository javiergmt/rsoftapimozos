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
    $idsec = $data->{'idsec'};
    $idplato = $data->{'idplato'};
    $cant = $data->{'cant'};
    $idtam = $data->{'idtam'};
    if ( $data->{'comanda'} ) {
      $comanda = 1; 
    } else {
      $comanda = 0;
    }

    $now=time();
    $fechahora = strftime("%d/%m/%Y %H:%M",$now);
    
    $sql = "Insert EN_MESADET_COMBOS (NroMesa, idDetalle, idSeccion, idPlato, Cant, Procesado, idTamanio, Obs, Cocinado, FechaHora, Comanda) ";
    $sql .= "Values( :nmesa, :iddet, :idsec, :idplato, :cant, :procesado, :idtam, :obs, :cocinado, :fechahora, :comanda)";
    
    try {

      $resultado = $cid->prepare($sql);
      $resultado->execute( array(":nmesa"=>$nmesa, ":iddet"=>$iddet, "idsec"=>$idsec, ":idplato"=>$idplato, 
                           ":cant"=>$cant, ":procesado"=>1, ":idtam"=>$idtam, ":obs"=>'', ":cocinado"=>0, 
                           ":fechahora"=>$fechahora, ":comanda"=>$comanda ) );
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
      echo json_encode( "error grabadet ".$sql." - Error: ".$e->getLine()  );
    }
    exit();
	
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
header("HTTP/1.1 400 Bad Request");

?>