<?php
require_once("MyDBStrings.php");
class DBManager{ 
	private static $db_name = "LUCHENG";

	function connect() { 
		// Create connection
		$conn = new mysqli(MyDBStrings::$db_servername , MyDBStrings::$db_username, MyDBStrings::$db_password, self::$db_name, 3306);

		// Check connection
		if ($conn->connect_error){
		    die("Connection failed: " . $conn->connect_error);
		} 
		return $conn;
	}

	function db_select($sql_str){
		$con = $this->connect();

		$result = $con->query($sql_str);
		$con->close();
		return $result;
	}

	function db_execute($sql_str){
		$con = $this->connect();

		$result = $con->query($sql_str);
		$con->close();
	}

	function getRowsArray($result){
		$resultArray = array();
		$index = 0;
		if ($result->num_rows > 0) {
		    // output data of each row
		    while($row = $result->fetch_assoc()) {
		    	$resultArray[$index] = $row;
		    	$index++;
		    }
		}
		return $resultArray;
	}
}
?>