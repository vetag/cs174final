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



function splitKey($keyInput)
{
    $key = array();
    for ($i=0; $i<strlen($keyInput); $i++)
    {
        $key[$i] = $keyInput[$i];
    }
    return $key;
}
	
	#Finding order of the characters in the key (permutation order)
function findKeyOrder($key)
{
    $order = array();
    for ($i = 0; $i < count($key); $i++)
    {
        $order[$i] = -1;
    }
    $tempArr = $key;
    sort($tempArr);
		
    for ($i=0; $i<sizeof($tempArr); $i++)
    {
        for ($j=0; $j<sizeof($key); $j++)
        {
            if (($tempArr[$i] == $key[$j]) && ($order[$j] == -1))
            {
				$order[$j] = $i;//here
				break;
            }
        }
    }
    return $order;
}
	
function encrypt($key1, $key2, $text)
{
    $ciphertext = columnarEncrypt($key1, $text);
    $ciphertext = columnarEncrypt($key2, $ciphertext);
    return $ciphertext;
}
	
function columnarEncrypt($keyInput, $text)
{
    $key = splitKey($keyInput);
    $order = findKeyOrder($key);
    $numColumns = count($key);
    $numRows = ceil(strlen($text)/$numColumns);
		
    #Initializing and setting up pre-transpose 2d array for cipherText
    $ciphertext = array();
    $textLocation = 0;
    for ($row = 0; $row<$numRows; $row++)
    {
        for ($column = 0; $column < $numColumns; $column++)
        {
            if ($textLocation<strlen($text))
            {
				$ciphertext[$row][$column] = $text[$textLocation];
            }
            else
            {
				$ciphertext[$row][$column] = '|';
            }
            $textLocation++;
        }
    }
		
	#Transposing cipherText and inputting into newCipherText
	$newCipherText = "";
	for ($i =0; $i<sizeof($order);$i++)
	{
		$j = 0;
		while ($order[$j] != $i)
		{
            $j++;
        }
        for ($row = 0; $row<$numRows; $row++)
        {
            $newCipherText = $newCipherText . $ciphertext[$row][$j];
        }
    }
    $newCipherText = str_replace('|','',$newCipherText);
    return $newCipherText;
}
	
function decrypt($key1, $key2, $ciphertext)
{
    $text = columnarDecrypt($key1, $ciphertext);
    $text = columnarDecrypt($key2, $text);
    return $text;
}
	
function columnarDecrypt($keyInput, $encryptedText)
{
    $key = splitKey($keyInput);
    $order = findKeyOrder($key);
	$numColumns = count($key);
    $numRows = ceil(strlen($encryptedText)/$numColumns);
    
	$ciphertext = array();
    for ($i = 0; $i<$numRows; $i++)
	{
        for($j =0; $j<$numColumns;$j++)
        {
            $ciphertext[$i][$j] = "-1";
        }
    }
    
    #Filling empty slots of 2d array to avoid characters-out-out-order transpositions
    $emptySlots = ($numRows*$numColumns) - strlen($encryptedText);
    for($j=$numColumns-$emptySlots; $j<$numColumns; $j++)
	{
        $ciphertext[$numRows-1][$j] = '|';
    }
    
	#Transposing
    $newCipherText = "";
    $textLocation = 0;
    for ($i =0; $i<sizeof($order);$i++)
    {
		$j = 0;
        while ($order[$j] != $i)
        {
            $j++;
        }
        for ($row = 0; $row<$numRows; $row++)
        {
            if ($ciphertext[$row][$j] == '|')
            {
                break;
            }
            else if ($textLocation<strlen($encryptedText))
            {
                $ciphertext[$row][$j] = $encryptedText[$textLocation];
            }
            else
            {
                $ciphertext[$row][$j] = ' ';
            }
            $textLocation++;
        }
    }
    
    #newCipherText will have the entire decrypted text as a string
    for ($row = 0; $row<$numRows; $row++)
    {
        for ($column = 0; $column < $numColumns; $column++)
        {
            $newCipherText = $newCipherText . $ciphertext[$row][$column];
        }
    }
    $newCipherText = str_replace('|','',$newCipherText);
    return $newCipherText;
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
        echo $plainText. "<br>";

        $dtEncrypt = encrypt('toast','peanuts', $content);
        echo "Double Transposition Cipher: ";
        echo $dtEncrypt . "<br>";
        $dtDecrypt = decrypt('peanuts', 'toast', $dtEncrypt);
        echo "Double Transposition Decipher: ";
        echo $dtDecrypt . "<br>";
        
		$mal = $conn->real_escape_string($_POST['malware']);
		$malstring = $conn->real_escape_string($signature);
        
} else {
	echo "Error: no post";
}
    
$conn->close();
echo "</body></html>";
}
