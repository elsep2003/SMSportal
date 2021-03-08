<?php
$ladp = new Ldap($ldapserver,$ldaplogin,$baseDSN); 
$InGroup = false;   
?>
<!doctype html>
<html lang="en">
		<!--
			/////////////////////////////////////////
			//
			// SMS Portal
			//
			// Developed by Chris Rouse		
			// Date Dec 2020
			//
			// 
			//
			/////////////////////////////////////////
		-->
 <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link href="fontawesome/css/all.css" rel="stylesheet"> <!--load all styles -->


	<link href="css/base.css" rel="stylesheet"> <!--load all styles -->
	<link rel="apple-touch-icon" sizes="180x180" href="images/icon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="images/icon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="images/icon/favicon-16x16.png">
	<link rel="manifest" href="images/icon/site.webmanifest" crossorigin="use-credentials">
	<link rel="mask-icon" href="images/icon/safari-pinned-tab.svg" color="#5bbad5">
	<meta name="msapplication-TileColor" content="#da532c">
	<meta name="theme-color" content="#ffffff">

	<script src="//code.jquery.com/jquery-2.1.4.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
	<script src="js/bootstrap-autocomplete.js"></script>

	<script src="js/base.js"></script>
	<title>SMS Portal</title>
</head>
<body>

<div id="busy" class="fullscreenerror d-none">
	<p class="h4">
	<img src=".\images\loading.gif" />
	Saving...
	</p>
</div>

<!-- Just an image -->
<nav class="navbar navbar-light">

<a class="navbar-brand" href="#">
	    <img src="images\northumbria-nhs-logo.svg" height="50" class="d-inline-block align-top" alt="">
	</a>
	<a class="navbar-brand" href="#">SMS Portal</a>

	<span class="navbar-text">
	<?php 
	$user = get_user();

	
	if (isAuthorised($ladp,$GroupsLdap,$user) === true)
	{
		$userDetails = array();
		$userDetails = $ladp->getUserSam($user,2);
		echo $userDetails[0]['givenname'][0]. " ".$userDetails[0]['sn'][0];
		?>
			</span>
		</nav>
		<?php

	}
	else
	{
		
		?>
			</span>
		</nav>
		<div class="container">
			<div class="alert alert-danger" role="alert">
				<h4 class="alert-heading">Access Denied</h4>
				<p>Your user account doesn’t have access to this content.</p>
			</div>
		</div>
		<?php
		require_once('footer.php');
		die();
	}

	?>


