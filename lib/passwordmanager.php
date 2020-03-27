<?php
require_once(__DIR__.'/PHPMailer/PHPMailer.php');
require_once(__DIR__.'/PHPMailer/Exception.php');
require_once(__DIR__.'/utilities.php');
require_once(__DIR__.'/phpmyadminmanager.php');

require_once(__DIR__.'/../config.php');


function createUser($con, $solisid, $name, $email, $password) {

  $mysqlUser = generateRandomString(13);

  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  $insertUser = $con->prepare('INSERT INTO `user` (`solisid`, `name`, `email`, `password`, `sqlusername`) VALUES(?, ?, ?, ?, ?);');
  $insertUser->bind_param('ssss', $_POST['solisid'], $_POST['name'], $_POST['email'], $hashedPassword, $mysqlUser);
  $result = $insertUser->execute();

  $return = array();
  if ($result) {
    $userId = $con->insert_id;
    $return = array('success' => true, 'userid' => $userId);

    createMysqlUser($con, $mysqlUser, $password);

  } else {
    $return = array('success' => false, 'error' => $insertUser->error);
  }
  $insertUser->close();

  return $return;
}

function createUserAndSendPassword($con, $solisid, $name, $email) {
  $password = generateRandomString(16);

  $result = createUser($con, $solisid, $name, $email, $password);

  if ($result['success']) {
    $return = sendPassword($name, $email, $password);
    $return['userid'] = $result['userid'];
    return $return;
  } else {
    return $result;
  }
}

function addUserToTeam($con, $user, $team) {
  $insertMember = $con->prepare('INSERT INTO `memberof` (`user`, `team`) VALUES(?, ?);');
  $insertMember->bind_param('ii', $user, $team);
  $insertMember->execute();
  $insertMember->close();
}


function createAndSendNewPassword($con, $userid, $name, $email) {
  $password = generateRandomString(16);

  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $con->prepare('UPDATE user SET password=? WHERE id=?');
  $stmt->bind_param('si', $hashedPassword, $userid);
  $result = $stmt->execute();
  $stmt->close();

  if ($result) {
    sendPassword($name, $email, $password);
    return true;
  } else {
    return false;
  }
}

function updateUser($con, $userid, $solisid, $name) {
    $stmt = $con->prepare('UPDATE `user` SET `solisid`=?, `name`=? WHERE id=?');
    $stmt->bind_param('ssi', $solisid, $name, $userid);
    $stmt->execute();
    $stmt->close();
}

function updatePassword($con, $userid, $password, $sqlusername = '') {
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $con->prepare('UPDATE `user` SET `password`=? WHERE `id`=?');
  $stmt->bind_param('si', $hashedPassword, $userid);
  $stmt->execute();
  $stmt->close();

  if (!empty(sqlusername)) {
    updateMysqlUser($con, $sqlusername, $password);
  }
}


function sendPassword($name, $email, $password) {
  if (empty($name)) {
    return array('success'=>false, 'error' => 'Incorrect name gven');
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return array('success'=>false, 'error' => 'Incorrect email given');
  }

  global $fromEmail, $fromName;

  $mail = new PHPMailer\PHPMailer\PHPMailer();

  $mail->setFrom($fromEmail, $fromName);

  $mail->addAddress($email, $name);
  $mail->Subject = 'ProcessMaker Manager Login details';

  $mail->Body = <<<EOT
Dear {$name},

Welcome at the ProcessMaker Manager of pais.science.uu.nl. We have created an account for you with the following details:

URL     : https://pais.science.uu.nl/manager/
login   : {$email}
password: {$password}

If you have any questions, just reply to this email.

Best regards,

{$fromName}
EOT;

  $result = array();
  if ($mail->send()) {
    $result['success'] = true;
  } else {
    $result['success'] = false;
    $result['error'] = $mail->ErrorInfo;
  }
  return $result;
}
