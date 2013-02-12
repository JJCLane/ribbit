<?php
	class Model{
		private $db; //Holds mysqli variable

		function __construct(){
			$this->db = new mysqli('localhost', 'root', '', 'ribbit');
		}
		//--- private function for performing standard INSERTs
		private function select($table, $arr){
			$query = "SELECT * FROM " . $table;
			$pref = " WHERE ";
			foreach ($arr as $key => $value)
			{
				$query .= $pref . $key . "='" . $value . "'";
				$pref = " AND ";
			}
			$query .= ";";
			return $this->db->query($query);
		}
		private function insert($table, $arr)
		{
		    $query = "INSERT INTO " . $table . " (";
		    $pref = "";
		    foreach($arr as $key => $value)
		    {
		        $query .= $pref . $key;
		        $pref = ", ";
		    }
		    $query .= ") VALUES (";
		    $pref = "";
		    foreach($arr as $key => $value)
		    {
		        $query .= $pref . "'" . $value . "'";
		        $pref = ", ";
		    }
		    $query .= ");";
		    return $this->db->query($query);
		}
		//--- private function for performing standard DELETEs
		private function delete($table, $arr){
		    $query = "DELETE FROM " . $table;
		    $pref = " WHERE ";
		    foreach($arr as $key => $value)
		    {
		        $query .= $pref . $key . "='" . $value . "'";
		        $pref = " AND ";
		    }
		    $query .= ";";
		    return $this->db->query($query);
		}
		//--- private function for checking if a row exists
		private function exists($table, $arr){
		    $res = $this->select($table, $arr);
		    return ($res->num_rows > 0) ? true : false;
		}
		//--- function for checking if a user matches hash
		public function userForAuth($hash){
		    $query = "SELECT Users.* FROM Users JOIN (SELECT username FROM UserAuth WHERE hash = '";
		    $query .= $hash . "' LIMIT 1) AS UA WHERE Users.username = UA.username LIMIT 1";
		    $res = $this->db->query($query);
		    if($res->num_rows > 0)
		    {
		        return $res->fetch_object();
		    }
		    else
		    {
		        return false;
		    }
		}
		public function signupUser($user){
			$emailCheck = $this->exists("Users", array("email" => $user['email']));
			if($emailCheck){
				return 1;
			}
			else {
				$userCheck = $this->exists("Users", array("username" => $user['username']));
				if($userCheck){
					return 2;
				}
				else{
					$user['created_at'] = date( 'Y-m-d H:i:s');
					$user['gravatar_hash'] = md5(strtolower(trim($user['email'])));
					$this->insert("Users", $user);
					$this->authorizeUser($user);
					return true;
				}
			}
		}
		public function authorizeUser($user){
			$chars = "qazwsxedcrfvtgbyhnujmikolp1234567890QAZWSXEDCRFVTGBYHNUJMIKOLP";
			$hash = sha1($user['username']);
			for($i = 0; $i<12; $i++)
			{
				$hash .= $chars[rand(0, 61)];
			}
			$this->insert("UserAuth", array("hash" => $hash, "username" => $user['username']));
			setcookie("Auth", $hash);
		}
		public function attemptLogin($userInfo){
			if($this->exists("Users", $userInfo)){
				$this->authorizeUser($userInfo);
				return true;
			}
			else{
				return false;
			}
		}
		public function logoutUser($hash){
			$this->delete("UserAuth", array("hash" => $hash));
			setcookie ("Auth", "", time() - 3600);
		}
		public function getUserInfo($user)
		{
			$query = "SELECT ribbit_count, IF(ribbit IS NULL, 'You have no Ribbits', ribbit) as ribbit, followers, following ";
			$query .= "FROM (SELECT COUNT(*) AS ribbit_count FROM Ribbits WHERE user_id = " . $user->id . ") AS RC ";
			$query .= "LEFT JOIN (SELECT user_id, ribbit FROM Ribbits WHERE user_id = " . $user->id . " ORDER BY created_at DESC LIMIT 1) AS R ";
			$query .= "ON R.user_id = " . $user->id . " JOIN ( SELECT COUNT(*) AS followers FROM Follows WHERE followee_id = " . $user->id;
			$query .=  ") AS FE JOIN (SELECT COUNT(*) AS following FROM Follows WHERE user_id = " . $user->id . ") AS FR;";
			$res = $this->db->query($query);
			return $res->fetch_object();
		}
		public function getFollowersRibbits($user)
		{
			$query = "SELECT name, username, gravatar_hash, ribbit, Ribbits.created_at FROM Ribbits JOIN (";
			$query .= "SELECT Users.* FROM Users LEFT JOIN (SELECT followee_id FROM Follows WHERE user_id = ";
			$query .= $user->id . " ) AS Follows ON followee_id = id WHERE followee_id = id OR id = " . $user->id;
			$query .= ") AS Users on user_id = Users.id ORDER BY Ribbits.created_at DESC LIMIT 10;";
			$res = $this->db->query($query);
			$fribbits = array();
			while($row = $res->fetch_object())
			{
				array_push($fribbits, $row);
			}
			return $fribbits;
		}
		public function postRibbit($user, $text){
			$r = array(
				"ribbit" => $text,
				"created_at" => date( 'Y-m-d H:i:s'),
				"user_id" => $user->id
			);
			$this->insert("Ribbits", $r);
		}

	}
?>