<?php
header("Content-Type: application/json;charset=utf-8");
require_once("DBManagers/FriendsManager.php");

/**
* 
*/
class FriendController
{
	
	function handleMemberRequest()
	{
		$data = json_decode(file_get_contents('php://input'), true);
		$fm = new FriendsManager();
		//$result = $fm->getFriends($data["username"],$data["date"]);
		$result = $fm->getmembers(null,$data["members"],"1970-01-01 01:00:00");
		$json = array();
		$json["members"] = $result;
		echo json_encode($json);
	}
}

$fc = new FriendController();
$fc->handleMemberRequest();
?>