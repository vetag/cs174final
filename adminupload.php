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


//Simple Substitution
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

function ssEncipher($input, $cipherAlphabet, &$output)
{
	$plainAlphabet = "abcdefghijklmnopqrstuvwxyz";
	return Cipher($input, $plainAlphabet, $cipherAlphabet, $output);
}

function ssDecipher($input, $cipherAlphabet, &$output)
{
	$plainAlphabet = "abcdefghijklmnopqrstuvwxyz";
	return Cipher($input, $cipherAlphabet, $plainAlphabet, $output);
}

//Double Transposition
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
	
function dtEncipher($key1, $key2, $text)
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
	
function dtDecipher($key1, $key2, $ciphertext)
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


//RC4
function rc4Cipher($key, $plainText) 
{
    $s = array();
    //Initialize array of 256 bytes
    for ($i = 0; $i < 256; $i++) 
    {
        $s[$i] = $i;
    }
    //Secret key array
    $t = array();
    for ($i = 0; $i < 256; $i++) 
    {
        $t[$i] = ord($key[$i % strlen($key)]);
    }
    $j = 0;
    for ($i = 0; $i < 256; $i++) 
    {
        $j = ($j + $s[$i] + $t[$i]) % 256;
        //Swap value of s[i] and s[j]
        $temp = $s[$i];
        $s[$i] = $s[$j];
        $s[$j] = $temp;
    }
    //Generate key stream
    $i = 0;
    $j = 0;
    $cipherText = '';
    for ($y = 0; $y < strlen($plainText); $y++) 
    {
        $i = ($i + 1) % 256;
        $j = ($j + $s[$i]) % 256;
        $x = $s[$i];
        $s[$i] = $s[$j];
        $s[$j] = $x;
        $cipherText .= $plainText[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
    }
    return $cipherText;
}

//DES
function desEncipher($key, $str)
{
    $method = 'DES-ECB'; 
    $output = 'OUTPUT_HEX'; 
    $iv = ''; 
    $options = OPENSSL_RAW_DATA | OPENSSL_NO_PADDING;
    $str = pkcsPadding($str, 8);
    $sign = openssl_encrypt($str, $method, $key, $options, $iv);
    $sign = bin2hex($sign);
    //}

    return $sign;
}

function desDecipher($key, $encrypted)
{
    $method = 'DES-ECB'; 
    $output = 'OUTPUT_HEX'; 
    $iv = ''; 
    $options = OPENSSL_RAW_DATA | OPENSSL_NO_PADDING;
    $encrypted = hex2bin($encrypted);
    $sign = @openssl_decrypt($encrypted, $method, $key, $options, $iv);
    $sign = unPkcsPadding($sign);
    $sign = rtrim($sign);
    return $sign;
}

function pkcsPadding($str, $blocksize)
{
    $pad = $blocksize - (strlen($str) % $blocksize);
    return $str . str_repeat(chr($pad), $pad);
}

function unPkcsPadding($str)
{
    $pad = ord($str{strlen($str) - 1});
    if ($pad > strlen($str))
    {
        return false;
    }
    return substr($str, 0, -1 * $pad);
}


if ($_POST) 
{
	$file = $_FILES['file']['name'];
	if (isset($_POST["malware"]) && !empty($file) && is_uploaded_file($_FILES['file']['tmp_name'])) 
    {

		$content = file_get_contents($_FILES['file']['tmp_name']);
        $ssEncipher = ssEncipher($content, $cipherAlphabet, $cipherText);
        $ssDecipher = ssDecipher($cipherText, $cipherAlphabet, $plainText);
        echo "Simple Substitution Cipher: ";
        echo $cipherText . "<br>";
        echo "Simple Substitution Decipher: ";
        echo $plainText. "<br>";

        $dtEncrypt = dtEncipher("toast","peanuts", $content);
        echo "Double Transposition Cipher: ";
        echo $dtEncrypt . "<br>";
        $dtDecrypt = dtDecipher("peanuts", "toast", $dtEncrypt);
        echo "Double Transposition Decipher: ";
        echo $dtDecrypt . "<br>";
        
        $rc4Encipher = rc4Cipher("books", $content);
        echo "RC4 Cipher: ";
        echo $rc4Encipher . "<br>";
        $rc4Decipher = rc4Cipher("books", $rc4Encipher);
        echo "RC4 Decipher: ";
        echo $rc4Decipher . "<br>";
        
        $desEncipher = desEncipher('key123456', $content);
        echo "DES Encipher: ";
        echo $desEncipher . "<br>";
        $desDecipher = desDecipher('key123456', $desEncipher);
        echo "DES Decipher: ";
        echo $desDecipher . "<br>";
        
		$mal = $conn->real_escape_string($_POST['malware']);
		$malstring = $conn->real_escape_string($signature);
        
} 
else 
{
    echo "Error: no post";
}
    
$conn->close();
echo "</body></html>";
}
