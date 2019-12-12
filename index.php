<?php
require_once 'dblogin.php';
require_once 'functions.php';

$conn = mysqli_connect($hn, $un, $pw, $db);
if ($conn->connect_error) {
	die(mysql_fatal_error(“error”, $conn));
}

$isValid = true;

if (isset($_POST['username']) && isset($_POST['password'])) {

	$username = $conn->real_escape_string($_POST['username']);
	$userHash = hash('sha256', $username);

	$password = $conn->real_escape_string($_POST['password']);

	$query = "SELECT * FROM finalproj.finalproj WHERE Username='$username' AND password = '$password'";
	$finalprojUsername = "";
	$finalprojPassword = "";
	$res = mysqli_query($conn, $query);
	if ($res->num_rows > 0) {
		if (!$res) {
			echo 'Could not find username and password';
		} else {
			while ($row = mysqli_fetch_row($res)) {
				echo 'username: ' . $row['Username'];
				echo 'password: ' . $row['Password'];
				echo 'salt: ' . $row['Salt'];
				$finalprojUsername = $row['Username'];
				$finalprojPassword = $row['Password'];
				$finalprojSalt = $row['Salt'];
			}
		}
		$passSalted = $password . $finalprojSalt;
		$passHash = hash('sha256', $passSalted);
		if ($userHash == $finalprojUsername && $passHash == $finalprojPassword) {
			echo 'Login Success!';
			header("Location: http://localhost:8000/adminupload.php");
		} else {
			echo 'Login Failed.';
			exit;
		}
		mysqli_free_result($res);
	}
} else if (isset($_POST['userupload'])) {
	header("Location: http://localhost:8000/userupload.php");
	exit;
}

echo <<<_END
		<html>
			<head>
				<title>Final Project</title>
			</head>
		<body>
		<div class='center-screen'>
			<form method='post' action='adminupload.php' enctype='multipart/form-data' style='width:500px; margin-bottom:0px'>
				<div style='margin-bottom: 5px'>
					<h1>Admin Login</h1>
			    </div>

				<div style='margin-bottom: 5px'>
					<label style='width: 50%;'>Username: </label>
					<input type='text' name='username'>
			    </div>

				<div style='margin-bottom: 20px'>
			    	<label style='width: 50%;'>Password: </label>
					<input type='text' name='password'>
				</div>

			    <div>
			   		<input type='submit' value='Login'>
			   	</div>
			</form>



		</div>	
_END;

echo "</body></html>";
