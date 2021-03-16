<?php
function sendNotification($token, $b, $badge, $lingua) {
	// Put your device token here (without spaces):
	$deviceToken = $token;
	
	// Put your private key's passphrase here:
	$passphrase = 'La1563CNNd';
	
	// Put your alert message here:
	if($b) {
		if($lingua == "it") {
			$message = "Devi spostare la macchina";
		}else {
			$message = "You must move the car";
		}
	}else {
		if($lingua == "it") {
			$message = "Avviso letto";
		}else {
			$message = "Notice bed";
		}
	}
	
	////////////////////////////////////////////////////////////////////////////////
	
	$ctx = stream_context_create();
	stream_context_set_option($ctx, 'ssl', 'local_cert', 'produzione.pem');
	stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
	
	// Open a connection to the APNS server
	$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
	
	if (!$fp)
		exit("Failed to connect: $err $errstr" . PHP_EOL);
	
	//echo 'Connected to APNS' . PHP_EOL;
	
	$file = fopen('/home/crm/targhe/targa2.txt', 'a');
	fwrite($file, "Risposta ".$message."\r\n");
	fclose($file);
	
	// Create the payload body
	$body['aps'] = array(
			'badge' => $badge,
			'alert' => $message,
			'sound' => 'default',
			'type' => $b
	);
	
	// Encode the payload as JSON
	$payload = json_encode($body);
	
	// Build the binary notification
	$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
	
	// Send it to the server
	$result = fwrite($fp, $msg, strlen($msg));
	
	if (!$result)
		return 0;
	else
		return 1;
	
	// Close the connection to the server
	fclose($fp);
}

function sendNotificationDeveloper($token, $b, $badge, $lingua) {
	// Put your device token here (without spaces):
	$deviceToken = $token;
	
	$file = fopen('/home/crm/targhe/targaToken.txt', 'w');
	fwrite($file, "\r\n".$deviceToken."\r\n");
	fclose($file);

	// Put your private key's passphrase here:
	$passphrase = 'La1563CNNd';

	// Put your alert message here:
	if($b) {
		if($lingua == "it") {
			$message = "Devi spostare la macchina";
		}else {
			$message = "You must move the car";
		}
	}else {
		if($lingua == "it") {
			$message = "Avviso letto";
		}else {
			$message = "Notice bed";
		}
	}

	////////////////////////////////////////////////////////////////////////////////

	$ctx = stream_context_create();
	stream_context_set_option($ctx, 'ssl', 'local_cert', 'certificati.pem');
	stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

	// Open a connection to the APNS server
	$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

	if (!$fp)
		exit("Failed to connect: $err $errstr" . PHP_EOL);

	//echo 'Connected to APNS' . PHP_EOL;

	$file = fopen('/home/crm/targhe/targa2.txt', 'a');
	fwrite($file, "Risposta EM se ".$message."\r\n");
	fclose($file);

	// Create the payload body
	$body['aps'] = array(
			'badge' => $badge,
			'alert' => $message,
			'sound' => 'default',
			'type' => $b
	);

	// Encode the payload as JSON
	$payload = json_encode($body);

	// Build the binary notification
	$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

	// Send it to the server
	$result = fwrite($fp, $msg, strlen($msg));

	if (!$result)
		return 0;
	else
		return 1;

	// Close the connection to the server
	fclose($fp);
}