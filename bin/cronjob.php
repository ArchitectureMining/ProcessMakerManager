#!/usr/bin/php
<?php

require_once(__DIR__'/../config.php');
require_once(__DIR__'/../lib/workspacemanager.php');
require_once(__DIR__'/../lib/passwordmanager.php');

$con = mysqli_connect($db_host, $db_user, $db_pass);
if (mysqli_connect_errno()) {
  die('Database error: ' . mysqli_connect_error());
}




$q = $con->query('SELECT a.`id`, a.`type`, a.`workspace`, u.`name`, u.`email` FROM `action` AS a, `user` AS u, `workspace` AS w WHERE a.`workspace` = w.`id` AND w.`user` = u.`id` ORDER BY w.`id`;');

$actions = array();

while($act = $q->fetch_object()) {
	$actions[] = array(
		'id'        => $act->id,
		'command'   => $act->type,
		'workspace' => $act->workspace,
		'params'    => $act->params,
		'user'  => array('name' => $act->name, 'email' => $act->email)
	);
}

$q->close();

foreach($action as $a) {
  $done = false;
	switch(strtolower($a['command'])) {
		case 'delete':
		  break;
		case 'resetpw':
			$result = resetPasswordAndMail($con, $a['workspace'], $a['user']['name'], $a['user']['email']);
			if ($result['success']) {
				$done = true;
			}
		  break;
		case 'backup':
		  break;
		case 'restore':
		  break;
		case 'create':
			break;
		default:
		  break;
	}

	if ($done) {
		// Remove the task from the action table
		$con->query('UPDATE `workspace` SET `status`=1 WHERE `id` = "'.$a['workspace'].'"';)
		$q = $con->query('REMOVE FROM `action` WHERE `id`='.$a['id']);
	}
}
