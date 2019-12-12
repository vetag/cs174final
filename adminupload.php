<?php
require_once 'dblogin.php';
require_once 'functions.php';

$SIGNATURE_LENGTH = 20;


$conn = mysqli_connect($hn, $un, $pw, $db);
if ($conn->connect_error) {
	die(mysql_fatal_error(“error”, $conn));
}

echo <<<_END
		<html>
			<head>
				<title>adminupload</title>
			</head>
		<div class='center-screen'>
			<form method='post' action='adminupload.php' enctype='multipart/form-data' style='width:500px'>

				<div style='margin-bottom: 15px'>
					<h1>Admin Upload</h1>
			    </div>

				<div style='margin-bottom: 20px'>
					<label style='width: 50%;'>Malware: </label>
					<input type='text' name='malware' style='width: 50%;'>
			    </div>

				<div style='margin-bottom: 20px'>
					<label style='width: 50%;'>Upload malware file: </label>
			    	<input type='file' name='file' style='width: 50%;'>
				</div>

			    <div>
			   		<input type='submit' name='submit' value='Upload'>
			   	</div>
			</form>
		</div>	
_END;


if ($_POST) {
	$file = $_FILES['file']['name'];
	if (isset($_POST["malware"]) && !empty($file) && is_uploaded_file($_FILES['file']['tmp_name'])) {
		$content = file_get_contents($_FILES['file']['tmp_name']);
		$signature = substr($content, 0, $SIGNATURE_LENGTH);

		$mal = $conn->real_escape_string($_POST['malware']);
		$malstring = $conn->real_escape_string($signature);

		$query = "INSERT INTO `finalproj`.`finalprojadmin` (`Malware`, `MalwareString`) VALUES ('$mal', '$malstring')";
		$result = $conn->query($query);

		if ($result === TRUE) {
			echo "Added " . $mal;
		} else {
			echo $malstring;
			echo "<br>";
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	} else {
		echo "Error: wrong";
	}
} else {
	echo "Error: no post";
}

$conn->close();
echo "</body></html>";
