<?php
include_once("data/dbparams.inc");
include_once("data/lib_data.inc");
ini_set('track_errors', 1);

$cid = conectar($host,$usr,$pwd);
selDB($db, $cid);

$data = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if ( isset($_GET['ip']) )
    {
       $ipimpre = $_GET['ip'];

       $saltosfin = 7;
       $imp_margen = 0;
       $imp_ancho = 40;
       $saltoscut = 7;

 
        if ( $handle = @fopen($ipimpre, "w") ) 
        {
            // imprimo encabezado
            fwrite($handle, chr(27). chr(64));//inicializacion
            fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,'################ INICIO ################') );
            fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
            fwrite($handle, chr(27). chr(33). chr(16));// letra Doble Alto
            fwrite($handle,ArmaSt('d',$imp_margen,$imp_ancho,'Comanda de Prueba'));
            fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
            fwrite($handle, chr(27). chr(33).chr(1)); // letra Normal
            fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,'Prueba Letra normal'));
            fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
            fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,str_repeat('-',40)));
            fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
            for($i=0;$i<10;$i++) {
                fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho," "));
                fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                };
            fwrite($handle, chr(27). chr(105));
            fclose($handle); // cierra el fichero PRN

            header("HTTP/1.1 200 " );
            header('Access-Control-Allow-Origin: *');
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
            echo json_encode( " ok " );
        } else {
            echo 'fopen error: '.$php_errormsg;
            header("HTTP/1.1 200 Error en Comandera " );
            header('Access-Control-Allow-Origin: *');
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
            echo json_encode( " Error en Comandera ". $ipimpre." ".$php_errormsg);
            // exit();  
        }
    } else {
    //En caso de que no esten bien los parametros
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
    header("HTTP/1.1 400 Bad Request Params");       
    } 
} else {
    //En caso de que ninguna de las opciones anteriores se haya ejecutado
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
    header("HTTP/1.1 400 Bad Request GET");
}


function ArmaSt($letra,$margen,$ancho,$st) {
    if ( $letra == 'd' ) {
       $ancho=$ancho*80/100;
       $margen=$margen*80/100;
    }
    $cst=str_repeat(' ',$margen).str_pad($st,$ancho-$margen,' ');
 
    return $cst;
 } 

 function PieComanda($handle, $imp_margen, $imp_ancho) {
    fwrite($handle, chr(27). chr(33).chr(1)); // letra Normal
    fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,str_repeat("-",40)));
    fwrite($handle, chr(27). chr(100). chr(1));//salto de linea

 }

?>