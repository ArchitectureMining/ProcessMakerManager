<?php
$error = array();

// Only true if the mail has been sent.
$success = false;



if (isset($_POST['email']) && isset($_POST['team']) && isset($_POST['name']) && isset($_POST['solisid']) ) {
  require_once('../../config.php');
  require_once('../../lib/utilities.php');
  require_once('../../lib/passwordmanager.php');

  $valid = true;

  if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $error[] = 'Given email address ('.$_POST['email'].') is not valid';
    $valid = false;
  }
  if (!in_array(substr($_POST['email'], -6), array('.uu.nl', '@uu.nl')) ) {
    $error[] = 'Given email address does not belong to the UU network';
    $valid = false;
  }
  if (strlen(trim($_POST['name'])) < 10) {
    $error[] = 'The name provided is too short. I expect at least 10 characters';
    $valid = false;
  }

  if (strlen(trim($_POST['solisid'])) < 7) {
    $error[] = 'The SOLISID provided is too short';
    $valid = false;
  }

  if ($valid) {

    $con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    if (mysqli_connect_errno()) {
      die('Failed to connect to MySQL: ' . mysqli_connect_error());
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
      $mailResult = createUserAndSendPassword($con, $_POST['solisid'], $_POST['name'], $_POST['email']);

      if (isset($mailResult['userid'])) {
        addUserToTeam($con, $mailResult['userid'], $teamId);
      }

      // add user to team
      if ($mailResult['success']) {
        $success = true;
      } else {
        $error[] = 'Error while creating user: '.$mailResult['error'];
      }
    } else {
      $error[] = 'No team found with code: ' . $_POST['team'];
    }

    $con->close();
  }
}


?><!doctype html>
<html lang="en">
	<head>
		<title>ProcessMaker Manager - Register</title>
		<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="css/app.css">
	</head>
	<body>
		<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
      <a class="navbar-brand" href="index.php">ProcessMaker Manager</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigator" aria-controls="navigator" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navigator">
      </div>
    </nav>
    <!-- content -->
    <div class="container">
    	<div class="row">
		<div class="col-8 py-3">
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
              <input type="text" maxlength="8" name="solisid" class="form-control" value="<?php echo $_POST['solisid'] ?? ''; ?>" />
            </div>
            <div class="form-group">
              <label for="name">Name</label>
              <input type="text" name="name" class="form-control" value="<?php echo $_POST['name'] ?? ''; ?>" />
            </div>
						<div class="form-group">
              <label for="email">Email address</label>
              <input type="email" name="email" class="form-control" value="<?php echo $_POST['email'] ?? ''; ?>" />
            </div>
            <div class="form-group">
							<label for="team">Team code</label>
							<input type="text" maxlength="8" name="team" class="form-control" value="<?php echo $_POST['team'] ?? ''; ?>" />
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

    <script src="js/app.js"></script>
	</body>
</html>
