<?php

namespace Controllers;

include_once 'controllers/controller.php';
include_once './manejo_fechas.php';

class Alumnos extends Controller {

	function cursos($id) {						//los cursos a los que está incripto el usuario del id
		$this->app->response()->header("Content-Type", "application/json");
    	$usuario = $this->db->usuario[$id];

	    if($usuario){
    		$cursos = array();
		    foreach ($usuario->curso_usuario() as $inscripcion) {
		        $cursos[] = array(
		            'id'=> $inscripcion->curso['id'],
		            'nombre'=> $inscripcion->curso['nombre'],
					'descripcion'=>$inscripcion->curso['descripcion'],
					'horarios'=>$inscripcion->curso['horarios'],
					'id_profesor'=>$inscripcion->curso['usuario_id'],
					'address_profesor'=>$inscripcion->usuario['device_address'],
					'nombre_profesor'=>$inscripcion->usuario['nombre'],
					'apellido_profesor'=>$inscripcion->usuario['apellido'],
					'usuario_profesor'=>$inscripcion->usuario['nombreusuario'],
		            );
		    }
	        echo json_encode($cursos);
	    } else {
	        echo json_encode(array(
	            'status' => false,
	            'message' => "El alumno $id no existe"
	        ));
    	}
	}
	
	function cursosComoProfesor($usuario_id) {				//devuelve array de cursos donde el usuario es profesor
		$this->app->response()->header("Content-Type", "application/json");
    	$usuario = $this->db->usuario[$usuario_id];
		
	    if($usuario){
			$cursos=array();
			foreach ($this->db->curso()->where("usuario_id", $usuario_id) as $curso){
				$cursos[]  = array(
					'id' => $curso['id'],
					'nombre' => $curso['nombre'],
					'descripcion' => $curso['descripcion'],
					'horarios' => $curso['horarios'],
					'profesor' => $curso->usuario['nombreusuario']
				);
			}
				
	        echo json_encode($cursos);
	    } else {
	        echo json_encode(array(
	            'status' => false,
	            'message' => "El alumno $id no existe"
	        ));
    	}
	}
	
	function view($id) {	//obtiene los datos del usuario by id
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
	
	function getByMac($address) {	//obtiene los datos del usuario by device_address (bluetooth mac)
		$this->app->response()->header("Content-Type", "application/json");
		$usuario = $this->db->usuario("device_address = ?", $address)->fetch();
		
		if($usuario){
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
	            'message' => "El alumno con dirección $address no existe"
	        ));
    	}
	}

	function asistencia($id,$curso_id) {	//obtiene la asistencia para un user_id
		$this->app->response()->header("Content-Type", "application/json");
		$usuario = $this->db->usuario[$id];
		if ($data = $usuario->fetch()) {

			$clases = $this->db->curso[$curso_id]->clase();
	    	$asistencias_result = array();
 
			foreach ($clases as $clase) {
				$asistencias = $clase->asistencia()->where('usuario_id', $id);
				foreach ($asistencias as $a) {					
					$asistencias_result[] =  array(
						'fecha' => fecha_legible($clase['fecha']),
						'estado' => $a->estado_asistencia['estado']
					);
				}
			}

			echo json_encode($asistencias_result);

		} else {
	        echo json_encode(array(
	            'status' => false,
	            'message' => "El usuario $id no existe"
	        ));
    	}
	}
	
	function esta_presente($usuario_id, $clase_id) {	//Se fija si está presente/justif para una clase en particular
		$this->app->response()->header("Content-Type", "application/json");
		
		$asistencia = $this->db->asistencia()->select("estado_asistencia_id")->where(array( "usuario_id" => $usuario_id,"clase_id" => $clase_id ) )->fetch();
		
		$codigo=$asistencia['estado_asistencia_id'];
		$status = $this->db->estado_asistencia()->select("estado")->where("id", $codigo)->fetch();
		$estado = $status['estado'];
		
		
		if ( ($codigo==2)or($codigo==3) ) {
			echo json_encode(array(
	        'status' => true,
	        //'message' => "El usuario $usuario_id esta $estado ($codigo) para la clase $clase_id",
			));
		} else {
			echo json_encode(array(
	        'status' => false,
			'id' => $codigo,
	        //'message' => "El usuario $usuario_id está  $estado ($codigo) para la clase $clase_id",
			));
		}
	}
	
	function checkname($nombre) {	//se fija que el nombreusuario esté disponible o no
		$this->app->response()->header("Content-Type", "application/json");
		$usuario = $this->db->usuario()->select("nombreusuario")->where("nombreusuario", $nombre);
		if ($usuario->fetch()) {
			echo json_encode(array(
	        'free' => false,
	        'message' => 'El nombre ya ha sido registrado',
			));
		} else {
			echo json_encode(array(
	        'free' => true,
	        'message' => 'El nombre está disponible',
			));
		}
	}

	function registrarAlumno($nombre,$apellido,$password,$legajo,$device_address,$username){		//almacenar un usuario nuevo (no chequea que ya exista)
		$this->app->response()->header("Content-Type", "application/json");
		$newStudent=array(
	        'nombre' => trim($nombre),		//trim() - Elimina espacio en blanco del inicio y el final del string
	        'apellido' => trim($apellido),
			'password' => hash("sha512",$password),
			'legajo' => trim($legajo),
			'device_address' => $device_address,
			'android_version' => 0,
			'nombreusuario' => trim($username)
		);
		if ($nombre == "" || $apellido == "" || $password=="" || $device_address=="" || $username==""){
			//Se podría chequear con el empty() de php pero chilla con los campos en cero, así acá no sirve
			echo json_encode(array(
					'status' => false,
					'message' => 'Debe completar todos los campos',
				));
		}else{
			$row = $this->db->usuario()->insert($newStudent);
			if($row){
				$insert_id = $this->db->usuario()->insert_id();
				echo json_encode(array(
					'status' => true,
					'message' => "usuario $insert_id insertado",
					'id'=> $insert_id,
					'device_address' => $device_address
					));
			} else {
				echo json_encode(array(
					'status' => false,
					'message' => 'El usuario o el dispositivo ya han sido registrados',
				));
			}
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
	
	function marcarAusente($usuario_id,$clase_id){			//Le pone estado ausente a la asistencia dada. Si esta no existe, se la genera.				
		$this->app->response()->header("Content-Type", "application/json");		
		$this->modificarAsistencia($usuario_id,$clase_id,'1');	
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
		
		if($row or $existe){
				echo json_encode(array(
				'status' => true,	
				//'message' => 'asistencia registrada',
				));
		} else {
			echo json_encode(array(
			'status' => false,
			'message' => 'Error en el guardado de la asistencia',
			));
		}
	}
	
	
	function inscribirEnCurso($usuario_id, $curso_id){
		$this->app->response()->header("Content-Type", "application/json");
		
		$newInscription=array(
	        'id' => null,// auto increment
	        'curso_id' => $curso_id,
	        'usuario_id' => $usuario_id
		);
		$row=$this->db->curso_usuario()->insert($newInscription);
		
		if($row){
			$insert_id = $this->db->curso_usuario()->insert_id();
			echo json_encode(array(
				'status' => true,
				'message' => 'Alumno inscripto correctamente al curso',
				'id' => $insert_id,
			));
		} else {
			echo json_encode(array(
				'status' => false,
				'message' => 'Error en la inscripción',
			));
		}
		
	}
	
	function check_mac($address) {	
		$this->app->response()->header("Content-Type", "application/json");
		$direccion = $this->db->usuario()->select("device_address")->where("device_address", $address);
		if ($direccion->fetch()) {
			echo json_encode(array(
				'status' => true,
				'message' => 'la dirección ya está registrada',
			));
		} else {
			echo json_encode(array(
				'status' => false,
				'message' => 'la dirección no ha sido registrada',
			));
		}
	}
	
	function es_profesor($usuario_id,$curso_id) {	
		$this->app->response()->header("Content-Type", "application/json");
		
		$row = $this->db->curso[$curso_id];
		
		if ($row["usuario_id"]==$usuario_id) {
			echo json_encode(array(
				'status' => true,
				//'message' => 'el usuario es profesor del curso',
			));
		} else {
			echo json_encode(array(
				'status' => false,
				//'message' => 'el usuario no es profesor del curso',
			));
		}
	}
	
	function cambiar_mac($usuario_id,$new_address){
		$this->app->response()->header("Content-Type", "application/json");
		
		$row = $this->db->usuario[$usuario_id];
		if ($row){
			$row["device_address"]=$new_address;
			$row->update();
			echo json_encode(array(
				'status' => true,
				'message' => "direccion del usuario $usuario_id actualizada",
			));
		}else{
			echo json_encode(array(
				'status' => false,
				'message' => "el usuario $usuario_id no existe",
			));
			
		}
		
	}
	
	function usuario_es_profesor($usuario_id) {	
		$this->app->response()->header("Content-Type", "application/json");
		
		$user = $this->db->usuario()->where('id',$usuario_id)->where('profesor',"1");
		
		if ($user->fetch()) {
			echo json_encode(array(
				'status' => true,
				//'message' => 'el usuario está definido como profesor',
			));
		} else {
			echo json_encode(array(
				'status' => false,
				//'message' => 'el usuario no es profesor',
			));
		}
	}
	

}

?>

