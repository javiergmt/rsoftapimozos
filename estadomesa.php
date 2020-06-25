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
    $est = $data->{'est'};

    $vacia = true;

    $sql = "Select NroMesa From En_MesaDet Where NroMesa = :nro";
    $resultado = $cid->prepare($sql);
    $resultado->execute(array(":nro"=>$nro));

    $totalrows = $resultado->fetch(PDO::FETCH_ASSOC);
    if ( $totalrows > 0 ) {
       $vacia = false;
    }  
    $resultado->closeCursor();

    if  ($est == 2 ) {
        // Accion de Bloquear la mesa
        if ( $vacia ) {
           // Agrego la mesa con Cerrada = 2 
           $now=time();
           $fechahora = strftime("%d/%m/%Y %H:%M",$now);
           
           $sql = "Insert INTO EN_MESA (NroMesa, IdMozo, Fecha, Cerrada, CantPersonas)";
           $sql .= " Values (:nro, :mozo, :fecha, :cerrada, :cant)";
                    
           try {

               $resultado = $cid->prepare($sql);
               $resultado->execute( array(":nro"=>$nro, ":mozo"=>0, ":fecha"=>$fechahora, ":cerrada"=>2, ":cant"=>0) );
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
               echo json_encode( "error al Bloquear mesa nueva ".$nro." - Error: ".$e->getLine() );
           }
    
          
        }  else {
           // Actualizo el estado a Cerrada = 2 
           $sql = "Update En_Mesa Set Cerrada = :est Where NroMesa = :nro";
    
           // echo $SQL;
    
           try {
               $resultado = $cid->prepare($sql);
               $resultado->execute( array(":est"=>$est, ":nro"=>$nro) );
               $resultado->closeCursor();
               header("HTTP/1.1 200 OK");
               header('Access-Control-Allow-Origin: *');
               header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
               header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
               echo json_encode( "Mesa ".$nro." Bloqueada" );
           } catch(Exception $e) {
               header("HTTP/1.1 200 OK");
               header('Access-Control-Allow-Origin: *');
               header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
               header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
               echo json_encode( "error al Bloquear mesa ".$nro." - ".$sql." - Error: ".$e->getLine() );   
           }
        }  
    } else { 
        // Desbloqueo de mesa Cerrada = 0
        if ( $vacia ) {
          // Esta vacia asi que Borro de En_Mesa el reg. creado
          $sql = "Delete En_Mesa Where NroMesa = :nro";
    
          // echo $SQL;
   
          try {
             $resultado = $cid->prepare($sql);
             $resultado->execute( array(":nro"=>$nro) );
             $resultado->closeCursor();
             header("HTTP/1.1 200 OK");
             header('Access-Control-Allow-Origin: *');
             header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
             header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
             echo json_encode( "Mesa Nueva ".$nro." Desbloqueada ".$SQL );
          } catch(Exception $e) {
             header("HTTP/1.1 200 OK");
             header('Access-Control-Allow-Origin: *');
             header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
             header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
             echo json_encode( "error al Desbloquear mesa nueva ".$nro." - ".$sql." - Error: ".$e->getLine() );   
          }
        } else {
          // Actualizo el estado a Cerrada = 0 
          $sql = "Update En_Mesa Set Cerrada = :est Where NroMesa = :nro";
    
          try {
             $resultado = $cid->prepare($sql);
             $resultado->execute( array(":est"=>$est, ":nro"=>$nro) );
             $resultado->closeCursor();
             header("HTTP/1.1 200 OK");
             header('Access-Control-Allow-Origin: *');
             header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
             header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
             echo json_encode( "Mesa ".$nro." Desbloqueada" );
          } catch(Exception $e) {
             header("HTTP/1.1 200 OK");
             header('Access-Control-Allow-Origin: *');
             header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
             header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
             echo json_encode( "error al Desbloquear mesa ".$nro." - ".$sql." - Error: ".$e->getLine() );   
          }
        }
    }
    exit();
	
}

//En caso de que ninguna de las opciones anteriores se haya ejecutado
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
header("HTTP/1.1 400 Bad Request");

?>