<?php

namespace Controllers;

include_once 'controllers/controller.php';

class Cursos extends Controller {

	function  index() {

		$cursos = array();
    	foreach ($this->db->curso() as $curso) {
	        $cursos[]  = array(
	            'id' => $curso['id'],
	            'nombre' => $curso['nombre'],
	            'descripcion' => $curso['descripcion'],
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
    	$inscripciones = $this->db->curso_usuario()->where('curso_id', $id);
    	$alumnos = array();
    	
	    foreach ($inscripciones as $i) {
	        $alumnos[] = array(
	            'id'=> $i->usuario['id'],
	            'nombre'=> $i->usuario['nombre'],
	            'apellido'=> $i->usuario['apellido'],
	            'device_address'=> $i->usuario['device_address'],
	            'nombreusuario'=> $i->usuario['nombreusuario'],
	            );
	    }

		
	    if($data = $curso->fetch()){

			$getProf=$this->db->usuario()->where('id', $data['usuario_id']);
			$profesor=$getProf->fetch();
			
	        echo json_encode(array(
	            'id' => $data['id'],
	            'nombre' => $data['nombre'],
	            'descripcion' => $data['descripcion'],
	            'horarios' => $data['horarios'],
				'id_profesor' => $profesor['id'],
				'usuario_profesor' => $profesor['nombreusuario'],
				'address_profesor' => $profesor['device_address'],
	            'alumnos' => $alumnos
	        ));
	    } else {
	        echo json_encode(array(
	            'status' => false,
	            'message' => "El curso $id no existe"
	        ));
    	}
	}
	
	
	function crearCurso($nombre,$descripcion,$horarios,$usuario_id){
		$this->app->response()->header("Content-Type", "application/json");
		$newCourse=array(
	        'id' => null,	//auto incremental
	        'nombre' => $nombre,
	        'descripcion' => $descripcion,
			'horarios' => $horarios,
			'usuario_id' => $usuario_id,	//id del owner
		);
		$row = $this->db->curso()->insert($newCourse);
		if($row){
			echo json_encode(array(
	        'status' => true,
	        'message' => 'curso creado',
			));
		} else {
			echo json_encode(array(
	        'status' => false,
	        'message' => 'Error en la creacion',
			));
		}
	}
	
	function checkname($nombre) {
		$this->app->response()->header("Content-Type", "application/json");
		$curso = $this->db->curso()->where("nombre", $nombre);
		if ($curso->fetch()) {
			echo json_encode(array(
	        'free' => false,
	        'message' => 'El nombre no está disponible',
			));
		} else {
			echo json_encode(array(
	        'free' => true,
	        'message' => 'El nombre está disponible',
			));
		}
	}
	
	function generarClase($curso_id,$fecha,$hora_inicio,$hora_fin){				//genera una clase para el curso_id dado
		$this->app->response()->header("Content-Type", "application/json");
		$newClass=array(
	        'id' => null,	//auto incremental
	        'curso_id' => $curso_id,
	        'fecha' => $fecha,
			'hora_inicio' => $hora_inicio,
			'hora_fin' => $hora_fin,
		);
		$row = $this->db->clase()->insert($newClass);
		if($row){
			echo json_encode(array(
	        'status' => true,
	        'message' => 'clase creada',
			));
		} else {
			echo json_encode(array(
	        'status' => false,
	        'message' => 'Error en la creacion',
			));
		}
		$this->generarAsistenciasPendientes($row['id'],$row['curso_id']);	
	}
	
	function generarAsistenciasPendientes($clase_id,$curso_id){		//crea las tuplas en la tabla de asistencias, en estado pendiente
		$asistencias= array();
		$inscripciones = $this->db->curso_usuario()->where('curso_id', $curso_id);

		foreach ($inscripciones as $i) {
	        $asistencias[]  = array(
	            'usuario_id'=> $i->usuario['id'],
				'clase_id' 	=> $clase_id,
				'estado_asistencia_id' => '4',
	        );
    	}

		$row = $this->db->asistencia()->insert_multi($asistencias);
		echo " ", (string)$row , " asistencias pendientes generadas";
	}
}

?>

