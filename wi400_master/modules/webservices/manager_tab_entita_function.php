<?php
	
	function wsdlTool($id_campo) {
		global $actionContext;
		$tool = new wi400CustomTool();
		$tool->setScript("checkWsdl('$id_campo')");
		$tool->setIco("themes/common/images/gear.png");
		$tool->setStyle("width: 20px; height: 20px;");
		
		return $tool;
	}
	
	function soapActionTool($id_campo) {
		global $actionContext;
		$tool = new wi400CustomTool($actionContext->getAction(), "LIST_SOAP_ACTION");
		//$tool->setScript("listSoapAction('$id_campo')");
		$tool->setIco("themes/common/images/table-select-row.png");
		$tool->addJsParameter($id_campo);
		$tool->setReturnParameter(true);
		
		return $tool;
	}

	function get_last_call($entita, $segmento = "") {
		global $db;
		$sql = "select max(logrcx) logrcx from zwebslog where logent='$entita'";
		if($segmento) {
			$sql .= " and logseg='$segmento'";
		}
		
		$res = $db->singleQuery($sql);
		$row = $db->fetch_array($res);
		
		return $row['LOGRCX'];
	}
	
	function create_detail_parametri($actionDetail, $tabella, $parametri, $modifica, $entita, $segmento='') {
		global $db;
		
		//$col_param = $db->columns($tabella);
		$valori = array();
		if($modifica) {
			$sql = "SELECT * FROM $tabella WHERE ASEENT='$entita'";
			if($segmento) {
				$sql .= " and ASECOD='$segmento'";
			}else {
				$sql .= " and ASECOD=''";
			}
			//echo $sql."<br/>";
			
			$rs = $db->query($sql);
			while($row = $db->fetch_array($rs)) {
				$valori[$row['ASEPRM']] = $row['ASEVAL'];
			}
		}
		
		foreach($parametri as $nome => $opzioni) {
			$myField = new wi400InputText($nome);
			$myField->setLabel($nome);
			if(isset($opzioni['SIZE'])) {
				$myField->setSize($opzioni['SIZE']);
			}
			if(isset($opzioni['TOOL'])) {
				$myField->addCustomTool($opzioni['TOOL']);
			}
			if(isset($valori[$nome]))
				$myField->setValue($valori[$nome]);
			$actionDetail->addField($myField, "scheda_2");
		}
	}
	
	function save_parametri($parametri, $modifica, $entita, $segmento='') {
		global $db, $messageContext;
		
		if($modifica) {
			$sql = "DELETE FROM FWSDPARM WHERE ASEENT='$entita' AND ASECOD='$segmento'";
			$rs = $db->query($sql);
		}
		
		$file = "FWSDPARM";
		$fields = getDs($file);
		$fields['ASEENT'] = $entita;
		$fields['ASECOD'] = $segmento;
		//showArray($fields);
			
		$stmt_ins_param = $db->prepare("INSERT", $file, null, array_keys($fields));
			
		foreach ($parametri as $nome => $opzioni) {
			if(isset($_REQUEST[$nome])) {
				$fields['ASEPRM'] = $nome;
				$fields['ASEVAL'] = $_REQUEST[$nome];
				$res = $db->execute($stmt_ins_param, $fields);
				if(!$res) $messageContext->addMessage("ERROR","Errore salvataggio parametro ".$nome);
			}
		}
	}
	
	function functionValidationRowParametriIO(wi400List $wi400List, $request) {
		global $db;

		$key = $request['LIST_KEY'];
		
		$errorListMessages= array();
		$list_key = explode("|", $key);
		$nrel = $list_key[0];
		$subfile_name = $wi400List->getSubfile();
		$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile_name);
		$subfile_table = $subfile->getTable();
		
		$row = $wi400List->getCurrentRow();
		
		$sql = "SELECT ASENAM FROM $subfile_table WHERE ASENAM='{$row['ASENAM']}' AND NREL<>'$nrel' AND IS_MODIFY='X'";
		$rs = $db->singleQuery($sql);
		if($res = $db->fetch_array($rs)) {
			$errorListMessages[$key][] = array("error", "Parametro duplicato", "ASENAM");
		}
		
		$wi400List->setErrorMessages($errorListMessages);
		
		return $wi400List;
	}
	
	function functionUpdateRowParametriIO(wi400List $wi400List, $request) {
		
		$key = $request['LIST_KEY'];
		
		$list_key = explode("|", $key);
		$nrel = $list_key[0];
		
		$subfile_name = $wi400List->getSubfile();
		$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $subfile_name);
		
		if(isset($request['COLONNA']) && $request['COLONNA']=='ELIMINA_RIGA') {
			$subfile->clearRecord($nrel);
			$subfile->updateRecord($nrel, array("IS_MODIFY" => "", 'ASESEQ'=> null));
			$row = $subfile->getRecordById($nrel);
			$wi400List->setErrorMessages(array($key => array()));
		}else {
			$row = $wi400List->getCurrentRow();
			$row['ASESEQ'] = $row['ASESEQ'] == "" ? null : $row['ASESEQ']; 
			$row['IS_MODIFY'] = 'X';
		}

		$subfile->updateRecord($nrel, $row);
		
		$row = $subfile->getRecordById($nrel);
		$wi400List->setCurrentRow($row);
		
		return $wi400List;
	}
	