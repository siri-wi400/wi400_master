<?php

	$azione = $actionContext->getAction();

	if($actionContext->getForm()=="DEFAULT")
		$history->addCurrent();
	
	if($actionContext->getForm()=="CHECK_ALL") {
		$_SESSION['UPDATE_STATUS']='ON';
	
		$checkAction = $_GET["VALUE"];
		$colKey = $_GET["COL"];
		$idList = $_GET['IDLIST'];
		$miaLista = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
//		echo "CHECK ACTION: $checkAction - COL KEY: $colKey<br>";
	
		$sql = "select * from FP2OPARM";
		$res = $db->query($sql, false, 0);
	
		$sel_rows = $miaLista->getSelectionArray();
//		echo "<br>SEL ROWS:<pre>"; print_r($sel_rows); echo "</pre>";
	
		while($row = $db->fetch_array($res)) {
//			echo "ROW:<pre>"; print_r($row); echo "</pre>";
			$key_rif = $row['OUTQNAME']."|".$row['OUTQLIB'];
//			echo "KEY RIF: $key_rif<br>";
	
			$params = array(
				"DUPLEX" => $row['DUPLEX'],
				"DRIVER" => $row['DRIVER'],
			);
	
			foreach($params as $k => $v) {
				if ($checkAction == 0) {
					$params['DUPLEX'] = "N";
				}
				else {
					$params['DUPLEX'] = "S";
				}
			}
	
//			echo "PARAMETRI:<pre>"; print_r($params); echo "</pre>";
			$miaLista->setSelectionKey($key_rif, $params);
		}
	
		if ($checkAction == 0) {
			$checkAction = 1;
		}
		else {
			$checkAction = 0;
		}
//		echo "CHECK ACTION: $checkAction<br>";
	
		$miaLista->setHeaderValue($colKey, $checkAction);
	
		wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $miaLista);
	
		$miaLista = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
//		var_dump($miaLista->getSelectionArray());
	
		die();
	}
	else if($actionContext->getForm()=="SAVE") {
		$idList = $_REQUEST['IDLIST'];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		
		$sql = "select * FROM FP2OPARM WHERE PROUTQ=?";
		$stmt = $db->singlePrepare($sql,0,true);
		
		$keyName = array("PROUTQ"=>'?');
		$fieldUpdtValues = array("PRDUPX" => "N", "PRDRIV" => "", "PRDTIM" => "", "PRDUSR" => "");			
		$stmtUpdate = $db->prepare("UPDATE", "FP2OPARM", $keyName, array_keys($fieldUpdtValues));
		
		$fieldInsValues = array("PROUTQ" => "", "PRDUPX" => "N", "PRDRIV" => "", "PRDOPT" => "", "PRDTIM" => "", "PRDUSR" => "");	
		$stmtInsert = $db->prepare("INSERT", "FP2OPARM", null, array_keys($fieldInsValues));
		
		$errors = false;
		
		$idUser = $_SESSION['user'];
		$timeStamp = getDb2Timestamp();
		
		foreach($wi400List->getSelectionArray() as $key => $value){
			$keyArray = explode("|",$key);
			$coda = $keyArray[0];
//			$libreria = $keyArray[1];
		
			$db->execute($stmt, array($coda));
			if($myRow = $db->fetch_array($stmt)) {
				$campi = $fieldUpdtValues;
		
				$campi['PRDUPX'] = $value['DUPLEX'];
				$campi['PRDRIV'] = $value['DRIVER'];
				$campi['PRDTIM'] = $timeStamp;
				$campi['PRDUSR'] = $idUser;
		
				$campi['PROUTQ'] = $coda;
		
				$result = $db->execute($stmtUpdate, $campi);
				
				if(!$result)
					$errors = true;
			}
			else {
				$fieldInsValues['PROUTQ'] = $coda;
				$fieldInsValues['PRDUPX'] = $value['DUPLEX'];
				$fieldInsValues['PRDRIV'] = $value['DRIVER'];
//				$fieldInsValues['PRDOPT'] = "";
				$fieldInsValues['PRDTIM'] = $timeStamp;
				$fieldInsValues['PRDUSR'] = $idUser;
				
				$result = $db->execute($stmtInsert, $fieldInsValues);
				
				if(!$result)
					$errors = true;
			}
		}
		
		$_SESSION['UPDATE_STATUS']='OFF';
		
		if($errors===true)
			$messageContext->addMessage("ERROR","Errori durante la modifica dei parametri");
		else
			$messageContext->addMessage("SUCCESS","Operazione eseguita in modo corretto!");
		
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DEFAULT");
	}
	else if($actionContext->getForm()=="PRINT_PROVA") {
		$keyArray = getListKeyArray("OUTQLIST_PARAMS_LIST");
		$outq = $keyArray['OUTQNAME'];
		$libl = $keyArray['OUTQLIB'];
		
		$sql = "select * FROM FP2OPARM WHERE PROUTQ=?";
		$stmt = $db->singlePrepare($sql,0,true);
		
		$duplex = "N";
		$db->execute($stmt, array($outq));
		if($myRow = $db->fetch_array($stmt)) {
			$duplex = $myRow['PRDUPX'];
		}
		
		$filename = "single.pdf";
		if($duplex=="S")
			$filename = "duplex.pdf";
		
		$file = $settings['template_path'].$filename;

//		echo "OUTQ: $outq - LIBL: $libl - PDF: $file - DUPLEX: $duplex<br>";
		
		$zp2oprt = new wi400Routine('ZP2OPRT', $connzend);
		$zp2oprt->load_description('ZP2OPRT');
		$zp2oprt->prepare();
		
		$zp2oprt->set("PDF", $file);
		$zp2oprt->set("OUTQ", $outq);
		$zp2oprt->set("LIBL", $libl);
		$zp2oprt->set("DUPLEX", $duplex);
		$zp2oprt->set("FLAG", "0");
		$zp2oprt->call();
		
		$actionContext->gotoAction($azione, "DEFAULT");
	}