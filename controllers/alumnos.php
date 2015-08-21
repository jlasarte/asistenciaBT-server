<?php

namespace Controllers;

include_once 'controllers/controller.php';

class Alumnos extends Controller {

	function cursos($id) {
		$this->app->response()->header("Content-Type", "application/json");
    	$usuario = $this->db->usuario[$id];

	    if($usuario){
    		$cursos = array();
		    foreach ($usuario->curso_usuario() as $i) {
		        $cursos[] = array(
		            'id'=> $i->curso['id'],
		            'nombre'=> $i->curso['nombre'],
		            );
		    }
	        echo json_encode(array(
	            'id' => $usuario['id'],
	            'nombre' => $usuario['nombre'],
	            'descripcion' => $usuario['apellido'],
	            'cursos' => $cursos
	        ));
	    } else {
	        echo json_encode(array(
	            'status' => false,
	            'message' => "El alumno $id no existe"
	        ));
    	}
	}

	function view($id) {
		$this->app->response()->header("Content-Type", "application/json");
		$usuario = $this->db->usuario[$id];
		
		if($usuario!=null){
	        echo json_encode(array(
	            'id' => $usuario['id'],
	            'nombre' => $usuario['nombre'],
	            'apellido' => $usuario['apellido'],
				'legajo' => $usuario['legajo'],
				'device_address' => $usuario['device_address'],
				'nombreusuario' => $usuario['nombreusuario'],
	        ));
	    } else {
	        echo json_encode(array(
	            'status' => false,
	            'message' => "El alumno $id no existe"
	        ));
    	}
	}

	function asistencia($id,$curso_id) {
		$this->app->response()->header("Content-Type", "application/json");
		$usuario = $this->db->usuario[$id];
		if ($data = $usuario->fetch()) {

			$clases = $this->db->curso[$curso_id]->clase();
	    	$asistencias_result = array();

			foreach ($clases as $clase) {
				$asistencias = $clase->asistencia()->where('usuario_id', $id);
				foreach ($asistencias as $a) {
					$asistencias_result[$clase['fecha']] =  array(
						'estado' => $a->estado_asistencia['estado']
					);
				}
			}

			echo json_encode($asistencias_result);

		} else {
	        echo json_encode(array(
	            'status' => false,
	            'message' => "El curso $id no existe"
	        ));
    	}
	}

	function checkname($nombre) {
		$this->app->response()->header("Content-Type", "application/json");
		$usuario = $this->db->usuario()->where("nombreusuario", $nombre);
		if ($usuario->fetch()) {
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

	function registrarAlumno($nombre,$apellido,$legajo,$device_address,$username){
		$this->app->response()->header("Content-Type", "application/json");
		$newStudent=array(
	        'id' => null,// auto increment
	        'nombre' => $nombre,
	        'apellido' => $apellido,
			'legajo' => $legajo,
			'device_address' => $device_address,
			'nombreusuario' => $username,
		);
		$row = $this->db->usuario()->insert($newStudent);
		if($row){
			echo json_encode(array(
	        'status' => true,
	        'message' => 'alumno insertado',
			));
		} else {
			echo json_encode(array(
	        'status' => false,
	        'message' => 'Error en la creación',
			));
		}
	}
	
	function marcarPresente($usuario_id,$clase_id){		//Crea una asistencia en estado "presente" para el usuario en la clase dada
														//o si ya existe, le cambie el estado a "presente"
		$this->app->response()->header("Content-Type", "application/json");
		
		$this->modificarAsistencia($usuario_id,$clase_id,'2');
		
	}
	
	function marcarJustificada($usuario_id,$clase_id){			//Le pone estado "J" a la asistencia dada. Si esta no existe, se la genera.				
		$this->app->response()->header("Content-Type", "application/json");
		
		$this->modificarAsistencia($usuario_id,$clase_id,'3');
		
	}
	
	function modificarAsistencia($usuario_id, $clase_id, $estado_asistencia_id){	//Este método no se llama desde afuera
		$existe = $this->db->asistencia()->where(array("usuario_id" => $usuario_id, "clase_id" => $clase_id));
		
		if ($existe->fetch()){
			$data = array(
				"estado_asistencia_id" => $estado_asistencia_id
			);
			$row = $existe->update($data);		
		}else{			
			$newAttendance=array(
					'id' => null,// auto increment
					'usuario_id' => $usuario_id,
					'clase_id' => $clase_id,
					'estado_asistencia_id' => $estado_asistencia_id,
				);					
			$row=$this->db->asistencia()->insert($newAttendance);			
		}
		
		if($row){
				echo json_encode(array(
				'status' => true,
				'message' => 'asistencia registrada',
				));
		} else {
			echo json_encode(array(
			'status' => false,
			'message' => 'Error en el guardado de la asistencia',
			));
		}
	}
}

?>

