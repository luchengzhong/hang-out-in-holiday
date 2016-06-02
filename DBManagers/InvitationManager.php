<?php
require_once("DBManager.php");
/**
* 
*/
class InvitationManager
{
	//Insert
	function addInvitation($username, $invite_time, $invited_array, $place_name= "", $coordinate= "", $comment= "")
	{
		if(!isset($username) || !isset($invite_time) || !isset($invited_array))
			return false;
		if(!isset($place_name))
			$place_name = "";
		if(!isset($coordinate))
			$coordinate = "";
		if(!isset($comment))
			$comment = "";
		$sql_str = "INSERT INTO invitation (create_time,invite_time,coordinate,place_name,inviter_id,comment,update_time) VALUES(?,?,?,?,?,?,?);";
		date_default_timezone_set("Asia/Shanghai");
		$create_time = date("Y-m-d h:i:s");

		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("sssssss", $create_time, $invite_time, $coordinate, $place_name, $username, $comment, $create_time);
		$stmt->execute();

		$IID = $stmt->insert_id;
		$stmt->close();

		//insert invitation
		$sql_str = "INSERT INTO invited_user (IID,UID,status) VALUES(?,?,?);";
		$stmt = $con->prepare($sql_str);
		foreach($invited_array as $i => $value) {
			$stmt->bind_param("iss", $IID, $value, '0');
			$stmt->execute();
		}
		$stmt->close();

		$this->addMessage($IID,$username, $create_time, "create","Create invitation",$con);
		$con->close();
		return true;
	}
	/**
	* create
	* change_place
	* change_time
	* 
	*/
	function addMessage($IID, $UID, $create_time, $type, $content, $con = NULL)
	{
		if(!isset($IID) || !isset($UID))
			return false;
		$sql_str = "INSERT INTO message (IID,UID,create_time,type,content) VALUES(?,?,?,?,?); UPDATE invitation SET update_time = ? WHERE IID = ?;";
		$external_con = true;
		if(!isset($con)){
			$db_manager = new DBManager();
			$con = $db_manager->connect();
			$external_con=false;
		}
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("sssssss", $IID, $UID, $create_time, $type, $content, $create_time, $IID);
		$stmt->execute();

		$stmt->close();
		if(!$external_con)
			$con->close();
		return true;
	}

	//GET
	function getInvitations($username,$date)
	{
		$json = array();
		if(!isset($username)){
			$json["invitations"] = null;
			return $json;
		}
		if(!isset($date)){
			$date = "1970-01-01 00:00:00";
		}
		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$stmt = $con->prepare("SELECT invitation.* FROM invitation,invited_user where invitation.IID = invited_user.IID and (invitation.inviter_id = ? or invited_user.UID = ?) and invitation.update_time > ?;");
		$stmt->bind_param("sss", $username, $username, $date);
		$stmt->execute();
		$result = $stmt->get_result();

		$resultArray = array();
		$memberArray = array();
		$resultArray = $db_manager->getRowsArray($result);

		$stmt->close();
		$stmt = $con->prepare("SELECT UID,status FROM invited_user where IID = ?;");
		foreach($resultArray as $i => $value) {
			$stmt->bind_param("s", $value["IID"]);
			$stmt->execute();
			$result = $stmt->get_result();
			$resultArray[$i]["invited_members"] = $db_manager->getRowsArray($result);
		}
		$stmt->close();
		$con->close();

		$json["invitations"] = $resultArray;
		return $json;
	}

	function getMessages($IID,$create_time)
	{
		$json = array();
		if(!isset($IID)){
			$json["messages"] = null;
			return $json;
		}
		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$sql_str = "SELECT * FROM message where IID = ?";
		if(isset($create_time))
			$sql_str = $sql_str . "and create_time > ?";
		$stmt = $con->prepare($sql_str);
		if(isset($create_time))
			$stmt->bind_param("ss", $IID, $create_time);
		else
			$stmt->bind_param("s", $IID);
		$stmt->execute();

		$result = $stmt->get_result();

		$stmt->close();
		$con->close();

		
		$json["messages"] = $db_manager->getRowsArray($result);
		return $json;
	}
	/**
	* update
	*/
	function updatePlace($IID,$username,$place_name,$coordinate)
	{
		if(!isset($IID) || !isset($place_name) || !isset($coordinate) || !isset($username)){
			return false;
		}
		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$sql_str = "UPDATE invitation SET place_name = ?, coordinate = ? WHERE IID = ?;";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("sss", $place_name, $coordinate, $IID);
		$stmt->execute();
		$stmt->close();

		date_default_timezone_set("Asia/Shanghai");
		$create_time = date("Y-m-d h:i:s");
		$this->addMessage($IID,$username, $create_time, "change_place",$place_name,$con);
		$con->close();
		return true;
	}

	function updateTime($IID,$username,$time){
		if(!isset($IID) || !isset($time) || !isset($username)){
			return false;
		}
		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$sql_str = "UPDATE invitation SET invite_time = ? WHERE IID = ?;";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("ss", $time, $IID);
		$stmt->execute();
		$stmt->close();

		date_default_timezone_set("Asia/Shanghai");
		$create_time = date("Y-m-d h:i:s");
		$this->addMessage($IID,$username, $create_time, "change_time",$time,$con);
		$con->close();
		return true;
	}

	function updateComment($IID,$username,$comment)
	{
		if(!isset($IID) || !isset($comment) || !isset($username)){
			return false;
		}
		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$sql_str = "UPDATE invitation SET comment = ? WHERE IID = ?;";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("ss", $comment, $IID);
		$stmt->execute();
		$stmt->close();

		date_default_timezone_set("Asia/Shanghai");
		$create_time = date("Y-m-d h:i:s");
		$this->addMessage($IID,$username, $create_time, "change_comment",$comment,$con);
		$con->close();
		return true;
	}
}
?>