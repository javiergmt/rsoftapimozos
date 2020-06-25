<?php
include_once("data/dbparams.inc");
include_once("data/lib_data.inc");

$cid = conectar($db,$host,$usr,$pwd);

$data = array();


if ($_SERVER['REQUEST_METHOD'] == 'GET')
{

    $sql = "SELECT * FROM Sectores where IdSector > 1 ORDER BY idSector ";
    $resultado = $cid->prepare($sql);
    $resultado->execute();

    try 
    {
        $resultado = $cid->prepare($sql);
        $resultado->execute();
        While( $row=$resultado->fetch(PDO::FETCH_ASSOC)) {

            $item = array (
            'idSector' => intval($row['idSector']),
            'descripcion' => $row['Descripcion']
            );
            array_push($data,$item);
            
        }
        $resultado->closeCursor();
        header("HTTP/1.1 200 OK");
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        echo json_encode( $data );

    }  catch(Exception $e) {
               
        header("HTTP/1.1 200 OK");
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');   
        echo json_encode( "Error al listar Sectores - Error: ".$e->getLine() );

    }

    exit();

}


//En caso de que ninguna de las opciones anteriores se haya ejecutado
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("HTTP/1.1 400 Bad Request");

?>
