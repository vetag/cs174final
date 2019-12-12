<?php
require_once 'dblogin.php';
require_once 'functions.php';

$conn = mysqli_connect($hn, $un, $pw, $db);
if ($conn->connect_error) {
	die(mysql_fatal_error(“error”, $conn));
}

echo <<<_END
		<html>
			<head>
				<title>userupload</title>
			</head>
		<body>
		<div class='center-screen'>
			<form method='post' action='userupload.php' enctype='multipart/form-data' style='width:500px'>

				<div style='margin-bottom: 15px'>
					<h1>User Upload</h1>
			    </div>

				<div style='margin-bottom: 20px'>
					<label style='width: 50%;'>Upload suspicious file: </label>
			    	<input type='file' name='userfile' style='width: 50%;'>
				</div>

			    <div>
			   		<input type='submit' name='submit' value='Upload'>
			   	</div>
			</form>
		</div>	
_END;


if ($_POST) {
	if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
		echo "Uploaded!";

		$content = file_get_contents($_FILES['userfile']['tmp_name']);

		$query = "SELECT * FROM midterm2.midterm2admin";
		$result = $conn->query($query);

		if ($result->num_rows > 0) {
			$malfiles = 0;
			while ($row = $result->fetch_assoc()) {

				$mal = $row['Malware'];
				$malstring = $row['MalwareString'];

				if (strpos($content, $malstring) !== false) {
					$malfiles++;
					echo "Malware detected: $mal";
					echo "<br>";
				}
			}

			if ($malfiles == 0) {
				echo "No malware detected";
			}
		} else {
			echo "<br>";
			echo "No malware to compare";
		}
	}
}

$conn->close();
echo "</body></html>";
