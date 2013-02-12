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
	}
?>