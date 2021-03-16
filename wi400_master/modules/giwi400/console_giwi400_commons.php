<?php	
	require_once 'config.php';
	require_once 'function/giwi_format.php';

	$tabLibrerie = 'ZOT5FILL';
	$tabForm = 'ZOT5RECL';
	$tabCampi = 'ZOT5FLDL';
	$tabClientAttributi = 'ZOT5FLDF';
	$tabParametriClient = 'ZOT5FLDFI';
	$tabDetailAttributi = 'ZOT5RECF';

	$file_path = 'ui.xml';
	
	$end_job_desc = array(
		'ENDPGM' => 'Programma terminato',
		'ENDJOB' => 'Lavoro terminato'
	);
	
	$param_decorator = array(
		'AJAX',
		'COMPLETE',
		'COMPLETE_MIN',
		'COMPLETE_MAX_RESULT',
		'COLUMN',
		'KEY_FIELD_NAME',
		'LU_SELECT',
		'LU_FIELDS',
		'TABLE_NAME',
		'FILTER_SQL',
		'WHERE_COND',
		'GROUP_BY',
		'UNION_ALL',
		'SELECTION_KEY',
		'QUERY_MASK',
		'RETURN_COLUMN',
	);
	
	$param_lookup = array(
		'KEY_FIELD_NAME',
		'DESCRIZIONE', //uguale a CAMPO
		'CAMPO', //uguale a DESCRIZIONE
		'LU_FIELDS',
		'LU_SELECT',
		'LU_AS_TITLES',
		'LU_AS_TYPES',
		'LU_AS_ALIGN',
		'LU_FROM',
		'LU_FROM_BASE64',
		'LU_WHERE',
		'FILTER_SQL',
		'LU_ORDER',
		'LU_GROUP',
		'GROUP_BY',
		'UNION_ALL',
		'CALC_TOT_ROWS',
		'LU_DES_CAMPO',
		'LU_KEY',
		'LU_FILTER',
		'LU_FILTER_SQL_KEY',
		'SPECIAL_VALUE',
		'ONCHANGE'
	);
	
	$var_condition = array('INH1','INH3','INH2','INH5','INH4','INH7','INH9','INH8','INKB','INKA','INH6','INKD','INKF','INKE','INKH','INKG','INKJ','INKL','INKK','INKN','INKM','INKI','INKQ','INKS','INKR','INKU','INKT','INKW','INKY','INKX','INL1','INL3','INL2','INLR','INKV','INKP','INKC','INL5','INL7','INL6','INMR','INL9','INOB','INOA','INL8','INOD','INOF','INOE','INOV','INU1','INRT','INOG','INU3','INU5','INU4','INU8','INU7','INU6','INU2','INOC','IN03','IN02','IN05','IN07','IN06','IN04','IN1P','IN09','IN11','IN10','IN13','IN15','IN14','IN12','IN08','IN01','INL4','IN17','IN19','IN18','IN21','IN23','IN22','IN20','IN25','IN27','IN26','IN29','IN31','IN30','IN28','IN24','IN33','IN35','IN34','IN37','IN39','IN38','IN36','IN41','IN43','IN42','IN45','IN47','IN46','IN44','IN40','IN49','IN51','IN50','IN53','IN55','IN54','IN52','IN57','IN59','IN58','IN61','IN63','IN62','IN60','IN56','IN48','IN32','IN65','IN67','IN66','IN69','IN71','IN70','IN68','IN73','IN75','IN74','IN77','IN79','IN78','IN76','IN72','IN81','IN83','IN82','IN85','IN87','IN86','IN84','IN89','IN91','IN90','IN93','IN95','IN94','IN97','IN99','IN98','IN96','IN92','IN88','IN80','IN64','IN16');
	
	function getUseClass() {
		global $useClass;
		
		return $useClass;
	}
	/*
	 * checkIdGiwi: controlla se l'ID è ancora attivo e nella condizione di ricevere chiamate
	 */
	
	function checkIdGiwi($id, $nome_programma="") {
		global $db;
		$status = False;
		// Controllo se il lavoro esiste ed è attivo per prima cosa
		$sql = "SELECT FUNCTION_TYPE, FUNCTION,JOB_STATUS                  
			 FROM TABLE(QSYS2.ACTIVE_JOB_INFO( JOB_NAME_FILTER =>      
			'GIWI400   ' )) X                                          
			where job_name like '$id%'";
		$result = $db->query($sql);
		if ($result) {
			$row = $db->fetch_array($result);
			if ($row) {
				$status = True;
				// In modlalità ricezione CODA
				if ($row['FUNCTION']=="ZGEWIMAIN" && $row['JOB_STATUS']=="DEQW") {
					//$status = True;			
				}
				// Altrimenti se sto controllando il mio ID ..
			}
		}
		return $status;
	}
	function getGiwi400Id($nome_programma="") {
		global $connzend;
		
		// Ricerco se per caso c'è un lavoro già attivo con il programma che voglio utilizzare
		
		$zgewiinit = new wi400Routine('ZGEWIINIT', $connzend);
		$zgewiinit->load_description();
		$zgewiinit->prepare(True);
		$zgewiinit->set('ID_UNIVOCO', "");
		$zgewiinit->set('UTENTE', $_SESSION['user']);
		$zgewiinit->set('ERRORE',"");
		$zgewiinit->set('DESERR',"");
			
		$zgewiinit->call();

		$id = $zgewiinit->get("ID_UNIVOCO");
		$file = $zgewiinit->get('ID_FILE');
		
		if( $zgewiinit->get('ERRORE') != '0') {
			developer_debug('Errore ZGEWIINIT: '.$zgewiinit->get('DESERR'));
		}
		
		//echo  "ritorno l'id $id<br>";
		// Metto in sessione l'ID per il nome programma
		$_SESSION['GIWI400_MULTI_PGM'][$nome_programma]=$id;
		return array($id, $file);
	}
	
	function readCoda($id) {
		global $db;
		global $dq_lib, $dq_name, $dq_key, $dq_data, $dq_oper, $dq_time, $output;
		$method = "UDFT";
		static $stmt_read_coda;
		$dq_lib = "GIWI400";
		$dq_name="UIWI400RTC";
		
		if ($method=="DB") {
			
			if(!isset($stmt_read_coda)) {
				$sql =  "call GIWI400/ZDT_DQUWT(?,?,?,?,?,?,?)";
				//$stmt_read_coda = $db->inzCallPGM();
				$stmt_read_coda = $db->prepareStatement($sql, 0, False);
			}
			
			if($id) {

				$dq_key="C".$id; //S => per inviare all'as400; C => per ricevere da AS
				$dq_data="PRIMA USER-SPACE DI TEST";
				$dq_oper="R"; //'R' per leggere -- 'W' per scrittura
				$dq_time=60;
				$output = "";
				
				db2_bind_param($stmt_read_coda, 1, "dq_lib", DB2_PARAM_IN );
				db2_bind_param($stmt_read_coda, 2, "dq_name", DB2_PARAM_IN );
				db2_bind_param($stmt_read_coda, 3, "dq_key", DB2_PARAM_IN );
				db2_bind_param($stmt_read_coda, 4, "dq_data", DB2_PARAM_IN );
				db2_bind_param($stmt_read_coda, 5, "dq_oper", DB2_PARAM_IN );
				db2_bind_param($stmt_read_coda, 6, "dq_time", DB2_PARAM_IN );
				db2_bind_param($stmt_read_coda, 7, "output", DB2_PARAM_OUT);
					
				$result = db2_execute($stmt_read_coda);
				//echo "<br>Risultato:".$output.'<br/>';
			}else {
				developer_debug('readCoda id nullo');
			}
		} else {
			// Se metodo è UDFT
			$dq_key=str_pad("C".$id, 20, " ");
			$dq_time=60;
			$sql = "SELECT CAST(MESSAGE_DATA AS CHAR(200)) AS OUTPUT FROM TABLE(QSYS2.RECEIVE_DATA_QUEUE(
					DATA_QUEUE => '$dq_name',
					DATA_QUEUE_LIBRARY => '$dq_lib',
					KEY_DATA => '$dq_key',
					KEY_ORDER => 'EQ',
					REMOVE => 'YES',
					WAIT_TIME => $dq_time))";
			//die($sql);
			$result = $db->query($sql);
			if ($result) {
				$row = $db->fetch_array($result);
				$output = $row['OUTPUT'];
			}
		}
		return $output;
	}
	
	function writeCoda($progressivo, $id, $operazione, $nome_programma = '') {
		global $db;
		global $dq_lib, $dq_name, $dq_key, $dq_data, $dq_oper, $output;
		$method = "UDFT";
		$dq_lib="GIWI400";
		$dq_name="UIWI400RTC";
		$dq_data = str_pad($progressivo, 30, ' ', STR_PAD_RIGHT).str_pad($id, 10, ' ', STR_PAD_RIGHT).str_pad($operazione, 10, ' ', STR_PAD_RIGHT).str_pad($nome_programma, 10, ' ', STR_PAD_RIGHT);
		
		if ($method =="DB") {
			$sql =  "call GIWI400/ZDT_DQUW(?,?,?,?,?,?)";
			//$dq_lib="GIWI400";
			//$dq_name="UIWI400RTC";
			$dq_key = "S".$id; //S => per inviare all'as400; C => per ricevere da AS
			//$dq_data = str_pad($progressivo, 30, ' ', STR_PAD_LEFT).str_pad($id, 10, ' ', STR_PAD_LEFT).str_pad($operazione, 10, ' ', STR_PAD_LEFT);
			//$dq_data = str_pad($progressivo, 30, ' ', STR_PAD_RIGHT).str_pad($id, 10, ' ', STR_PAD_RIGHT).str_pad($operazione, 10, ' ', STR_PAD_RIGHT).str_pad($nome_programma, 10, ' ', STR_PAD_RIGHT);
			//showArray($dq_data);
			$dq_oper="W"; //'R' per leggere -- 'W' per scrittura
			$output = '';
			$stmt = $db->prepareStatement($sql, 0, False);
			db2_bind_param($stmt, 1, "dq_lib", DB2_PARAM_IN );
			db2_bind_param($stmt, 2, "dq_name", DB2_PARAM_IN );
			db2_bind_param($stmt, 3, "dq_key", DB2_PARAM_IN );
			db2_bind_param($stmt, 4, "dq_data", DB2_PARAM_IN );
			db2_bind_param($stmt, 5, "dq_oper", DB2_PARAM_IN );
			db2_bind_param($stmt, 6, "output", DB2_PARAM_OUT);
			
			//$rs = $db->execute($stmt, array($dq_lib, $dq_name, $dq_key, $dq_data, $dq_oper, $output));
			//$row = $db->fetch_array($stmt);
			$result = db2_execute($stmt);
		} else {
			$dq_key=str_pad("S".$id, 20, " ");
			$sql = "CALL QSYS2.SEND_DATA_QUEUE(MESSAGE_DATA => '$dq_data',
					DATA_QUEUE => '$dq_name',
					DATA_QUEUE_LIBRARY => '$dq_lib',
					KEY_DATA => '$dq_key')";
			//die($sql);
			$result = $db->query($sql);
			if ($result) {
				$output = "OK";
			} else {
				$output = "KO";
			}
		}
		
		return $output;
	}
	
	function getDatiOutput($output) {
		
		$dati = array();
		
		if($output == '*TIMEOUT') {
			$dati['TIMEOUT'] = true;
		}else {
			$dati['OPERAZIONE'] = trim(substr($output, 40, 10));
			$dati['FILE_PATH'] = substr($output, 50, 150);
			$dati['TIMEOUT'] = false;
		}

		return $dati;
	}
	
	function getDatiClientAttributi($legame, $form, $field) {
		global $db, $tabClientAttributi;
		
		$where = array(
			"OT5KEY='$legame'",
			"OT5FMT='$form'",
			"OT5FLD='$field'"
		);
		$sql = "SELECT * FROM $tabClientAttributi WHERE ".implode(' and ', $where);
		$rs = $db->singleQuery($sql);
		$row = $db->fetch_array($rs);
		
		return $row;
	}
	
	function getParametriClient($legame, $form, $field, $type) {
		global $db, $tabParametriClient;
		
		$dati = array();
		
		$where = array(
			"OT5KEY='$legame'",
			"OT5FMT='$form'",
			"OT5FLD='$field'",
			"OT5FLD1='$type'"
		);
		$sql = "SELECT * FROM $tabParametriClient WHERE ".implode(' and ', $where);
		$rs = $db->query($sql);
		while($row = $db->fetch_array($rs)) {
			$dati[] = $row;
		}
		
		return $dati;
	}
	
	function deleteClientAttributi($legame, $form, $field) {
		global $db, $tabClientAttributi;
		
		$where = array(
			"OT5KEY" => $legame,
			"OT5FMT" => $form,
			"OT5FLD" => $field
		);
		
		$stmt_delete_attributi = $db->prepare('DELETE', $tabClientAttributi, array_keys($where), null);
		$rs = $db->execute($stmt_delete_attributi, $where);
		
		return $rs;
	}
	
	function insertClientAttributi($legame, $form, $field, $dati) {
		global $db, $tabClientAttributi;
		
		$fields = getDs($tabClientAttributi);
		
		$stmt_insert_attributi = $db->prepare('INSERT', $tabClientAttributi, null, array_keys($fields));
		
		$fields['OT5KEY'] = $legame;
		$fields['OT5FMT'] = $form;
		$fields['OT5FLD'] = $field;
		$fields['OT5DEC'] = $dati['OT5DEC'];
		$fields['OT5LOK'] = $dati['OT5LOK'];
		$fields['OT5ABI'] = $dati['OT5ABI'];
		$fields['OT5OTH'] = $dati['OT5OTH'];
		$fields['OT5STY'] = $dati['OT5STY'];
		$fields['OT5VIS'] = $dati['OT5VIS'];
		$fields['OT5TAB'] = +$dati['OT5TAB'];
		
		$hla_src = get_switch_check_value($dati['OT5HLA']);
		
		$fields['OT5HLA'] = $hla_src;
		
		$fields['OT5FOP'] = $dati['OT5FOP'];
		$fields['OT5FOR'] = $dati['OT5FOR'];
		$fields['OT5SEQ'] = doubleViewToModel($dati['OT5SEQ']);
		$fields['OT5TXT'] = $dati['OT5TXT'];
		
		$rs = $db->execute($stmt_insert_attributi, $fields);
		
		return $rs;
	}
	
	function deleteDetailAttributi($legame, $form) {
		global $db, $tabDetailAttributi;
	
		$where = array(
			"OT5KEY" => $legame,
			"OT5FMT" => $form
		);
	
		$stmt_delete_attributi = $db->prepare('DELETE', $tabDetailAttributi, array_keys($where), null);
		$rs = $db->execute($stmt_delete_attributi, $where);
	
		return $rs;
	}
	
	function deleteSessionManager($file, $libreria) {

		$id = "GIWI_DISPLAY_".$file."_".$libreria;
		
		//echo wi400Session::getFileName(wi400Session::$_TYPE_GENERIC, $id);
		$rs = wi400Session::delete(wi400Session::$_TYPE_GENERIC, $id);
		
		return $rs;
	}
	
	function insertDetailAttributi($legame, $form, $dati) {
		global $db, $tabDetailAttributi;
	
		$fields = getDs($tabDetailAttributi);
	
		$stmt_insert_attributi = $db->prepare('INSERT', $tabDetailAttributi, null, array_keys($fields));
	
		$fields['OT5KEY'] = $legame;
		$fields['OT5FMT'] = $form;
		$fields['OT5COL'] = $dati['OT5COL'];
		$fields['OT5TXT'] = $dati['OT5TXT'] ? 'S' : 'N';
		$fields['OT5RDE'] = $dati['OT5RDE'];
		
		if($dati['OT5TAB']) {
			foreach($dati['OT5TAB'] as $i => $label_tab) {
				$dati['OT5TAB'][$i] = str_replace("_", " ", $label_tab);
			}
			$fields['OT5TAB'] = implode('|', $dati['OT5TAB']);
		}
		
		$rs = $db->execute($stmt_insert_attributi, $fields);
	
		return $rs;
	}
	
	function updateRowParametri(wi400List $wi400List, $request) {
		global $db;
		
		$key = $request['LIST_KEY'];
		$list_key = explode("|", $key);
		$nrel = $list_key[0];
		
		$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, 'PARAMETRI_CLIENT');
		
		$datiRiga = $wi400List->getCurrentRow();
		
		//$firephp->fb($datiRiga);
			
		$subfile->updateRecord($nrel, $datiRiga);

		$wi400List->setCurrentRow($datiRiga);
		
		return $wi400List;
	}
	
	function deleteParametriClient($legame, $form, $field, $type) {
		global $db, $tabParametriClient;
		
		$where = array(
			"OT5KEY" => $legame,
			"OT5FMT" => $form,
			"OT5FLD" => $field,
			"OT5FLD1" => $type
		);
		
		$stmt_delete_parametri = $db->prepare('DELETE', $tabParametriClient, array_keys($where), null);
		$rs = $db->execute($stmt_delete_parametri, $where);
		
		return $rs;
	}
	
	function saveParametriClient($legame, $form, $field, $type) {
		global $db, $tabParametriClient;
		
		$errors = array();
		
		$rs = deleteParametriClient($legame, $form, $field, $type);
		if($rs) {
			$fields = getDs($tabParametriClient);
			
			$fields['OT5KEY'] = $legame;
			$fields['OT5FMT'] = $form;
			$fields['OT5FLD'] = $field;
			$fields['OT5FLD1'] = $type;
			
			//showArray($fields);
			
			$stmt_insert_parametri = $db->prepare('INSERT', $tabParametriClient, null, array_keys($fields));
			
			$subfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, 'PARAMETRI_CLIENT');
			
			$sql = "SELECT * FROM ".$subfile->getTable()." WHERE OT5PRM<>'' order by NREL";
			//showArray($sql);
			$rs = $db->query($sql);
			while($dati = $db->fetch_array($rs)) {
				
				$fields['OT5SEQ'] = $dati['NREL'];
				$fields['OT5PRM'] = $dati['OT5PRM'];
				$fields['OT5VAL'] = $dati['OT5VAL'];
				$fields['OT5STA'] = $dati['OT5STA'];
			
				$res = $db->execute($stmt_insert_parametri, $fields);
				if(!$res) {
					$errors[] = 'Errore inserimento '.$dati['OT5PRM'];
				}
			}
		}else {
			$errors[] = 'Errore delete parametri client';
		}
		
		return $errors;
	}
	
	function functionFormattazioneRiga(wi400List $wi400List, $row, $wi400Column, $tipo, $params=array()) {
		
		/*global $moduli_path;
		static $form, $manager;
		
		require_once $moduli_path.'/giwi400/classi/giwi400Display.cls.php';
		$colonna = $wi400Column->getKey();
		
		if (!isset($form)) {
			$where = $wi400List->getWhere();
			$pos = strpos($where, "S_GIWI_FRM=");
			$pos2 = strpos($where, "'", $pos+12);
			$form = substr($where, $pos+12, $pos2-($pos+12));
			$manager = new giwi400Manager($_SESSION['GIWI_CURRENT_FILE'], $_SESSION['GIWI_CURRENT_FLIB']);
		}
		//error_log($colonna."__".$tipo);
		
		if($tipo=="readonly") {
			//per abilitare il passaggio qui scommentare l'istruzione a riga 733 in giwi400.cls.php
			//return true se abilitare il readonly
		}else if($tipo=="style") {
			//return "wi400_grid_hidden";
			//echo $wi400List->getWhere();
			//echo $form;
			//showArray($row);
			//echo $_SESSION['GIWI_CURRENT_FILE'].":";
			//echo $_SESSION['GIWI_CURRENT_FLIB'];
			//echo $_SESSION['GIWI400_CURRENT_FORM'];
		}*/
	}
	
	function functionFormattazioneInput(wi400List $wi400List, $inputField, $row) {
		global $db, $moduli_path;
		static $form, $manager;
		
		require_once $moduli_path.'/giwi400/classi/giwi400Display.cls.php';
		if (!isset($form)) {
			$where = $wi400List->getWhere();
			$pos = strpos($where, "S_GIWI_FRM=");
			$pos2 = strpos($where, "'", $pos+12);
			$form = substr($where, $pos+12, $pos2-($pos+12));
			$manager = new giwi400Manager($_SESSION['GIWI_CURRENT_FILE'], $_SESSION['GIWI_CURRENT_FLIB']);
		}
		$arr_id = explode("-", $inputField->getId());
		$colonna = $arr_id[2];
		//echo "<br>ci sono";
		$obj= $manager->getDisplay()->getForm($form)->getField($colonna);
		$indicatori = $manager->INDStringToArray($row['S_GIWI_IND']);
		//showArray($indicatori);
		$inputField2 = $manager->evaluateAttribute($obj, $inputField, $row, $indicatori,"LIST");
		$inputField = $inputField2;
		//$inputField->setReadOnly(True);
		//$inputField->setStyleClass("wi400_grid_hidden inputtext");
		//showArray($row);
		//error_log('input_'.$colonna);
		
		return $inputField;
	}
	
	function functionValidationValori(wi400List $wi400List, $request) {
		global $db, $firephp;
		
		$errorListMessages= array();
		
		$datiRiga = $wi400List->getCurrentRow();
		$callback = $wi400List->getRuntimeField("callBack");
		
		//$firephp->fb($callback);
		if($callback != "validation") {
			
		}else {
			
		}
		
		$wi400List->setErrorMessages($errorListMessages);
		$wi400List->setCurrentRow($datiRiga);
		
		return $wi400List;
	}
	
	//Gestione bottoni campio pagina
	function functionButtonChangePage(wi400List $wi400List, $request) {
		global $temaDir;
		 
		$idList = $wi400List->getIdList();
		$nextPage = '';
		$lastPage = '';
		$currPage = 1;
?>
		<table class="wi400-grid-menu">
			<tr>
				<td class="wi400-grid-menu-page">
					<div id="<?= $idList ?>_PAGINATION_LABEL" style="cursor: pointer;"><?=$currPage?> / *</div>
				</td>
				<td><input type="image" src="<?=  $temaDir ?>images/grid/next<?= $nextPage ?>.gif" alt="<? echo _t("SUCCESSIVA")?>"
					id="<?= $idList ?>_NEXT_BUTTON"
					onClick="submitPressButton('ROLLUP', '25', false)"></td>
			</tr>
		</table>
<?php 
    	
    	return $wi400List;
    }
	
	//Auto update di lista
	function functionUpdateValori(wi400List $wi400List, $request) {
		global $messageContext, $firephp;
	
		$row = $wi400List->getCurrentRow();
		//$firephp->fb($request);
		//return $wi400List;
		//error_log($_REQUEST['COLONNA']."__updateList giwi400");
		
		$_SESSION['LAST_FOCUSED_FIELD'] = $request['IDLIST']."-".$request['ROW_COUNTER']."-".$request['COLONNA'];
		//error_log('ultimo focus '.$_SESSION['LAST_FOCUSED_FIELD']);

		$tabella = $wi400List->getFrom();
		$where = $wi400List->getWhere();
		// TEST
		//$firephp->fb($where);
		
		$list_key = explode("|", $request['LIST_KEY']);
		$nrel = $list_key[0];
		$rs = updateValoriSubfile($nrel, $tabella, $where, $row);
		if(!$rs) {
			error_log("Errore udapteValoriSubfile console_giwi400.");
		}
		
		return $wi400List;
	}
	
	function updateValoriSubfile($num_riga, $tabella, $where, $row=Null) {
		global $db, $firephp, $routine_path;
		
		static $update_stmt;
		require_once 'modules/giwi400/classi/giwi400Display.cls.php';
		require_once $routine_path."/generali/conversion.php";
		// Aggiorno i dati del tracciato record subfile
		if (isset($row)) {
			// Reperisco i dati del subfile
			$sql1 = "SELECT S_GIWI_FRM, S_GIWI_FIL, S_GIWI_LIB, S_GIWI_REC FROM $tabella WHERE S_GIWI_RRN='$num_riga' and ".$where;
			$rs1 = $db->query($sql1);
			$row1 = $db->fetch_array($rs1);
			// Vado a sostituire i valori della $row;
			$sql = '';
			$key = $row1['S_GIWI_FIL']."_".$row1['S_GIWI_LIB'];
			// Utilizzo il manager
			$manager = new giwi400Manager($row1['S_GIWI_FIL'], $row1['S_GIWI_LIB']);
			//$sql1 = "SELECT * FROM ZOT5FLDL WHERE OT5KEY='".$key."' AND OT5FMT='".$row1['S_GIWI_FRM']."'";
			//$result1 = $db->query($sql1);
			//$sepa="";
			//$field = array('S_GIWI_RRN');
			//$start = 1;
			$original = $row1['S_GIWI_REC'];
			//error_log("PRIMA:".$original);
			//$fields = $manager->getDisplay()->getForm($row1['S_GIWI_FRM'])->getFields();
			$fields = $manager->getDisplay()->getForm($row1['S_GIWI_FRM'])->getFieldsByType("B");
			//echo $original."<---->";
			//while ($row1 = $db->fetch_array($result1)) {
			foreach ($fields as $key => $obj) {
				// Attacco il campo
				//if (substr($row1['OT5FLD'],0,1)!='*') {
				if ($obj->getUse()=="B" && substr($key,0,1)!='*') {
					$mystring = "";
					if (substr($row[$key],0,2)=="Hx") {
					    continue;
					}
					//error_log($key." Value:".$row[$key]);
					if (array_key_exists($key, $row)) {
						// Sostituisco il valore costruendo la stringa
						if ($obj->getDigits()=="0") {
							$mystring = str_pad($row[$key], $obj->getLen(), " ");
						}
						if ($obj->getDigits()!="0") {
							//error_log("DIGITS:".$key);
							$mystring = str_replace(",", ".", $row[$key]);
							if ($mystring=="") {
								$mystring = 0;
							}
							//error_log($key." PRIM= ".$mystring);
							//$mystring = doubleViewToModel($row[$key]);
							//$mystring = $row[$key];
							$mystring = string2Zoned($mystring, $obj->getDigits(), $obj->getDecimal());
							//error_log($key." DOPO= ".$mystring." LUNGHEZZA ".$obj->getLen());
						}
						//echo "<br>$mystring";
						$original = mb_substr_replace($original, $mystring, $obj->getInputBuffer()-1, $obj->getLen());
					} else {
						error_log("GIWI WRITE SUBFILE NOT FOUND:".$key);
					}
					//error_log($key." Value2:".$row[$key]. " INP ".$obj->getInputBuffer(). " DIGITS ".$obj->getDigits());
					//$start += $obj->getLen();
				}
			}	
		}
		//error_log("DOPOA:".$original);
		// Prepare statement, potrebbero essere apici e doppi apici
		$sql = "UPDATE $tabella SET S_GIWI_MOD='X', S_GIWI_REC=? WHERE S_GIWI_RRN='$num_riga' and ".$where;
		$update_stmt = $db->singlePrepare($sql);
		$result = $db->execute($update_stmt, array($original));
		
		return $result;
	}
	
	//DEPRECATO è stato cambiato l'id dei detail
	/*function getDetailValueParamValidation() {
		$dati = array();
		for($i=0; $i<5; $i++) {
			$idDetail = 'GIWI400_PARAM_'.$i;
			if(existDetail($idDetail)) {
				$param = wi400Detail::getDetailValues($idDetail);
			}else {
				break;
			}
			
			$dati = array_merge($dati, $param);
		}
		
		return $dati;
	}*/
	/**
	 * OBSOLETA NON USARE: Usare funzione dentro la classe
	 * @param unknown $libreria
	 * @param unknown $file
	 * @param unknown $form
	 * @param unknown $datiForm
	 * @return boolean
	 */
	function salvataggioFormSuDb($libreria, $file, $form, $datiForm) {
		global $db, $routine_path;
		
		require_once 'modules/giwi400/classi/giwi400Display.cls.php';
		require_once $routine_path."/generali/conversion.php";
		// Cerco la riga originale
		$tabella = "PHPTEMP/GIWI".$_SESSION['GIWI400_ID'];
		$sql1 = "SELECT S_GIWI_REC FROM $tabella WHERE S_GIWI_RRN=0 AND S_GIWI_LIB='$libreria' AND S_GIWI_FIL='$file' AND S_GIWI_FRM='$form' AND S_GIWI_TIP='F'";;
		$rs1 = $db->query($sql1);
		$row1 = $db->fetch_array($rs1);
		$originale= "";
		$fr=False;
		if ($row1) {
			$fr=True;
			$originale= $row1['S_GIWI_REC'];
		}		
		//error_log("ORIGINALE:".$originale);
		// Utilizzo il manager per ciclare sui campi
		//$_SESSION["DEBUG"]=True;
		//getMicroTimeStep("inizio", True);
		$manager = new giwi400Manager($file, $libreria);
		//getMicroTimeStep("fine", True);
		//$fields = $manager->getDisplay()->getForm($form)->getFields();
		$fields = $manager->getDisplay()->getForm($form)->getFieldsByType("B");
		//$start = 1;
		$mystring = "";
		//error_log($originale);
		// @todo reperisco i campi presenti sulla DS
		//$DSfields = getDSFields($file, $libreria, $form);
		foreach ($fields as $key => $obj) {
			// Attacco il campo
			//if (substr($row1['OT5FLD'],0,1)!='*') {
			//if ($obj->getUse()=="B" && substr($key,0,1)!='*' && in_array($key, $DSfields)) { //&& $obj->getInputBuffer() >"0") {
			if ($obj->getUse()=="B" && substr($key,0,1)!='*') { //&& $obj->getInputBuffer() >"0") {
				// Converto in *BLANK i dati esadecimali
				$mystring = "";	
				//error_log($key);
				if (array_key_exists($key, $datiForm)) {
					if (substr($datiForm[$key],0,2)=="Hx") {
						// GO next
						//$start += $obj->getLen();
						continue;
						$datiForm[$key]="";
					}
					if ($obj->getDigits()=="0") {
						//$mystring = str_replace("à", "a", $datiForm[$key]);
					    $mystring = $datiForm[$key];
					    $mystring = str_pad($mystring, $obj->getLen(), " ");
						
						//$mystring = utf8_encode($mystring);
					}
					if ($obj->getDigits()!="0") {
						//$mystring = string2Zoned($datiForm[$key], $obj->getDigits(), $obj->getDecimal());
						//$mystring = str_replace(",", ".", $datiForm[$key]);
						//if ($mystring=="") {
						//	$mystring = 0;
						//}
						$mystring = doubleViewToModel($datiForm[$key]);
						//if ($mystring<0) $mystring = $mystring*-1;
						$mystring = string2Zoned($mystring, $obj->getDigits(), $obj->getDecimal());
						//$mystring = utf8_encode($mystring);
					}
				} else {
					// Cosa faccio se non è presente ..
					if ($obj->getDigits()=="0") {
						$mystring .= str_pad("", $obj->getLen(), " ");
					}
					if ($obj->getDigits()!="0") {
						$mystring .= string2Zoned(0, $obj->getDigits(), $obj->getDecimal());
					}
				}
				$originale = mb_substr_replace($originale, $mystring, $obj->getInputBuffer()-1, $obj->getLen());
				//$start += $obj->getLen();
			}
		}
		//error_log("DATI:$originale");
		// Verifico se esiste la riga
		//$sql = "SELECT * FROM $tabella WHERE S_GIWI_RRN= 0 AND S_GIWI_LIB='$libreria' AND S_GIWI_FIL='$file' AND S_GIWI_FRM='$form' AND S_GIWI_TIP='F'";
		//$result = $db->singleQuery($sql);
		//if ($result) {
			//$row = $db->fetch_array($result);
				if ($fr) {
					$sql = "UPDATE $tabella SET S_GIWI_MOD='X', S_GIWI_REC=? WHERE S_GIWI_RRN=0 AND S_GIWI_LIB='$libreria' AND S_GIWI_FIL='$file' AND S_GIWI_FRM='$form' AND S_GIWI_TIP='F'";
					$update_stmt = $db->singlePrepare($sql);
					$result = $db->execute($update_stmt, array($originale));
				} else {
					$sql = "INSERT INTO $tabella VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
					$update_ins = $db->singlePrepare($sql);
					$result = $db->execute($update_ins, array($libreria, $file, $form, 0, $originale, "X", "", '', 'F'));
				} 
			//}
		return true;
	}
	function loadExtraData($libreria, $file, $form) {
		global $db, $routine_path;
		
		$dati = array();
		require_once 'modules/giwi400/classi/giwi400Display.cls.php';
		require_once $routine_path."/generali/conversion.php";
		// Cerco la riga originale
		$tabella = "PHPTEMP/GIWI".$_SESSION['GIWI400_ID'];
		$sql1 = "SELECT S_GIWI_FUN, S_GIWI_REC, HEX(S_GIWI_REC) STRHEX FROM $tabella WHERE S_GIWI_RRN=0 AND S_GIWI_LIB='$libreria' AND S_GIWI_FIL='$file' AND S_GIWI_FRM='$form' AND S_GIWI_TIP='E'";;
		$rs1 = $db->query($sql1);
		while ($row1 = $db->fetch_array($rs1)) {
			// Verifico che funzione di decodifica usare per estrarre i dati
			if ($row1['S_GIWI_FUN']=="SIPEATTR") {
				$split = str_split($row1['STRHEX'], 2);
				$prefix = 'AI';
				$ix = 1;
				for ($i=0 ; $i<297 ; $i++) {
					$key = $prefix."_".str_pad($ix, 2, "0", STR_PAD_LEFT);
					$dati[$key]='Hx"'.$split[$i].'"';
					if ($ix==99) {
						$ix = 0;
						if ($prefix =='AI') {
							$prefix = 'AO';
						} else {
							$prefix = 'AC';
						}
					}
					$ix++;
				}
			}
			// @TODO Altre funzioni da gestire anche su p13n
		}
		return $dati;
	}
	function getDSFields($file, $libreria, $form) {
		global $db;
		$fields = array();
		$tabella = "PHPTEMP/GIWI".$_SESSION['GIWI400_ID'];
		$sql1 = "SELECT S_GIWI_REC FROM $tabella WHERE S_GIWI_RRN=0 AND S_GIWI_LIB='$libreria' AND S_GIWI_FIL='$file' AND S_GIWI_FRM='$form' AND S_GIWI_TIP='*'";
		$rs1 = $db->query($sql1);
		$row1 = $db->fetch_array($rs1);
		$stringa= "";
		if ($row1) {
			$stringa= $row1['S_GIWI_REC'];
		}
		// Se stringa diversa da vuoto
		if ($stringa!="") {
			$fields = explode(";", $stringa);
		}
		return $fields;
	}
	
	function microtime_float() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	function writeDurationProgram($time_start, $id, $testo, $end = false) {
		$time_end = microtime_float();
		$time = $time_end - $time_start;
		
		$file = fopen('/www/giwi400/session/'.$id.'_LOG.txt', 'a');
		$rs = fwrite($file, date('d-m-Y H:i.s').': '.$testo.' '.$time.($end ? "\r\n\n\n" : "\r\n"));
		fclose($file);
		
		/*if($rs) error_log('SUCCESS WRITE FILE');
		else error_log('ERROR WRITE FILE');*/
		
	}
	
	function evalListaFormatingDate($id, $mask, $val) {

		$field = new giwi400Field($id);
		$field->setEditMask($mask);
		
		$myField = new wi400InputText($id);
		
		$val = giwi400EditCode($myField, $field, $val);
		return $val;
	}
	
	function giwi400EditCode(&$myField, $obj, $val) {
		$editCode = $obj->getEditCode();
		$cambioTastiera = $obj->getScambioTastiera();
		//echo $myField->getId()."_".$cambioTastiera."__".$editCode."___".$obj->getType()."<br>";
		
		if($editCode == 'Y') {
			//echo $myField->getId()."__id<br>";
			$myField->addValidation('date');
			if($val == '0') {
				$val = '';
			}else {
				$val_pad = str_pad($val, 6, '0', STR_PAD_LEFT);
				if(strlen($val) == 6 || strlen($val_pad) == 6) {
					$val = $val_pad;
					//$data = str_split($val, 2);
					$date = DateTime::createFromFormat('dmy', $val);
					$val = $date->format('d/m/Y');
					//$val = implode("")
				}
			}
		}else if($obj->getType() == 'L') {
			//echo $myField->getId()."__tipo_L__".$obj->getDateTimeFormat()."_format__".$val."<br>";
			$myField->addValidation('date');
			
			if(strlen($val) == 8) {
				$date = DateTime::createFromFormat('d/m/y', $val);
				$val = $date->format('d/m/Y');
			}else {
				//echo 'Campo_'.$myField->getId().' type L ma non è lungo 8 caratteri<br>';
			}
		}else if(in_array($obj->getEditMask(), array("'  /  /  '", "'  /  /    '"))) {
			//echo $myField->getId()."___$val<br>";
			//echo $obj->getEditMask()."__mask<br>";
			
			$myField->addValidation('date');
			
			if($val && strlen(''.$val) >= 5) {
				//error_log('valore__'.$val."___".$myField->getId());
				if(strpos($val, '/') === false) {
					
					if(strlen(''.$val) == 5) $val = '0'.$val;
					
					$format = 'dmy';
					if(strlen(''.$val) == 8) $format = 'dmY';
					
					$date = DateTime::createFromFormat($format, $val);
					$val = $date->format('d/m/Y');
				}
			}else {
				$val = '';
			}
			//showArray($obj);
		}
		
		return $val;
	}
	
	function giwi400MaskToRpg($id, $field, $val) {
		
		if($field->getEditCode() == 'Y' && $val) {
			if(strlen($val) == 10) {
				$date = DateTime::createFromFormat('d/m/Y', $val);
				$val = $date->format('dmy');
			}else {
				error_log($id." il valore non è lungo 10");
			}
		}else if($field->getType() == 'L' && $val) {
			if(strpos($val, '-') !== false) {
				$date = DateTime::createFromFormat('Y-m-d', $val);
				
			}else {
				$date = DateTime::createFromFormat('d/m/Y', $val);
			}
			
			if($field->getDateTimeFormat() == '*DMY') {
				$val = $date->format('d/m/y');
			}else if($field->getDateTimeFormat() == '*ISO') {  
				$val = $date->format('Y-m-d');
			}else {
				$val = $date->format('Y-m-d');
			}
			
			
		}else if($field->getEditMask()) {
			//error_log('getEditMask val '.$val." -> mask ".$field->getEditMask());
			
			if($val) {
				if($field->getEditMask() == "'  /  /  '") {
					if(strlen($val) == 10) {
						$date = DateTime::createFromFormat('d/m/Y', $val);
					}else {
						$date = DateTime::createFromFormat('d/m/y', $val);
					}
					$val = $date->format('dmy');
				}
				if($field->getEditMask() == "'  /  /    '") {
					$date = DateTime::createFromFormat('d/m/Y', $val);
					$val = $date->format('dmY');
				}
			}else {
				$val = 0;
			}
		}
		// OUTPUT RPG Particolare gestito da Funzione
		$attributo = $field->getClientAttributes("OUTPUT_RPG");
		if (isset($attributo)) {
			$funzione = $attributo->getValore();
			if (is_callable($funzione)) {
				$val = call_user_func($funzione, $id, $field, $val, $field, "OUTPUT_RPG");
			}
		}
		
		return $val;
	}

	function check_has_legame($file_src, $form_src, $field_src, $legame="") {
		global $db;

		static $stmt_legame;
		
		if(!isset($stmt_legame)) {
			$sql_legame = "select OT5FL1 from ZOT5FLDR where OT5KEY='$file_src' and OT5FMT='$form_src' and OT5FL1=?";
			
			$stmt_legame = $db->singlePrepare($sql_legame, 0, true);
		}
		
		if($legame!="")
			return "S";
		
		$res = $db->execute($stmt_legame, array($field_src));
		
		if($row = $db->fetch_array($stmt_legame)) {
			return "L";
		}
		
		return "N";
	}
	
	function getStringaButtoniDesc($form, $file, $libreria, $toArray=False) {
		global $db;
		
		$stringa = '';
		
		$sql = "SELECT LISTAGG(TRIM(OT5TXT), ' ') AS RIGA
				FROM ZOT5TXTL
				WHERE OT5FMT = '$form'
				AND OT5KEY = '{$file}_{$libreria}'  GROUP BY ot5row";
		//showArray($sql);
		
		$dati = array();
		$rs = $db->query($sql);
		while($row = $db->fetch_array($rs)) {
			$dati[] = trim($row['RIGA']);
		}
		if ($toArray==True) {
			return $dati;
		}
		$stringa = implode(' ', $dati);
		
		return $stringa;
	}
	function mb_substr_replace($string, $replacement, $start, $length=NULL) {
	    if (is_array($string)) {
	        $num = count($string);
	        // $replacement
	        $replacement = is_array($replacement) ? array_slice($replacement, 0, $num) : array_pad(array($replacement), $num, $replacement);
	        // $start
	        if (is_array($start)) {
	            $start = array_slice($start, 0, $num);
	            foreach ($start as $key => $value)
	                $start[$key] = is_int($value) ? $value : 0;
	        }
	        else {
	            $start = array_pad(array($start), $num, $start);
	        }
	        // $length
	        if (!isset($length)) {
	            $length = array_fill(0, $num, 0);
	        }
	        elseif (is_array($length)) {
	            $length = array_slice($length, 0, $num);
	            foreach ($length as $key => $value)
	                $length[$key] = isset($value) ? (is_int($value) ? $value : $num) : 0;
	        }
	        else {
	            $length = array_pad(array($length), $num, $length);
	        }
	        // Recursive call
	        return array_map(__FUNCTION__, $string, $replacement, $start, $length);
	    }
	    
	    return substr_replace($string, $replacement, $start, $length);
	    
	    /*preg_match_all('/./us', (string)$string, $smatches);
	    preg_match_all('/./us', (string)$replacement, $rmatches);
	    
	    if ($length === NULL) $length = mb_strlen($string);
	    array_splice($smatches[0], $start, $length, $rmatches[0]);
	    return join($smatches[0]);*/
	}