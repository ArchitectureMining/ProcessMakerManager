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
  $q = $con->query("SELECT `id` FROM `workspace`");

  while($result = $q->fetch_assoc()) {
  	$workspaces[] = $result['id'];
  }
  $q->close();
}

foreach($workspaces as $workspace) {
	$cmd = 'cd '. $processmaker_dir. '; '.$processmaker_cmd.' artisan queue:work --stop-when-empty --workspace='.$workspace;
	exec($cmd);
}
