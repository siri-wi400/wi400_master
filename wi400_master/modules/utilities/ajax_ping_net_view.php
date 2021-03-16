<?php
if (!isset($_SESSION['user'])) {
	header("HTTP/1.0 404 Not Found");
	die();
}
// Imposto l'array di default per la risposta
$risposta = array("result"=>"OK", "message"=>"");
$ip = $_SERVER['REMOTE_ADDR'];
if (isset($settings['check_net_mon']) && $settings['check_net_mon']==True) {
	require_once "monitor_ip_commons.php";
	setMonitorLLogAndistengFlag($ip);
}
// Cerco se c'è qualche messaggio in coda
/*$riga = read_and_delete_first_line($settings['message_path'].session_id().".txt");
if ($riga!="") {
	$dati = explode("|", $riga);
	$code = $dati[0];
	$message = $dati[1];
	$sender = $dati[2];
	$reply = $dati[3];
	$risposta = array("result"=>$code, "message"=>$message, "sender"=>$sender, "reply"=>$reply);
}*/
echo utf8_encode(json_encode($risposta));

/*function read_and_delete_first_line($filename) {
	$output="";
	if (file_exists($filename)) {
		$file = file($filename);
		if (isset($file[0])) {
			$output = $file[0];
			unset($file[0]);
			file_put_contents($filename, $file);
		}
	}
	return $output;
}*/