<?php
include_once("data/dbparams.inc");
include_once("data/lib_data.inc");

$cid = conectar($db,$host,$usr,$pwd);

$data = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{

      if ( isset($_GET['idplato']) )
      {

        $idplato = $_GET['idplato'];
        $idsector = $_GET['idsector'];


        $sql = "EXEC sp_PlatosTamanios @idplato=? , @idsector=?"; 

        try {
            $resultado = $cid->prepare($sql);
            $resultado->bindParam(1,$idplato ,PDO::PARAM_INT);
            $resultado->bindParam(2,$idsector ,PDO::PARAM_INT);
            $resultado->execute();

            While ( $row=$resultado->fetch(PDO::FETCH_ASSOC)) {

                $item = array (
                'idPlato' => intval($row['idPlato']),
                'idTamanio' => intval($row['idTamanio']),
                'DescTam' => $row['DescTam']
                );  
                array_push($data,$item);
          
            }
            $resultado->closeCursor();
            header("HTTP/1.1 200 OK");
            header('Access-Control-Allow-Origin: *');
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
            echo json_encode( $data );
          
                
        } catch(Exception $e) {

            header("HTTP/1.1 200 OK");
            header('Access-Control-Allow-Origin: *');
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
            echo json_encode( "Error al listar tamanios - Error: ".$e->getLine() );

        }   
        exit(); 
      }
}
//En caso de que ninguna de las opciones anteriores se haya ejecutado
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
header("HTTP/1.1 400 Bad Request");


?>