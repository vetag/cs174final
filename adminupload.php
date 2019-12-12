<?php
require_once 'dblogin.php';
require_once 'functions.php';

$SIGNATURE_LENGTH = 20;
$cipherAlphabet = "yhkqgvxfoluapwmtzecjdbsnri";
$cipherText;
$plainText;

$conn = mysqli_connect($hn, $un, $pw, $db);
if ($conn->connect_error) {
	die(mysql_fatal_error(“error”, $conn));
}

echo <<<_END
		<html>
			<head>
				<title>Decryptoid</title>
			</head>
		<div class='center-screen'>
			<form method='post' action='adminupload.php' enctype='multipart/form-data' style='width:500px'>

				<div style='margin-bottom: 15px'>
					<h1>Decryptoid</h1>
			    </div>

				<div style='margin-bottom: 20px'>
					<label style='width: 50%;'>Enter text to be encrypted/decrypted: </label>
					<input type='text' name='malware' style='width: 50%;'>
			    </div>

				<div style='margin-bottom: 20px'>
					<label style='width: 50%;'>Upload a .txt file to be encrypted/decrypted: </label>
			    	<input type='file' name='file' style='width: 50%;'>
				</div>
                
                <div style='margin-bottom: 20px'>
			   		<input type='submit' name='submit' value='Simple Substitution Encrypt'>
			   	</div>
                <div style='margin-bottom: 20px'>
			   		<input type='submit' name='submit' value='Simple Substitution Decrypt'>
			   	</div>
                <div style='margin-bottom: 20px'>
			   		<input type='submit' name='submit' value='Double Transpostion Encrypt'>
			   	</div>
                <div style='margin-bottom: 20px'>
			   		<input type='submit' name='submit' value='Double Transpostion Decrypt'>
			   	</div>
                <div style='margin-bottom: 20px'>
			   		<input type='submit' name='submit' value='RC4 Encrypt'>
			   	</div>
                <div style='margin-bottom: 20px'>
			   		<input type='submit' name='submit' value='RC4 Decrypt'>
			   	</div>
                <div style='margin-bottom: 20px'>
			   		<input type='submit' name='submit' value='DES Encrypt'>
			   	</div>
                <div style='margin-bottom: 20px'>
			   		<input type='submit' name='submit' value='DES Decrypt'>
			   	</div>
			</form>
		</div>	
_END;

function Cipher($input, $oldAlphabet, $newAlphabet, &$output)
{
	$output = "";
	$inputLen = strlen($input);

	if (strlen($oldAlphabet) != strlen($newAlphabet))
		return false;

	for ($i = 0; $i < $inputLen; ++$i)
	{
		$oldCharIndex = strpos($oldAlphabet, strtolower($input[$i]));

		if ($oldCharIndex !== false)
			$output .= ctype_upper($input[$i]) ? strtoupper($newAlphabet[$oldCharIndex]) : $newAlphabet[$oldCharIndex];
		else
			$output .= $input[$i];
	}

	return true;
}

function Encipher($input, $cipherAlphabet, &$output)
{
	$plainAlphabet = "abcdefghijklmnopqrstuvwxyz";
	return Cipher($input, $plainAlphabet, $cipherAlphabet, $output);
}

function Decipher($input, $cipherAlphabet, &$output)
{
	$plainAlphabet = "abcdefghijklmnopqrstuvwxyz";
	return Cipher($input, $cipherAlphabet, $plainAlphabet, $output);
}

if ($_POST) {
	$file = $_FILES['file']['name'];
	if (isset($_POST["malware"]) && !empty($file) && is_uploaded_file($_FILES['file']['tmp_name'])) {
		$content = file_get_contents($_FILES['file']['tmp_name']);
        $ssEncipher = Encipher($content, $cipherAlphabet, $cipherText);
        $ssDecipher = Decipher($cipherText, $cipherAlphabet, $plainText);
        echo "Simple Substitution Cipher: ";
        echo $cipherText . "<br>";
        echo "Simple Substitution Decipher: ";
        echo $plainText;
        
		$mal = $conn->real_escape_string($_POST['malware']);
		$malstring = $conn->real_escape_string($signature);
        
		//$query = "INSERT INTO `midterm2`.`midterm2admin` (`Malware`, `MalwareString`) VALUES ('$mal', '$malstring')";
		//$result = $conn->query($query);

		//if ($result === TRUE) {
		//	echo "Added " . $mal;
		//} else {
		//	echo $malstring;
		//	echo "<br>";
		//	echo "Error: " . $sql . "<br>" . $conn->error;
		//}
	//} else {
	//	echo "Error: wrong";
	//}
} else {
	echo "Error: no post";
}
    
$conn->close();
echo "</body></html>";
}