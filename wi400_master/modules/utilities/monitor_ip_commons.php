<?php
$pathsemafori = "/networktracking/semafori/";
$pathlog = "/networktracking/logs/";
$timeout=10;
$timeout_monitor=5;
$ping_command="/QOpenSys/pkgs/bin/ping";
$php_command="/QOpenSys/pkgs/bin/php";
function setMonitorLLogAndistengFlag($ip) {
	global $pathsemafori, $pathlog;
	// Scrittura Log
	$filelog = $pathlog.$ip.".txt";
	$handle = fopen($filelog, "a+");
	$datereq = " REQUEST: ".date("Y-m-d h:i:s", substr($_REQUEST['_'],0 ,10));
	$content = date("Y-m-d h:i:s").": FROM WI400 ".$ip." SESSION ID:".session_id(). " USER:".$_SESSION['user']." - ".$datereq."\r\n";
	fwrite($handle, $content);
	fclose($handle);
	// Scrittura Semaforo
	$semaforo = $pathsemafori.$ip.".txt";
	if (!file_exists($semaforo)) {
		file_put_contents($semaforo, date("Y-m-d H:i:s"));
	}
	return True;
}