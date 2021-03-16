<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
<meta name = "format-detection" content = "telephone=no">
<title><?= $actionContext->getLabel() ?></title>
<?php 
	require_once $base_path."/includes/theme.inc";
	require_once $base_path."/includes/javascript.inc";
	require_once $moduli_path."/tree/treeMenu.php";
	require_once $themes_path."/common/header_wait.php";
	
	if(isset($_SESSION['zoom_scale'])) {
		echo '<meta name="viewport" content="width=device-width, initial-scale='.$_SESSION['zoom_scale'].'"/>';
	}
?>
<link rel="stylesheet" type="text/css" href="routine/jquery/css/jquery-ui.min.css">
<script>
window.onload = function() {
	var tabID = sessionStorage.tabID ? sessionStorage.tabID : sessionStorage.tabID = Math.random();
	jQuery("#wi400Form").append("<input type=\"hidden\" name=\"WI400_TAB_ID\" value=\""+tabID+"\">");
	wi400Init();
	updateBrowser();
	showMessages();
	cacheOff();
}
<?php if ($settings['check_net']==true) { 
	$goCheck = True;
	if (isset($_SESSION['user']) && isset($settings['check_net_users'])) {
		if (is_array($settings['check_net_users']) && !in_array($_SESSION['user'], $settings['check_net_users'])) {
			$goCheck=False;
		}
	}
	if ($goCheck==True) {
?>
jQuery(document).ready(function(){
	var url = window.location.protocol + "//" + window.location.host + _APP_BASE + "index.php?t=AJAX_PING_NET&DECORATION=clean"; 
    jQuery.fn.checknet();
    checknet.config.warnMsg = "Impossibile connettersi con il server. Attendere il ripristino del collegamento";
    checknet.config.checkURL = url;
    //checknet.start();
});
<?php
	}
    }
	if(isset($settings['keep_connect_alive']) && $settings['keep_connect_alive']) {?>
		function keepMeAlive(imgName) {
			myImg = document.getElementById(imgName);
			if (myImg) myImg.src = myImg.src.replace(/\?.*$/, '?' + Math.random());
		}
		window.setInterval("keepMeAlive('keepAliveIMG')", 1000);
<?php }
	if(isset($_REQUEST['t']) && isset($_REQUEST['f']) && isset($_SESSION['SCROLL_TOP'][$_REQUEST['t']."|".$_REQUEST['f']]) && $_SESSION['SCROLL_TOP'][$_REQUEST['t']."|".$_REQUEST['f']]) {
?>
		globalScrollTop = <?= $_SESSION['SCROLL_TOP'][$_REQUEST['t']."|".$_REQUEST['f']] ?>;
<?php
	}else {
		echo "globalScrollTop = 0;";
	}
?>
</script>
<?php 
 if(isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) && $_SESSION['NAVIGAZIONE_TABLET_ATTIVA'] && $_REQUEST['t'] != "TELNET_5250") {
	echo '<link rel="stylesheet" type="text/css" href="themes/common/css/menu_tablet.css">';
 }
?>
</head>
<body>
<form name="wi400Form" id="wi400Form" method="POST" onSubmit="return false">
<?
	require_once $themes_path."/common/hidden_fields.php";
	if ($settings['check_net']==true) {
	?>
	<div id="message_notification" style="display:none; cursor: default"> 
        <h1 id="message_notification_text">wiMessage</h1> 
        <input type="button" id="ok" value="ok" /> 
	</div> 
	<?php 
	}
	if(isset($settings['keep_connect_alive']) && $settings['keep_connect_alive']) {
		echo '<img id="keepAliveIMG" width="1" height="1" src="themes/common/images/check_connect_alive.png?iaowdo" style="display: none;"/>';
	}
?>
	<!-- Siccome height="100%" nella tabella � deprecato utilizzo l'altezza in px calcolata con Javascript -->
	<script>
		//var altezzaBrowser = JQuery('window').innerHeight();
		var altezzaBrowser = document.documentElement.clientHeight;
		//alert("Altezza: "+altezzaBrowser);
		document.write('<table id="altezzaTabella" width="100%" height="'+altezzaBrowser+'px" cellpadding="0" cellspacing="0" border="0">');
	</script>
		<!-- <table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0"> -->
		<tr>
			<td valign="top" class="left-menu" id="leftMenu" <?= $leftMenuStyle ?>>
                <div id="leftMenuContainer" <?= $leftMenuContainerStyle ?>>
					<table id="tableMenu" width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td class="left-menu-logo">
	                            	<div id="logoShake"><img onclick="jQuery('#logoShake').effect('shake'); return false;" src="<?=  $temaDir ?>images/logo_header.png" /></div>
							</td>
     					</tr>
        				<tr>
        					<td>
<?
								if (file_exists($doc_root."/".$temaDir."left_menu.php")) {
									include_once $doc_root."/".$temaDir."left_menu.php";
								} else {	
									include_once $themes_path."/common/left_menu.php";
								}
	
?>
							</td>
						</tr>
					</table>
				</div>
			</td>
			<td class="left-menu-slider"  onClick="slideMenu()">
				<img src="<?=  $temaDir ?>images/left_menu_slider.gif" width="7" height="125" style="visibility: hidden;">
				<div class="trapezoid trapezoid_border <?= isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) ? "none" : ""?>"></div>
				<div class="trapezoid tema <?= isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) ? "none" : ""?>"><i class='fa fa-angle-<?= $leftMenuOpen == "open" ? "left" : "right"?> tema' aria-hidden='true'></i></div>
			</td>
			<td valign="top" width="100%">
				<script>
					document.write('<table id="altezzaTabella2" width="100%" height="'+altezzaBrowser+'px" cellpadding="0" cellspacing="0" border="0">');
				</script>
				<!-- <table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">-->
<?
				if ($show_header){
					include_once $themes_path."/common/header_menu.php";
				}
				
				include_once $base_path."/includes/messagesContainer.php";
				?>
				<tr height="100%">
					<td valign="top" class="body-area">
<? if (isset($_SESSION["WI400_WIZARD"])){
		require_once $themes_path."/common/wizard_header.php";
	}
	
	//$cookie_name = "user";
	//$cookie_value = "John Doe";
	//setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
?>
<div id="wi400_info_box"><?echo _t("ATTENDERE_CARICAMENTO")?></div>
<div id="wi400_modify_box" style="z-index: 0;"></div>
<div id="wi400_msg_box"></div>
<div id="windows_reference" value="0"></div>