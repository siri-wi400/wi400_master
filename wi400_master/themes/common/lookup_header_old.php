<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?= $settings['window_title']  ?></title>
<?php 
	require_once $base_path."/includes/theme.inc";
	require_once $base_path."/includes/javascript.inc";
?>
<style>
body{
	margin:0px;
	padding:0px;
	overflow:auto;
}

</style>
</head>
<body onload="wi400Init();showMessages()">
<div id="wi400_info_box"><?echo _t("ATTENDERE_CARICAMENTO")?></div>
<div id="wi400_msg_box"></div>
<form name="wi400Form" id="wi400Form" method="POST" onSubmit="return false">
<input id="DECORATION" name="DECORATION" type="hidden" value="lookUp">
<input type="hidden" id="CURRENT_ACTION" name="CURRENT_ACTION" value="<?= $actionContext->getAction() ?>">
<input type="hidden" id="CURRENT_FORM" 	 name="CURRENT_FORM"   value="<?= $actionContext->getForm() ?>">
 <input id="LOOKUP_PARENT" name="LOOKUP_PARENT" type="hidden" value="<? if (isset($_REQUEST["LOOKUP_PARENT"])) echo $_REQUEST["LOOKUP_PARENT"]; ?>">
	<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td valign="top">
								<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
<?
	if ($actionContext->getLabel() !== false){
?>
								<tr>
										<td valign="top" align="right" class="top-area" style="height:27px">
										<div style="display:inline;float:left;margin-left:10px;line-height:27px" class="label"><?= $actionContext->getLabel(); ?></div>
<?
	if ($actionContext->getTimer() > 0){
?>
										<div style="display:inline;float:right;margin-top:6px;margin-right:4px;"><script>page_timer = true;page_timer_state = false;</script><input id="page_TIMER_IMG" class="wi400-pointer" type="image" title="(<?= $actionContext->getTimer()?> sec.)" onClick="timerPause('page', 'RESUBMIT')" src="themes/common/images/grid/grid_timer.gif"></div>
<?
	}
?>
										<div style="display:inline;float:right;margin-top:4px;margin-right:4px;" id="pageLoader"></div>
										</td>
                                    </tr>
<?
	}
?>   
<?                               
  include_once $base_path."/includes/messagesContainer.php";
?>                                 
									<tr height="100%">
										<td valign="top" class="body-area">