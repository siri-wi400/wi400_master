<?php

	function stampa_logconv($outq, $duplex, $params) {
		global $db, $connzend;
		
		$user = $params[0];
		$job = $params[1];
		$nbr = $params[2];
		$user_data = $params[3];
		$path = $params[4];
		$filename = $params[5];
		$modulo = $params[6];
		$ultima_conv = $params[7];
		
		$file = $path."/".$filename;
		
		$coda = substr($outq,0, 10);
		$libl = substr($outq,10, 10);
		
//		echo "STAMPA<br>"; die();

		$timeStamp = getDb2Timestamp();
//		echo "TIMESTAMP: $timeStamp<br>";die();

		$sql_log = "select LOGNEL from FLOGCONV where LOGUSR=? and LOGJOB=? and LOGNBR=? and LOGDTA=? and LOGNOM=? and LOGMOD=? and LOGID=?";
		$stmt_log = $db->singlePrepare($sql_log,0,true);

		$keyUpdt = array("LOGUSR" => "?", "LOGJOB" => "?", "LOGNBR" => '?', "LOGDTA" => '?', "LOGNOM" => '?', "LOGMOD" => '?', "LOGID" => '?');
		$fieldsValue = array("LOGSTP" => 'S', "LOGOUT" => $outq, "LOGSTT" => $timeStamp, "LOGNEL" => 1);
		$stmt_updt = $db->prepare("UPDATE", "FLOGCONV", $keyUpdt, array_keys($fieldsValue));

		$zp2oprt = new wi400Routine('ZP2OPRT', $connzend);
		$zp2oprt->load_description('ZP2OPRT');
		$zp2oprt->prepare();
		
		$zp2oprt->set("PDF", $file);
		$zp2oprt->set("OUTQ", $coda);
		$zp2oprt->set("LIBL", $libl);
		$zp2oprt->set("DUPLEX", $duplex);
		$zp2oprt->set("FLAG", "0");
		$zp2oprt->call();
		
		$res_log = $db->execute($stmt_log,  array($user, $job, $nbr, $user_data, $filename, $modulo, $ultima_conv));
		
		$row_log = $db->fetch_array($stmt_log);
		$nel = $row_log['LOGNEL'];
//		echo "NEL: $nel<br>";
		$nel++;
		
		$fieldsValue['LOGNEL'] = $nel;
		$fieldsValue[] = $user;
		$fieldsValue[] = $job;
		$fieldsValue[] = $nbr;
		$fieldsValue[] = $user_data;
		$fieldsValue[] = $filename;
		$fieldsValue[] = $modulo;
		$fieldsValue[] = $ultima_conv;
		
		$res_updt = $db->execute($stmt_updt, $fieldsValue);
	}