<?php

session_start();

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

require_once('../../config.php');
require_once('../../lib/utilities.php');

$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Check the actions
if (isset($_POST['action']) && in_array(strtolower($_POST['action']), array('delete', 'resetpw', 'backup','create', 'restore'))) {

  $wid = '';

  $continue = false;
  if (strtolower($_POST['action']) == 'create') {
    $wid = generateRandomString(8);
    $insert = $con->prepare('INSERT INTO workspace (`id`, `name`, `user`) VALUES(?, ?, ?)');
    $insert->bind_param('ssi', $wid, $_POST['name'], $_SESSION['user']);
    $insert->execute();
    $insert->close();

    $continue = true;

  } else {
    $wid = $_POST['wid'];

    $stmt = $con->prepare('SELECT id, name, status FROM workspace WHERE status < 3 AND user=? AND id= ?');
    $stmt->bind_param('is', $_SESSION['user'], $wid);
    $stmt->execute();
    $stmt->store_result();
    $continue = ($stmt->num_rows > 0);
    $stmt->close();
  }

  if ($continue) {
    if (strtolower($_POST['action']) != 'create') {
      // set the status to 2
      $setDel = $con->prepare('UPDATE workspace SET status=2 WHERE id= ?');
      $setDel->bind_param('s', $wid);
      $setDel->execute();
      $setDel->close();
    }

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

  // We always go to the new location, to clean up the $_POST.
  header('Location: processmaker.php');
  exit;
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
		<title>ProcessMaker Manager</title>
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
            <a class="nav-link active" href="processmaker.php">ProcessMaker</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/phpmyadmin" target="_blank">PhpMyAdmin</a>
          </li>
          <li class="nav-item">
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
    		<div class="col-md-11 col-lg-8 col-xl-8 py-3">
    			<h1>ProcessMaker workspaces</h1>
			Workspaces: <span class="badge badge-secondary"><?php echo count($workspaces) ?> / <?php echo $maxWorkspaces ?></span>
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Workspace</th>
                <th>Database</th>
                <th>Actions</th>
              </tr>
            </thead>
	    <tbody>
<?php $counter = 0;
foreach($workspaces as $w) { ?>
              <tr>
		            <th><?php ++$counter; echo $w['id'] ?></th>
<?php if ($w['status'] < 1) { ?>
		            <td>
                  <?php echo $w['name'] . ' (being created)'; ?>
                </td>
                <td></td>
                <td></td>
<?php } else { ?>
                <td>
                  <a href="https://pais.science.uu.nl/sys<?php echo $w['id']?>/en/neoclassic/login/login" target="_blank"><?php echo $w['name'] ?></a> <?php if ($w['status'] == 2) { ?> (action requested) <?php }?>
                </td>
                <td>
                  <pre>wf_<?php echo $w['id'] ?></pre>
                </td>
                <td>
		                <a href="#" class="btn btn-outline-primary openResetPassword"  data-wid="<?php echo $w['id']?>" data-wname="<?php echo $w['name']?>" title="Reset admin password" data-toggle="modal" data-target="#resetAdmin">
                      <svg class="bi bi-person" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M13 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1h10zm-9.995-.944v-.002.002zM3.022 13h9.956a.274.274 0 00.014-.002l.008-.002c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664a1.05 1.05 0 00.022.004zm9.974.056v-.002.002zM8 7a2 2 0 100-4 2 2 0 000 4zm3-2a3 3 0 11-6 0 3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                  <!--
                  <a href="#" class="btn btn-outline-secondary" title="Backup workspace"><i class="fas fa-cloud-download-alt"></i></a>
                  <a href="#" class="btn btn-outline-secondary" title="Restore workspace"><i class="fas fa-cloud-upload-alt"></i></a>
                  -->
		              <a href="#" class="btn btn-outline-danger deleteWorkspace" data-wid="<?php echo $w['id']?>" data-wname="<?php echo $w['name']?>" title="Remove workspace" data-toggle="modal" data-target="#deleteWorkspace">
                    <svg class="bi bi-trash" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                      <path d="M5.5 5.5A.5.5 0 016 6v6a.5.5 0 01-1 0V6a.5.5 0 01.5-.5zm2.5 0a.5.5 0 01.5.5v6a.5.5 0 01-1 0V6a.5.5 0 01.5-.5zm3 .5a.5.5 0 00-1 0v6a.5.5 0 001 0V6z"/>
                      <path fill-rule="evenodd" d="M14.5 3a1 1 0 01-1 1H13v9a2 2 0 01-2 2H5a2 2 0 01-2-2V4h-.5a1 1 0 01-1-1V2a1 1 0 011-1H6a1 1 0 011-1h2a1 1 0 011 1h3.5a1 1 0 011 1v1zM4.118 4L4 4.059V13a1 1 0 001 1h6a1 1 0 001-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z" clip-rule="evenodd"/>
                    </svg>
                  </a>
                </td>
<?php } ?>
	      </tr>
<?php } ?>
            </tbody>
	  </table>
<?php if (count($workspaces) < $maxWorkspaces) { ?>
	  <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#createWorkspace">Create</a>
<?php } ?>

			</div>
		</div>
    <div class="row py-3">
      <div class="col-md-11 col-lg-8 col-xl-8">
        <div class="alert alert-primary">
	For PhpMyAdmin, use the following username: <strong><?php echo $_SESSION['sqlusername'] ?></strong>.<br />
        The password is the password you use to login at this ProcessMaker Manager.
        </div>
      </div>
    </div>


    <div class="modal fade" id="resetAdmin" tabindex="-1" role="dialog" aria-labelledby="resetAdminLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header bg-secondary">
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
        <div class="modal-dialog modal-dialog-centered" role="document">
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

    <div class="modal fade" id="createWorkspace" tabindex="-1" role="dialog" aria-labelledby="createWorkspaceLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <form action="processmaker.php" method="post">
              <div class="modal-header bg-secondary">
                <h5 class="modal-title" id="createWorkspaceLabel">New workspace</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body container">
                <div class="form-group row">
                  <label for="name" class="col-sm-2 col-form-label">Name:</label>
                  <div class="col-sm-10">
                    <input type="text" class="form-control" id="name" name="name"/>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <input type="hidden" name="action" value="create" />
                <button type="submit" class="btn btn-success submitForm">Create</button>
                <a href="#" class="btn btn-secondary" data-dismiss="modal">Cancel</a>
              </div>
            </form>
          </div>
        </div>
    </div>

    <script src="js/app.js"></script>
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
