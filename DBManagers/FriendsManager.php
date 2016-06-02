<?php
require_once("DBManager.php");

/**
* 
*/
class FriendsManager
{
	
	function getFriends($username, $date)
	{
		$json = array();
		if(!isset($username)){
			$json["friends"] = null;
			return $json;
		}

		if(!isset($date) || $date == null){
			$date = "1970-01-01 00:00:00";
		}

		$db_manager = new DBManager();
		$con = $db_manager->connect();

		$sql_str = "SELECT * from friends where source_id = ?";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("s", $username);
		$stmt->execute();

		$friend_list = $db_manager->getRowsArray($stmt->get_result());
		$stmt->close();

		$sql_str = "SELECT * from user_info where username in (";
		foreach($friend_list as $i => $value) {
			if($i!=0)
				$sql_str = $sql_str . ", '" . $value["friend_id"] . "'";
			else
				$sql_str = $sql_str . "'" . $value["friend_id"] . "'";
		}
		$sql_str = $sql_str . ") and update_time > ? ORDER BY update_time DESC";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("s", $date);
		$stmt->execute();
		$user_info = $stmt->get_result();
		$friend_info_list = $db_manager->getRowsArray($user_info);

		/*" = ? and update_time > ?";
		$stmt = $con->prepare($sql_str);
		$index=0;
		$friend_info_list = array();
		foreach($friend_list as $i => $value) {
			$stmt->bind_param("ss", $value["friend_id"], $date);
			$stmt->execute();
			$user_info = $stmt->get_result();
			if($user_info->num_rows>0){
				$friend_info_list[$index] = $user_info->fetch_assoc();
				$index++;
			}
		}*/
		$stmt->close();
		$con->close();

		$json["friends"] = $friend_info_list;
		return $json;
	}
}

?>