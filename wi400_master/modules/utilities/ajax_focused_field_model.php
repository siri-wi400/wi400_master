<?php
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	if (isset($_GET['FOCUSED_FIELD'])) {
		if (isset($_SESSION['LAST_FOCUSED_FIELD'])) {
		 	$_SESSION['LAST_LAST_FOCUSED_FIELD']=$_SESSION['LAST_FOCUSED_FIELD'];
		}
		 $_SESSION['LAST_FOCUSED_FIELD']=$_GET['FOCUSED_FIELD'];
	}
	if (isset($_GET['FOCUSED_TAB'])) {
		if (isset($_SESSION['LAST_FOCUSED_TAB'])) {
			$_SESSION['LAST_LAST_FOCUSED_TAB']=$_SESSION['LAST_FOCUSED_TAB'];
		}
		$_SESSION['LAST_FOCUSED_TAB']=$_GET['FOCUSED_TAB'];
	}
} else {
	die("not Ajax Request");
}
?>