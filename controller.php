<?php
	require("model.php");
	require("router.php");

	class Flash{
		public $msg;
		public $type;
		function __construct($msg, $type)
		{
			$this->msg = $msg;
			$this->type = $type;
		}
		public function display()
		{
			echo "<div class=\"flash " . $this->type . "\">" . $this->msg . "</div>";
		}
	}
	class Controller{
		private $model; // will model class
		private $router; // will router class
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
		private function redirect($url){
		    header("Location: /" . $url);
		}
		private function loadView($view, $data = null){
		    if (is_array($data))
		    {
		        extract($data);
		    }
		    require("Views/" . $view . ".php");
		}
		private function loadPage($user, $view, $data = null, $flash = false){
		    $this->loadView("header", array('User' => $user));
		    if ($flash !== false)
		    {
		        $flash->display();
		    }
		    $this->loadView($view, $data);
		    $this->loadView("footer");
		}
		private function checkAuth(){
		    if(isset($_COOKIE['Auth']))
		    {
		        return $this->model->userForAuth($_COOKIE['Auth']);
		    } else
		    {
		        return false;
		    }
		}

	}
?>