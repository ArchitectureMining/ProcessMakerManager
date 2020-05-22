<?php


require_once(__DIR__.'/../config.php');
$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) {
  die('Database error: ' . mysqli_connect_error());
}

$workspaces = array();

// If the workspaces are given on the command line, use those
if ($_SERVER['argc'] > 1) {
	for($i = 1 ; $i < $_SERVER['argc'] ; $i++ ) {
		$workspaces[] = $_SERVER['argv'][$i];
	}
} else {
	// get the workspaces from the database
  $q = $con->query("SELECT `id` FROM `workspace` WHERE `checked`=0");

  while($result = $q->fetch_assoc()) {
  	$workspaces[] = $result['id'];
  }
  $q->close();
}

$template = file_get_contents($supervisor_template) or die('Could not read template from: '.$supervisor_template);

foreach($workspaces as $workspace) {
	$configfilename = $supervisor_dir.'/'.$workspace.'.conf';

	var_dump($configfilename);
	
	if (!file_exists($configfilename)) {
		$content = str_replace('%%workspace%%', $workspace, $template);
		file_put_contents($configfilename, $content);
	}
}

$con->close();
