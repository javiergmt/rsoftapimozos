<?php
include_once("data/dbparams.inc");
include_once("data/lib_data.inc");

$cid = conectar($db,$host,$usr,$pwd);

$data = array();

$lb = '\r\n';

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $input = $_POST;
    $json = file_get_contents('php://input');
    $data = json_decode($json);
  
    $nmesa = $data->{'nmesa'};
    $idetdesde = $data->{'idetdesde'};
    $idethasta = $data->{'idethasta'};

    // Obtengo datos de parametros de comandas
    $sql = "Select * From Parametros_Comandas";
   
    try {
        $resultado = $cid->prepare($sql);
        $resultado->execute();
        $row=$resultado->fetch(PDO::FETCH_ASSOC);
        $imprimecomandas = $row['ImprimeComandas'];
        $idimprectral = $row['idImpresoraComandaCentral'];
        $cantcopias = $row['CantCopiasComanda'];
        $dobleentrada = $row['DobleComandaConEntrada'];
        $concatgustos = $row['ConcatenarGustos'];
        $detallarcombina = $row['DetallarPlatosEnCombina'];
        $delim1 = trim( $row['DelimitadorEntrada1'] );
        $delim2 = trim ($row['DelimitadorEntrada2'] );
        $delim3 = trim ( $row['DelimitadorEntrada3'] );
        $resultado->closeCursor();
    }  catch(Exception $e) {
        die('Error al traer parametros'.$e->GetMessage());
    }

    // Obtengo datos del encabezado de la Mesa
    $sql  = "Select E.idMozo,E.Fecha,E.CantPersonas, M.Nombre as NomMozo From EN_MESA E ";
    $sql .= " Inner Join Mozos M On M.idmozo = E.idmozo";
    $sql .= " Where NroMesa=:nmesa"; 

   
    try {
        $resultado = $cid->prepare($sql);
        $resultado->execute(array(":nmesa"=>$nmesa));
        $row=$resultado->fetch(PDO::FETCH_ASSOC);
        $idMozo = $row['idMozo'];
        $nomMozo = $row['NomMozo'];
        $fecha = $row['Fecha'];
        $comensales = $row['CantPersonas'];
        $resultado->closeCursor();
    }  catch(Exception $e) {
        die('Error al traer enc mesa'.$e->GetMessage());
    } 

    // Recorro todos los sectores y busco si hay platos a imprimir
    $sql  = "Select S.IdSectorExped, S.Descripcion, S.CombinaComandas ,I.IP, I.CantSaltos,I.Margen,I.AnchoHoja,I.CantSaltosCut"; 
    $sql .= " From Sectores_Exped S";
    $sql .= " inner join IMPRESORAS I on S.IdImpresora = I.idImpresora ";

    try {
        $resultado = $cid->prepare($sql);
        $resultado->execute();
      
        While ( $row=$resultado->fetch(PDO::FETCH_ASSOC) ) 
        { 
            $sectorexp = $row['IdSectorExped'];
            $sectorcomanda = $row['Descripcion'];
            $ipimpre = $row['IP'];
            $saltosfin = $row['CantSaltos'];
            $imp_margen = $row['Margen'];
            $imp_ancho = $row['AnchoHoja'];
            $saltoscut = $row['CantSaltosCut'];
            $combinacomandas = $row['CombinaComandas'];

            //echo $ipimpre;
            //for($ii=1 ;$ii <= $cantcopias ;$ii++) 
            //{
            // Obtengo renglones del sector
            $sql  = "EXEC sp_Comandas @nmesa=?, @desde=?, @hasta=?, @sectorexp=?"; 
           
            try {
            
                $rescom = $cid->prepare($sql);
                $rescom->bindParam(1, $nmesa, PDO::PARAM_INT);
                $rescom->bindParam(2, $idetdesde, PDO::PARAM_INT);
                $rescom->bindParam(3, $idethasta, PDO::PARAM_INT);
                $rescom->bindParam(4, $sectorexp, PDO::PARAM_INT);
                $rescom->execute();

                // Aca  va una funcion para saber si la consulta trae registros PDO rowcount , no funciona
                $registros = 1;
                  
                if ( $registros > 0 ) {
                // Abro comandera 
                                
                    if ( $handle = @fopen($ipimpre, "w") ) 
                    {
                        
                        // imprimo lineas
                        $idetg = 0; $esentrada = false;
                        $impencab = 0;
                        $combinacomandas = 0;

                        While ( $row=$rescom->fetch(PDO::FETCH_ASSOC)) {

                            // imprimo encabezado
                            if ($impencab == 0) {
                                TitComanda($handle, $imp_margen, $imp_ancho,$sectorcomanda,$nmesa,$idMozo,$nomMozo,$comensales,$fecha);
                                $impencab = 1;
                            }    
                            $idet = $row['idDetalle'];
                            $idplato = $row['idPlato'];
                            $cant = intval($row['Cant']);
                            $descrip = $row['Descripcion'];
                            $combo = trim( $row['Combo'] );
                            $tamanio = trim( $row['Tamanio'] );
                            $gusto = trim( $row['Gusto'] );
                            $combinacion = trim( $row['Combinacion'] );
                            $obs = trim( $row['Obs'] );
                            $entrada = trim( $row['Entrada'] );
                            
                            // Si es entrada imprimo delimitadores
                            if ( $esentrada and ( $entrada <> -1)  ) {
                               fwrite($handle, chr(27). chr(33).chr(1)); // letra Normal 
                               fwrite($handle,ArmaSt('d',$imp_margen,$imp_ancho,$delim1 ));
                               fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                               fwrite($handle,ArmaSt('d',$imp_margen,$imp_ancho,$delim2 ));
                               fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                               fwrite($handle,ArmaSt('d',$imp_margen,$imp_ancho,$delim3 )); 
                               fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                               $esentrada = false;
                            }
                            fwrite($handle, chr(27). chr(33). chr(16));// letra Doble Alto
                                                     
                            if ( intval( strval($idet).strval($idplato) ) <> $idetg )  {
                                if ( $combo == 'En Combo' ) {
                                    fwrite($handle,ArmaSt('d',$imp_margen,$imp_ancho,$cant." En Combo: ".$descrip ));
                                } else {
                                    fwrite($handle,ArmaSt('d',$imp_margen,$imp_ancho,$cant." ".$descrip ));
                                }
                                fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                              
                                // Tamanio
                                if ( ( $tamanio <> 'NULL' ) and ( trim($tamanio) <> '' ) ){
                                    fwrite($handle,ArmaSt('d',$imp_margen,$imp_ancho," Tam.: ".$tamanio ));
                                    fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                                };
                                if ( ( $combinacion <> 'NULL' ) and ( trim($combinacion) <> '' ) ){
                                    fwrite($handle,ArmaSt('d',$imp_margen+3,$imp_ancho,$combinacion ));
                                    fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                                };
                            };
                            // Gustos
                            if ( ( $gusto <> 'NULL' ) and ( trim($gusto) <> '' ) ){
                                $idetg = intval( strval($idet).strval($idplato) );
                                fwrite($handle,ArmaSt('d',$imp_margen+3,$imp_ancho,$gusto ));
                                fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                            } else {
                                $idetg = 0 ;
                            };
                            if ( ( $obs <> 'NULL' ) and ( trim($obs) <> '' ) ){
                                fwrite($handle,ArmaSt('n',$imp_margen+3,$imp_ancho,$obs ));
                                fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                            }
                            if ( $entrada == -1) {
                                $esentrada = true;
                            }
                        
                        }
                        if ( $combinacomandas == 1 ) {
                            $SQL  = " Select * From ( ";
                            $SQL .= " Select S.idSectorExped, S.Descripcion as Sector ,E.NroMesa, E.idDetalle, P.Descripcion, '' as EnCombo FROM EN_MESADET E";
                            $SQL .= " inner join platos p on P.idplato = E.idplato";
                            $SQL .= " inner join SECTORES_EXPED S on S.idSectorExped = P.idSectorExped";
                            $SQL .= " where E.NroMesa = :nmesa and ( E.idDetalle between :idetdesde and :idethasta ) and S.idSectorExped <> :sectorexp";
                            $SQL .= " UNION ALL";
                            $SQL .= " Select S.idSectorExped, S.Descripcion as Sector ,E.NroMesa, E.idDetalle, P.Descripcion, 'En Combo: ' as EnCombo From EN_MESADET_COMBOS E";
                            $SQL .= " inner join platos p on P.idplato = E.idplato";
                            $SQL .= " inner join SECTORES_EXPED S on S.idSectorExped = P.idSectorExped";
                            $SQL .= " where E.NroMesa = :nmesa and ( E.idDetalle between :idetdesde and :idethasta ) and S.idSectorExped <> :sectorexp";
                            $SQL .= " ) X";
                            $SQL .= " order by X.idSectorExped,X.NroMesa";
                            
                            try {
                                $result = $cid->prepare($SQL);
                                $result->execute(array(":nmesa"=>$nmesa, ":idetdesde"=>$idetdesde, ":idethasta"=>$idethasta, ":sectorexp"=>$sectorexp));

                                //if ( mssql_num_rows($retid2) > 0 ) {

                                   $row=$result->fetch(PDO::FETCH_ASSOC);
                                   $descsect = $row['Sector']; 
                                   $descplat = $row['Descripcion'];
                                   While( $row=$result->fetch(PDO::FETCH_ASSOC)) {
                                       if (  $row['Sector'] == $descsect) {                                                                          
                                          $descplat = $descplat.', '.$row['Descripcion'];
                                       } else { 
                                        fwrite($handle, chr(27). chr(33).chr(1)); // letra Normal 
                                        fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                                        fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,str_repeat('-',40)));
                                        fwrite($handle,ArmaSt('n',$imp_margen+3,$imp_ancho," Combina Con: ".$descsect ));
                                        fwrite($handle, chr(27). chr(100). chr(1));//salto de linea  
                                        if ( $detallarcombina == 1) {
                                        fwrite($handle, chr(27). chr(33).chr(1)); // letra Normal 
                                        fwrite($handle,ArmaSt('n',$imp_margen+3,$imp_ancho,$descplat ));
                                        fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                                        fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,str_repeat('-',40)));
                                        fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                                        }
                                        $descsect = $row['Sector']; 
                                        $descplat = $row['Descripcion'];
                                       }   
                                   }
                                   if ( trim($descsect) <> '') {
                                    fwrite($handle, chr(27). chr(33).chr(1)); // letra Normal 
                                    fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                                    fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,str_repeat('-',40)));
                                    fwrite($handle,ArmaSt('n',$imp_margen+3,$imp_ancho," Combina Con: ".$descsect ));
                                    fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                                   }
                                   if ( $detallarcombina == 1) {
                                        if ( trim($descplat) <> '') {
                                            fwrite($handle, chr(27). chr(33).chr(1)); // letra Normal 
                                            fwrite($handle,ArmaSt('n',$imp_margen+3,$imp_ancho,$descplat ));
                                            fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                                            fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,str_repeat('-',40)));
                                            fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                                        }
                                   }
                                }  catch(Exception $e) {
                                    die('Error al traer combinacion'.$e->GetMessage());
                                }

                               
                            
                        }

                        if ($impencab == 1 ) {
                            PieComanda($handle, $imp_margen, $imp_ancho);
                            fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,"################## FIN #################"));

                            for($ii=0; $ii<$saltosfin; $ii++) {
                                fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho," "));
                                fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
                            };
                            fwrite($handle, chr(27). chr(105));
                        }
                        fclose($handle); // cierra el fichero PRN
                    
                    } else {

                        header("HTTP/1.1 200 Error en Coamndera " + $sqlL);
                        header('Access-Control-Allow-Origin: *');
                        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
                        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
                        echo json_encode( " Error en Comandera " + $ipimpre);
                        exit();  
                    }
                }  // No hay datos a comandar  
                } catch(Exception $e) {
                    die('Error al traer datos'.$e->GetMessage());
                }    
                
            };
            //}; // Cantidad de copias
        //
        $resultado->closeCursor();
        header("HTTP/1.1 200 OK - FIN");
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
        echo json_encode( "ok ".$ipimpre." - ".$column );
       
            
    } catch(Exception $e) {
        die('Error al traer sectores'.$e->GetMessage());
    } 

} else {
    //En caso de que ninguna de las opciones anteriores se haya ejecutado
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); 
    header("HTTP/1.1 400 Bad Request");
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

 function TitComanda($handle, $imp_margen, $imp_ancho,$sectorcomanda,$nmesa,$idMozo,$nomMozo,$comensales,$fecha) {
    fwrite($handle, chr(27). chr(64));//inicializacion
    fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,'################ INICIO ################') );
    fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
    fwrite($handle, chr(27). chr(33). chr(16));// letra Doble Alto
    fwrite($handle,ArmaSt('d',$imp_margen,$imp_ancho,'Sector: '.$sectorcomanda));
    fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
    fwrite($handle,ArmaSt('d',$imp_margen,$imp_ancho,'MESA: '.$nmesa.' - Mozo: ('.$idMozo.') '.$nomMozo ));
    fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
    fwrite($handle, chr(27). chr(33).chr(1)); // letra Normal
    fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,$fecha));
    fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
    fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,'Comensales: '.$comensales));
    fwrite($handle, chr(27). chr(100). chr(1));//salto de linea
    fwrite($handle,ArmaSt('n',$imp_margen,$imp_ancho,str_repeat('-',40)));
    fwrite($handle, chr(27). chr(100). chr(1));//salto de linea

 }

?>