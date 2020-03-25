<?php

session_start();

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

require_once('config.php');

$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$stmt = $con->prepare('SELECT maxworkspaces FROM user WHERE id=?');
$stmt->bind_param('i', $_SESSION['user']);
$stmt->execute();
$stmt->store_result();

$maxWorkspaces = 0;

if ($stmt->num_rows > 0) {
  $stmt->bind_result($maxWorkspaces);
  $stmt->fetch();
  $stmt->close();
}

// Get all workspaces in an array
$workspaces = array();
$stmt = $con->prepare('SELECT id, name FROM workspace WHERE user=?');
$stmt->bind_param('i', $_SESSION['user']);
$stmt->execute();
$stmt->store_result();

$stmt->bind_result($wid, $wname);
while($stmt->fetch()) {
	$workspaces[] = array('id' => $wid, 'name'=>$wname);
}
$stmt->close();

$con->close();
?><!doctype html>
<html lang="en">
	<head>
		<title>ProcessMaker Manager - Login</title>
		<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/all.css" />
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
            <a class="nav-link active" href="processmaker.html">ProcessMaker</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/phpmyadmin" target="_blank">PhpMyAdmin</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="account.html">Account</a>
          </li>
        </ul>
      </div>
    </nav>
    <!-- content -->
    <div class="container">
    	<div class="row">
    		<div class="col-md-11 col-lg-8 col-xl-8">
    			<h1>ProcessMaker workspaces</h1>
			Workspaces: <span class="badge badge-secondary"><?php echo count($workspaces) ?> / <?php echo $maxWorkspaces ?></span>
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Workspace</th>
                <th></th>
              </tr>
            </thead>
	    <tbody>
<?php $counter = 0;
foreach($workspaces as $w) { ?>
              <tr>
		<th><?php echo ++$counter ?></th>
		<td><a href="https://pais.science.uu.nl/sys<?php echo $w['id']?>/en/neoclassic/login/login" target="_blank"><?php echo $w['name'] ?></a></td>
                <td>
                  <a href="#" class="btn btn-outline-primary" title="Reset admin password"><i class="far fa-address-card"></i></a>
                  <a href="#" class="btn btn-outline-secondary" title="Backup workspace"><i class="fas fa-cloud-download-alt"></i></a>
                  <a href="#" class="btn btn-outline-secondary" title="Restore workspace"><i class="fas fa-cloud-upload-alt"></i></a>
                  <a href="#" class="btn btn-outline-danger" title="Remove workspace"><i class="far fa-times-circle"></i></a>
                </td>
	      </tr>
<?php } ?>
            </tbody>
	  </table>
<?php if (count($workspaces) < $maxWorkspaces) { ?>
	  <a href="#" class="btn btn-primary">Create</a>
<?php } ?>
				</div>
			</div>
		</div>

		<script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/popper-1.16.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
	</body>
</html>
