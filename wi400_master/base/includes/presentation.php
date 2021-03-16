<?php

	$leftMenuOpen = "open";

	// Apertura menu
	if(isset($_GET["LEFT_MENU_STATUS"]) && $_GET["LEFT_MENU_STATUS"] != ""){
		$_SESSION["LEFT_MENU_STATUS"] = $_GET["LEFT_MENU_STATUS"];
		$leftMenuOpen	  = $_GET["LEFT_MENU_STATUS"];
	}else if(isset($_POST["LEFT_MENU_STATUS"]) && $_POST["LEFT_MENU_STATUS"] != ""){
		$_SESSION["LEFT_MENU_STATUS"] = $_POST["LEFT_MENU_STATUS"];
		$leftMenuOpen	  = $_POST["LEFT_MENU_STATUS"];
	}else if (isset($_SESSION["LEFT_MENU_STATUS"])){
		$leftMenuOpen = $_SESSION["LEFT_MENU_STATUS"];
	}
	
	// Apertura righe menu
	if(isset($_POST["LEFT_MENU_ROWS_STATUS"]) && $_POST["LEFT_MENU_ROWS_STATUS"] != ""){
		$_SESSION["LEFT_MENU_ROWS_STATUS"] = $_POST["LEFT_MENU_ROWS_STATUS"];
		$settings['leftMenuRows'] = $_POST["LEFT_MENU_ROWS_STATUS"];
	}else if (isset($_SESSION["LEFT_MENU_ROWS_STATUS"])){
		$settings['leftMenuRows'] = $_SESSION["LEFT_MENU_ROWS_STATUS"];
	}
	
	// Modifica della data di riferimento
	if(isset($_POST["REF_DATE"])){
		$_SESSION["data_validita"] = dateViewToModel($_POST["REF_DATE"]);
	}
	
	// Update STATUS
	if (isset($_REQUEST["UPDATE_STATUS"])){
		$_SESSION["UPDATE_STATUS"] = $_REQUEST["UPDATE_STATUS"];
	} else if (!isset($_SESSION["UPDATE_STATUS"])){
		$_SESSION["UPDATE_STATUS"] = "OFF";
	}
	
	if (isset($settings['mobile_init']) && $settings['mobile_init']==True) {
		if(isMobile() || (isset($_REQUEST['WIDTH_WINDOW']) && isset($_REQUEST['HEIGHT_WINDOW']))) {
			$_SESSION['NAVIGAZIONE_TABLET_ATTIVA']="SI";
			$leftMenuOpen = "close";
		}
	}

	if (isset($_REQUEST['SCROLL_TOP']) && $_REQUEST['SCROLL_TOP'] >= 0 && isset($_REQUEST['CURRENT_ACTION'])) {
//		echo "SCROLL_TOP: ".$_REQUEST['SCROLL_TOP']."<BR/>";
		$_SESSION['SCROLL_TOP'][$_REQUEST['CURRENT_ACTION']."|".$_REQUEST['CURRENT_FORM']] = $_REQUEST['SCROLL_TOP'];
	}
	if(isset($settings['widget']) && $settings['widget']) {
		if(isset($_REQUEST['ACTIVE_WIDGET'])) {
			$_SESSION['WIDGET_ENABLE'] = $_REQUEST['ACTIVE_WIDGET'];
		}
	}
	

	// MENU LATERALE WI400
	$leftMenuSize = 210;
	$leftMenuContainerStyle = "";
	$leftMenuStyle = "";
	if ($leftMenuOpen == 'close'){
		$leftMenuSize = 2;
		$leftMenuContainerStyle = "style=\"overflow:hidden;visibility:hidden;width:2px\"";
		$leftMenuStyle = "style=\"width:2px; display: none;\"";
	}

?>