<?php

session_start();

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}
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
            <a class="nav-link" href="processmaker.html">ProcessMaker</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/phpmyadmin" target="_blank">PhpMyAdmin</a>
          </li>
          <li class="nav-item active">
            <a class="nav-link" href="account.php">Account</a>
          </li>
        </ul>
      </div>
    </nav>
    <!-- content -->
    <div class="container">
    	<div class="row">
    		<div class="col-8">
    			<h1>Your account</h1>
          <form action="#" method="post">
            <div class="form-group row">
              <label for="name" class="col-sm-2 col-form-label">Name</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="name" value="Jan Martijn van der Werf" />
              </div>
            </div>
            <div class="form-group row">
              <label for="solisid" class="col-sm-2 col-form-label">SolisID</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="solisid" value="1234567" />
              </div>
            </div>
            <div class="form-group row">
              <label for="email" class="col-sm-2 col-form-label">Email</label>
              <div class="col-sm-10">
                <input type="email" class="form-control-plaintext" id="email" value="j.m.e.m.vanderwerf@uu.nl" />
              </div>
            </div>
            <div class="form-group row">
              <label for="memberof" class="col-sm-2 col-form-label">Team</label>
              <div class="col-sm-10">
                <input type="text" class="form-control-plaintext" id="memberof" value="Informatiesystemen 2019/2020" />
              </div>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
          </form>
				</div>
			</div>
		</div>

		<script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/popper-1.16.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
	</body>
</html>
