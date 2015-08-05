<?php

/* Require Slim and plugins */
require 'Slim/Slim.php';
require 'plugins/NotORM.php';
require 'plugins/Spyc.php';

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
    (new \Controllers\Cursos($app, $db))->index();
    });

    $app->get('/:id', function($id) use ($app, $db) {
        (new \Controllers\Cursos($app, $db))->view($id);
    });
});



$app->group('/alumnos', function() use ($app, $db) {

    $app->get('/', function() use($app, $db){
        echo 'lista de alumnos';
    });

    $app->get('/:id/cursos', function($id) use ($app, $db) {
        (new \Controllers\Alumnos($app, $db))->cursos($id);
    });
});


/* Run the application */
$app->run();