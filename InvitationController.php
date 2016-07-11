<?php
header("Content-Type: application/json;charset=utf-8");
require_once("DBManagers/InvitationManager.php");
/**
* 
*/
class InvitationController
{
	
	function handleInvitationRequest()
	{
		$data = json_decode(file_get_contents('php://input'), true);
		if(!isset($data["request_type"]))
			return;

		$im = new InvitationManager();
		$result = NULL;
		if(strcmp($data["request_type"],"getInvitations") == 0){
			$result = $im->getInvitations($data["username"],$data["date"]);
			
		}else if(strcmp($data["request_type"],"getMessages") == 0){
			$result = $im->getMessages($data["IID"],$data["create_time"]);
		}else if(strcmp($data["request_type"],"addInvitation") == 0){
			$result = array();
			$result["result"] = $im->addInvitation($data["username"],$data["invite_time"],$data["invited_array"],$data["type"],$data["pay_method"],$data["place_name"],$data["coordinate"],$data["comment"]);
		}else if(strcmp($data["request_type"],"addMessage") == 0){
			$result = array();
			$result["result"] = $im->addInvitation($data["username"],$data["invite_time"],$data["invited_array"],$data["place_name"],$data["coordinate"],$data["comment"]);
		}
		if($result == NULL){
			$result = array();
			$result["request_type"] = $data["request_type"];
		}
		echo json_encode($result);
	}
}
$ic = new InvitationController();
$ic->handleInvitationRequest();
?>