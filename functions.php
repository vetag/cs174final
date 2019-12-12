<?php

function mysql_fatal_error($errorMsg, $conn)
{
	$errorMsg2 = mysqli_error($conn);
	echo <<< _END
	Error: <p>$errorMsg: $errorMsg2</p>
_END;
}
