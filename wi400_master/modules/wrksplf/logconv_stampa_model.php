<?php

	require_once 'logconv_stampa_common.php';
	
	$azione = $actionContext->getAction();
//	echo "AZIONE: $azione - FORM: ".$actionContext->getForm()."<br>";

	if(in_array($actionContext->getForm(), array("STAMPA", "STAMPA_TUTTO"))) {
		$outq = wi400Detail::getDetailValue($azione.'_STAMPA_SEL_DET','OUTQ');
//		echo "OUTQ: $outq<br>";
	
		$duplex = wi400Detail::getDetailValue($azione.'_STAMPA_SEL_DET','DUPLEX_SEL');
	
		$idList = $_REQUEST['IDLIST'];
		$wi400List = getList($idList);
	}
	
	// Aumentata la dimensione del limite della memoria
	ini_set("memory_limit","1000M");
	set_time_limit(0);
	
	if($actionContext->getForm()=="STAMPA") {
		if(isset($_REQUEST['DETAIL_KEY']) && $_REQUEST['DETAIL_KEY']!="") {
//			echo "DA LINK<br>";
			$rowsSelectionArray = array();
			$rowsSelectionArray[$_REQUEST['DETAIL_KEY']] = array();
		}
		else {
//			echo "DA AZIONE<br>";
			$rowsSelectionArray = $wi400List->getSelectionArray();
		}
	
//		echo "SEL_ARRAY:<pre>"; print_r($rowsSelectionArray); echo "</pre>";
	
		if(!empty($rowsSelectionArray)) {
			foreach($rowsSelectionArray as $key => $val) {
				$sel_keys = explode("|", $key);
//				echo "SEL KEYS:<pre>"; print_r($sel_keys); echo "</pre>";
	
				stampa_logconv($outq, $duplex, $sel_keys);
			}
		}
	
		$actionContext->onError($azione, "STAMPA_SEL");
		$actionContext->onSuccess($azione, "CLOSE_WINDOW");
	}
	else if($actionContext->getForm()=="STAMPA_TUTTO") {
		require_once $routine_path."/classi/wi400ExportList.cls.php";
	
		$exportTarget = "ALL";
	
		$export = new wi400ExportList($exportTarget, $wi400List);
	
		$export->prepare();
	
		$sql_list = $export->get_query();
//		echo "SQL: $sql_list<br>";
	
		$resultSet = $db->query($sql_list, False, 0);
	
		while($row = $db->fetch_array($resultSet)) {
//			echo "ROW:<pre>"; print_r($row); echo "</pre>"; continue;
				
			$params = array(
					$row["LOGUSR"],
					$row["LOGJOB"],
					$row["LOGNBR"],
					$row["LOGDTA"],
					$row["LOGPTH"],
					$row["LOGNOM"],
					$row["LOGMOD"],
					$row["LOGID"]
			);
	
			stampa_logconv($outq, $duplex, $params);
		}
	
		$actionContext->onError($azione, "STAMPA_SEL_TUTTO");
		$actionContext->onSuccess($azione, "CLOSE_WINDOW");
	}
	
	if(in_array($actionContext->getForm(), array("STAMPA_SEL", "STAMPA_SEL_TUTTO"))) {
		if($actionContext->getForm()=="STAMPA_SEL")
			$actionContext->setLabel("Stampa");
		else if($actionContext->getForm()=="STAMPA_SEL_TUTTO")
			$actionContext->setLabel("Stampa Tutto");
	
		wi400Session::delete(wi400Session::$_TYPE_DETAIL, $azione.'_STAMPA_SEL_DET');
	
		$idList = $azione."_LIST";
		$wi400List = getList($idList);
	
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