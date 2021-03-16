<?php
	if(isset($_REQUEST['MENU_STATUS'])) {
		if($_REQUEST['MENU_STATUS']) {
			$_SESSION["LEFT_MENU_STATUS"] = $_REQUEST['MENU_STATUS'];
		}else {
			$_SESSION["LEFT_MENU_STATUS"] = $_REQUEST['MENU_STATUS'];
		}
	}
	if(isset($_REQUEST['MENU_RIGHT'])) {
		$_SESSION["RIGHT_MENU_STATUS"] = $_REQUEST['MENU_RIGHT'];
	}
?>