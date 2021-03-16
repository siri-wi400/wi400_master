<?php

	function check_error($errore, $not_err_str, $is_error) {
		$array_not_err = explode("|", $not_err_str);
		
		if($is_error===false) {
			if(in_array($errore, $array_not_err))
				return true;
		}
		else {
			if(!in_array($errore, $array_not_err))
				return true;
		}
		
		return false;
	}
	
	function check_email_contents($ID) {
		global $db;
		
		static $stmt_cnts;
		
		if(!isset($stmt_cnts)) {
			$sql_cnts = "select * from FEMAILCT where ID=?";
			$stmt_cnts = $db->singlePrepare($sql_cnts, 0, true);
		}
//		echo "check_email_contents - ID: $ID<br>";
		
		$res_cnts = $db->execute($stmt_cnts, array($ID));
		
		if($row_cnts = $db->fetch_array($stmt_cnts)) {
			return true;
		}
		
		return false;
	}
/*	
	function functionUpdateRow_email(wi400List $wi400List, $request) {
		// Aggiungo Messaggi
		$errorListMessages= array();
	
		$subfile_name = $wi400List->getSubfile();
		$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile_name);
	
		$key = $request['LIST_KEY'];
	
		$keyArray = explode("|", $key);
	
		$nrel = $keyArray[0];
	
		$errorListMessages[$key][] = array("error", "NREL: $nrel", "RLAASAS");
	
		$row = $wi400List->getCurrentRow();
	
		$row2 = array();
	
		// Tengo solo i campi di input che ho modificato
		foreach($wi400List->getColumnsOrder() as $columnKey) {
			$wi400Column = $wi400List->getCol($columnKey);
	
			if(!is_object($wi400Column)) {
				developer_debug("Errore reprimento colonna: non esiste la colonna '$columnKey' nella lista '".$_REQUEST['IDLIST']."'");
				continue;
			}
	
			if($wi400Column->getInput() != null){
				$inputField = $wi400Column->getInput();
	
				$inputType = $inputField->getType();
	
				$idKey = $inputField->getId();
	
				$idKeyArray = explode("-", $idKey);
	
				$id = $idKeyArray[2];
	
				if(isset($row[$id])) {
					$row2[$id] = $row[$id];
				}
					
//				$errorListMessages[$key][] = array("error", "ID: $id - VAL OLD: ".$row[$id]." - VAL NEW: ".$row2[$id], "RLAASAS");
			}
		}
	
//		$errorListMessages[$key][] = array("error", "UPDATE ROW", "RLAASAS");
	
		// Fine ciclo sui campi di input
		$subfile->updateRecord($nrel, $row2);
	
		$row = $subfile->getRecordById($nrel);
	
		$wi400List->setCurrentRow($row);
	
		$wi400List->setErrorMessages($errorListMessages);
	
		return $wi400List;
	}
*//*
	function prova() {
		$stringa="<a style='cursor:pointer'onClick='doSubmit(\"OP_RESI_VUOTI_GESTIONE\",\"STAMPA&g=STAMPA_DOC_COLLEGATI&ID_MOVIMENTO=90877&COD_CAUSALE=0811&DATA_MOVIMENTO=201908-22\")'>90877</a>";
		
//		if (isHtml($stringa)) {
//			$stringa = strip_tags($stringa);
//		}	
		
		return $stringa;
	}*/