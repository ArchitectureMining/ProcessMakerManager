<?php


require_once(__DIR__.'/../config.php');
$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) {
  die('Database error: ' . mysqli_connect_error());
}

$workspaces = array();

// If the workspaces are given on the command line, use those
if ($_SERVER['argc'] > 1) {
	for($i = 0 ; $i < $_SERVER['argc'] ; $i++ ) {
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

foreach($workspaces as $workspace) {
	$file = $processmaker_shared.'/'. $workspace .'/db.php';
	if (file_exists($file)) {
		$content = file($file);

		$params = array();

		foreach($content as $line) {
			preg_match("/^[\\t\\f ]*define\\s*\\(\\s*'([^']+)',\\s*'([^']+)'\\s*\\)\\s*;/", $line, $matches);
				if (count($matches) == 3) {
					$params[$matches[1]] = $matches[2];
				}
		}

		if (isset($params['DB_USER']) && isset($params['DB_PASS']) && isset($params['DB_HOST'])&& isset($params['DB_NAME'])) {

			$username = $con->real_escape_string($params['DB_USER']);
			$password = $con->real_escape_string($params['DB_PASS']);
			$database = $con->real_escape_string($params['DB_NAME']);

			$pos = strpos($params['DB_HOST'], ':');
			if ($pos !== false) {
				$host = substr($params['DB_HOST'], 0, $pos);
			} else {
				$host = $params['DB_HOST'];
			}

			$hostname = $con->real_escape_string($host);

			// check if the user name exists in the database
			$query = 'CREATE USER IF NOT EXISTS \''.$username.'\'@\''.$hostname.'\' IDENTIFIED BY \''.$password.'\'';

			$con->query($query) or die("Error in query (".$query."): ".$con->sqlstate);
			//var_dump($query);
			$query = 'GRANT ALL PRIVILEGES ON `'.$database.'`.* TO \''.$username.'\'@\''.$hostname.'\'';
			$con->query($query);

			$stmt = $con->prepare('UPDATE `workspace` SET `checked`=2 WHERE `id`=?');
			$stmt->bind_param('s', $workspace);
			$stmt->execute();
			$stmt->close();
		}

	}
}

$con->close();
