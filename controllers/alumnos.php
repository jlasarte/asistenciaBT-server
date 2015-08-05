<?php

namespace Controllers;

class Alumnos extends Controller {

	function cursos($id) {
		$this->app->response()->header("Content-Type", "application/json");
    	$usuario = $this->db->usuario()->where('id', $id);

	    if($data = $usuario->fetch()){
	    	$inscripciones = $this->db->curso_usuario()->where('usuario_id', $id);
    		$cursos = array();
		    foreach ($inscripciones as $i) {
		        $cursos[] = array(
		            'id'=> $i->curso['id'],
		            'nombre'=> $i->curso['nombre'],
		            );
		    }
	        echo json_encode(array(
	            'id' => $data['id'],
	            'nombre' => $data['nombre'],
	            'descripccion' => $data['apellido'],
	            'cursos' => $cursos
	        ));
	    } else {
	        echo json_encode(array(
	            'status' => false,
	            'message' => "El curso $id no existe"
	        ));
    	}
	}

	function view($id) {

	}

	function asistencia($id) {

	}

}

?>

