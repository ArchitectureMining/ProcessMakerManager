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
    updateUser($con, $_SESSION['user'], $_POST['solisid'], $_POST['name']);

    $con->close();
    header('Location: account.php');
    exit;
  }
}

if (isset($_POST['password']) && isset($_POST['retypePassword'])) {

  if ($_POST['password'] == $_POST['retypePassword']) {
    updatePassword($con, $_SESSION['user'], $_POST['password'], $_SESSION['sqlusername']);
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
          <form action="account.php" method="post" id="form">
            <form-group class="form-group" :validator="$v.solidId" :messages="messages.solidId" label="SolisID">
              <template slot-scope="{ validator, hasErrors }">
                <input class="form-control" :class="{ 'is-invalid': hasErrors && validator.$dirty, 'is-valid': !hasErrors && validator.$dirty }" type="text" name="solisid" v-model.trim.lazy="$v.solidId.$model" required minlength="6" maxlength="8" />
              </template>
            </form-group>
            <form-group class="form-group" :validator="$v.name" :messages="messages.name" label="Name">
              <template slot-scope="{ validator, hasErrors }">
                <input class="form-control" :class="{ 'is-invalid': hasErrors && validator.$dirty, 'is-valid': !hasErrors && validator.$dirty }" type="text" name="name" v-model.trim.lazy="$v.name.$model" required />
              </template>
            </form-group>
            <div class="form-group row">
              <label for="solisid" class="col-sm-2 col-form-label">SolisID</label>
              <div class="col-sm-10">
                <input type="text" :validator="$v.solidId" :messages="messages.solidId" label="SolisID" class="form-control" id="solisid" name="solisid" value="<?php echo $solisid ?>" />
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
            <button class="btn btn-primary" @click.prevent="submit">Update</button>
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
    <script>
      (function () {
        new Vue({
          el: '#form',

          data: function () {
            return {
              solidId: '<?php echo $solisid ?? ''; ?>',
              name: '<?php echo $name ?? ''; ?>',

              messages: {
                solidId: {
                  required: 'The SolidID is a required field!',
                  minLength: 'The SolidID should be at least 7 characters long.'
                },
                name: {
                  required: 'It is required to provide a name.',
                  minLength: 'Your name should at least be 10 characters long.'
                }
              }
            }
          },

          validations: {
            solidId: {
              required: validators.required,
              minLength: validators.minLength(7),
            },
            name: {
              required: validators.required,
              minLength: validators.minLength(10),
            },
            email: {
              required: validators.required,
              email: validators.email,
              network: function (value) {
                return value.endsWith('.uu.nl') || value.endsWith('@uu.nl');
              },
            },
            team: {
              required: validators.required,
            },
          },

          methods: {
            submit: function () {
              this.$v.$touch()
              if (! this.$v.$invalid) {
                this.$el.submit();
              }
            },
          }
        })
      })();
    </script>
	</body>
</html>
