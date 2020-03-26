<?php

if ($_SERVER['argc'] < 2) {
	echo 'Usage of this script: ./'.$_SERVER['argv'][0].' <workspace id> <password>'.PHP_EOL;
	echo '  workspace id should be at most 13 characters long'.PHP_EOL;
	echo '  (c) 2020, Jan Martijn van der Werf, Utrecht University'.PHP_EOL;

	exit(1);
}

if (strlen($_SERVER['argv'][1]) > 13) {
	die('workspace id is too long. I expected at most 13 characters. Instead I got: '.strlen($_SERVER['argv'][1]).' characters'.PHP_EOL);
}


require_once(__DIR__'/../config.php');

$database = 'wf_'.$_SERVER['argv'][1];
$password = $_SERVER['argv'][2];

$con = mysqli_connect($db_host, $db_user, $db_pass, $database);
if (mysqli_connect_errno()) {
  die('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$stmtUsers = $con->prepare("UPDATE USERS SET USR_PASSWORD=md5(?) WHERE USR_UID='00000000000000000000000000000001';");
$stmtUsers->bind_param('s', $password);
$result = $stmtUsers->execute();
if (!$result) {
	die('Failed updating USERS table: ' . $stmtUsers->error);
}
$stmtUsers->close();

$stmtRbac = $con->prepare("UPDATE RBAC_USERS SET USR_PASSWORD=md5(?) WHERE USR_UID='00000000000000000000000000000001';");
$stmtRbac->bind_param('s', $password);
$result = $stmtRbac->execute();
if (!$result) {
	die('Failed updating RBAC_USERS table: ' . $stmtRbac->error);
}

$stmtRbac->close();
$con->close();
