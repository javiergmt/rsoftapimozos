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
    $idplato = $data->{'idplato'};
    $cant = $data->{'cant'};
    $punit = $data->{'punit'};
    $obs = $data->{'obs'};
    $idtam = $data->{'idtam'};
    $tamanio = $data->{'tamanio'};
    $idmozo = $data->{'idmozo'};
    $esentrada = 0;
    if ( $data->{'esentrada'}  ) {
      $esentrada = 1;
    };
    $descrip = $data->{'descrip'};
    if ( $data->{'comanda'} ) {
      $comanda = 1; 
    } else {
      $comanda = 0;
    }

    $now=time();
    $fechahora = strftime("%d/%m/%Y %H:%M",$now);
    $hora = strftime("%H:%M",$now);
    $importe = $cant * $punit;
  
    $sql = "Insert EN_MESADET (NroMesa, idDetalle, idPlato, Cant, PcioUnit, Importe, ";
    $sql .= "Obs, idTamanio, Tamanio, Procesado, Hora, idMozo, Cocinado, EsEntrada, Descripcion, FechaHora, Comanda) ";
    $sql .= "Values( :nmesa, :iddet, :idplato, :cant, :punit, :importe, :obs, ";
    $sql .= " :idtam, :tamanio, :procesado, :hora, :idmozo, :cocinado, :esentrada, :descrip, :fechahora, :comanda)";
    
    //$sql .= "Values( $nmesa, $iddet, $idplato, $cant, $punit, $importe, '$obs', ";
    //$sql .= "$idtam, '$tamanio', 1, '$hora', $idmozo, 0, $esentrada, '$descrip', '$fechahora', $comanda)";
    // echo $SQL;
    try 
    {
      $resultado = $cid->prepare($sql);
      $resultado->execute( array(":nmesa"=>$nmesa, ":iddet"=>$iddet, ":idplato"=>$idplato, ":cant"=>$cant, ":punit"=>$punit,
                                 ":importe"=>$importe, ":obs"=>$obs, ":idtam"=>$idtam, ":tamanio"=>$tamanio, ":procesado"=>1,
                                 ":hora"=>$hora, ":idmozo"=>$idmozo, ":cocinado"=>0, ":esentrada"=>$esentrada, ":descrip"=>$descrip,
                                 ":fechahora"=>$fechahora, ":comanda"=>$comanda ) );
      $resultado->closeCursor();
    
      header("HTTP/1.1 200 OK");
      header('Access-Control-Allow-Origin: *');
      header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
      header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
      echo json_encode( "ok " );

    } catch(Exception $e)  {
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