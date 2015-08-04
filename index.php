<?php

/* Require Slim and plugins */
require 'Slim/Slim.php';
require 'plugins/NotORM.php';

/* Database Configuration */
$dbhost   = 'localhost';
$dbuser   = 'root';
$dbpass   = '';
$dbname   = 'movilesbluetooth';
$dbmethod = 'mysql:dbname=';
$charset = 'charset=utf8';

$dsn = $dbmethod.$dbname.";".$charset;
$pdo = new PDO($dsn, $dbuser, $dbpass);
$db = new NotORM($pdo);

/* Register autoloader and instantiate Slim */
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

/* Routes */
$app->get('/', function(){
    echo 'App BT';
});

// Get all cars
$app->get('/cursos', function() use($app, $db){
    $cursos = array();
    foreach ($db->cursos() as $curso) {
        $cursos[]  = array(
            'id' => $curso['id'],
            'nombre' => $curso['nombre'],
            'descripccion' => $curso['descripccion'],
            'horarios' => $curso['horarios'],
            'profesor' => $curso->usuario['nombreusuario']
        );
    }
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($cursos, JSON_FORCE_OBJECT);
});

$app->get('/cursos/:id', function($id) use ($app, $db) {
    $app->response()->header("Content-Type", "application/json");
    $curso = $db->cursos()->where('id', $id);
    $inscripciones = $db->inscripcion()->where('cursos_id', $id);
    $alumnos = [];
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
    }
    else{
        echo json_encode(array(
            'status' => false,
            'message' => "El curso $id no existe"
        ));
    }
});

/* Run the application */
$app->run();