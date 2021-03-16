<?php 

	if($actionContext->getForm()=="DEFAULT") {
		$myButton = new wi400InputButton('EXPORT_BUTTON');
		$myButton->setLabel(_t("XLS_CONVERT"));
		$myButton->setAction($azione);
		$myButton->setForm("EXPORT_XLS");
		$myButton->setTarget("WINDOW");
		$myButton->setConfirmMessage("Esportare?");
		$myButton->dispose();
	
		// Aggiunta albero merceologico
		$treeMerc = new wi400Tree("TREE_TO");
		$treeMerc->setRootFunction("settori_127");
		$treeMerc->setSelectionLevels(array(false, false, false));
		$treeMerc->setFilterLevels(array("T3.T127CD","T2.T127CD","T1.T127CD"));
		$treeMerc->dispose();
	}
	else if($actionContext->getForm()=="EXPORT_XLS") {
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	
		downloadDetail($TypeImage, $filename, $temp, "Esportazione completata");
	}