<?php 

//	echo "MENU VALIDATION<br>";
	if($actionContext->getForm()=="DEFAULT") {
		validation_default();
	}
	
	function validation_default() {
		global $db, $messageContext;
		
		if($_REQUEST['f']=="DETAIL") {
			if(!isset($_POST['codmen']) || trim($_POST['codmen'])==""){
				$messageContext->addMessage("ERROR", "Inserire il codice del menu","codmen");
			}
		}
		else if($_REQUEST['f']=="COPIA") {
			if((isset($_POST['codmen1']) && trim($_POST['codmen1'])!="") &&
				(isset($_POST['codmen2']) && trim($_POST['codmen2'])!="")
			) {
				if(trim($_POST['codmen1'])==trim($_POST['codmen2']))
					$messageContext->addMessage("ERROR", "Il codice del menu originale e il codice del nuovo menu sono uguali");
				else {
					$sql="select * from FMNUSIRI where MENU =?";
					$stmt = $db->singlePrepare($sql,0,true);
					
					$result = $db->execute($stmt,array(trim($_POST['codmen2'])));
					$row = $db->fetch_array($stmt);
					
					if(isset($row["MENU"])) {
						$messageContext->addMessage("ERROR", "Il menu in cui si vuole copiare i dati esiste già");
					}
				}
			}
			if((!isset($_POST['codmen1']) || trim($_POST['codmen1'])=="") &&
				(!isset($_POST['codmen2']) || trim($_POST['codmen2'])=="")
			) {
				$messageContext->addMessage("ERROR", "Inserire il codice del menu originale e il codice del nuovo menu");
			}
			if((!isset($_POST['codmen1']) || trim($_POST['codmen1'])=="") &&
				(isset($_POST['codmen2']) && trim($_POST['codmen2'])!="")
			) {
				$messageContext->addMessage("ERROR", "Inserire il codice del menu originale","codmen1");
			}
			if((isset($_POST['codmen1']) && trim($_POST['codmen1'])!="") &&
				(!isset($_POST['codmen2']) || trim($_POST['codmen2'])=="")
			) {
				$messageContext->addMessage("ERROR", "Inserire il codice del nuovo menu","codmen2");
			}
		}
	}

?>