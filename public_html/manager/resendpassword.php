<!doctype html>
<html lang="en">
	<head>
		<title>ProcessMaker Manager - Forgot password</title>
		<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<link rel="stylesheet" href="css/bootstrap.min.css" />
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
    		<div class="col-8">
    			<h1>Resend password</h1>
					<form action="resend.php">
						<div class="form-group">
							<label for="email">Email address</label>
							<input type="email" name="email" class="form-control" />
						</div>
						<button type="submit" class="btn btn-primary">Send new password</button>
						<a class="d-inline p-2 bg-light" href="login.html">Login</a>
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
