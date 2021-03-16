<?php

if (isDeveloper(False)) {
	// It's OK
} else {
	// E' un utente che non è developer e deve essere autorizzato via password
	define('USE_AUTHENTICATION', 1);
	define('USERNAME', 'developer');
	$password = "dev-".date("i");
	define('PASSWORD', $password);
	echo $password;
	if ( USE_AUTHENTICATION == 1 ) {
		if (!empty($_SERVER['AUTH_TYPE']) && !empty($_SERVER['REMOTE_USER']) && strcasecmp($_SERVER['REMOTE_USER'], 'anonymous'))
		{
			if (!in_array(strtolower($_SERVER['REMOTE_USER']), array_map('strtolower', $user_allowed))
			&& !in_array('all', array_map('strtolower', $user_allowed)))
			{
				echo 'You are not authorised to view this page. Please contact server admin to get permission. Exiting.';
				exit;
			}
		}
		else if ( !isset($_SERVER['PHP_AUTH_USER'] ) || !isset( $_SERVER['PHP_AUTH_PW'] ) ||
		$_SERVER['PHP_AUTH_USER'] != USERNAME || $_SERVER['PHP_AUTH_PW'] != PASSWORD ) {
			header( 'WWW-Authenticate: Basic realm="DEVELOPER Console Login"' );
			header( 'HTTP/1.0 401 Unauthorized' );
			exit;
		}
	}
}