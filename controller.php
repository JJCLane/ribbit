<?php
	require("model.php");
	require("router.php");
	class Controller{
		private $model; // will model class
		private $router // will router class
		function __construct(){
			//initialize private variables
			$this->model = new Model();
			$this->router = new Router();

			// process query string
			$queryParams = false;
			if(strlen($_GET['query']) > 0)
			{
				$queryParams = explode("/", $_GET['query']);
			}

			// handle page load
			$page = $_GET['page'];
			$endpoint = $this->router->lookup($page);
			if($endpoint === false)
			{
				header("HTTP/1.0 404 Not Found");
			} else 
			{
				$this->$endpoint($queryParams);
			}
		}
	}
?>