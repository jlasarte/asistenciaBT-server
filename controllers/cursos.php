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

	function buscar($usuario_id,$busqueda) {
		$this->app->response()->header("Content-Type", "application/json");
		
		$resultado = $this->db->curso()->where('nombre LIKE ? AND usuario_id <> ?', "%$busqueda%" , $usuario_id );	//busca sin los cursos donde sea profesor
		
		$resultado->where('NOT id',$this->db->curso_usuario()->select('curso_id')->where('usuario_id',$usuario_id));  //Filtra los cursos donde es alumno

		$cursos=array();
    	foreach ($resultado as $curso) {
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
	
	function generarClase($curso_id){				//genera una clase para el curso_id dado
		$this->app->response()->header("Content-Type", "application/json");
		$newClass=array(
	        'id' => null,	//auto incremental
	        'curso_id' => $curso_id,
	        'fecha' => null,	//se inserta la fecha-hora actual

		);
		$row = $this->db->clase()->insert($newClass);
		if($row){
			$insert_id = $this->db->clase()->insert_id();
			echo json_encode(array(
				'status' => true,
				'id'=> $insert_id,
			));
			$this->generarAsistenciasPendientes($insert_id,$row['curso_id']);
		} else {
			echo json_encode(array(
				'status' => false,
				'message' => 'Error en la creacion',
			));
		}
			
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
		//echo " ", (string)$row , " asistencias pendientes generadas";
	}
	
	function resolver_pendientes($clase_id){	//pasa todos los pendientes a ausentes en la clase dada
		$pendientes= $this->db->asistencia()->where('clase_id',$clase_id);
		$ausentes = array();
		foreach ($pendientes as $p){
			$ausentes['usuario_id']=$p['usuario_id'];
			$ausentes['estado_asistencia_id']=1;
		}
		$pendientes->update($ausentes);
		echo json_encode(array(
	        'status' => true,
	        'message' => "pendientes marcados como ausentes",
			));
	
	}
	
	function obtener_clase($curso_id) {
		$this->app->response()->header("Content-Type", "application/json");
    	$clase = $this->db->clase()->select("id","fecha")->where(array("curso_id" => $curso_id, "completada" => "0"))
									->order("fecha")
									->limit(1)
									->fetch();
    	if ($clase){
			echo json_encode(array(
				'status' => true,
				'id'	=> $clase['id'],
				'fecha' => $clase['fecha'],
			));
		}else{
			echo json_encode(array(
				'status' => false,
			));
		}
	}
	
	function marcar_completada($clase_id){	//marca una clase como completada 
		$this->resolver_pendientes($clase_id);
		$clase= $this->db->clase()->where('id',$clase_id)->fetch();
		$clase['completada']=1;
		$clase->update();
		echo json_encode(array(
	        'status' => true,
	        //'message' => "clase $clase_id marcada como completada",
		));
	
	}
	
}

?>

