<?php

namespace Controllers;

class Cursos extends Controller {

	function  index() {

		$cursos = array();
    	foreach ($this->db->curso() as $curso) {
	        $cursos[]  = array(
	            'id' => $curso['id'],
	            'nombre' => $curso['nombre'],
	            'descripccion' => $curso['descripccion'],
	            'horarios' => $curso['horarios'],
	            'profesor' => $curso->usuario['nombreusuario']
	        );
    	}

	    $this->app->response()->header("Content-Type", "application/json");
	    echo json_encode($cursos, JSON_FORCE_OBJECT);
	}

	function view($id) {
		$this->app->response()->header("Content-Type", "application/json");
    	$curso = $this->db->curso()->where('id', $id);
    	$inscripciones = $this->db->inscripcion()->where('cursos_id', $id);
    	$alumnos = array();
    	
	    foreach ($inscripciones as $i) {
	        $alumnos[] = array(
	            'id'=> $i->usuario['id'],
	            'nombre'=> $i->usuario['nombre'],
	            'apellido'=> $i->usuario['apellido'],
	            'UUID'=> $i->usuario['UUID'],
	            'nombreusuario'=> $i->usuario['nombreusuario'],
	            );
	    }


	    if($data = $curso->fetch()){
	        echo json_encode(array(
	            'id' => $data['id'],
	            'nombre' => $data['nombre'],
	            'descripccion' => $data['descripccion'],
	            'horarios' => $data['horarios'],
	            'alumnos' => $alumnos
	        ));
	    } else {
	        echo json_encode(array(
	            'status' => false,
	            'message' => "El curso $id no existe"
	        ));
    	}
	}

}

?>

