<?php

	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()=="DEFAULT") {
		$check_abil = data_area_read("WIOPENTERM", 1, 1);
		$check_log_file = data_area_read("WIOPENTERM", 2, 1);
		$check_log_txt = data_area_read("WIOPENTERM", 3, 1);
		$check_reply_command = data_area_read("WIOPENTERM", 4, 1);
		$check_mappa_campi = data_area_read("WIOPENTERM", 5, 1);
		
		$ccsid = data_area_read("WIOPENTERM", 6, 9);
		$code_page = data_area_read("WIOPENTERM", 15, 9);
		$tipo_terminale = data_area_read("WIOPENTERM", 24, 9);
		
		$check_id_video = data_area_read("WIOPENTERM", 33, 1);
	}
	else if($actionContext->getForm()=="SAVE") {
		$check_abil = get_switch_bool_value($azione."_DETAIL", "ABILITATO");		
		$abil = get_switch_value($azione."_DETAIL", "ABILITATO");
//		echo "<font color='blue'>ABILITATO</font> - CHECK: "; var_dump($check_abil); echo " - VALUE: "; var_dump($abil); echo "<br>";

		$check_log_file = get_switch_bool_value($azione."_DETAIL", "LOG_FILE");
		$log_file = get_switch_value($azione."_DETAIL", "LOG_FILE");
//		echo "<font color='blue'>LOG_FILE</font> - CHECK: "; var_dump($check_log_file); echo " - VALUE: "; var_dump($log_file); echo "<br>";
		
		$check_log_txt = get_switch_bool_value($azione."_DETAIL", "LOG_TXT");
		$log_txt = get_switch_value($azione."_DETAIL", "LOG_TXT");
//		echo "<font color='blue'>LOG_TXT</font> - CHECK: "; var_dump($check_log_txt); echo " - VALUE: "; var_dump($log_txt); echo "<br>";

		$check_reply_command = get_switch_bool_value($azione."_DETAIL", "REPLY_COMMAND");
		$reply_command = get_switch_value($azione."_DETAIL", "REPLY_COMMAND");
//		echo "<font color='blue'>REPLY_COMMAND</font> - CHECK: "; var_dump($check_reply_command); echo " - VALUE: "; var_dump($reply_command); echo "<br>";
		
		$check_mappa_campi = get_switch_bool_value($azione."_DETAIL", "MAPPA_CAMPI");
		$mappa_campi = get_switch_value($azione."_DETAIL", "MAPPA_CAMPI");
//		echo "<font color='blue'>MAPPA_CAMPI</font> - CHECK: "; var_dump($check_mappa_campi); echo " - VALUE: "; var_dump($mappa_campi); echo "<br>";

		$ccsid = wi400Detail::getDetailValue($azione."_DETAIL",'CCSID');
		$code_page = wi400Detail::getDetailValue($azione."_DETAIL",'CODE_PAGE');
		$tipo_terminale = wi400Detail::getDetailValue($azione."_DETAIL",'TIPO_TERMINALE');
		
		$check_id_video = get_switch_bool_value($azione."_DETAIL", "ID_VIDEO");
		$id_video = get_switch_value($azione."_DETAIL", "ID_VIDEO");
//		echo "<font color='blue'>ABILITATO</font> - CHECK: "; var_dump($check_id_video); echo " - VALUE: "; var_dump($id_video); echo "<br>";
		
		$string = sprintf("%-1s", $abil);
		$string .= sprintf("%-1s", $log_file);
		$string .= sprintf("%-1s", $log_txt);
		$string .= sprintf("%-1s", $reply_command);
		$string .= sprintf("%-1s", $mappa_campi);
		
		$string .= sprintf("%09s", $ccsid);
		$string .= sprintf("%09s", $code_page);
		$string .= sprintf("%09s", $tipo_terminale);
		
		$string .= sprintf("%-1s", $id_video);
//		echo "STRING: $string<br>";
		
		$string_prova = str_replace(' ','-',$string);
//		echo "STRING: $string_prova<br>";
//die("HERE");		
		data_area_write("WIOPENTERM", $string);
		
		$messageContext->addMessage("SUCCESS", "Aggiornamento effettuato con successo");
		
		$actionContext->gotoAction($azione, "DEFAULT");
	}