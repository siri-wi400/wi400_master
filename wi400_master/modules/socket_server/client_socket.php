<?php
global $time_start,$time_step,$settings;
getMicroTimeStep("START");
$fp = @fsockopen("10.0.40.1", "5050", $errno, $errstr, 1);
if (!$fp) {
    echo "Impossibile aprire il socket sull'indirizzo ";
	return false;
}
stream_set_timeout($fp, 60);
stream_set_blocking($fp, 1);
$stringa="";
$txcmd="CIAO!!";
//for($i=0; $i<100; $i++) {	
	fputs($fp, $txcmd.chr(0));
	$rxbuff=fread ($fp,1);
	echo "<br>".$rxbuff;
//}
getMicroTimeStep("CLOSE");
function getMicroTime(){
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
}
function getMicroTimeStep($stepName){
	global $time_start,$time_step,$settings;
		$thisTimeStart = getMicroTime() - $time_start;
		$thisTimeStep = $thisTimeStart - $time_step;
		//echo "<div style=\"background-color:#000000;color:#FFFFFF\"><b>".$stepName."</b>: ".$thisTimeStart." (<i>".$thisTimeStep." dallo step precedente</i>)</div>";
		echo "<b>".$stepName."</b>: ".$thisTimeStart." (<i>".$thisTimeStep." dallo step precedente</i>)";
		$time_step = $thisTimeStart;
}