<?php
header("Content-Type: application/json;charset=utf-8");
require_once("DBManagers/DBManager.php");
require_once("DBManagers/UserInfoManager.php");
require_once("DBManagers/InvitationManager.php");
require_once("DBManagers/FriendsManager.php");
//echo "i'mlucheng\n";
/*
$db_manager = new DBManager();
$str = "select * from user_info";
$result = $db_manager->db_select($str);
$resultArray = array();
$index = 0;
if ($result->num_rows > 0) {
    // output data of each row
    
    while($row = $result->fetch_assoc()) {
    	$resultArray[$index] = $row;
    	$index++;
        echo "id: " . $row["username"]. " - Name: " . $row["name"]. " " . $row["password"]. "<br>";
        echo json_encode($row);
	}
} else {
    echo "0 results";
}
echo json_encode($resultArray);*/
/*
$userinfo_mana = new UserInfoManager();
$result = $userinfo_mana->login("luchengzhong","wohahahah");
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
        echo json_encode($row);
	}
}*/

$im = new InvitationManager();
//echo json_encode($im->addInvitation("luchengzhong",null));
//$mem = array("gouge", "habinan");
//echo json_encode($im->addInvitation("luchengzhong","2016-05-26 16:40:00",$mem,"桌游","A-A","丁哥黑鱼馆","","费用全包"));
//$im->updateTime(3,'gouge',"2016-05-27 11:06:40");
echo json_encode($im->getMessages(3,"1970-01-01 00:00:00"));
//$im->updateComment(3,'gouge',"去不去啊");
//$im->updatePlace(3,'gouge',"临平",'65.1234;66.7413');

/*$fm = new FriendsManager();
        $result = $fm->getFriends("luchengzhong",null);

        echo json_encode($result);*/
?>

