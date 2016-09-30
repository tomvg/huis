<?php

// Send an email.
//$taskStates = unserialize(file_get_contents('../taskStates.txt'));
//$message = strval($taskStates);

mail('tomvgroeningen@gmail.com', 'php mail test', 'hello');


// Actually do the reset.
if(file_exists('taskStates.txt')) {
	unlink('taskStates.txt');
}

?>
