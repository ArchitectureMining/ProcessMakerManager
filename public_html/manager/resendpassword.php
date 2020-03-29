<?php

if (isset($_POST['email'])) {
	require_once('../../config.php');

	$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
	if (mysqli_connect_errno()) {
  	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
	}

	$stmt = $con->prepare('SELECT id, name FROM user WHERE email=?');
	$stmt->bind_param('s', $_POST['email']);
	$stmt->execute();
	$stmt->store_result();

	if ($stmt->num_rows > 0) {
		// User exists!
		$stmt->bind_result($userid, $username);
		$stmt->fetch();

		require_once('../../lib/passwordmanager.php');

		createAndSendNewPassword($con, $userid, $username, $_POST['email']);

		$stmt->close();

	}

	$con->close();

	$send = true;
}


?><!doctype html>
<html lang="en">
	<head>
		<title>ProcessMaker Manager - Forgot password</title>
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
    			<h1>Resend password</h1>
<?php if ($send) { ?>
						<div class="alert alert-success">
            If the email address is known to the system, you will receive a new password.
					  </div>
<?php } ?>
					<form action="resendpassword.php" method="post" id="form">
			            <form-group class="form-group" :validator="$v.email" :messages="messages.email" label="Email address">
			              <template slot-scope="{ validator, hasErrors }">
			                <input class="form-control" :class="{ 'is-invalid': hasErrors && validator.$dirty, 'is-valid': !hasErrors && validator.$dirty }" type="email" name="email" v-model.trim.lazy="$v.email.$model" required />
			              </template>
			            </form-group>
						<button class="btn btn-primary" @click.prevent="submit">Send new password</button>
						<a class="d-inline p-2 bg-light" href="index.php">Login</a>
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

	              messages: { 
	                email: {
	                  required: 'You forgot to provide your email address.',
	                  email: 'This does\'t look like a valid email address.',
	                },
	              }
	            }
	          },

	          validations: {
	            email: {
	              required: validators.required,
	              email: validators.email,
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
