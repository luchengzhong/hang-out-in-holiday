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
		if(!$username || !$invite_time || !$invited_array)
			return false;
		if(!$place_name)
			$place_name = "";
		if(!$coordinate)
			$coordinate = "";
		if(!$comment)
			$comment = "";
		$sql_str = "INSERT INTO invitation (create_time,invite_time,coordinate,place_name,inviter_id,comment) VALUES(?,?,?,?,?,?)";
		date_default_timezone_set("Asia/Shanghai");
		$create_time = date("Y-m-d h:i:s");

		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("ssssss", $create_time, $invite_time, $coordinate, $place_name, $username, $comment);
		$stmt->execute();

		$IID = $stmt->insert_id;
		$stmt->close();

		//insert invitation
		$sql_str = "INSERT INTO invited_user (IID,UID,status) VALUES(?,?,?)";
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
		if(!$IID || !$UID)
			return false;
		$sql_str = "INSERT INTO message (IID,UID,create_time,type,content) VALUES(?,?,?,?,?)";
		$external_con = true;
		if(!$con){
			$db_manager = new DBManager();
			$con = $db_manager->connect();
			$external_con=false;
		}
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("sssss", $IID, $UID, $create_time, $type, $content);
		$stmt->execute();

		$stmt->close();
		if(!$external_con)
			$con->close();
		return true;
	}

	//GET
	function getInvitations($username,$date)
	{
		if(!$username){
			return null;
		}
		
		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$stmt = $con->prepare("SELECT invitation.* FROM invitation,invited_user where invitation.IID = invited_user.IID and (invitation.inviter_id = ? or invited_user.UID = ?)");
		$stmt->bind_param("ss", $username, $username);
		$stmt->execute();
		$result = $stmt->get_result();
		$resultArray = array();
		$memberArray = array();
		$resultArray = $db_manager->getRowsArray($result);
		$stmt->close();
		$stmt = $con->prepare("SELECT UID,status FROM invited_user where IID = ?");
		foreach($resultArray as $i => $value) {
			$stmt->bind_param("s", $value["IID"]);
			$stmt->execute();
			$result = $stmt->get_result();
			$resultArray[$i]["invited_members"] = $db_manager->getRowsArray($result);
		}
		$stmt->close();
		$con->close();

		return $resultArray;
	}

	function getMessages($IID,$MID)
	{
		if(!$IID){
			return null;
		}
		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$sql_str = "SELECT * FROM message where IID = ?";
		if($MID)
			$sql_str = $sql_str . "and MID > ?";
		$stmt = $con->prepare($sql_str);
		if($MID)
			$stmt->bind_param("ss", $IID, $MID);
		else
			$stmt->bind_param("s", $IID);
		$stmt->execute();

		$result = $stmt->get_result();

		$stmt->close();
		$con->close();

		return $db_manager->getRowsArray($result);
	}
	/**
	* update
	*/
	function updatePlace($IID,$username,$place_name,$coordinate)
	{
		if(!$IID || !$place_name || !$coordinate || !$username){
			return false;
		}
		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$sql_str = "UPDATE invitation SET place_name = ?, coordinate = ? WHERE IID = ?";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("sss", $place_name, $coordinate, $IID);
		$stmt->execute();
		$stmt->close();

		date_default_timezone_set("Asia/Shanghai");
		$create_time = date("Y-m-d h:i:s");
		$this->addMessage($IID,$username, $create_time, "change_place",$place_name,$con);
		$con->close();
	}

	function updateTime($IID,$username,$time){
		if(!$IID || !$time || !$username){
			return false;
		}
		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$sql_str = "UPDATE invitation SET invite_time = ? WHERE IID = ?";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("ss", $time, $IID);
		$stmt->execute();
		$stmt->close();

		date_default_timezone_set("Asia/Shanghai");
		$create_time = date("Y-m-d h:i:s");
		$this->addMessage($IID,$username, $create_time, "change_time",$time,$con);
		$con->close();
	}

	function updateComment($IID,$username,$comment)
	{
		if(!$IID || !$comment || !$username){
			return false;
		}
		$db_manager = new DBManager();
		$con = $db_manager->connect();
		$sql_str = "UPDATE invitation SET comment = ? WHERE IID = ?";
		$stmt = $con->prepare($sql_str);
		$stmt->bind_param("ss", $comment, $IID);
		$stmt->execute();
		$stmt->close();

		date_default_timezone_set("Asia/Shanghai");
		$create_time = date("Y-m-d h:i:s");
		$this->addMessage($IID,$username, $create_time, "change_comment",$comment,$con);
		$con->close();
	}
}
?>