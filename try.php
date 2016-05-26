<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
</head>
<body>
<?php
require_once("DBManager.php");
require_once("UserInfoManager.php");
require_once("InvitationManager.php");
echo "i'mlucheng\n";
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
//echo json_encode($im->getInvitations("luchengzhong","asd"));
//$im->addInvitation("luchengzhong","2016-05-26 16:40:00",null);
//$im->updateTime(3,'gouge',"2016-05-27 11:06:40");
$im->updateComment(3,'gouge',"去不去啊");
//$im->updatePlace(3,'gouge',"临平",'65.1234;66.7413');
?>
</body> 
</html>
