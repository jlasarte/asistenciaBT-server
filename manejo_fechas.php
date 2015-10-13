<?php



function fecha_legible($input){
	$meses = array("zero","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
	
	$f= date_parse($input);		//genera un array asociativo con información detallada de la fecha de input
	
	$fechaFormateada= $f['day'] . " de " 
					. $meses[$f['month']-1] . " de " 
					. $f['year'] . " " 
					. $f['hour'] . ":" 
					. $f['minute'];
					
	return $fechaFormateada;
}




?>