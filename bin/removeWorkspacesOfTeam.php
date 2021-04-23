#!/usr/bin/php
<?php

if ($_SERVER['argc'] < 2) {
	echo 'Usage of this script: ./'.$_SERVER['argv'][0].' <team id>'.PHP_EOL;
	echo '  team id should be at most 8 characters long'.PHP_EOL;
	echo '  (c) 2020, Jan Martijn van der Werf, Utrecht University'.PHP_EOL;

	exit(1);
}

require_once(__DIR__.'/../config.php');
require_once(__DIR__.'/../lib/workspacemanager.php');
require_once(__DIR__.'/../lib/passwordmanager.php');

$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) {
  die('Database error: ' . mysqli_connect_error());
}

$team = $_SERVER['argv'][1];


$query = 'SELECT * FROM `workspace` WHERE `user` IN (SELECT `user` FROM memberof AS mm INNER JOIN team AS t ON mm.team = t.id WHERE t.code = "'.$team.'")';

$q = $con->query($query);

if (!$q) {
	die('Error while executing query: ' . $con->error);
}

$counter = 0;

while($workspace = $q->fetch_object()) {
	removeWorkspace($con, $workspace->id);
	echo $workspace->id.",".$workspace->user.PHP_EOL;
	$counter++;
}

echo PHP_EOL."Removed: ".$counter." workspaces".PHP_EOL;

?>
