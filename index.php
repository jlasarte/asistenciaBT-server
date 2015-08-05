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