<?php 

	if($actionContext->getForm()=="MODIFICA_ATTRIBUTI") {
		if(isset($_POST['DAPAGINA']) && $_POST['DAPAGINA']!="") {
			if(isset($_POST['APAGINA']) && $_POST['APAGINA']!="") {
				if($_POST['DAPAGINA']>$_POST['APAGINA'])
					$messageContext->addMessage("ERROR", _t('W400016'),"DAPAGINA");
				else {
					//$wi400List = $_SESSION['WRKSPLF'];
					$wi400List = getList('WRKSPLF');
					$rowsSelectionArray = $wi400List->getSelectionArray();
					
					$rows = array_keys($rowsSelectionArray);
					
					$row = $rows[0];
					
					$keyArray = array();
					$keyArray = explode("|",$row);
					
					if($_POST['APAGINA']>$keyArray[7])
						$messageContext->addMessage("ERROR", _t('W400017'),"APAGINA");
				}
			}
		}
		else {
			if(isset($_POST['APAGINA']) && $_POST['APAGINA']!="")
				$messageContext->addMessage("ERROR", _t('W400018'),"DAPAGINA");
		}
	}

?>