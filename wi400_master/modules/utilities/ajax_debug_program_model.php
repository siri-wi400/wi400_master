<?php
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	if (isset($_GET['DEVELOPER_LOG_XMLSERVICE'])) {
		if (isset($_SESSION['DEVELOPER_LOG_XMLSERVICE'])) {
		 	unset($_SESSION['DEVELOPER_LOG_XMLSERVICE']);
		} else {
			$_SESSION['DEVELOPER_LOG_XMLSERVICE']=True;
		}
	}
	if (isset($_GET['DEVELOPER_DEBUG_NEXT_CALL'])) {
		if (isset($_SESSION['DEVELOPER_DEBUG_NEXT_CALL'])) {
			unset($_SESSION['DEVELOPER_DEBUG_NEXT_CALL']);
		} else {
			$_SESSION['DEVELOPER_DEBUG_NEXT_CALL']=True;
		}
	}
} else {
	die("not Ajax Request");
}
?>