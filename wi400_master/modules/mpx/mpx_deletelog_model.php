<?php

	/* Includere sempre il file delle funzioni comuni */
	require_once 'mpx_commons.php';
	ini_set('max_execution_time', 0);
	
	if($actionContext->getForm() == "DEFAULT") {
		$history->addCurrent();
	}

	/*
	 * Eliminazione dei record con data MAIINS precedente alla data di riferimento, 
	 * di tutti i record delle altre tabelle ad esso collegato
	 * (tabelle interessate: FPDFCONV, FMPXPARM, FEMAILDT, FEMAILAL)
	 * e dei file e cartelle temporanei in MAIATC
	 */
	if($actionContext->getForm() == "DELETE") {
		$data_rif = dateToTimestamp($_POST['DATA_RIF']);
		$sql = "SELECT ID, MAIINS FROM FPDFCONV WHERE MAIINS<'".$data_rif."' ORDER BY MAIINS";		
		$sel_res = $db->query($sql, false, 0);
		
		$key_del = array("ID");
		$stmt_del_email = $db->prepare("DELETE", "FPDFCONV", $key_del, null);
		$stmt_del_parm = $db->prepare("DELETE", "FMPXPARM", $key_del, null);
		$stmt_del_al = $db->prepare("DELETE", "FEMAILAL", $key_del, null);
		$stmt_del_dt = $db->prepare("DELETE", "FEMAILDT", $key_del, null);
		$stmt_del_ct = $db->prepare("DELETE", "FEMAILCT", $key_del, null);
		$stmt_del_ud = $db->prepare("DELETE", "FEMAILUD", $key_del, null);

		$noRecs = true;
		$count = 0;
      	
		while($row = $db->fetch_array($sel_res)) {
			$noRecs = false;
			
			$ID = $row['ID'];

			// Eliminazione del record ID dalla tabella FPDFCONV
			$result = $db->execute($stmt_del_email, array($ID));
			
	       	if(!$result) {
		    	$messageContext->addMessage("ERROR", "Errore nella cancellazione del record ID ".$ID." della tabella FPDFCONV");
		    	continue;
	       	}

	       	// Eliminazione dei parametri MPX legati al record ID dalla tabella FMPXPARM
		    $result = $db->execute($stmt_del_parm, array($ID));
		    
	      	if(!$result) {
		    	$messageContext->addMessage("ERROR", "Errore nella cancellazione del record ID ".$ID." della tabella FMPXPARM");
		    	continue;
	      	}
			
		    // Eliminazione dei file e delle cartelle temporanee (campo MAIATC)	
		    $sql_atc = "SELECT * FROM FEMAILAL WHERE ID='".$ID."'";
		    $res_atc = $db->query($sql_atc, false, 0);		    
		    
		    while($atc = $db->fetch_array($res_atc)) {
		    	if($atc['TPCONV']=="BODY") {
		    		// Nel caso in cui sia allegato un file da utilizzare come BODY dell'e-mail
		    		if(isset($atc['MAIATC']) && $atc['MAIATC']!="" && file_exists($atc['MAIATC'])) {  		
/*		    		
			    		// se questo è un body di default (chiamato default o presente in una cartella private) non va cancellato
			    		$dir = dirname($atc['MAIATC']);
			    		$last_folder = basename($dir);
	
			    		if(strpos("default", $atc['MAIATC'])===false || $last_folder!="private")
			    			unlink($atc['MAIATC']);
*/
		    			// se questo è presente in "/Siri/EMAIL/body/private/" non va cancellato
/*		    			
		    			$body_path = "/Siri/EMAIL/body/private/";
		    			$dir = "";
			    		if(strncasecmp($atc['MAIATC'], $body_path, strlen($body_path))!=0)
			    			$dir = dirname($atc['MAIATC']);
*/
		    			$dir = "";
		    			$pos = strpos(strtolower($atc['MAIATC']), "private");
		    			if ($pos !== false) {
		    				// nothing to do
		    			} else {
		    				$dir = dirname($atc['MAIATC']);
		    			}
		    			
			    		if($dir!="")
			    			unlink($atc['MAIATC']);
		    		}
		    	}
		    	else {		    	
			    	// Eliminare il file $atc['MAIATC'] legato al record ID solo se presente in "/Siri/EMAIL/"
			    	// (si suppone che non si voglia cancellare files provenienti da altri indirizzi)
			    	$dir = "";
			    	if(strncasecmp($atc['MAIATC'], "/Siri/EMAIL/", 12)==0)
		    			$dir = dirname($atc['MAIATC']);
			    	
			   	 	if(isset($atc['MAIATC']) && $atc['MAIATC']!="" && file_exists($atc['MAIATC']) && $dir!="") {
						unlink($atc['MAIATC']);
			    	}
			    	
			    	if(isset($atc['MAIPAT']) && $atc['MAIPAT']!="" && file_exists($atc['MAIPAT'])) {
			    		unlink($atc['MAIPAT']);
			    		$path = dirname($atc['MAIPAT']);
			    	}
			    	// Se è il risultato di una copia non devo cancellarlo. Cancello solamente il risultato automatico della conversione
			    	if(isset($path) && isset($atc['MAINAM']) && $atc['MAINAM']!="") {
			    		$file_path = $path."/".$atc['MAINAM'];
			    		if(file_exists($atc['MAINAM'])) {
			    			//unlink($file_path);
			    		}
			    	}
		    	}
		    }

		    // Eliminare la cartella legata al record ID
		    deleteDir("/Siri/EMAIL/$ID");

		    // Eliminazione degli allegati legati al record ID dalla tabella FEMAILAL
	        $result = $db->execute($stmt_del_al, array($ID));
	        
	      	if(!$result) {
		    	$messageContext->addMessage("ERROR", "Errore nella cancellazione del record ID ".$ID." della tabella FEMAILAL");
		    	continue;
	      	}

	      	// Eliminazione dei destinatari legati al record ID dalla tabella FEMAILDT
	        $result = $db->execute($stmt_del_dt, array($ID));

	        // Eliminazione dei contenuti
	        $result = $db->execute($stmt_del_ct, array($ID));

	        // Eliminazione degli USER DATA
	        $result = $db->execute($stmt_del_ud, array($ID));
	        
	      	if(!$result) {
		    	$messageContext->addMessage("ERROR", "Errore nella cancellazione del record ID ".$ID." della tabella FEMAILDT");
		    	continue;
	      	}
	      	
	      	$count++;
		} 

		if($noRecs===true) {
			$messageContext->addMessage("ERROR", "Non è stato trovato alcun record entro la data ".$_POST['DATA_RIF']);
		}
		else {
			if($count>0)
				$messageContext->addMessage("SUCCESS", "Eliminati $count records");
		}
	    		    	
	    $actionContext->onSuccess("MPX_DELLOG", "DEFAULT");
	   	$actionContext->onError("MPX_DELLOG", "DEFAULT");
	}
	
	function deleteDir($path) {
		return !empty($path) && is_file($path) ?
		@unlink($path) :
		(array_reduce(glob($path.'/*'), function ($r, $i) {
			return $r && deleteDir($i);
		}, TRUE)) && @rmdir($path);
	}