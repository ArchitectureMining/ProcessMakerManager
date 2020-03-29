<?php

session_start();

$error = array();
$errorstring = 'User / password combination is unknown';

if (isset($_POST['email']) && isset($_POST['password'])) {
  require_once('../../config.php');

  $con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
  if (mysqli_connect_errno()) {
    die('Failed to connect to MySQL: ' . mysqli_connect_error());
  }

  $email = $_POST['email'];

  $stmt = $con->prepare('SELECT id, name, password, sqlusername FROM user WHERE email LIKE ?');
  $stmt->bind_param('s', $email);
  $result = $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $name, $password, $sqlusername);
    $stmt->fetch();
    if (password_verify($_POST['password'], $password)) {
      session_regenerate_id();
      $_SESSION['user'] = $id;
      $_SESSION['name'] = $name;
      $_SESSION['sqlusername'] = $sqlusername;

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
			<h1>Login</h1>
<?php if (isset($error) && is_array($error) && (count($error) > 0)) { ?>
			<div class="alert alert-danger"><ul>
<?php foreach($error as $a) { ?>
				<li><?php echo $a ?></li>
<?php } ?>
			</ul></div>
<?php } ?>
					<form action="index.php" method="post" id="form">
            <form-group class="form-group" :validator="$v.email" :messages="messages.email" label="Email address">
              <template slot-scope="{ validator, hasErrors }">
                <input class="form-control" :class="{ 'is-invalid': hasErrors && validator.$dirty, 'is-valid': !hasErrors && validator.$dirty }" type="email" name="email" v-model.trim.lazy="$v.email.$model" required />
              </template>
            </form-group>
            <form-group class="form-group" :validator="$v.password" :messages="messages.password" label="Password">
              <template slot-scope="{ validator, hasErrors }">
                <input class="form-control" :class="{ 'is-invalid': hasErrors && validator.$dirty, 'is-valid': !hasErrors && validator.$dirty }" type="password" name="password" v-model.trim.lazy="$v.password.$model" required />
              </template>
            </form-group>
						<button class="btn btn-primary" @click.prevent="submit">Log in</button>
						<a class="d-inline p-2 bg-light" href="resendpassword.php">Forgot password?</a>
						<a class="d-inline p-2 bg-light" href="register.php">Register</a>
					</form>
				</div>
				<div class="col-4">
				</div>
			</div>
		</div>

    <script src="js/app.js"></script>
    <script>
      (function () {
        new Vue({
          el: '#form',

          data: function () {
            return {
              email: '<?php echo $_POST['email'] ?? ''; ?>',
              password: '<?php echo $_POST['password'] ?? ''; ?>',

              messages: { 
                email: {
                  required: 'You forgot to provide your email address.',
                  email: 'This does\'t look like a valid email address.',
                },
                password: {
                  required: 'You forgot to provide your password.',
                }
              }
            }
          },

          validations: {
            email: {
              required: validators.required,
              email: validators.email,
            },
            password: {
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
