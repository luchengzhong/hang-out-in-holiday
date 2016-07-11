<?php
header("Content-Type: application/json;charset=utf-8");
require_once("DBManagers/InvitationManager.php");

/**
* 
*/
class MessageController
{
	
	function handleMessageRequest()
	{
		$data = json_decode(file_get_contents('php://input'), true);
		$im = new InvitationManager();
		//$result = $fm->getFriends($data["username"],$data["date"]);
		$result = $im->getMessages($data["IID"],null);
		echo json_encode($result);
	}
}

$mc = new MessageController();
$mc->handleMessageRequest();
?>