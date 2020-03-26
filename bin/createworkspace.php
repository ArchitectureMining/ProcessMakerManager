<?php

if ($_SERVER['argc'] < 1) {
	echo 'Usage of this script: ./'.$_SERVER['argv'][0].' <workspace id>'.PHP_EOL;
	echo '  workspace id should be at most 13 characters long'.PHP_EOL;
	echo '  (c) 2020, Jan Martijn van der Werf, Utrecht University'.PHP_EOL;

	exit(1);
}

if (strlen($_SERVER['argv'][1]) > 13) {
	die('workspace id is too long. I expected at most 13 characters. Instead I got: '.strlen($_SERVER['argv'][1]).' characters'.PHP_EOL);
}

require_once(__DIR__.'/../config.php');
require_once(__DIR__.'/../lib/workspacemanager.php');


if (!isset($workspace_template) || !is_file($workspace_template)) {
	die('Variable $workspace_template does not point to a file in config.php!');
}

if (!isset($processmaker_cmd) || !is_file($processmaker_cmd)) {
	die('Variable $processmaker_cmd does not point to a file in config.php!');
}

$con = mysqli_connect($db_host, $db_user, $db_pass);
if (mysqli_connect_errno()) {
  die('Database error: ' . mysqli_connect_error());
}

createWorkspace($con, $_SERVER['argv'][1]);
