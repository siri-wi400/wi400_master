<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?= $actionContext->getLabel() ?></title>
<?php 
	require_once $base_path."/includes/theme.inc";
	require_once $base_path."/includes/javascript.inc";
	require_once $moduli_path."/tree/treeMenu.php";
	require_once $themes_path."/common/header_wait.php";
?>
<meta name="viewport" content="width=device-width">
<link href="themes/common/css/phone.css" rel="stylesheet" type="text/css" media="only screen and (max-width: 753px)">
<link rel="stylesheet" type="text/css" href="routine/jquery/css/jquery-ui.min.css">
<link rel="stylesheet" href="routine/jquery/css/jquery.ferro.ferroMenu.css" type="text/css">
<link rel="stylesheet" type="text/css" href="routine/jquery/jqplot/jquery.jqplot.min.css" />
<link rel="stylesheet" type="text/css" href="routine/jquery/jqplot/examples/jquery-ui/css/smoothness/jquery-ui.css" />
<script>
window.onload = function() {
	wi400Init();
	updateBrowser();
	showMessages();
	cacheOff();
}
<?php if ($settings['check_net']==true) { ?>
jQuery(document).ready(function(){
	var url = window.location.protocol + "//" + window.location.host + _APP_BASE + "index.php?t=AJAX_PING_NET&DECORATION=clean"; 
    jQuery.fn.checknet();
    checknet.config.warnMsg = "Impossibile connettersi con il server. Attendere il ripristino del collegamento";
    checknet.config.checkURL = url;
    //checknet.start();
});
<?php } ?>
</script>
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
                           <td class="left-menu-slider"  onClick="slideMenu()"><img src="<?=  $temaDir ?>images/left_menu_slider.gif" width="7" height="125"></td>
                            <td valign="top">
                            	<script>
                            		document.write('<table id="altezzaTabella2" width="100%" height="'+altezzaBrowser+'px" style="overflow: hidden;" cellpadding="0" cellspacing="0" border="0">');
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
?>
