<?php

	if($actionContext->getForm()=="DEFAULT") {
//		echo "GET:<pre>"; print_r($_GET); echo "</pre>";
//		echo "POST:<pre>"; print_r($_POST); echo "</pre>"; die();
		
		if($_GET['f']=="LIBERE" && (!isset($_POST['GERARCHIA_LIB']) || trim($_POST['GERARCHIA_LIB'])=="")) {
			$messageContext->addMessage("ERROR", "Per visualizzare l'albero di una gerarchie LIBERA serve prima selezionarne una.", "GERARCHIA_LIB",true);
		}
		else if($_GET['f']=="CLASSICHE" && (!isset($_POST['GERARCHIA_CLS']) || trim($_POST['GERARCHIA_CLS'])=="")) {
			$messageContext->addMessage("ERROR", "Per visualizzare l'albero di una gerarchie CLASSICA serve prima selezionarne una.", "GERARCHIA_CLS",true);
		}
	}