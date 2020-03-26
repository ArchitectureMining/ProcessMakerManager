<?php
$error = array();

// Only true if the mail has been sent.
$success = false;



if (isset($_POST['email']) && isset($_POST['team']) && isset($_POST['name']) && isset($_POST['solisid'])) {
  require_once('../../config.php');
  require_once('../../lib/passwordmanager.php');

  $con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
  if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
  }

  // 1. Check if the team exists
  $stmt = $con->prepare('SELECT id, name FROM team WHERE code=?');
  $stmt->bind_param('s', $_POST['team']);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    // The team exists!
    $stmt->bind_result($teamId, $teamName);
    $stmt->fetch();
    $stmt->close();

    // 2. Create a random password, and create the user

    $password = generateRandomString(16);

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insertUser = $con->prepare('INSERT INTO `user` (`solisid`, `name`, `email`, `password`) VALUES(?, ?, ?, ?);');
    $insertUser->bind_param('ssss', $_POST['solisid'], $_POST['name'], $_POST['email'], $hashedPassword);
    $result = $insertUser->execute();

    if ($result) {
      // get inserted id
      $userId = $con->insert_id;

      // 3. Connect the user to the right team
      $insertMember = $con->prepare('INSERT INTO `memberof` (`user`, `team`) VALUES(?, ?);');
      $insertMember->bind_param('ii', $userId, $teamId);
      $insertMember->execute();
      $insertMember->close();

      // 4. Send an email to the user with the password
      $name = $_POST['name'];
      $email = $_POST['email'];

      $mailResult = sendPassword($_POST['name'], $_POST['email'], $password);

      if ($mailResult['success']) {

        $success = true;

      } else {
	echo 'a';
        $error[] = 'Error while sending message: '.$mailResult['error'];
      }
    } else {
	echo 'b';
	$error[] = 'Error while creating user: '.$insertUser->error;
    }
    $insertUser->close();
  } else {
    $error[] = 'No team found with code: ' . $_POST['team'];
  }

  $con->close();
}


?><!doctype html>
<html lang="en">
	<head>
		<title>ProcessMaker Manager - Register</title>
		<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<link rel="stylesheet" href="css/bootstrap.min.css" />
	</head>
	<body>
		<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
      <a class="navbar-brand" href="#">ProcessMaker Manager</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigator" aria-controls="navigator" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navigator">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a class="nav-link" href="processmaker.php">ProcessMaker</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/phpmyadmin" target="_blank">PhpMyAdmin</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="account.php">Account</a>
          </li>
        </ul>
      </div>
    </nav>
    <!-- content -->
    <div class="container">
    	<div class="row">
		<div class="col-8">
<?php if ($success) { ?>
          <div class="alert alert-success">
            Registration was successful. You received an e-mail with the login details.
	  </div>
<?php } else { ?>
    			<h1>Register</h1>
<?php if (isset($error) && is_array($error) && (count($error) > 0)) {
?>          <div class="alert alert-danger"><ul>
<?php
  foreach($error as $a) {
?>           <li><?php echo $a ?></li>
<?php }
?>
            </ul></div>
<?php
  }
?>
    			<form action="register.php" method="post">
						<div class="form-group">
              <label for="solisid">SolisID</label>
              <input type="text" maxlength="8" name="solisid" class="form-control" />
            </div>
            <div class="form-group">
              <label for="name">Name</label>
              <input type="text" name="name" class="form-control" />
            </div>
						<div class="form-group">
              <label for="email">Email address</label>
              <input type="email" name="email" class="form-control" />
            </div>
            <div class="form-group">
							<label for="team">Team code</label>
							<input type="text" maxlength="8" name="team" class="form-control" />
						</div>
						<button type="submit" class="btn btn-primary">Register</button>
						<a class="d-inline p-2 bg-light" href="index.php">Login</a>
					</form>
				</div>
<?php } ?>
				<div class="col-4">
				</div>
			</div>
		</div>

		<script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/popper-1.16.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
	</body>
</html>
