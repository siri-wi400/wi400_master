<?php

	//if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_DET", false);
		$searchAction->setColsNum(1);
		
		$myField = new wi400InputText('DATA_ELIMINAZIONE');
		$myField->addValidation('date');
		$myField->addValidation('required');
		$myField->setLabel("Elimina log fino al giorno");
		$searchAction->addField($myField);
		
		// Seleziona
		$myButton = new wi400InputButton($azione.'_BUTTON');
		$myButton->setLabel("Pulisci");
		$myButton->setAction($azione);
		$myButton->setForm("DELETE_LOG");
		$myButton->setConfirmMessage("Sei sicuro di voler cancellare i log fino alla data richiesta? I file non potranno più essere recuperati!");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
		
		echo "<br/>";
	if($actionContext->getForm()=="DELETE_LOG") {
		
		//echo $data_eliminazione."<br/>";
		$ora = "00.00.00";
		
		$query = "SELECT LOGENT, LOGRCX, LOGXIN, LOGXOU, LOGADL FROM ZWEBSLOG WHERE LOGRCX < '$data_eliminazione-$ora.000000'";
		
		//echo $query."<br/>";
		
		$rs = $db->query($query);
		$count = 0;
		$html = "";
		while($row = $db->fetch_array($rs)) {
			if ($row['LOGXIN']!="") {
				if(file_exists($row['LOGXIN'])) {
					if(unlink($row['LOGXIN'])) {
						$htlm .= "Eliminato il file ".$row['LOGXIN']."<br/>";
						$count++;
					}else {
						$htlm .= "Non eliminato il file ".$row['LOGXIN']."<br/>";
					}
				}else {
					$htlm .= "File non esiste ".$row['LOGXIN']."<br/>";
				}
			}
			if ($row['LOGXOU']!="") {
				if(file_exists($row['LOGXOU'])) {
					if(unlink($row['LOGXOU'])) {
						$htlm .= "Eliminato il file ".$row['LOGXOU']."<br/>";
						$count++;
					}else {
						$htlm .= "Non eliminato il file ".$row['LOGXOU']."<br/>";
					}
				}else {
					$htlm .= "File non esiste ".$row['LOGXIN']."<br/>";
				}
			}
			if ($row['LOGADL']!="") {
				if(file_exists($row['LOGADL'])) {
					if(unlink($row['LOGADL'])) {
						$htlm .= "Eliminato il file ".$row['LOGADL']."<br/>";
						$count++;
					}else {
						$htlm .= "Non eliminato il file ".$row['LOGADL']."<br/>";
					}
				}else {
					$htlm .= "File non esiste ".$row['LOGADL']."<br/>";
				}
			}
		}
		
		echo "Numero file eliminati ".$count."<br/><br/><br/>";
		echo $htlm;
		
		$query = "DELETE FROM ZWEBSLOG WHERE LOGRCX < '$data_eliminazione-$ora.000000'";
		if($db->query($query)) {
			$messageContext->addMessage("SUCCESS", "Messaggio eliminato con successo!");
		}
	}