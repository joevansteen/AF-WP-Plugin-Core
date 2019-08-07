<?php
    // To be near where both this and the comment entry should have some close "sameness"
		define('AF_HONEYPOT_VERSION',							'5.2019.0725');

// Insure a correct execution context ... redirect to the standard invalid breakin filter
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
	$host = $_SERVER['HTTP_HOST'];
	$myuri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$afuri = $_SERVER['PHP_SELF'];
	$redirect = 'login.php';
	header ("Location: https://$host$afuri/$redirect");
	exit;
	}
?>