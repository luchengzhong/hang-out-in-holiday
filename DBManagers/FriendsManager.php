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

		// can be advanced
		$sql_str = "SELECT user_info.username, user_info.name, user_info.photo from friends,user_info where friends.source_id = ? AND friends.friend_id = user_info.username";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("s", $username);
		$stmt->execute();

		$friend_list = $db_manager->getRowsArray($stmt->get_result());
		$stmt->close();
		//$friend_info_list = $this->getmembers($con, $friend_list, $date);

		$con->close();

		$json["friends"] = $friend_list;
		return $json;
	}

	function getmembers($con, $friend_list, $date){
		$conn=$con;
		if(!isset($con)){
			$db_manager = new DBManager();
			$conn = $db_manager->connect();
		}
		$sql_str = "SELECT * from user_info where username in (";
		foreach($friend_list as $i => $value) {
			if($i!=0)
				$sql_str = $sql_str . ", '" . $value . "'";
			else
				$sql_str = $sql_str . "'" . $value . "'";
		}
		$sql_str = $sql_str . ") and update_time > ? ORDER BY update_time DESC";
		$stmt = $conn->prepare($sql_str);
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
		return $friend_info_list;
	}
}

?>