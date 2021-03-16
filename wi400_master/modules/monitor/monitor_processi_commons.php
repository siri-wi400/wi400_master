<?php

	$tab_processi = 'ZRUNPROC';
	
	function getTracciatoProcessi($sessioni) {
		global $db, $tab_processi;
		
		if(!is_array($sessioni)) $sessioni = array($sessioni);
		
		$dati = array();
		
		$sql = "SELECT * FROM $tab_processi WHERE PROSID IN ('".implode("', '",  $sessioni)."')";
		$rs = $db->query($sql);
		while($row = $db->fetch_array($rs)) {
			$dati[] = $row;
		}
		
		return $dati;
		
	}