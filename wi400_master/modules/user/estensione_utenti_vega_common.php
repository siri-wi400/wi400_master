<?php
	global $tipo_tab_ext;
	$tipo_tab_ext = "SIR_USVGA";

	$tipo_user_amm_array = array (
		"NAM" => "Utente NON amministrativo",
		"AAM" => "Utente ANCHE amministrativo",
		"SAM" => "Utente SOLO amministrativo"
	);
	function getUserExtraInfoVega($user) {
		global $tipo_tab_ext, $db, $settings;
		
		$sql_ext = "SELECT * FROM $tipo_tab_ext WHERE USER_NAME=?";
		$stmt_ext = $db->singlePrepare($sql_ext,0,true);
		if ($stmt_ext) {
			$result_ext = $db->execute($stmt_ext,array($user));
			$row = $db->fetch_array($stmt_ext);
			// Se non esiste scrivo altrimenti aggiorno
			return $row;
		}
		return False;
	}
