<?php

//	echo "BACK SESSION<br>";
	
	$new_id = session_id();
//	echo "CURRENT SESSION ID: $new_id<br>";
	
	$old_id = $_SESSION['OLD_SESSION_ID'];
//	echo "OLD SESSION ID: $old_id<br>";
	
	unset($_SESSION['OLD_SESSION_ID']);
	unset($_SESSION['OLD_MY_IP']);
	
	$_SESSION['LOGOUT_ACTION'] = "LOGOUT";
	
	session_write_close();

	session_id($old_id);
	session_start();
//	echo "SESSION ID: ".session_id()."<br>";
		
//	echo "IP: ".$_SESSION['MY_IP']." - MY IP: ".$_SESSION['OLD_MY_IP']."<br>";
	
	// Redirect
	$action = $_SESSION['DEFAULT_ACTION'];
	
	$history = new wi400History();
	wi400Session::save(wi400Session::$_TYPE_HISTORY, "BREAD_CRUMBS", $history);	
	//$_SESSION["WI400_HISTORY"] = $history;
	goHeader($appBase."index.php?t=".$action);
	//header("Location: ".$appBase."index.php?t=".$action);
	exit();

?>