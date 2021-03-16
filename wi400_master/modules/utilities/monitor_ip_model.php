<?php
require_once "monitor_ip_commons.php";
echo "Scansione File Semafori\r\n";
$user= $batchContext->user;
$thefile = $pathsemafori."monitor.txt";
$handle = fopen($thefile, "w+");
if (!$handle) {
	die("File $thefile non scritto!!");
}
if (!flock($handle, LOCK_EX | LOCK_NB)) {
	die("Il monitor è gia in esecuzione\r\n");
} else {
	try {
		while (1==1) {
			$files =scandir ($pathsemafori);
			foreach ($files as $key => $value) {
				//echo "File $value\r\n";
				if ($value!='.' && $value!=".." && $value!="monitor.txt") {
					$thefile2=$pathsemafori.$value;
					echo "Verifico se lock! $thefile2\r\n";
					$handle2 = fopen($thefile2, "w+");
					echo var_dump($handle2);
					if (!flock($handle2, LOCK_EX | LOCK_NB)) {
						echo "File $thefile2 in uso\r\n";
						// Qualcuno lo sta utilizzando
					} else {
						echo "SBMJOB $thefile2\r\n";
						// Sottometto il lavoro di ping
						flock($handle2, LOCK_UN);
						fclose($handle2);
						$ip4 = explode(".",$value);
						$job = "PING_".$ip4[3];
						$ip = str_replace(".txt","", $value);
						echo "Immissione job $job per PING\r\n";
						//echo "Doc root:"."$doc_root/$appBase/cli.php"."\r\n";
						//$cmd ="SBMJOB CMD(CALL PGM(QP2SHELL) PARM('/usr/local/zendsvr6/bin/php' '/DATI/www/zendsvr/htdocs/$appBase/cli.php' 'appBase=$appBase' 'user=$user' 'action=DF001' 'ip=$ip' 'port=$port' 'tipo=$tipo' 'private=BILANCE')) JOB($job) JOBQ(CCQ_COMM/CCQC_JOBQ)";
						$cmd ="SBMJOB CMD(CALL PGM(QP2SHELL) PARM('$php_command' '$doc_root/$appBase/cli.php' 'ip=$ip' 'appBase=$appBase' 'user=$user' 'action=MONITOR_PING' 'private=MONITOR_IP')) JOB($job) JOBQ(CCQ_COMM/CCQC_JOBQ)";
						//echo $cmd."\r\n";
						$xml="<scipt><cmd>$cmd</cmd></script>";
						echo callXMLService($xml)."\r\n";
					}
					//echo "Nulla da fare!\r\n";
				}
			}
			sleep ($timeout_monitor);
		}
	} catch (Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
}
//$cmd = '/QOpenSys/pkgs/bin/ping 10.0.40.1 | while read pong; do echo "$(date "+%Y-%m-%d %H:%M:%S"): $pong"; done >> /www/thefile.txt';
//exec($cmd);
//sleep(60);