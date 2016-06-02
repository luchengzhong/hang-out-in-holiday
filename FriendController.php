<?php
header("Content-Type: application/json;charset=utf-8");
require_once("DBManagers/FriendsManager.php");

/**
* 
*/
class FriendController
{
	
	function handleFriendRequest()
	{
		$data = json_decode(file_get_contents('php://input'), true);
		$fm = new FriendsManager();
		//$result = $fm->getFriends($data["username"],$data["date"]);
		$result = $fm->getFriends("luchengzhong",$data["date"]);
		echo json_encode($result);
	}
}

$fc = new FriendController();
$fc->handleFriendRequest();
?>