<?php
// DEFINE our cipher
define('AES_256_CBC', 'aes-256-cbc');
// Generate a 256-bit encryption key
// This should be stored somewhere instead of recreating it each time
function wi400_encrypt_string($string, $key="") {
	global $settings;
	if ($key=="") {
		$key=$settings['wi400_encrypt_key'];
	}
	//$encryption_key = openssl_random_pseudo_bytes(32);
	// Generate an initialization vector
	// This *MUST* be available for decryption as well
	$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(AES_256_CBC));
	// Create some data to encrypt
	$data = $string;
	//echo "Before encryption: $data\n";
	// Encrypt $data using aes-256-cbc cipher with the given encryption key and
	// our initialization vector. The 0 gives us the default options, but can
	// be changed to OPENSSL_RAW_DATA or OPENSSL_ZERO_PADDING
	$encrypted = openssl_encrypt($data, AES_256_CBC, $encryption_key, 0, $iv);
	//echo "Encrypted: $encrypted\n";
	// If we lose the $iv variable, we can't decrypt this, so:
	// - $encrypted is already base64-encoded from openssl_encrypt
	// - Append a separator that we know won't exist in base64, ":"
	// - And then append a base64-encoded $iv
	$encrypted = $encrypted . ':' . base64_encode($iv);
	return $encrypted;
}
function wi400_decrypt_string($string, $key) {
	global $settings;
	if ($key=="") {
		$key=$settings['wi400_encrypt_key'];
	}
	// To decrypt, separate the encrypted data from the initialization vector ($iv).
	$encrypted = $string;
	$parts = explode(':', $encrypted);
	// $parts[0] = encrypted data
	// $parts[1] = base-64 encoded initialization vector
	// Don't forget to base64-decode the $iv before feeding it back to
	//openssl_decrypt
	$decrypted = openssl_decrypt($parts[0], AES_256_CBC, $encryption_key, 0, base64_decode($parts[1]));
	//echo "Decrypted: $decrypted\n";
 	return $descrypted;
}
/**
 * wi400_runAction: Esecuzione Pragmatica di una Azione WI400 Passata Come Parametro senza richiesta di URL
 * @param unknown $azione
 * @param unknown $get
 * @param unknown $post
 * @param unknown $request
 */
function wi400_runAction($azione, $form="", $get="", $post="", $request="", $session_id="", $trigger_param=array(), $batch=False, $wait=True) {
	// Da decidere cosa overrizzare e cosa no ..
	global $appBase,$root_path,$wi400Debug,$settings,$time_start,$time_step,$temaDir, $architettura, $currentAction, $currentForm;
	global $viewContext,$debugContext,$modelContext,$gatewayContext,$menuContext,$messageContext,$actionContext,$breadCrumbs,$listContext,$lookUpContext;
	global $wi400Wizard,$pageDefaultDecoration,$showLoginForm,$show_footer,$show_header;
	global $data_path,$actionLabel,$buttonsBar,$tab_index;
	global $dbUser, $dbPath, $log_path, $db, $connzend, $users_table;
	global $history, $currentHMAC, $routine_path, $moduli_path, $wi400GO, $wi400_trigger;

	if ($batch==False) {
		$saveAction=$actionContext;
		$actionContext = new wi400ActionContext();
		// Salvo Parametri Attuali
		$SAVGET = $_GET;
		$SAVPOST= $_POST;
		$SAVREQUEST= $_REQUEST;
		$SAVSESSION= $_SESSION;
		// Reperisco i dati scritti fino ad ora
		$dati_reperiti = ob_get_clean();
		ob_start();
		if ($get!="") {
			$_GET= $get;
		}
		if ($post!="") {
			$_POST=$post;
		}
		if ($request!="") {
			$_REQUEST=$request;
		}
		// Carico Parametri di SESSIONE
		if ($session_id!="") {
			
		} else {
			// Uso i dati che ho a disposizione
		}
		// @todo Come posso fare a reperire il file di sessione e caricarmi tutti i dati deserializzati ...
		// Fa quello che dovrebbe fare la chiamata in entrata da INDEX
		$actionRow = rtvAzione($azione);
		if ($actionRow['TIPO']=="B") {
			$batchContext = new wi400ValuesContainer();
			foreach ($post as $key => $value) {
				$batchContext->__set($key, $value);
			}
		}
		// Devo duplicare i file per permettere l'include dinamico
		$actionContext->setType($actionRow["TIPO"]);
		$actionContext->setModule($actionRow["MODULO"]);
		$actionContext->setModel($actionRow["MODEL"]);
		$actionContext->setView($actionRow["VIEW"]);
		$actionContext->setAction($azione);
		if ($form!="") {
			$actionContext->setForm($form);
		}
		//set_error_handler("exception_error_handler_action");
		try {
			if (isset($actionRow["GATEWAY"]) && $actionRow["GATEWAY"] != "" && $actionContext->getGateway() != ""){
				//$codice = file_get_contents(p13n($actionContext->getGatewayUrl($actionRow["GATEWAY"])));
				/*eval('?>' . $codice . '<?php ');
				//eval('?>' . $codice);
				//require_once p13n($actionContext->getGatewayUrl($actionRow["GATEWAY"]));*/
				require p13n($actionContext->getGatewayUrl($actionRow["GATEWAY"]));
			}
			// ACTION AND FORM
			$currentAction = $actionContext->getAction();
			$currentForm   = $actionContext->getForm();
			if ($actionContext->getModel() != ""){
				//$codice = file_get_contents(p13n($actionContext->getModelUrl()));
				/*eval('?>' . $codice . '<?php ');
				//eval('?>' . $codice);*/
				//require_once p13n($actionContext->getModelUrl());
				require p13n($actionContext->getModelUrl());
			}
			if ($actionContext->getView() != "") {
				//$codice = file_get_contents(p13n($actionContext->getViewUrl()));
				/*eval('?>' . $codice . '<?php ');
				//eval('?>' . $codice);
				//require_once p13n($actionContext->getModelUrl());*/
				require p13n($actionContext->getViewUrl());
			}
		} catch (ErrorException $e) {
			error_log("TRIGGER NON ESEGUITO CORRETTAMENTE ".$e->getMessage());
			echo "TRIGGER NON ESEGUITO CORRETTAMENTE!";
			// LOG per Azione Trigger Non richiamata
		}
		//restore_error_handler();
		// Ripristino Valori correnti
		$_GET= $SAVGET;
		$_POST =$SAVPOST;
		$_REQUEST = $SAVREQUEST;
		$_SESSION = $SAVSESSION;
		$actionContext=$saveAction;
		// Ritorno il buffer generato dall'azione
		$dati_restituiti = ob_get_clean();
		// Restarto l'ob e butto fuori l'output che avevo salvato prima di partire con l'azione forzata
		ob_start();
		echo $dati_reperiti;
		
		return $dati_restituiti;
	} else {
		// Esecuzione Batch con CURL
		$dom = new DomDocument('1.0');
		$param_1 = $dom->appendChild($dom->createElement('parametri'));
		$parametri['action'] = "TRIGGER_BATCH";
		$parametri['form'] = "DEFAULT";
		$parametri['gateway'] = "";
		$parametri['user']=$_SESSION['user'];
		$parametri_batch = array(
				'azione' => $azione,
				'form' => $form,
				'get' => $get,
				'post' => $post,
				'request' => $request,
				'session_id' => $session_id,
				'trigger_param' => $trigger_param
		);
		$parametri['TRIGGER_PARM'] = base64_encode(serialize($parametri_batch));
		foreach($parametri as $key => $val) {
			$param_2 = $param_1->appendChild($dom->createElement('parametro'));
			$field_name = $dom->createAttribute('id');
			$param_2->appendChild($field_name);
			$name = $dom->createTextNode($key);
			$field_name->appendChild($name);
			$field_name = $dom->createAttribute('value');
			$param_2->appendChild($field_name);
			$name = $dom->createTextNode($val);
			$field_name->appendChild($name);
		}
		// Output XML del documento DOM
		$dom->formatOutput = true;
		$postxml = $dom->saveXML();
		$postxml = str_replace(array('<br>','</br>', "\r\n", "\n", "\r"), "", $postxml);
		$postxml = str_replace(array('>  <'), "><", $postxml);
		$fields = array("POSTXML" => $postxml);
		
		$fields_string = "";
		foreach($fields as $key => $value) {
			$fields_string .= $key.'='.$value.'&';
		}		
		rtrim($fields_string, '&');
		// set the url, number of POST vars, POST data
		$url = "http://".$_SERVER['HTTP_HOST'].$appBase.'batch.php';
		if ($wait== True) {
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//execute post
			$result = curl_exec($ch);
			return $result;
		} else {
			post_without_wait($url, $fields);
		}
	}
}
function exception_error_handler_action($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
function post_without_wait($url, $params)
{
	foreach ($params as $key => &$val) {
		if (is_array($val)) $val = implode(',', $val);
		$post_params[] = $key.'='.urlencode($val);
	}
	$post_string = implode('&', $post_params);
	
	$parts=parse_url($url);
	
	$fp = fsockopen($parts['host'],
			isset($parts['port'])?$parts['port']:80,
			$errno, $errstr, 30);
	if (!$fp) {
		error_log("TRIGGER_NO_WAIT:No Connection to Host");
	}
	$out = "POST ".$parts['path']." HTTP/1.1\r\n";
	$out.= "Host: ".$parts['host']."\r\n";
	$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	$out.= "Content-Length: ".strlen($post_string)."\r\n";
	$out.= "Connection: Close\r\n\r\n";
	if (isset($post_string)) $out.= $post_string;
	
	fwrite($fp, $out);
	fclose($fp);
}