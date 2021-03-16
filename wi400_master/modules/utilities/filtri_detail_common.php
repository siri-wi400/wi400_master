<?php

	// Verifico che file utilizzare per il controllo delle utenze
	// Se il sistema accetta solo utenti AS400 carico da tabella architettura
	if (isset($settings['only_as_user']) && $settings['only_as_user']==True) {
		$id_user_name = $architettura->user_name;
		$id_user_desc = $architettura->user_desc;
		$id_user_file = $architettura->user_file;
		$id_user_file_lib = $settings['lib_architect'];
	}
	else {
		$id_user_name = "USER_NAME";
		$id_user_desc = "LAST_NAME";
		$id_user_file = $users_table;
		$id_user_file_lib = $settings['db_name'];
	}
	
	if ($db->type=="GENERIC_PDO") {
		$id_user_file_lib .=".dbo";
	}
	
//	$sql_user = "select DSPRAD from ".$settings['lib_architect']."/JPROFADF	where NMPRAD=?";
	$sql_user = "select $id_user_name, $id_user_desc as DES_USER
		from $id_user_file_lib".$settings['db_separator']."$id_user_file
		where $id_user_name=?";