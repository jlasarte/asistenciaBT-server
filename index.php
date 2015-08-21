<?php

/* Require Slim and plugins */
require 'Slim/Slim.php';
require 'plugins/NotORM.php';
require 'plugins/Spyc.php';

include_once 'controllers/controller.php';
include_once 'controllers/alumnos.php';
include_once 'controllers/cursos.php';

$config = Spyc::YAMLLoad('config.yaml');

$dsn = $config["db"]["method"].$config["db"]["name"].";charset=".$config["db"]["charset"];

$pdo = new PDO($dsn, $config["db"]["user"], $config["db"]["pass"]);
$db = new NotORM($pdo);

/* Register autoloader and instantiate Slim */
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

// Configuración de las rutas
$app->get('/', function(){
    echo 'App BT';
});

$app->group('/cursos', function() use ($app, $db) {

    $app->get('/', function() use($app, $db){
		$courseController=(new \Controllers\Cursos($app, $db));
		$courseController->index();
    });

    $app->get('/:id', function($id) use ($app, $db) {
        $courseController=(new \Controllers\Cursos($app, $db));
		$courseController->view($id);
    });
	
	 $app->get('/checkname/:name', function($name) use ($app, $db) {	//Verificar si existe un curso con nombre "name"
        $courseController=(new \Controllers\Alumnos($app, $db));
        $courseController->checkname($name);
    });
	
	$app->post('/alta', function() use($app, $db){		//dar de alta un nuevo curso
        try {
            // get and decode JSON request body
            $request = $app->request();
            $body = $request->getBody();
            $input = json_decode($body);             
           
			$courseController=(new \Controllers\Cursos($app, $db));
			$courseController->crearCurso((string)$input->nombre,
											(string)$input->descripcion,
											(string)$input->horarios,
											(string)$input->usuario_id);	//id del owner
          } catch (Exception $e) {
            $app->response()->status(400);
            $app->response()->header('X-Status-Reason', $e->getMessage());
          }

    });
});



$app->group('/alumnos', function() use ($app, $db) {

    $app->get('/', function() use($app, $db){
        echo 'lista de alumnos';
    });
	
	$app->get('/:id', function($id) use($app, $db){
        $userController=(new \Controllers\Alumnos($app, $db));
		$userController->view($id);
    });
    $app->get('/:id/cursos', function($id) use ($app, $db) {		//los cursos a los que está incripto el usuario del id
        $userController=(new \Controllers\Alumnos($app, $db));
		$userController->cursos($id);
    });

     $app->get('/:id/asistencia_curso/:curso_id/', function($id, $curso_id) use ($app, $db) {	//asistencia de un usuario a un curso
        $userController=(new \Controllers\Alumnos($app, $db));
		$userController->asistencia($id, $curso_id);
    });

    $app->get('/checkname/:name', function($name) use ($app, $db) {		//verificar si esta disponible un nombre de usuario
        $userController=(new \Controllers\Alumnos($app, $db));
        $userController->checkname($name);
    });

	
	$app->post('/registro/', function() use($app, $db){
        try {
            // get and decode JSON request body
            $request = $app->request();
            $body = $request->getBody();
            $input = json_decode($body);             
           
			$userController=(new \Controllers\Alumnos($app, $db));
			$userController->registrarAlumno((string)$input->nombre,
												(string)$input->apellido,
												(string)$input->legajo,
												(string)$input->device_address,
												(string)$input->username);
			
          } catch (Exception $e) {
            $app->response()->status(400);
            $app->response()->header('X-Status-Reason', $e->getMessage());
          }

    });
	
	$app->get('/:id/marcar_presente/:clase_id/', function($id, $clase_id) use($app, $db){		//le pone presente al usuario en una clase    
			$userController=(new \Controllers\Alumnos($app, $db));				//si no existe, crea la asistencia. Si esta como ausente, lo pasa a presente
			$userController->marcarPresente($id, $clase_id);

    });	
	
	
	$app->get('/:id/marcar_justificada/:clase_id/', function($id, $clase_id) use($app, $db){		//le pone estado "J" al usuario en una clase    
		$userController=(new \Controllers\Alumnos($app, $db));				//si no existe, crea la asistencia. Si esta como ausente, lo pasa a Justificada
		$userController->marcarJustificada($id, $clase_id);

    });	
	
});


/* Run the application */
$app->run();