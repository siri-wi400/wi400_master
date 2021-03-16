<?php
	session_start();
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	date_default_timezone_set('Europe/Rome');
	
	function showArray($a) {
		echo "<pre>"; print_r($a); echo "</pre>";
	}
	
	$error = '';
	
	if(isset($_REQUEST['GET_CAPTCHA'])) {
		
		$string = '';
		for ($i = 0; $i < 5; $i++) {
		    $string .= chr(rand(97, 122));
		}
		
		$_SESSION['RESET_PSW_CAPTCHA'] = $string; //store the captcha
		
		$dir = 'routine/jpgraph/fonts/';
		$image = imagecreatetruecolor(200, 100); //custom image size
		$font = "PlAGuEdEaTH.ttf"; // custom font style
		$color = imagecolorallocate($image, 113, 193, 217); // custom color
		$white = imagecolorallocate($image, 255, 255, 255); // custom background color
		imagefilledrectangle($image,0,0,399,99,$white);
		imagettftext ($image, 40, 0, 10, 70, $color, $dir.$font, $_SESSION['RESET_PSW_CAPTCHA']);
		
		header("Content-type: image/png");
		imagepng($image);
		/*$rs = imagepng($image, 'provaAlberto.png');
		
		if($rs) {
			echo "Ok";
		}else {
			echo "nooo";
		}*/
		
		die();
		
	}else if(isset($_REQUEST['CHECK_DATI'])) {
		$user = $_REQUEST['user'];
		$captcha = $_REQUEST['captcha'];
		
		if(isset($_SESSION['RESET_PSW_CAPTCHA']) && $_SESSION['RESET_PSW_CAPTCHA'] == $captcha) {
			$error = '';
		}else {
			$error = 'Errore codice captcha! Riprovare.';
		}
		
		if(!$user) {
			$error = 'Utente obbligatorio';
		}
		
		//showArray($_SESSION);
		//showArray($_REQUEST);
		
		if(!$error) {
			$file = fopen('userResetPsw.txt', 'a');
			fwrite($file, $user."|".date('d/m/Y H:i:s')."\r\n");
			fclose($file);
			
			$path_url = explode("/", $_SERVER['PHP_SELF']);
			
			$_SESSION['RESET_SUCCESS'] = true;
			
			header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$path_url[1].'/index.php?t=LOGIN&RESET_SUCCESS');
		}
		
		/*$path_url = explode("/", $_SERVER['PHP_SELF']);
		header('Location: http://'.$_SERVER['HTTP_HOST'].'/'.$path_url[1].'/index.php');*/
		//showArray($_SERVER);
	}
?>
<html>
<header>
	<title>
		Reset password
	</title>
	<script src="routine/jquery/jquery-1.9.1.js"></script>
	<script type="text/javascript">
		/*jQuery('#buttonConf').click(function() {
			console.log(this);
		});*/

		/*function buttonConf(that) {
			jQuery.ajax(function() {
				type: "GET",
				url: "resetPassword.php?t=AJAX_FOCUSED_FIELD&DECORATION=clean&FOCUSED_FIELD="+FOCUSED_FIELD.id+"&FOCUSED_TAB="+FOCUSED_TAB
			}).done(function ( response ) {  
				console.log(response);
			}).fail(function ( data ) {  
				console.log('errore ajax');
			});
		}*/
		function resizeMessageArea(){
			jQuery("#messageArea").slideUp("slow");
		}
		function userBlur(that) {
			that.value = that.value.toLocaleUpperCase();
		}
	</script>
	<style>
		body {
			background-color: #f3f3f3;
		}
		.contenitore {
			position: absolute;
			width: 200px;
			/*height: 215px;*/
			left: 50%;
			top: 50%;
			margin-left: -120px;
			margin-top: -127.5px;
			padding: 20px;
			background-color: white;
			border-radius: 10px;
			border: 1px black solid;
		}
		.messageArea_ERROR,
		.messageArea_ALERT,
		.messageArea_INFO,
		.messageArea_WARNING,
		.messageArea_SUCCESS {
			text-align: center;
			overflow-y: visible;
			cursor: n-resize;
		}
		
		
		.messageArea_ERROR {
			border: 1px solid #ecafb8;
			scrollbar-base-color: #ffebe8;
			background-color: #ffebe8;
			position: absolute;
			top: 0px;
			left: 0px;
			width: 240px;
			height: auto;
			padding-top: 18px;
			padding-bottom: 10px;
		}
		.messageLabel_ERROR {
			color: #de0021;
		}
	</style>
</header>
<body bgcolor='red'>
	<form name="wi400ResetPsw" id="wi400ResetPsw" method="POST" action="resetPassword.php?CHECK_DATI">
		<div style="position: absolute; left: 50%; margin-left: -120px; top: 50%; margin-top: 153px; width: 240px; height: auto; background-color: red;">
			<div id="messageArea" onclick="resizeMessageArea()" class="messageArea_ERROR" style=" display: <?= $error ? 'block' : 'none'?>;">
				<div class='messageLabel_ERROR'>
					<?= $error?>
				</div>
			</div>
		</div>
		<div class='contenitore'>
			<b>Utente</b><br><input type="text" name='user' onblur="userBlur(this)" value='<?= isset($user) ? $user : ''; ?>'><br><br>
			
			<b>Captcha</b><br>
			<img src="resetPassword.php?GET_CAPTCHA" /><br>
			<input type="text" size='25' name='captcha' placeHolder='Inserisci il codice qui sopra'><br><br>
			
			<!-- onClick='buttonConf(this)'-->
			<input type='submit' id='buttonConf' value='Conferma'>
		</div>
		
		<div class="body-area" style="position: absolute; bottom: 0px; width: 100%; height: auto; z-index: 1; padding: 0px; padding-top: 10px; padding-bottom: 10px;">
			<center><img src="themes/default/images/logo_siri.png" ></center>
		</div>
	</form>
</body>

</html>