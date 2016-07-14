<?php
require_once("DBManager.php");
class UserInfoManager{
	function register($username,$password,$name,$photo){
		if(!$username || !$password || !$name || !$photo){
			return false;
		}
		date_default_timezone_set('Europe/Stockholm');
		$create_time = date("Y-m-d H:i:s");

		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$stmt = $con->prepare("INSERT INTO user_info (username, password, name, create_time, photo) VALUES (?, ?, ?, ?, ?)");
		$stmt->bind_param("sssss", $username, $password, $name, $date_time, $photo);
		$stmt->execute();
		$stmt->close();
		$con->close();
		return true;
	}

	function login($username,$password){
		if(!$username || !$password){
			return false;
		}

		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$stmt = $con->prepare("SELECT * FROM user_info where username = ? and password = ?");
		$stmt->bind_param("ss", $username, $password);
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close();
		$con->close();

		return $result;
	}
}
?>