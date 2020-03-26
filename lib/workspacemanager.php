<?php

require_once(__DIR__.'/PHPMailer/PHPMailer.php');
require_once(__DIR__.'/PHPMailer/Exception.php');

require_once(__DIR__.'/utilities.php');
require_once(__DIR__.'/../config.php');

function workspaceExists($con, $workspace) {
	$database = $con->real_escape_string('wf_'.$workspace);

	$q = $con->query("SHOW DATABASES LIKE '".$database."'");

	return ($q->num_rows > 0);
}

function resetPassword($con, $workspace, $password) {

	if (strlen($workspace) > 13) {
		return false;
	}

	if (!workspaceExists($con, $workspace)) {
		return false;
	}

	$database = $con->real_escape_string('wf_'.$workspace);

	$stmtUsers = $con->prepare("UPDATE `".$database."`.`USERS` SET `USR_USERNAME` = 'admin', `USR_PASSWORD` = md5(?) WHERE `USR_UID` = '00000000000000000000000000000001';");
	$stmtUsers->bind_param('s', $password);
	$result = $stmtUsers->execute();
	if (!$result) {
		return false;
	}
	$stmtUsers->close();

	$stmtRbac = $con->prepare("UPDATE `".$database."`.`RBAC_USERS` SET `USR_USERNAME` = 'admin', `USR_PASSWORD` = md5(?) WHERE `USR_UID` = '00000000000000000000000000000001';");
	$stmtRbac->bind_param('s', $password);
	$result = $stmtRbac->execute();
	if (!$result) {
		return false;
	}

	$stmtRbac->close();


	return true;
}


function resetPasswordAndMail($con, $workspace, $name, $email) {
	$password = generateRandomString(16);

	if (!resetPassword($con, $workspace, $password)) {
		return false;
	}

	global $fromEmail, $fromName;

  $mail = new PHPMailer\PHPMailer\PHPMailer();

  $mail->setFrom($fromEmail, $fromName);

  $mail->addAddress($email, $name);
  $mail->Subject = 'ProcessMaker Manager Login details';

  $mail->Body = <<<EOT
Dear {$name},

You requested the login details for a ProcessMaker instance. The details are as follows:

URL     : https://pais.science.uu.nl/sys{$workspace}/en/neoclassic/login/login
login   : admin
password: {$password}

If you have any questions, just reply to this email.

Best regards,

{$fromName}
EOT;

  $result = array();
  $result['password'] = $password;

  if ($mail->send()) {
    $result['success'] = true;
  } else {
    $result['success'] = false;
    $result['error'] = $mail->ErrorInfo;
  }

  return $result;
}


function createWorkspace($con, $workspace) {
	if (workspaceExists($con, $workspace)) {
		return false;
	}

	global $processmaker_cmd, $workspace_template;

	$command = $processmaker_cmd . ' workspace-restore ' . $workspace_template . ' ' . $_SERVER['argv'][1];

	$output = '';
	$return = 0;

	exec($command, $output, $return);
}
