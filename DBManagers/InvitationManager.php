<?php
require_once("DBManager.php");
/**
* 
*/
class InvitationManager
{
	//Insert
	function addInvitation($username, $invite_time, $invited_array,$i_type, $pay_method, $place_name= "", $coordinate= "", $comment= "")
	{
		if(!isset($username) || !isset($invite_time) || !isset($invited_array))
			return false;
		if(!isset($place_name))
			$place_name = "";
		if(!isset($coordinate))
			$coordinate = "";
		if(!isset($comment))
			$comment = "";
		$sql_str = "INSERT INTO invitation (create_time,invite_time,coordinate,place_name,inviter_id,comment,update_time,type,pay_method) VALUES(?,?,?,?,?,?,?,?,?);";
		
		date_default_timezone_set('Europe/Stockholm');
		$create_time = date("Y-m-d H:i:s");

		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("sssssssss", $create_time, $invite_time, $coordinate, $place_name, $username, $comment, $create_time, $i_type, $pay_method);
		$stmt->execute();

		$IID = $stmt->insert_id;
		$stmt->close();

		//insert invitation
		$sql_str = "INSERT INTO invited_user (IID,UID,status) VALUES(?,?,?);";
		$stmt = $con->prepare($sql_str);
		$status = '0';
		foreach($invited_array as $i => $value) {
			$stmt->bind_param("iss", $IID, $value, $status);
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
	* change_comment
	* change_status
	* user_comment
	*/
	function addMessage($IID, $UID, $create_time, $type, $content, $con = NULL)
	{
		$result =array();
		if(!isset($IID) || !isset($UID)){
			$result["result"] = false;
			return $result;
		}
		$sql_str = "INSERT INTO message (IID,UID,create_time,type,content) VALUES(?,?,?,?,?);";
		$external_con = true;
		if(!isset($con) || $con == NULL){
			$db_manager = new DBManager();
			$con = $db_manager->connect();
			$external_con=false;
		}
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("issss", $IID, $UID, $create_time, $type, $content);
		$stmt->execute();
		$stmt->close();

		$sql_str = "UPDATE invitation SET update_time = ? WHERE IID = ?;";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("si", $create_time, $IID);
		$stmt->execute();

		$MID = $stmt->insert_id;
		$stmt->close();
		if(!$external_con)
			$con->close();

		
		$result["result"] = true;
		$result["MID"] = $MID;
		return $result;
	}

	//GET
	//status: created, all accepted, canceled
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
		$resultArray = array();
		$memberArray = array();
		$db_manager = new DBManager();
		$con = $db_manager->connect();

		$stmt = $con->prepare("SELECT invitation.* FROM invitation,invited_user where invitation.IID = invited_user.IID and (invitation.inviter_id = ? or invited_user.UID = ?) and invitation.update_time > ? order by invitation.update_time DESC;");
		$stmt->bind_param("sss", $username, $username, $date);
		$stmt->execute();

		$result = $stmt->get_result();

		$resultArray = $db_manager->getRowsArray($result);

		$stmt->close();
		$stmt = $con->prepare("SELECT invited_user.UID as UID, user_info.photo as photo, user_info.name as name,invited_user.status as status FROM invited_user,user_info where IID = ? AND user_info.username = invited_user.UID;");
		foreach($resultArray as $i => $value) {
			$stmt->bind_param("i", $value["IID"]);
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
			$sql_str = $sql_str . " and create_time > ?";
		$sql_str = $sql_str . " order by create_time DESC";
		$stmt = $con->prepare($sql_str);
		if(isset($create_time))
			$stmt->bind_param("is", $IID, $create_time);
		else
			$stmt->bind_param("i", $IID);
		$stmt->execute();

		$result = $stmt->get_result();

		$stmt->close();

		$sql_str = "SELECT * FROM invitation where IID = ?";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("i", $IID);
		$stmt->execute();
		$i_result = $stmt->get_result();
		$stmt->close();

		$stmt = $con->prepare("SELECT invited_user.UID as UID, user_info.photo as photo, user_info.name as name,invited_user.status as status FROM invited_user,user_info where IID = ? AND user_info.username = invited_user.UID;");
		$stmt->bind_param("i", $IID);
		$stmt->execute();
		$m_result = $stmt->get_result();
		$stmt->close();
		
		$con->close();

		
		$json["messages"] = $db_manager->getRowsArray($result);
		$json["invitation"] = $db_manager->getRowsArray($i_result);
		$json["invitation"][0]["invited_members"] = $db_manager->getRowsArray($m_result);
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
		$stmt->bind_param("ssi", $place_name, $coordinate, $IID);
		$stmt->execute();
		$stmt->close();

		//date_default_timezone_set("Asia/Shanghai");

		date_default_timezone_set('Europe/Stockholm');
		$create_time = date("Y-m-d H:i:s");
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
		$stmt->bind_param("si", $time, $IID);
		$stmt->execute();
		$stmt->close();

		
		date_default_timezone_set('Europe/Stockholm');
		$create_time = date("Y-m-d H:i:s");
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
		$stmt->bind_param("si", $comment, $IID);
		$stmt->execute();
		$stmt->close();

		
		date_default_timezone_set('Europe/Stockholm');
		$create_time = date("Y-m-d H:i:s");
		$this->addMessage($IID,$username, $create_time, "change_comment",$comment,$con);
		$con->close();
		return true;
	}
	/*
	status: 
	0 unaccepted
	1 accepted
	-1 rejected
	*/
	function updateStatus($IID,$username,$status)
	{
		if(!isset($IID) || !isset($username) || !isset($status)){
			return -2;
		}
		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$sql_str = "UPDATE invited_user SET status = ? WHERE IID = ? AND UID = ?;";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("sis",$status, $IID, $username);
		$stmt->execute();
		$stmt->close();
		
		date_default_timezone_set('Europe/Stockholm');
		$create_time = date("Y-m-d H:i:s");
		$this->addMessage($IID,$username, $create_time, "change_status",$status,$con);
		$con->close();
		return $status;
	}
}
?>