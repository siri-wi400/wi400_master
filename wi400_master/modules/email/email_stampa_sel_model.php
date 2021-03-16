<?php

	$azione = $actionContext->getAction();
	echo "AZIONE: $azione<br>";
	
	$steps = $history->getSteps();

	if(in_array($actionContext->getForm(), array("STAMPA_SEL", "STAMPA_TUTTO"))) {
		$actionContext->setLabel("Stampa spool");
	
		wi400Session::delete(wi400Session::$_TYPE_DETAIL, $azione.'_STAMPA_SEL_DET');
	
		$idList = "MONITOR_EMAIL_LIST";
		$wi400List = getList($idList);
	
		$rowsSelectionArray = $wi400List->getSelectionArray();
//		echo "SEL_ARRAY:<pre>"; print_r($rowsSelectionArray); echo "</pre>";
	
		$outq = "";
		$outq = wi400Detail::getDetailValue($azione.'_STAMPA_SEL_DET','OUTQ');
//		echo "OUTQ: $outq<br>";
/*	
		if(!isset($outq) || $outq=="") {
//			echo "COUNT: ".count($rowsSelectionArray)."<br>";
			if(count($rowsSelectionArray)==1) {
				$sel_keys_array = array_keys($rowsSelectionArray);
	
				$sel_keys = explode("|", $sel_keys_array[0]);
//				echo "SEL KEYS:<pre>"; print_r($sel_keys); echo "</pre>";
	
				$ID = $sel_keys[0];
	
				$sql = "select * from FEMAILAL where ID='$ID' and TPCONV<>'BODY'";
				$res = $db->query($sql, false, 0);
	
				$i = 0;
				while($row = $db->fetch_array($res)) {
					$outq = $row['MAIOUT'];
						
					$i++;
				}
	
				if($i>=2)
					$outq = "";
	
//				echo "OUTQ: $outq<br>";
			}
		}
*/	
		// Stampa Duplex
		$check_duplex = wi400Detail::getDetailValue($azione.'_STAMPA_SEL_DET','DUPLEX_SEL');
	
		if(!isset($check_duplex) || $check_duplex=="") {
			$check_duplex = "S";
		}
//		echo "STAMPA DUPLEX: $check_duplex<br>";
	}
	else if ($actionContext->getForm() == "CALCULATE"){
		$check_duplex = "";
		if (isset($_GET["OUTQ"])){
			$sql = "select * from FP2OPARM where PROUTQ='{$_GET["OUTQ"]}'";
			$res = $db->singleQuery($sql);
			if($row = $db->fetch_array($res)) {
				$check_duplex = "S";
			}
		}
		die($check_duplex);
	}