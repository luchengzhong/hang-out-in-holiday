<?php
require_once("DBManagers/InvitationManager.php");
/**
* 
*/
class InvitationController
{
	
	function handleInvitationRequest()
	{
		if(!isset($_POST["request_type"]))
			return;

		$im = new InvitationManager();
		$result = NULL;
		if(strcmp($_POST["request_type"],"getInvitations") == 0){
			$result = $im->getInvitations($_POST["username"],$_POST["date"]);
		}else if(strcmp($_POST["request_type"],"getMessages") == 0){
			$result = $im->getMessages($_POST["IID"],$_POST["create_time"]);
		}else if(strcmp($_POST["request_type"],"addInvitation") == 0){
			$result = new array();
			$result["result"] = $im->addInvitation($_POST["username"],$_POST["invite_time"],$_POST["invited_array"],$_POST["place_name"],$_POST["coordinate"],$_POST["comment"]);
		}else if(strcmp($_POST["request_type"],"addMessage") == 0){
			$result = new array();
			$result["result"] = $im->addInvitation($_POST["username"],$_POST["invite_time"],$_POST["invited_array"],$_POST["place_name"],$_POST["coordinate"],$_POST["comment"]);
		}
		echo json_encode($result);
	}
}

header('Content-Type: application/json');
$ic = new InvitationController();
$ic->handleInvitationRequest();
?>