<?php 

//	echo "AZIONI VALIDATION<br>";
	if($actionContext->getForm()=="DEFAULT") {
		validation_default();
	}
	
	function validation_default() {
		global $db, $messageContext;
		
		if($_REQUEST['f']=="DETAIL") {
			if(!isset($_POST['codazi']) || trim($_POST['codazi'])==""){
				$messageContext->addMessage("ERROR", "Inserire il codice dell'azione","codazi");
			}
		}
		else if($_REQUEST['f']=="COPIA") {
			if((isset($_POST['codazi1']) && trim($_POST['codazi1'])!="") &&
				(isset($_POST['codazi2']) && trim($_POST['codazi2'])!="")
			) {
				if(trim($_POST['codazi1'])==trim($_POST['codazi2']))
					$messageContext->addMessage("ERROR", "Il codice dell'azione originale e il codice della nuova azione sono uguali");
				else {
					$sql="select * from FAZISIRI where AZIONE =?";
					$stmt = $db->singlePrepare($sql,0,true);
					
					$result = $db->execute($stmt,array(trim($_POST['codazi2'])));
					$row = $db->fetch_array($stmt);
					
					if(isset($row["AZIONE"])) {
						$messageContext->addMessage("ERROR", "L'azione in cui si vuole copiare i dati esiste già");
					}
				}
			}
			if((!isset($_POST['codazi1']) || trim($_POST['codazi1'])=="") &&
				(!isset($_POST['codazi2']) || trim($_POST['codazi2'])=="")
			) {
				$messageContext->addMessage("ERROR", "Inserire il codice dell'azione originale e il codice della nuova azione");
			}
			if((!isset($_POST['codazi1']) || trim($_POST['codazi1'])=="") &&
				(isset($_POST['codazi2']) && trim($_POST['codazi2'])!="")
			) {
				$messageContext->addMessage("ERROR", "Inserire il codice dell'azione originale","codazi1");
			}
			if((isset($_POST['codazi1']) && trim($_POST['codazi1'])!="") &&
				(!isset($_POST['codazi2']) || trim($_POST['codazi2'])=="")
			) {
				$messageContext->addMessage("ERROR", "Inserire il codice della nuova azione","codazi2");
			}
		}
	}

?>