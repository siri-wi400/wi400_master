<!-- <!DOCTYPE html>-->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?= $settings['window_title']  ?></title>
<?php
	require_once $base_path."/includes/theme.inc";
	require_once $base_path."/includes/javascript.inc";
	$autocomplete = "";
	if (isset($settings['security_autocomplete_password']) && $settings['security_autocomplete_password']===False) {
		$autocomplete = 'autocomplete="off"';
	}
	
	if(isset($_REQUEST['RESET_SUCCESS'])) {
		$messageContext->addMessage('SUCCESS', 'Procedura di ripristino password avviata correttamente.<br>Arriverà a breve una mail per il ripristino.');
	}
?>

<script type="text/javascript">
	if ('standalone' in navigator && !navigator.standalone && (/iphone|ipod|ipad/gi).test(navigator.platform) && (/Safari/i).test(navigator.appVersion)) {
		document.write('<link rel="stylesheet" href="/ipad/add2home.css">');
		document.write('<script type="application\/javascript" src="/ipad/add2home.js"><\/s' + 'cript>');
	}
</script>
<script type="text/javascript">
	function loading() {
		resizeMessageArea();
		jQuery(".spinner").removeClass("spinner-none");
	}
	function enter(event) {
		if(event.keyCode == 13) {
			loading();
			if(document.URL.indexOf('PROFILE') != -1) {
				doSubmit('LOGIN_PROFILE');
			}
			else{
				doSubmit('CHECK_LOGIN');
			}
		}
	}
</script>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="viewport" content="width=device-width">
<link rel="apple-touch-icon" sizes="72x72" href="/ipad/apple-touch-icon-ipad.png" />
<link rel="apple-touch-icon" sizes="114x114" href="/ipad/apple-touch-icon-iphone4.png" />
<link rel="stylesheet" href="<?=$appBase?>themes/common/css/login_loading.css">
</head>
<body onLoad="showMessages();" onkeyDown="enter(event)">
<form name="wi400Form" id="wi400Form" method="POST" onSubmit="return false" <?= $autocomplete ?>>
	<input id="formSubmit" name="" type="hidden" value="true">
	<input type="hidden" id="CURRENT_ACTION" name="CURRENT_ACTION" value="<?= $actionContext->getAction() ?>">
	<input type="hidden" id="CURRENT_FORM" 	 name="CURRENT_FORM"   value="<?= $actionContext->getForm() ?>">
	<script>
		var SCREEN_ALTEZZA = screen.height;
		var SCREEN_BASE = screen.width;
		document.write("<input type=\"hidden\" name=\"HTTP_URL\" value=\"");
		document.write(location.protocol+"//"+location.host);
		document.write("\">");
		document.write("<input type=\"hidden\" name=\"SCREEN_ALTEZZA\" value=\""+SCREEN_ALTEZZA+"\">");
		document.write("<input type=\"hidden\" name=\"SCREEN_BASE\" value=\""+SCREEN_BASE+"\">");
	</script>
	
	<!--[if lte IE 8]>	
	<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
		<tr height="80%">
			<td valign="middle" align="center" class="body-area">
	<![endif]-->
	
	<!--[if !IE]> -->
	<div style="position: absolute; min-width: 500px; min-height: 470px; width: 100%; height: 100%;">
	<!-- <![endif]-->
	
		