<?php
//print_r(getProcessList("qpgmr", array("QIBM" , "OpenSSH")));
//print_r(getProcessList("qpgmr", "QIBM"));
//print_r(kill(501));
function getProcessList($user="", $filters="") {
	$output = shell_exec("ps -ef -o '%p|%u|%a'");
	$output = explode("\n", $output);
	$processList = array();
	$first = True;
	foreach ($output as $key=>$value) {
	    // Salto il primo giro perchè contiene la testata
		if ($value=="") {
			continue;
		}
	    if ($first) {
			$first = false;
			continue;
		}
		$parts = explode("|", $value);
		/*echo "<pre>";
		print_r($parts);
		echo "</pre>";*/
        if ($user!="" && trim(strtoupper($parts[1]))!=strtoupper($user)) {
			continue;
		}
		if ($filters!="" && strpos_arr(trim($parts[2]), $filters)==False) {
			continue;
		}
		$processList[$parts[0]]=$value;
	}
	return $processList;
}
function kill($pid){
	 $command = 'kill '.$pid;
	 exec($command, $output);
	 return $output;
}
function strpos_arr($haystack, $needle) {
     if(!is_array($needle)) $needle = array($needle);
	 $found = False;
     foreach($needle as $key=>$what) {
         if(strpos($haystack, $what)!==false) {
			$found = True;
		 } else {
			return False;
		 }
     }
     return $found;
 }
