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

$app->get('/cursos', function() use($app, $db){
    (new \Controllers\Cursos($app, $db))->index();
});

$app->get('/cursos/:id', function($id) use ($app, $db) {
    (new \Controllers\Cursos($app, $db))->view($id);
});

/* Run the application */
$app->run();