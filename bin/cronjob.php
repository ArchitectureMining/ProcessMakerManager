#!/usr/bin/php
<?php

require_once(__DIR__'/../config.php');

$con = mysqli_connect($db_host, $db_user, $db_pass);
if (mysqli_connect_errno()) {
  die('Database error: ' . mysqli_connect_error());
}


$q = $con->query('SELECT `id`,`type`, `workspace`, `params` FROM `action` ORDER BY `id`');

$actions = array();

while($act = $q->fetch_object()) {
	$actions[] = array(
		'id' => $act->id,
		'command' => $act->type,
		'workspace' => $act->workspace,
		'params' => $act->params
	);
}

$q->close();

foreach($action as $a) {

	switch(strtolower($a['command'])) {
		case 'delete':
		  break;
		case 'resetpw':
		  break;
		case 'backup':
		  break;
		case 'restore':
		  break;
		case 'create':
			create($a['workspace']);
			break;
		default:
		  break;
	}
}



function create($workspace) {
	global $con;

}
