<?php

require_once(__DIR__.'/utilities.php');


function createMysqlUser($con, $username, $password) {
	$user = $con->real_escape_string($username);
	$pass = $con->real_escape_string($password);

	$con->query("CREATE USER IF NOT EXISTS '".$user."'@'localhost' IDENTIFIED BY '".$pass."';");
}


function updateMysqlUser($con, $username, $password) {
	$user = $con->real_escape_string($username);
	$pass = $con->real_escape_string($password);

	$query = "ALTER USER '".$user."'@'localhost' IDENTIFIED BY '".$pass."';";

	$con->query($query);
}

function addMysqlDatabasePermission($con, $username, $database) {
	$user = $con->real_escape_string($username);
	$db   = $con->real_escape_string($database);

	$query = "GRANT ALL PRIVILEGES ON `".$db."`.* TO '".$user."'@'localhost';";

	$con->query($query); // or die('Error in SQL: '. $con->error);;
}
