<?php
include_once("data/dbparams.inc");
include_once("data/lib_data.inc");

$cid = conectar($db,$host,$usr,$pwd);

$data = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{

    if ( isset($_GET['oper']) )
    {
        // B: Busqueda x Contenido de @cadena
	    // F: Favoritos
	    // R: Perteneciente al rubro @nRubro
	    // S: Perteneciente al rubro @nRubro y @nSubrubro

        $oper = $_GET['oper'];
        $cadena = $_GET['cadena'];
        $rub = $_GET['rub'];
        $subrub = $_GET['subrub'];

        $sql = "EXEC sp_Platos @cOper=?, @cCadena=?, @nRubro=?, @nSubRubro=?";
       

        try {
            $resultado = $cid->prepare($sql);
            $resultado->bindParam(1, $oper, PDO::PARAM_STR);
            $resultado->bindParam(2, $cadena, PDO::PARAM_STR);
            $resultado->bindParam(3, $rub, PDO::PARAM_INT);
            $resultado->bindParam(4, $subrub, PDO::PARAM_INT);
            $resultado->execute();
            While ( $row=$resultado->fetch(PDO::FETCH_ASSOC)) {

                $item = array (
                'idPlato' => intval($row['idPlato']),
                'DescCorta' => $row['DescCorta'],
                'Precio' => floatval($row['Precio']),
                'TamanioUnico' => intval($row['TamanioUnico']),
                'idTipoConsumo' => $row['idTipoConsumo'],
                'idRubro' => intval($row['idRubro']),
                'idSubRubro' => intval($row['idSubRubro']),
                'cantgustos' => intval($row['cantgustos'])
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
            echo json_encode( "Error al calcular prercio de ".$nplato." - Error: ".$e->getLine() );
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
