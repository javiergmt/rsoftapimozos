<?php
include_once("data/dbparams.inc");
include_once("data/lib_data.inc");

$cid = conectar($db,$host,$usr,$pwd);

$data = array();



if ($_SERVER['REQUEST_METHOD'] == 'GET')
{

    $sql  = "Select isnull(idCubiertos,0) as idCubiertos, PedirCubiertos, ISNULL(P.DescCorta,'') as CubDesc";
    $sql .= " From Parametros R"; 
    $sql .= " Left Outer Join PLATOS P On P.idPlato = R.idCubiertos";

    try {
        $resultado = $cid->prepare($sql);
        $resultado->execute();

        While ( $row=$resultado->fetch(PDO::FETCH_ASSOC)) {

            $precio = 0 ;
            if ( $row['idCubiertos'] > 0 ) { 
                $nplato = $row['idCubiertos'];
                $idtam = 0;
                $idsector = 2;
                $now=time();
                $hora = strftime("%H:%M",$now);

                $sql = "EXEC sp_DaPrecio @nplato=?, @idtam=?, @idsector=?, @hora=?";
                try 
                {
                    $result = $cid->prepare($sql);
                    $result->bindParam(1,$nplato ,PDO::PARAM_INT);
                    $result->bindParam(2,$idtam ,PDO::PARAM_INT);
                    $result->bindParam(3,$idsector ,PDO::PARAM_INT);
                    $result->bindParam(4,$hora ,PDO::PARAM_STR);
                    $result->execute();

                    if ( $row1 = $result->fetch(PDO::FETCH_ASSOC) ) {
                        $precio = $row1['Precio'];
                    }  
                } catch(Exception $e) {
                    $precio=0 ;
                }
                $result->closeCursor(); 
            }
            $item = array (
            'idCubiertos' => intval($row['idCubiertos']),
            'PedirCubiertos' => intval($row['PedirCubiertos']),
            'CubDesc' => $row['CubDesc'],
            'CubPrecio' => floatval($precio)
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
        echo json_encode( "Error traer parametros - Error: ".$e->getLine() );

     }    
     exit();
}
//En caso de que ninguna de las opciones anteriores se haya ejecutado
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
header("HTTP/1.1 400 Bad Request");


?>