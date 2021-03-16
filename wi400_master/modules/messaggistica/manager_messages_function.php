<?php

	require_once 'manager_messages_common.php';
	
	function pubblica_messaggio($id, $divulga=false) {
		global $db;
		global $messageContext;
		
		static $stmtTes;
/*		
		$field = array(	"TESSTA" => '1',
			"USRMOD" => $_SESSION['user'],
			"TMSMOD" => getDb2Timestamp(date('d/m/Y H:i:s'))
		);
		
		if(!isset($stmtTes)) {
			$file = "ZMSGTES";
			
			$key = array("TESID" => "?");			
			//showArray($key);
			
			$stmtTes = $db->prepare("UPDATE", $file, $key, array_keys($field));
		}

		$campi = $field;
		$campi[] = $id;
		//showArray($campi);
			
		$result = $db->execute($stmtTes, $campi);
*/
		$messageSet = new wi400AnnounceMessageSet($id);
		$result = $messageSet->aggiornaDataModifica();
		
		if($result)  {
			$messageContext->addMessage("SUCCESS", "Messaggio pubblicato con successo!");
			
			if($divulga===true) {
				$announce = new wi400AnnounceMessage();
				$dati_tes = $announce->getTestata($id);
				
				if($dati_tes['TESDIV'] == "*IMMED") {
					$rs = $announce->divulgaMessageId($id);
				
//					$messageContext->addMessage("SUCCESS", "Messaggio divulgato con successo!");
				}
			}
		}
		else {
			$messageContext->addMessage("ERROR", "Errore pubblicazione messaggio!");
			return false;
		}
		
		return true;
	}
	
	function getDescrizioneUtente($utente) {
		global $db;
		
		$query = "SELECT LAST_NAME FROM sir_users WHERE USER_NAME = '$utente'";
		$rs = $db->query($query);
		if($row = $db->fetch_array($rs)) {
			return $row['LAST_NAME'];
		}
		
		return "";
	}
	
	function getDescrizione($tipo, $valore) {
		$desc = "";
		switch ($tipo) {
			case "*USER": $desc = getDescrizioneUtente($valore); 
							break;
			case "*GRUPPO": $desc = ""; break;
			case "*INT": $desc = getPackageDescrizioneCliente($valore);
						break;
			case "*ENTE": $desc = getPackageDescrizioneEnte($valore);
							break;
		}
		
		return $desc;
	}
	
	
	function wi400_decorator_ICONE($value){
		global $tipo_img, $stmt_query, $db;
		
		list($img, $id, $formato) = explode(";", $value);
		
		$stmt = $stmt_query[$img];
		
		$opacity = "0.2";
		if(is_array($stmt)) {
			if($formato == "TXT") {
				$stmt = $stmt[0];
			}else {
				$stmt = $stmt[1];
			}
		}
		
		$rs = $db->execute($stmt, array($id));
		if($row = $db->fetch_array($stmt)) {
			$opacity = "1.0";
		}		
		
		if($value=="")
			return;
		
		$filesIco = new wi400Image($img);
		$filesIco->setUrl($tipo_img[$img]);
		$filesIco->setStyle("opacity: $opacity;");
	
		return $filesIco->getHtml();
	}