<?php

//	$file_path = $data_path."inventari";
//	$file_path = '/www/zendsvr/data/'.$settings['db_host']."/inventari";
	$file_path = '/www/zendsvr6/data/'.$settings['db_host']."/";

//	echo "<br><font color='red'>INDIRIZZO: ".$file_path."</font><br>";
	
	$array_stati = array(
		"ERRORE",
		"RESET",
		"IN ELABORAZIONE",
		"IN ATTESA DI ELABORAZIONE"
	);
	
	$tipo_invio = array(
		"INVENTARI" => "INVENTAR",
		"CARICHI" => "CARICHI",
		"GESTIONALI" => "GESTIONA",
		"BOLLE" => "BOLLE"	
	);
	
	function invioInventarioTras($filename, $user, $id, $tipo) {
		global $connzend, $db;
	
		// Metto in coda il dato per la successiva elaborazione
		$sendCoda="QSTKCODA";
		$sendLibr="*LIBL";
		// Struttura messaggio
		$MESSAGE = array(
				"DSName"=>"MSG",
				"DSParm"=>array(
					array("Name"=>"FILE", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"200"),
					array("Name"=>"UTENTE", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"10"),
					array("Name"=>"ID", "IO"=>I5_INOUT,"Type"=>I5_TYPE_CHAR, "Length"=>"30")
				)
		);
		$queueSend = dtaq_prepare(trim($sendLibr).'/'.trim($sendCoda), $MESSAGE, "S",-1, True);
		$dati=array(
			"FILE"=>$filename,
			"UTENTE"=>$user,
			"ID"=>$id
		);
		dtaq_send($queueSend,$tipo,$dati);
	}

?>