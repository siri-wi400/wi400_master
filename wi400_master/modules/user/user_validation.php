<?php 

//	echo "AZIONI VALIDATION<br>";
	if($actionContext->getForm()=="DEFAULT") {
		if($_REQUEST['f']=="DETAIL") {
			if(!isset($_POST['codusr']) || trim($_POST['codusr'])==""){
				$messageContext->addMessage("ERROR", "Inserire il codice dell'utente","codusr");
			}
		}
		else if($_REQUEST['f']=="COPIA") {
			if((isset($_POST['codusr1']) && trim($_POST['codusr1'])!="") &&
				(isset($_POST['codusr2']) && trim($_POST['codusr2'])!="")
			) {
				if(trim($_POST['codusr1'])==trim($_POST['codusr2']))
					$messageContext->addMessage("ERROR", "Il codice dell'utente originale e il codice del nuovo utente sono uguali");
				else {
					$sql = "SELECT * FROM $users_table WHERE USER_NAME=?";
					$stmt = $db->singlePrepare($sql,0,true);
					
					$result = $db->execute($stmt,array(trim($_POST['codusr1'])));
					$row = $db->fetch_array($stmt);
					
					if(!isset($row["USER_NAME"])) {
						$messageContext->addMessage("ERROR", "Valore di Codice non valido!", "codusr1");
					}
					
					$result = $db->execute($stmt,array(trim($_POST['codusr2'])));
					$row = $db->fetch_array($stmt);
					
					if(isset($row["USER_NAME"])) {
						$messageContext->addMessage("ERROR", "L'utente in cui si vuole copiare i dati esiste già");
					}
				}
			}
			if((!isset($_POST['codusr1']) || trim($_POST['codusr1'])=="") &&
				(!isset($_POST['codusr2']) || trim($_POST['codusr2'])=="")
			) {
				$messageContext->addMessage("ERROR", "Inserire il codice dell'utente originale e il codice del nuovo utente");
			}
			if((!isset($_POST['codusr1']) || trim($_POST['codusr1'])=="") &&
				(isset($_POST['codusr2']) && trim($_POST['codusr2'])!="")
			) {
				$messageContext->addMessage("ERROR", "Inserire il codice dell'utente originale","codusr1");
			}
			if((isset($_POST['codusr1']) && trim($_POST['codusr1'])!="") &&
				(!isset($_POST['codusr2']) || trim($_POST['codusr2'])=="")
			) {
				$messageContext->addMessage("ERROR", "Inserire il codice del nuovo utente","codusr2");
			}
		}
	}
	else if(in_array($_REQUEST['f'], array("INSERT","UPDATE","CHECK"))) {
		if(isset($_POST['OFFICE']) && trim($_POST['OFFICE'])!="") {
			$file_path = $root_path."p13n/".trim($_POST['OFFICE']);
			if(!file_exists($file_path) || !is_dir($file_path)) {
				$messageContext->addMessage("ERROR", "Directory inesistente","OFFICE",true);
			}
		}
				
		if(isset($_POST['THEME']) && trim($_POST['THEME'])!="") {
			$file_path = $root_path."themes/".trim($_POST['THEME']);
			if(!file_exists($file_path) || !is_dir($file_path)) {
				$messageContext->addMessage("ERROR", "Directory inesistente","THEME",true);
			}
		}
		

		if(isset($_POST['PACKAGE']) && trim($_POST['PACKAGE'])!="") {
			$file_path = $root_path."base/package/".trim($_POST['PACKAGE']);
			if(!file_exists($file_path) || !is_dir($file_path)) {
				$messageContext->addMessage("ERROR", "Directory inesistente","PACKAGE",true);
			}
		}
	}

?>