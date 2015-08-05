<?php

namespace Controllers;

class Controller {

	protected $app;
	protected $db;
	
	function __construct($app, $db) {
		$this->app = $app;
		$this->db = $db;
	}

}

?>

