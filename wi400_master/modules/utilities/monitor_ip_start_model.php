<?php
require_once "monitor_ip_commons.php";
echo "Inizializzazione Monitor Batch IP da tracciare\r\n";
// Controllo se sta già girando un monitor
$job = substr($script, 5, 3).$pieces[3];
$user= $batchContext->user;
$job = "MASTER_MON";
//echo "Doc root:"."$doc_root/$appBase/cli.php"."\r\n";
//$cmd ="SBMJOB CMD(CALL PGM(QP2SHELL) PARM('/usr/local/zendsvr6/bin/php' '/DATI/www/zendsvr/htdocs/$appBase/cli.php' 'appBase=$appBase' 'user=$user' 'action=DF001' 'ip=$ip' 'port=$port' 'tipo=$tipo' 'private=BILANCE')) JOB($job) JOBQ(CCQ_COMM/CCQC_JOBQ)";
$cmd ="SBMJOB CMD(CALL PGM(QP2SHELL) PARM('$php_command' '$doc_root/$appBase/cli.php' 'appBase=$appBase' 'user=$user' 'action=MONITOR_IP' 'private=MONITOR_IP')) JOB($job) JOBQ(CCQ_COMM/CCQC_JOBQ)";
//echo $cmd."\r\n";
$xml="<scipt><cmd>$cmd</cmd></script>";
echo callXMLService($xml)."\r\n";
die();
