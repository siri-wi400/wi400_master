<?php

	function create_subfile($wi400Subfile, $idList=null, $wi400List=null, $totalArray=array(), $extraRows=array()) {
		global $db, $routine_path;
		// ***************************************************
		// GESTIONE SUBFILE
		// ***************************************************
		
		// Inizializzazione subfile personale
		$modulo=$wi400Subfile->getModulo();
		// @todo intervenire con debug 
		if ($modulo!='') {
			require_once p13n('modules/'.$modulo.'/subfile/'.$wi400Subfile->getConfigFileName().".cls.php");
		} else {
			require_once $routine_path.'/classi/subfile/'.$wi400Subfile->getConfigFileName().".cls.php";
		}
		$subfileClassName = $wi400Subfile->getConfigFileName();
		$customSubfile = new $subfileClassName($wi400Subfile->getParameters());
		$customSubfile->setFullTableName($wi400Subfile->getFullTableName());
			
		// creo array colonne se non presente
		$customSubfile->init($wi400Subfile->getParameters());
			
		// inizializzo subfile se non esistente
		$wi400Subfile->inz($customSubfile->getCols());
		
		// Azzeramento totali del subfile
		$newTotalArray = array();
		if(!empty($totalArray)) {
			foreach (array_keys($totalArray) as $totalKey){
				if (strpos($totalArray[$totalKey], "EVAL:")===0){
					$newTotalArray[$totalKey] = $totalArray[$totalKey];
				}else{
					$newTotalArray[$totalKey] = 0;
				}
			}
		}
		$wi400Subfile->setTotals($newTotalArray);
			
		// Azzeramento righe extra
		$wi400Subfile->setExtraRows(array());
			
		// operazioni iniziali
		$customSubfile->start($wi400Subfile);
			
		// Eseguo query
		if ($wi400Subfile->getSql()!='*AUTOBODY' && $wi400Subfile->getSql()!="") {
			$result = $db->query($wi400Subfile->getSql(), False , -1);
		
			while($row = $db->fetch_array($result)){
				$rowSubFile = $customSubfile->body($row, $wi400Subfile->getParameters());
				if($rowSubFile!==false){
					$wi400Subfile->write($rowSubFile);
				}
			}
		} else if ($wi400Subfile->getSql()=='*AUTOBODY') {
		
			while($rowSubFile = $customSubfile->body(array(), $wi400Subfile->getParameters())){
				$wi400Subfile->write($rowSubFile);
			}
		}
			
		$wi400Subfile->finalize();
			
		// Operazioni finali
		$customSubfile->end($wi400Subfile);
			
		// Aggiunta righe extra
		if(!empty($extraRows)) {
			foreach ($extraRows as $extraDesc => $extraRow){
				$wi400Subfile->addExtraRow($extraDesc, $customSubfile->extraRow($extraDesc, $wi400Subfile->getParameters()));
				$wi400Subfile->addExtraRowExport($extraDesc, $customSubfile->extraRowExport($extraDesc, $wi400Subfile->getParameters()));
			}
		}
			
//		$_SESSION[$idList."_TOTAL_ROWS"] = $wi400Subfile->getTotalRecord();
			
//		wi400Session::save(wi400Session::$_TYPE_SUBFILE,  $wi400List->getSubfile(), $wi400Subfile);
		//$_SESSION[$idList."_TOTAL_ROWS_ALL"] = $wi400Subfile->getTotalRecord();
//		$totalFromSubfile=True;
		// ***************************************************
	}