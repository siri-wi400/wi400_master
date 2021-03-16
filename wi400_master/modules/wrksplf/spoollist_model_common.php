<?php

	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()!="DEFAULT") {
		//$wi400List = $_SESSION['WRKSPLF'];
		$wi400List = getList('WRKSPLF');		
		$rowsSelectionArray = $wi400List->getSelectionArray();
//		echo "ROWS:<pre>"; print_r($rowsSelectionArray); echo "</pre><br>";
		
		$fieldsValue = array();
		$fieldsValue = array(
			"FILE" => "",
			"JOB" => "",
			"SPLNBR" => ""
		);
			
		if(wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "SAVE")!="")
			$fieldsValue['SAVE'] = wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "SAVE");
		
		if(wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "OUTQ")!="")
			$fieldsValue['OUTQ'] = wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "OUTQ");

		if(wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "DAPAGINA")!="") {
			$da_pag = wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "DAPAGINA");
			
			$a_pag = "*END";
			if(wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "APAGINA")!="")
				$a_pag = wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "APAGINA");
				
			$fieldsValue['PAGERANGE'] = $da_pag." ".$a_pag;
		}
		
		if(wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "COPIES")!="")
			$fieldsValue['COPIES'] = wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "COPIES");
			
		$fieldsValue['USRDTA'] = "''";
		if(wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "USRDTA")!="")
			$fieldsValue['USRDTA'] = wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "USRDTA");
			
		$fieldsValue['FORMTYPE'] = "''";
		if(wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "FORMTYPE")!="")
			$fieldsValue['FORMTYPE'] = wi400Detail::getDetailValue($azione."_MOD_ATTR_DET", "FORMTYPE");
	}

	if($actionContext->getForm()=="UPDATE_ATTRIBUTI") {
		$update_all = false;
		if(isset($_REQUEST["UPDATE_ALL"]))
			$update_all = true;
		
		if($update_all===true) {
			foreach($rowsSelectionArray as $key => $value){
				$keyArray = array();
				$keyArray = explode("|",$key);
				
				$fieldsValue['FILE'] = $keyArray[3];
				$fieldsValue['JOB'] = $keyArray[2]."/".$keyArray[1]."/".$keyArray[0];
				$fieldsValue['SPLNBR'] = $keyArray[4];
								
//				echo "FIELDS:<pre>"; print_r($fieldsValue); echo "</pre><br>";
	
				$do = executeCommand("CHGSPLFA",$fieldsValue,array(),$connzend);
				
				if ($do===false){
					$ret = i5_errormsg();
					print_r(i5_error());
					$messageContext->addMessage("ERROR",$ret);
//					$actionContext->setForm("CLOSE_WINDOW");
				}
			}
			
			$actionContext->setForm("CLOSE_WINDOW");
		}
		else {
//			echo "UPDATE UNO ALLA VOLTA<br>";
			
			$rows = array_keys($rowsSelectionArray);
//			echo "ROWS:<pre>"; print_r($rows); echo "</pre><br>";
			
			$row = $rows[0];
//			echo "ROW: $row<br>";

			$keyArray = array();
			$keyArray = explode("|",$row);

			$fieldsValue['FILE'] = $keyArray[3];
			$fieldsValue['JOB'] = $keyArray[2]."/".$keyArray[1]."/".$keyArray[0];
			$fieldsValue['SPLNBR'] = $keyArray[4];

//			echo "FIELDS:<pre>"; print_r($fieldsValue); echo "</pre><br>";

			$do = executeCommand("CHGSPLFA",$fieldsValue,array(),$connzend);
			
			if ($do===false){
				$ret = i5_errormsg();
				print_r(i5_error());
				$messageContext->addMessage("ERROR",$ret);
//				$actionContext->setForm("CLOSE_WINDOW");
			}
			else {
				// eliminare la riga dall'array
				unset($rowsSelectionArray[$row]);
//				echo "ROWS:<pre>"; print_r($rowsSelectionArray); echo "</pre><br>";
			
				if(!empty($rowsSelectionArray)) {
					$wi400List->setSelectionArray($rowsSelectionArray);
				
					$actionContext->setForm("MODIFICA_ATTRIBUTI");
				}
				else {
					$actionContext->setForm("CLOSE_WINDOW");
				}
			}
		}
	}
	
	if($actionContext->getForm()=="MODIFICA_ATTRIBUTI") {
		$actionContext->setLabel("Modifca attributi spool");
	}
	
?>