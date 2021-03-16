<?php

	// Controllo se l'ente associato all'utente è un locale
	$isPdv = false;
	if(isset($_SESSION['ente'])) {
		$ente_user_array = get_campo_ente($_SESSION['ente'],  date("Ymd"));
		if($ente_user_array['MAFTPE']=='01' || $ente_user_array['MAFTPE']=='02') {
			$isPdv = true;
			$ente = $_SESSION['ente'];
		}
	}