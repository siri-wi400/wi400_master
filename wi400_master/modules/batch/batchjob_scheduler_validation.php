<?php

	if(in_array($actionContext->getForm(),array("NEW_JOB","DETAIL_JOB"))) {
		if($_POST['FREQUENZA']=="*TIME") {
			if(!isset($_POST['INTERVALLO']) || empty($_POST['INTERVALLO']))
				$messageContext->addMessage("ERROR", "Inerire l'intervallo", "INTERVALLO", true);
		}
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$form = $_REQUEST['f'];
		$status = trim($_REQUEST['STATUS']);
		$actstatus = trim($_REQUEST['ACTSTATUS']);
//		echo "FORM: $form - STATUS: $status - ACTSTATUS: $actstatus<br>";

		if($form=="START_BATCH_SCH") {
			if($status=="*ACTIVE" && $actstatus!="HLD")
				$messageContext->addMessage("ERROR", "La schedulazione dei lavori è già attiva.");
		}
		
		if($form=="STOP_BATCH_SCH" || $form=="FREEZE_BATCH_SCH") {
			if($status=="*OUTQ")
				$messageContext->addMessage("ERROR", "La schedulazione dei lavori è già terminata.");
		}
		
		if($form=="FREEZE_BATCH_SCH") {
			if($actstatus=="HLD")
				$messageContext->addMessage("ERROR", "La schedulazione dei lavori è già congelata.");
		}
	}
	
?>