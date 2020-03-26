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


if (!isset($workspace_template) || !is_file($workspace_template)) {
	die('Variable $workspace_template does not point to a file in config.php!');
}

if (!isset($processmaker_cmd) || !is_file($processmaker_cmd)) {
	die('Variable $processmaker_cmd does not point to a file in config.php!');
}

if (!isset($php_cmd) || !is_file($php_cmd)) {
	die('Variable $php_cmd does not point to a file in config.php!');
}

$con = mysqli_connect($db_host, $db_user, $db_pass);
if (mysqli_connect_errno()) {
  die('Database error: ' . mysqli_connect_error());
}

$database = $con->real_escape_string('wf_'.$_SERVER['argv'][1]);

$q = $con->query("SHOW DATABASES LIKE '".$database."'");

if ($q->num_rows > 0) {
	die('Instance "'.$_SERVER['argv'][1].'" already exists!');
}

$command = $php_cmd . ' ' . $processmaker_cmd . ' workspace-restore' . $workspace_template . ' ' . $_SERVER['argv'][1];

$output = '';
$return = 0;

exec($command, $output, $return);

if ($return != 0) {
	echo $command;
	echo PHP_EOL . PHP_EOL . 'gave output: ' . PHP_EOL . PHP_EOL;
	echo $output;
	exit($return);
}
