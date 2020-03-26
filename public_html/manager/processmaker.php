<?php

session_start();

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

require_once('../../config.php');

$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Check the actions
if (isset($_POST['action']) && in_array(strtolower($_POST['action']), array('delete', 'resetpw', 'backup','create', 'restore'))) {
  $wid = $_POST['wid'];

  $stmt = $con->prepare('SELECT id, name, status FROM workspace WHERE status < 3 AND user=? AND id= ?');
  $stmt->bind_param('is', $_SESSION['user'], $wid);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {

    // set the status to 2
    $setDel = $con->prepare('UPDATE workspace SET status=2 WHERE id= ?');
    $setDel->bind_param('s', $wid);
    $setDel->execute();
    $setDel->close();

    $action = $con->prepare('INSERT INTO action (type, workspace, params ) VALUES (?, ?, ?)');

    $params = '';
    if (isset($_POST['params'])) {
      $params = $_POST['params'];
    }

    $action->bind_param('sss', $_POST['action'], $wid, $params);
    $action->execute();
    $action->close();

  } else {
    // Either the wid does not exist any more, the status is already inactive,
    // or it does not belong to the current user!
  }
  $stmt->close();
}


// Get all the data
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
$stmt = $con->prepare('SELECT id, name, status FROM workspace WHERE status < 3 AND user=?');
$stmt->bind_param('i', $_SESSION['user']);
$stmt->execute();
$stmt->store_result();

$stmt->bind_result($wid, $wname, $wstatus);
while($stmt->fetch()) {
	$workspaces[] = array('id' => $wid, 'name'=>$wname, 'status' => $wstatus);
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
		            <td><?php if ($w['status'] < 1) { ?>
                  <?php echo $w['name'] . ' (being created)'; ?>
                <?php } else { ?>
                  <a href="https://pais.science.uu.nl/sys<?php echo $w['id']?>/en/neoclassic/login/login" target="_blank"><?php echo $w['name'] ?></a> <?php if ($w['status'] == 2) { ?> (action requested) <?php }?></td>
                  <?php } ?>
                <td>
                  <a href="#" class="btn btn-outline-primary openResetPassword"  data-wid="<?php echo $w['id']?>" data-wname="<?php echo $w['name']?>" title="Reset admin password" data-toggle="modal" data-target="#resetAdmin"><i class="far fa-address-card"></i></a>
                <!--
                  <a href="#" class="btn btn-outline-secondary" title="Backup workspace"><i class="fas fa-cloud-download-alt"></i></a>
                  <a href="#" class="btn btn-outline-secondary" title="Restore workspace"><i class="fas fa-cloud-upload-alt"></i></a>
                -->
                  <a href="#" class="btn btn-outline-danger deleteWorkspace" data-wid="<?php echo $w['id']?>" data-wname="<?php echo $w['name']?>" title="Remove workspace" data-toggle="modal" data-target="#deleteWorkspace"><i class="far fa-times-circle"></i></a>
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

    <div class="modal fade" id="resetAdmin" tabindex="-1" role="dialog" aria-labelledby="resetAdminLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="resetAdminLabel">Reset password for '<span class="wname"></span>'</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to reset the password of workspace '<span class="wname"></span>'?.</p>
              <p>If you answer yes, the new settings will be sent by email within 5 minutes.</p>
            </div>
            <div class="modal-footer">
              <form action="processmaker.php" method="post">
                <input type="hidden" name="wid" id="wid" value="" />
                <input type="hidden" name="action" value="resetpw" />
                <button type="submit" class="btn btn-success submitForm">Yes</button>
                <a href="#" class="btn btn-danger" data-dismiss="modal">No</a>
              </form>
            </div>
          </div>
        </div>
    </div>

    <div class="modal fade" id="deleteWorkspace" tabindex="-1" role="dialog" aria-labelledby="deleteWorkspaceLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header bg-danger">
              <h5 class="modal-title" id="deleteWorkspaceLabel">Delete '<span class="wname"></span>'</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to <strong>delete</strong> workspace '<span class="wname"></span>'?.</p>
            </div>
            <div class="modal-footer">
              <form action="processmaker.php" method="post">
                <input type="hidden" name="wid" id="wid" value="" />
                <input type="hidden" name="action" value="delete" />
                <button type="submit" class="btn btn-success submitForm">Yes</button>
                <a href="#" class="btn btn-danger" data-dismiss="modal">No</a>
              </form>
            </div>
          </div>
        </div>
    </div>

		<script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/popper-1.16.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
      $(document).on("click", ".openResetPassword", function () {
        var wId = $(this).data('wid');
        var wName = $(this).data('wname');
        $("#resetAdmin #wid").val( wId );
        $("#resetAdmin .wname").text( wName );
      });

      $(document).on("click", ".deleteWorkspace", function () {
        var wId = $(this).data('wid');
        var wName = $(this).data('wname');
        $("#deleteWorkspace #wid").val( wId );
        $("#deleteWorkspace .wname").text( wName );
      });

      $(document).on("click", ".submitForm", function() {
        $(this).closest('form:first').submit();
      });

    </script>
	</body>
</html>
