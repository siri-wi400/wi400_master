<?php

	//$tab_runproc = 'ZRUNPROC'; //non funziona con il global

	function runproc_exist_picFile() {
		global $db;
		
		$sql = "SELECT * FROM ZRUNPROC WHERE PROSID='".session_id()."'";
		$rs = $db->singleQuery($sql);
		$row = $db->fetch_array($rs);
		if($row && isset($row['PROSID'])) {
			return true;
		}else {
			return false;
		}
	}
	
	function getAzione() {
		if(isset($_REQUEST['t'])) return $_REQUEST['t'];
		if(isset($_REQUEST['CURRENT_ACTION'])) return $_REQUEST['CURRENT_ACTION'];
		
		return '';
	}
	
	function getForm() {
		if(isset($_REQUEST['f'])) return $_REQUEST['f'];
		if(isset($_REQUEST['CURRENT_FORM'])) return $_REQUEST['CURRENT_FORM'];
		
		return '';
	}
	
	function runproc_insert_pidFile($sessione, $proc_php, $proc_as400, $proc_db) {
		global $db, $appBase;
		
		$tab_runproc = 'ZRUNPROC';
		$fields = getDs($tab_runproc);
		$stmt_insert_runproc = $db->prepare('INSERT', $tab_runproc, null, array_keys($fields));
		
		$timestamp = getDb2Timestamp();
		
		$fields['PROSID'] = $sessione;
		$fields['PROPID'] = $proc_php;
		$fields['PROJOA'] = $proc_as400;
		$fields['PROJAD'] = $proc_db ? $proc_db : '';
		$fields['PROSTA'] = 'R';
		$fields['PROTIF'] = $timestamp;
		$fields['PROTIL'] = $timestamp;
		$fields['PROTIE'] = getDb2Timestamp('*INZ');
		$fields['PROUSR'] = $_SESSION['user'];
		$fields['PROAZI'] = getAzione();
		$fields['PROFRM'] = getForm();
		$fields['PROURL'] = $appBase;
		
		$rs = $db->execute($stmt_insert_runproc, $fields);
		
		return $rs;
	}
	
	function runproc_update_pidFile($sessione, $proc_php, $proc_as400, $proc_db) {
		global $db, $appBase;
		
		$where = array(
			'PROSID' => $sessione
		);
		
		$timestamp = getDb2Timestamp();
		$tab_runproc = 'ZRUNPROC';
		
		$fields = getDs($tab_runproc);
		unset($fields['PROTIF']);
		
		$stmt_update_runproc = $db->prepare('UPDATE', $tab_runproc, $where, array_keys($fields));
		
		$fields['PROSID'] = $sessione;
		$fields['PROPID'] = $proc_php;
		$fields['PROJOA'] = $proc_as400;
		$fields['PROJAD'] = $proc_db ? $proc_db : '';
		$fields['PROSTA'] = 'R';
		$fields['PROTIL'] = $timestamp;
		$fields['PROTIE'] = getDb2Timestamp('*INZ');
		$fields['PROUSR'] = $_SESSION['user'];
		$fields['PROAZI'] = getAzione();
		$fields['PROFRM'] = getForm();
		$fields['PROURL'] = $appBase;
		
		//showArray($fields);
		
		$rs = $db->execute($stmt_update_runproc, $fields);
		
		return $rs;
	}
	
	function runproc_shutdown($sessione) {
		global $db;
		
		$tab_runproc = 'ZRUNPROC';
		
		$where = array(
			'PROSID' => $sessione
		);
		
		if(gettype($db) == 'NULL') {
			return true;
		}
		$stmt_update_runproc = $db->prepare('UPDATE', $tab_runproc, $where, array('PROSTA', 'PROTIE', 'PROAZI', 'PROFRM'));
		
		$fields['PROSTA'] = 'E';
		$fields['PROTIE'] = getDb2Timestamp();
		$fields['PROAZI'] = getAzione();
		$fields['PROFRM'] = getForm();
		
		//showArray($fields);
		
		$rs = $db->execute($stmt_update_runproc, $fields);
		
		return $rs;
		
	}