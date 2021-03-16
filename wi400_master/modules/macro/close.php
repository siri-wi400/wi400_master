<?php

	$i5Session = $_GET['I5_SESSION'];
	$i5Job = 'WI'.substr($_GET['I5_SESSION'],3, 7)."*";
	//$handle = fopen('/www/close.txt', "a+");
	// Chiusura lavoro
	//$command = '/www/utility/samplecl.out "ENDJOB JOB('.trim($i5Session).') OPTION(*IMMED) SPLFILE(*YES) LOGLMT(0)"';
	$command = '/www/utility/samplecl.out "DSCJOB JOB('.trim($i5Session).')"';	
	//fwrite($handle, "Sessione $i5Session ".$command.'\r\n');
    $do = system($command, $retval);
    //fwrite($handle, "Comando:".$do.'\r\n');
    sleep(5);
    // Spegnimento Device
    $command = '/www/utility/samplecl.out "VRYCFG CFGOBJ('.trim($i5Job).') CFGTYPE(*DEV) STATUS(*OFF)"';
	//fwrite($handle, "Sessione $i5Session ".$command.'\r\n');
    $do = system($command, $retval);
    //fwrite($handle, "Comando:".$do.'\r\n');
    // Cancellazione Device
    sleep(1);
    $command = '/www/utility/samplecl.out "DLTDEVD DEVD('.trim($i5Job).')"';
	//fwrite($handle, "Sessione $i5Session ".$command.'\r\n');
    $do = system($command, $retval);
    //fwrite($handle, "Comando:".$do.'\r\n');
    
    //fclose($handle);
    
