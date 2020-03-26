<?php

session_start();

$error = array();
$errorstring = 'User / password combination is unknown';

if (isset($_POST['login']) && isset($_POST['password'])) {
  require_once('../../config.php');

  $con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
  if (mysqli_connect_errno()) {
    exit('Failed to connect to MySQL: ' . mysqli_connect_error());
  }

  $email = $_POST['login'];

  $stmt = $con->prepare('SELECT id, name, password FROM user WHERE email LIKE ?');
  $stmt->bind_param('s', $email);
  $result = $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $name, $password);
    $stmt->fetch();
    if (password_verify($_POST['password'], $password)) {
      session_regenerate_id();
      $_SESSION['user'] = $id;
      $_SESSION['name'] = $name;

    } else {
        $error[] = $errorstring;
    }
    $stmt->close();
  } else {
    $error[] = $errorstring;
  }

  $con->close();
}

if (isset($_SESSION['user'])) {
  header('Location: processmaker.php');
  exit;
}

?><!doctype html>
<html lang="en">
	<head>
		<title>ProcessMaker Manager - Login</title>
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
    </nav>
    <!-- content -->
    <div class="container">
    	<div class="row">
    		<div class="col-8">
			<h1>Login</h1>
<?php if (isset($error) && is_array($error) && (count($error) > 0)) { ?>
			<div class="alert alert-danger"><ul>
<?php foreach($error as $a) { ?>
				<li><?php echo $a ?></li>
<?php } ?>
			</ul></div>
<?php } ?>
					<form action="index.php" method="post">
						<div class="form-group">
							<label for="login">Email address</label>
							<input type="email" name="login" class="form-control" />
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" name="password" class="form-control" />
						</div>
						<button type="submit" class="btn btn-primary">Log in</button>
						<a class="d-inline p-2 bg-light" href="resendpassword.php">Forgot password?</a>
						<a class="d-inline p-2 bg-light" href="register.php">Register</a>
					</form>
				</div>
				<div class="col-4">
				</div>
			</div>
		</div>

		<script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/popper-1.16.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
	</body>
</html>
