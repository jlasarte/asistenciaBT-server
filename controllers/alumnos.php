<?php

namespace Controllers;

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
	            'descripccion' => $usuario['apellido'],
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

}

?>

