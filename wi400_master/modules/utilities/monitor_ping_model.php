<?php
require_once "monitor_ip_commons.php";
$ip = $batchContext->ip;
$logfile=$pathlog.$ip;
$semaforo = $pathsemafori.$ip.".txt";
$handle = fopen($semaforo, "w+");
if (!flock($handle, LOCK_EX | LOCK_NB)) {
	die("Ping gia in esecuzione\r\n");
}
$cmd = "$ping_command ".$ip.' --timeout='.$timeout.' | while read pong; do echo "$(date "+%Y-%m-%d %H:%M:%S"): $pong"; done >> '.$logfile.".txt";
exec($cmd);
// Cancello il semaforo
//flock($semaforo, LOCK_UN);
unlink($semaforo);
flock($semaforo, LOCK_UN);
//sleep(60);