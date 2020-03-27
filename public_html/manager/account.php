<?php

session_start();

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

require_once('../../config.php');
require_once('../../lib/passwordmanager.php');

$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$error = array();

if (isset($_POST['solisid']) && isset($_POST['name'])) {

  $valid = true;

  if (strlen(trim($_POST['name'])) < 10) {
    $error[] = 'The name provided is too short. I expect at least 10 characters';
    $valid = false;
  }

  if (strlen(trim($_POST['solisid'])) < 7) {
    $error[] = 'The SOLISID provided is too short';
    $valid = false;
  }

  if ($valid) {
    function updateUser($con, $_SESSION['user'], $_POST['solisid'], $_POST['name']);

    $con->close();
    header('Location: account.php');
    exit;
  }
}

if (isset($_POST['password']) && isset($_POST['retypePassword'])) {

  if ($_POST['password'] == $_POST['retypePassword']) {
    updatePassword($con, $_SESSION['user'], $_POST['password']);
  }

  $con->close();
  header('Location: account.php');
  exit;
}


$stmt = $con->prepare('SELECT id, solisid, name, email, maxworkspaces FROM user WHERE id=?');
$stmt->bind_param('i', $_SESSION['user']);
$stmt->execute();
$stmt->store_result();

$stmt->bind_result($id, $solisid, $name, $email, $maxworkspaces);
$stmt->fetch();
$stmt->close();

$teamQ = $con->prepare('SELECT t.name FROM team AS t INNER JOIN memberof AS m ON m.team = t.id WHERE m.user = ? ORDER BY name');
$teamQ->bind_param('i', $_SESSION['user']);
$teamQ->execute();
$teamQ->store_result();

$teams = array();

$teamQ->bind_result($team_name);
while($teamQ->fetch()) {
  $teams[] = $team_name;
}
$teamQ->close();

$con->close();

?><!doctype html>
<html lang="en">
	<head>
		<title>ProcessMaker Manager - Login</title>
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
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a class="nav-link" href="processmaker.php">ProcessMaker</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/phpmyadmin" target="_blank">PhpMyAdmin</a>
          </li>
          <li class="nav-item active">
            <a class="nav-link" href="account.php">Account</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">Log out</a>
          </li>
        </ul>
      </div>
    </nav>
    <!-- content -->
    <div class="container">
    	<div class="row">
    		<div class="col-8 py-3">
    			<h1>Your account</h1>
<?php if (isset($error) && is_array($error) && (count($error) > 0)) { ?>
      <div class="alert alert-danger"><ul>
<?php foreach($error as $a) { ?>
        <li><?php echo $a ?></li>
<?php } ?>
      </ul></div>
<?php } ?>
          <form action="#" method="post">
            <div class="form-group row">
              <label for="name" class="col-sm-2 col-form-label">Name</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $name ?>" />
              </div>
            </div>
            <div class="form-group row">
              <label for="solisid" class="col-sm-2 col-form-label">SolisID</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="solisid" name="solisid" value="<?php echo $solisid ?>" />
              </div>
            </div>
            <div class="form-group row">
              <label for="email" class="col-sm-2 col-form-label">Email</label>
              <div class="col-sm-10">
                <input type="email" class="form-control-plaintext" id="email" value="<?php echo $email ?>" />
              </div>
            </div>
            <div class="form-group row">
              <label for="memberof" class="col-sm-2 col-form-label">Team</label>
              <div class="col-sm-10">
                <ul class="list-group">
<?php foreach($teams as $t) { ?>
                  <li class="list-group-item"><?php echo $t ?></li>
<?php } ?>
                </ul>
              </div>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
          </form>
				</div>
			</div>
      <div class="col-8 py-3">
        <div class="card">
          <form action="account.php" method="post">
            <div class="card-header">
              Change password
            </div>
            <div class="card-body">
              <div class="form-group row">
                <label for="password" class="col-sm-3 col-form-label">Password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control" id="password" name="password" value=""/>
                </div>
              </div>
              <div class="form-group row">
                <label for="retypePassword" class="col-sm-3 col-form-label">Retype password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control" id="retypePassword" name="retypePassword" value=""/>
                </div>
              </div>
              <button type="submit" name="change" class="btn btn-primary disabled">Change</button>
            </div>
          </form>
      </div>
		</div>

    <script src="js/app.js"></script>
	</body>
</html>
