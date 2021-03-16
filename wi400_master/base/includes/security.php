<?php
//echo number_format(time()/"4000", 0, "","");
//$settings['security_on'] = false;
// @SECURE PATCH * VERIFICO SE ATTIVA
$currentHMAC = "";
$settings['security_on']=False;
if (isset($settings['security_on']) && $settings['security_on']==True) {
	$hmacLife = 4000;
	if (isset($settings['security_hmac_life']) && $settings['security_hmac_life']!="") {
		$hmacLife = $settings['security_hmac_life'];
	}
	//$key = substr(number_format(time()/$hmacLife, 0, "",""),0, -1);
	$key = time();
	$data =$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].session_id().$key;
	$currentHMAC = base64_encode($key.":".hash_hmac('sha1', $data, $settings['security_private_key'], TRUE));
	$currentHMAC = rtrim(strtr($currentHMAC, '+/', '-_'), '=');
	if (isset($settings['security_x_frame_options']) && $settings['security_x_frame_options']==True) {
		header('X-Frame-Options: SAMEORIGIN');
	}
	$allow_site = "";
	if (isset($settings['security_allow_site']) && $settings['security_allow_site']!="") {
		$allow_site = implode(" " , $settings['security_allow_site']);
	} else {
		// Default allow site
		$allow_site = "http://*.gstatic.com/ http://*.google.com/ http://maps.gstatic.com/  http://*.googleapis.com 'unsafe-eval' 'unsafe-inline'";
	}
	//header( "Set-Cookie: hidden=value; httpOnly" );
	If (!isIE()) {
		header("Content-Security-Policy: default-src 'self' $allow_site;"); // FF 23+ Chrome 25+ Safari 7+ Opera 19+
	} else {
		header("X-Content-Security-Policy: default-src 'self' $allow_site;"); // IE 10+
	}
	header("X-Content-Type-Options: nosniff");
	// Adds the HTTP Strict Transport Security (HSTS) (remember it for 1 year)
	$isHttps = !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off';
	if ($isHttps)
	{
		header('Strict-Transport-Security: max-age=31536000'); // FF 4 Chrome 4.0.211 Opera 12
	}
	// Filtro di tutto il GET
	if (isset($settings['security_parse_get']) && $settings['security_parse_get']==True) {
		foreach ($_POST as $key =>$value) {
			if (!is_array($_POST[$key])) {
				$_POST[$key]=filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
			}
		}
	}
	//die();
	if (isset($settings['security_parse_post']) && $settings['security_parse_post']==True) {
		foreach ($_GET as $key =>$value) {
			$_GET[$key]=filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
		}
	}
	// Flag per segnalare che sono passato per almeno un security flag
	$security_flag = false;
	// Controllo se è un redirect da PHP se mi è arrivato lo stesso url che ho chiesto
	/*if (isset($settings['security_check_url']) && $settings['security_check_url']==True) {
		if (isset($_SESSION['GO_URL'])) {
			if (strpos($_SESSION['GO_URL'], $_SERVER['REQUEST_URI'])===False) {
				error_log("SECURITY: Check sum URL non corretto. (SESSION):".$_SERVER['REQUEST_URI']);
				echo getLoginUrl()."<br>";
				die("<br>Check sum URL non corretto. (SESSION)");
			} else {
				unset($_SESSION['GO_URL']);
				$security_flag = True;
			}
		}
	} NON FUNZIONA SE LA SESSIONE E CONDIVISA CON FINESTRE E AJAX*/
	// Se è stato fatto un post controllo che mi arrivi la chiave di validazione dentro il post
	if (isset($_POST['wi400_validate_url']) && $_POST['wi400_validate_url']!="") {
		if (strlen($_SERVER['REQUEST_URI']) != $_POST['wi400_validate_url']) {
			error_log("SECURITY: Check sum URL non corretto. (POST):".$_SERVER['REQUEST_URI']);
			echo getLoginUrl()."<br>";
			die("Check sum URL non corretto (POST)");
		}
		$security_flag = True;
	}
	// Se è stato fatto un get controllo che mi arrivi la chiave posta nell'url
	if (isset($_GET['CHECKID']) && $_GET['CHECKID']!="") {
		$datalen = substr($_SERVER['PHP_SELF']."&".$_SERVER['QUERY_STRING'], 0, strpos($_SERVER['PHP_SELF']."&".$_SERVER['QUERY_STRING'], "&CHECKID"));
		$length = strlen(urldecode($datalen));
		$max = $_GET['CHECKID'] +1;
		$min = $_GET['CHECKID'] -1;
		if ($length > $max || $length < $min) {
		//if ($length != $_GET['CHECKID']) {
			error_log("SECURITY: Check sum URL non corretto. (DECODING):".$_SERVER['REQUEST_URI']);
			echo getLoginUrl()."<br>";
			die("Check sum URL non corretto. (DECODING)");
		}
		$security_flag = True;
	}
	// Test HMAC per passaggio URL
	// Se è stato fatto un get controllo che mi arrivi la chiave posta nell'url
	if (isset($_SERVER['HTTP_WI400_CHECKID']) && $_SERVER['HTTP_WI400_CHECKID']!="") {
		$datalen = $_SERVER['PHP_SELF']."&".$_SERVER['QUERY_STRING'];
		$length = strlen(urldecode($datalen));
		$max = $_SERVER['HTTP_WI400_CHECKID'] +1;
		$min = $_SERVER['HTTP_WI400_CHECKID'] -1;
		if ($length > $max || $length < $min) {
			error_log("SECURITY: Check sum URL non corretto. (DECODING-2):".$_SERVER['REQUEST_URI']);
			echo getLoginUrl()."<br>";
			die("Check sum URL non corretto. (DECODING-2)");
		}
		$security_flag = True;
	}
	// Controllo HMAC: presenza HMAC su HEADER, POST, GET
	$hmacParm = "";
	if (isset($_SERVER['HTTP_WI400_HMAC']) && $_SERVER['HTTP_WI400_HMAC']!="") {
		$hmacParm =$_SERVER['HTTP_WI400_HMAC']; 
	} elseif (isset($_POST['WI400_HMAC']) && $_POST['WI400_HMAC']!="") {
		$hmacParm =$_POST['WI400_HMAC'];
	} elseif (isset($_GET['WI400_HMAC']) && $_GET['WI400_HMAC']!="") {
		$hmacParm=$_GET['WI400_HMAC'];
	}
	//echo "<br>".$currentHMAC;
	//echo "<br>".$hmacParm;
	if ($hmacParm!="") {
		// Controllo se $hmac generato in modo corretto
		$sendHmac = base64_decode($hmacParm);
		$dati = explode(":", $sendHmac);
		//print_r($dati);
		// Genero l'HMAC così come dovrebbe essere quello passato per vedere se è corretto
		$data =$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].session_id().$dati[0];
		$generatedHMAC = base64_encode($dati[0].":".hash_hmac('sha1', $data, $settings['security_private_key'], TRUE));
		$generatedHMAC = rtrim(strtr($generatedHMAC, '+/', '-_'), '=');
		//echo "<br>:".$generatedHMAC;
		//echo "<br>:".$hmacParm."<br>";
		if ($generatedHMAC != $hmacParm) {
			error_log("SECURITY: HMAC non corretto. (GENERAL):".$_SERVER['REQUEST_URI']);
			echo getLoginUrl()."<br>";
			die("HMAC non corretto. (GENERAL)");
		}
		//echo "<br>".date("h:i:s-d/m/Y").">-<".date("h:i:s-d/m/Y",$dati[0])."<->$hmacLife";
		if (time() > ($dati[0] + $hmacLife)) {
			error_log("SECURITY: HMAC scaduto.".date("h:i:s-d/m/Y").">-<".date("h:i:s-d/m/Y",$dati[0])."<->$hmacLife"."(GENERAL):".$_SERVER['REQUEST_URI']);
			echo getLoginUrl()."<br>";
			die("HMAC scaduto. (GENERAL)");			
		}
		$security_flag = True;
	}
	/**
	 * Implementare parametro HMAC che deve essere presente e valido per passare
	 */
	// Controlli AJAX/COMPLETE
	if (isset($_REQUEST["DECODE_PARAMETERS"])) {
		$decodeParameters = unserialize($_REQUEST["DECODE_PARAMETERS"]);
		if (isset($decodeParameters['KEYID'])) {
			$keyid = $decodeParameters['KEYID'];
			unset($decodeParameters['KEYID']);
			$decodeKey = base64_encode(md5(serialize($decodeParameters)));
			if ($keyid !== $decodeKey) {
				error_log("SECURITY: Check sum Parametri Errato. (AJAX):".$_SERVER['REQUEST_URI']);
				echo getLoginUrl()."<br>";
				die("Check sum Parametri Errato. (AJAX)");
			}
		} else {
			error_log("SECURITY: Check sum Parametri Mancante. (AJAX):".$_SERVER['REQUEST_URI']);
			echo getLoginUrl()."<br>";
			die("Check sum Parametri Mancante. (AJAX)");			
		}
		$security_flag = True;
	}
	// Se non sono passato per nessun controllo permetto l'accesso solo se non ho nulla sulla query string e se sono loggato. Gli utenti loggati devono per forza 
	// passare per uno dei punti precedenti
	/*if ($security_flag==False) {
		if ($_SERVER['QUERY_STRING']!="") {
			if (isset($_SESSION['user']) && $_GET['t']!="LOGIN") {
				error_log("SECURITY: Security flag OFF. (SECURITY):".$_SERVER['REQUEST_URI']);
				echo getLoginUrl()."<br>";
				die("Security flag OFF. (SECURITY)");
			}		
		}
	}*/
}