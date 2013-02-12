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
		private function indexPage($params){
			$user = $this->checkAuth();
			if($user !== false) { $this->redirect("buddies"); }
			else
			{
				$flash = false;
				if($params !== false)
				{
					$flashArr = array(
						"0" => new Flash("Your Username and/or Password was incorrect.", "error"),
						"1" => new Flash("There's already a user with that email address.", "error"),
						"2" => new Flash("That username has already been taken.", "error"),
						"3" => new Flash("Passwords don't match.", "error"),
						"4" => new Flash("Your Password must be at least 6 characters long.", "error"),
						"5" => new Flash("You must enter a valid Email address.", "error"),
						"6" => new Flash("You must enter a username.", "error"),
						"7" => new Flash("You have to be signed in to access that page.", "warning")
					);
					$flash = $flashArr[$params[0]];
				}
				$this->loadPage($user, "home", array(), $flash);
			}
		}

	}
?>