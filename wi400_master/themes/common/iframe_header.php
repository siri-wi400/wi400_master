<!DOCTYPE html>
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
<link rel="stylesheet" type="text/css" href="routine/jquery/css/jquery-ui.min.css">
</head>
<body onLoad="wi400Init();showMessages()">
<form name="wi400Form" id="wi400Form" method="POST" onSubmit="return false">
<input id="DECORATION" name="DECORATION" type="hidden" value="iframe">
<input type="hidden" id="CURRENT_ACTION" name="CURRENT_ACTION" value="<?= $actionContext->getAction() ?>">
<input type="hidden" id="CURRENT_FORM" name="CURRENT_FORM" value="<?= $actionContext->getForm() ?>">
<input type="hidden" id="LOOKUP_PARENT" name="LOOKUP_PARENT" value="<? if (isset($_REQUEST["LOOKUP_PARENT"])) echo $_REQUEST["LOOKUP_PARENT"]; ?>">
<?php 
  include_once $base_path."/includes/messagesContainer.php";
  
  if(isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) && $_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) {
  	echo '<link rel="stylesheet" type="text/css" href="themes/common/css/menu_tablet.css">';
  }
 