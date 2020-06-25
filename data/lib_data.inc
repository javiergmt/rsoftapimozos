<?php

	function conectar($base,$servidor,$usuario,$password) {
		try {
				$cnx = new PDO("sqlsrv:Server=$servidor;Database=$base", $usuario, $password);
				return $cnx;
		}
		catch(PDOException $e) {
				die("Error connecting to SQL Server: " . $e->getMessage());
		}
	};

	////////////////////////////////////////////////////
	//Convierte fecha de mysql a normal
	////////////////////////////////////////////////////
	function cambiaf_a_normal($fecha){
   	 ereg( "([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})", $fecha, $mifecha);
    	$lafecha=$mifecha[3]."/".$mifecha[2]."/".$mifecha[1];
    	return $lafecha;
	};

	////////////////////////////////////////////////////
	//Convierte fecha de normal a mysql
	////////////////////////////////////////////////////

	function cambiaf_a_mysql($fecha){
	    ereg( "([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $fecha, $mifecha);
	    $lafecha=$mifecha[3]."-".$mifecha[2]."-".$mifecha[1];
	    return $lafecha;
	};

   /*
   Ejemplos

   <input type="text" name="fecha" value="<?echo cambiaf_a_normal($fila->fecha);">

   mysql_query ("insert into documento (titulo_documento, fecha_documento, cuerpo_documento) values ('$titulo_documento', '" . cambiaf_a_mysql($fecha) . "', '$cuerpo_documento')");
	*/

?>
