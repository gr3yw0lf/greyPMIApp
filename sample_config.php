<?php
// config data for the connection to the database for the greyPMIApp system
// this file needs to be named config.php

$DB = "DATABASE";
$USER = "USERNAME";
$PASS = "PASSWORD";
$HOST = "HOST";

$SEND_AUTH = 'CHANGE THIS TO SOMETHING';

// A very simple auth function. Must return true for the ajax.php to send to database
//  this one is very simple!
function checkSendAuth($auth) {
	global $SEND_AUTH;
	return ($SEND_AUTH === $auth);
}
?>
