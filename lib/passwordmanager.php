<?php
require_once(__DIR__.'/PHPMailer/PHPMailer.php');
require_once(__DIR__.'/PHPMailer/Exception.php');
require_once(__DIR__.'/utilities.php');

require_once(__DIR__.'/../config.php');

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
