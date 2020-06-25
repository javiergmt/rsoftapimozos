<?php
include_once("data/dbparams.inc");
include_once("data/lib_data.inc");

$cid = conectar($db,$host,$usr,$pwd);

$data = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{

      if (isset($_GET['id']))
      {
        $id = intval($_GET['id']);
        $sql = "EXEC sp_DetMesa @nmesa=?";
        $resultado = $cid->prepare($sql);
        $resultado->bindParam(1,$id ,PDO::PARAM_INT);
        $resultado->execute();

        if (!$resultado) {
            echo("Error en la consulta");
            header("HTTP/1.1 400 Bad Request");
        } else {

            While ( $row=$resultado->fetch(PDO::FETCH_ASSOC)) {
                $item = array (
                'idDetalle' => intval($row['IdDetalle']),
                'NroMesa' => intval($row['NroMesa']),
                'idPlato' => intval($row['idPlato']),
                'Cant' => intval($row['Cant']),
                'Descripcion' => $row['Descripcion'],
                'PcioUnit' => floatval($row['PcioUnit']),
                'Nuevo' => intval($row['Nuevo']),
                'idTamanio' => intval($row['idTamanio']),
                'Tamanio' => $row['Tamanio'],
                'idMozo' => intval($row['idTamanio']),
                'Obs' => $row['Obs'],
                'esEntrada' => intval($row['EsEntrada'])
                );
                array_push($data,$item);

            }
            $resultado->closeCursor();
            header("HTTP/1.1 200 OK");
        }
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        echo json_encode( $data );
        exit();


      }
}
//En caso de que ninguna de las opciones anteriores se haya ejecutado
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("HTTP/1.1 400 Bad Request");


?>
