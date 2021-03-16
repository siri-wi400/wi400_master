<?php

	require_once $moduli_path.'/utilities/filtri_detail_common.php';
	require_once 'query_tool_db_commons.php';
	require_once 'query_tool_commons.php';
	
	$azione = $actionContext->getAction();
	
	if(in_array($actionContext->getForm(), array("DEFAULT"))) {
		$history->addCurrent();
	}
	
	$from_det = wi400Detail::getDetailValue($azione."_DET",'FROM_FILE');
/*	
	$check_overwrite = get_switch_bool_value($azione."_DET",'OVERWRITE');
	$overwrite = get_switch_value($azione."_DET",'OVERWRITE');
//	echo "OVERWRITE: $check_overwrite - OVERWRITE: $overwrite<br>";
*/
	$tipo_gest = wi400Detail::getDetailValue($azione."_DET",'TIPO_GEST');
	
//	$to_user_sel = array();
//	if(wi400Detail::getDetailValue($azione."_DET",'TO_USER')!="")
		$to_user_sel = wi400Detail::getDetailValue($azione."_DET",'TO_USER');
//	echo "TO USER SEL:<pre>"; var_dump($to_user_sel); echo "</pre>";

	if(is_null($to_user_sel))
		$to_user_sel[] = $idUser;
	else if(empty($to_user_sel))
		$to_user_sel = array();
//	echo "TO USER SEL:<pre>"; print_r($to_user_sel); echo "</pre>";
	
	if($actionContext->getForm()=="DEFAULT") {
	
	}
	else if($actionContext->getForm()=="MIGRA") {
		$to_user_array = array();
		if(empty($to_user_sel)) {
			$sql = "select $id_user_name from $id_user_file_lib/$id_user_file";
//			echo "SQL: $sql<br>";
			$res = $db->query($sql, false, 0);
		
			while($row = $db->fetch_array($res)) {
				$to_user_array[] = $row[$id_user_name];
			}
		}
		else {
			$to_user_array = $to_user_sel;
		}
//		echo "TO USER ARRAY:<pre>"; print_r($to_user_array); echo "</pre>";

		$errors = false;
		if(!empty($to_user_array)) {
			// Controllo esistenza filtro in DB
			$sql_nome = "select * from TABQUERY where DES_QUERY=?";
			$stmt_nome = $db->singlePrepare($sql_nome, 0, true);
			
			// Controllo esistenza associazione ad utente
			$sql_user = 'select * from USERQUERY where ID_QUERY=? and USER_NAME=?';
			$stmt_user = $db->singlePrepare($sql_user, 0, true);
			
			// Confronto query
			$sql_conf = "select * from TABQUERY where DES_QUERY=?";
			foreach($field_names_array as $ch => $ca) {
				if(array_key_exists($ca, $array_campi))
					$sql_conf .= " and $ch=?";
			}
//			echo "SQL CONF: $sql_conf<br>";
			$stmt_conf = $db->singlePrepare($sql_conf, 0, true);
			
			// MAX ID_QUERY
			$sql_max = "select max(ID_QUERY) MAX_ID from TABQUERY";
			$res_max = $db->singleQuery($sql_max);
			$max_id = 0;
			if($row_id = $db->fetch_array($res_max))
				$max_id = $row_id['MAX_ID'];
			
			$fieldsValue = getDs("TABQUERY");
			unset($fieldsValue['TMSEXE']);
			
//			echo "TAB FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
			
			// INSERT filtro in DB
			$stmt_ins = $db->prepare("INSERT", "TABQUERY", null, array_keys($fieldsValue));
			
			// UPDATE filtro in DB
//			$keyUpdt = array("DES_QUERY" => "?");
			$keyUpdt = array("ID_QUERY" => "?");
			
			$fieldsValue_updt = $fieldsValue;
			unset($fieldsValue_updt['ID_QUERY']);
			unset($fieldsValue_updt['DES_QUERY']);
			unset($fieldsValue_updt['STATO']);
			unset($fieldsValue_updt['USERINS']);
			unset($fieldsValue_updt['DATINS']);
			unset($fieldsValue_updt['ORAINS']);
			
			$stmt_updt = $db->prepare("UPDATE", "TABQUERY", $keyUpdt, array_keys($fieldsValue_updt));
			
			// Associazione filtro ad utente
			$fieldsValue_user = getDs("USERQUERY");
			unset($fieldsValue_user['TMSEXE']);
//			echo "USER FIELDS:<pre>"; print_r($fieldsValue_user); echo "</pre>";

			$fieldsValue_user['ID_FOLDER'] = -1;
			$fieldsValue_user['STATO'] = "1";
			$fieldsValue_user['USERINS'] = $idUser;
			$fieldsValue_user['DATINS'] = $date;
			$fieldsValue_user['ORAINS'] = $hour;
			$fieldsValue_user['USERMOD'] = $idUser;
			$fieldsValue_user['DATMOD'] = $date;
			$fieldsValue_user['ORAMOD'] = $hour;

			$stmt_ins_user = $db->prepare("INSERT", "USERQUERY", null, array_keys($fieldsValue_user));
			
			$num_ignora = 0;
			$num_over = 0;
			$num_dupli = 0;
			$num_conf = 0;
			$num_confd = 0;
			$num_confi = 0;
			
			foreach($to_user_array as $to_user) {
//				echo "<font color='blue'>USER: $to_user</font><br>";
				
				$file_path =  $settings['data_path'].$to_user."/detail/";
				
				$from_file = $file_path.$from_det.".dtl";
//				echo "FROM FILE: $from_file<br>";
				
				// TO CONTENTS
				$from_filtri_objs = array();
				if(file_exists($from_file)) {
					$handle = fopen($from_file, "r");
					$from_contents = fread($handle, filesize($from_file));
					fclose($handle);
					$from_filtri_objs = unserialize($from_contents);
				}
				else {
					continue;
				}
//				echo "FROM FILTRI:<pre>"; print_r($from_filtri_objs); echo "</pre>";
				
				if(empty($from_filtri_objs)) {
					continue;
				}
				
				foreach($from_filtri_objs as $filtro => $vals) {
					// @todo Inserimento dei filtri in DB
//					echo "<font color='red'>FILTRO: $filtro</font><br>"; 
//					echo "<pre>"; print_r($vals); echo "</pre>";
/*					
					if(!array_key_exists($filtro, $to_filtri_objs) || $overwrite=="S") {
						$to_filtri_objs[$filtro] = $from_filtri_objs[$filtro];
					}
*/					
					// Recupero elementi filtro
					$filtro_array = array();
//					foreach($vals as $key => $fieldObj) {
					foreach($fieldsValue as $key => $val) {
						$chiave = $key;
						if(array_key_exists($key, $field_names_array))
							$chiave = $field_names_array[$key];
//						echo "CAMPO: $key - CHIAVE: $chiave<br>";
						
						if(!array_key_exists($chiave, $vals))
							continue;
						
						$fieldObj = $vals[$chiave];
						
						$filtro_array[$key] = $fieldObj->getValue();
					}
//					echo "FILTRO_ARRAY:<pre>"; var_dump($filtro_array); echo "</pre>";

					$filtro_array['DES_QUERY'] = $filtro;
					$filtro_array['STATO'] = "1";
					$filtro_array['USERINS'] = $to_user;
					$filtro_array['DATINS'] = $date;
					$filtro_array['ORAINS'] = $hour;
					$filtro_array['USERMOD'] = $idUser;
					$filtro_array['DATMOD'] = $date;
					$filtro_array['ORAMOD'] = $hour;

					$insert = false;
					$add_user = true;
					
					// Controllo esistenza filtro in DB
					$res_nome = $db->execute($stmt_nome, array($filtro));
					if($row_nome = $db->fetch_array($stmt_nome)) {
						$id_query = $row_nome['ID_QUERY'];
//						echo "ID QUERY: $id_query - $filtro<br>";
						
//						if($overwrite==true) {
						if($tipo_gest=="OVERWRITE") {
							// SOVRASCRIVI query con stessa descrizione
							
//							echo "UPDATE<br>";

							$num_over++;
							
//							$campi = array_merge($fieldsValue_updt, $filtro_array);
							$campi = $fieldsValue_updt;
							foreach($campi as $k => $v) {
								if(isset($filtro_array[$k]))
									$campi[$k] = $filtro_array[$k];
								else 
									$campi[$k] = "";
							}
							
//							$campi[] = $filtro;
							$campi[] = $id_query;
//							echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
							
							$res = $db->execute($stmt_updt, $campi);
							
							if(!$res) {
								$errors = true;
								$add_user = false;
							}
						}
						else if($tipo_gest=="DUPLICA") {
							// DUPLICA, aggiungi nuova query
							
							$num_dupli++;
							
							$insert = true;
						}
						else if($tipo_gest=="CONFRONTA") {
							// CONFRONTA QUERY CON QUELLE GIA' ESISTENTI, se non ce n'è una uguale aggiungi nuova query altrimenti aggiungi associazione utente/query
							
							$num_conf++;
							
							$campi_conf = array();
							$campi_conf[] = $filtro;
							foreach($field_names_array as $ch => $ca) {
								if(array_key_exists($ca, $array_campi)) {
									if(isset($filtro_array[$ch]))
										$campi_conf[] = $filtro_array[$ch];
									else 
										$campi_conf[] = "";
								}
							}
//							echo "CAMPI CONF:<pre>"; var_dump($campi_conf); echo "</pre>";
							
							$res_conf = $db->execute($stmt_conf, $campi_conf);
							if($row_conf = $db->fetch_array($stmt_conf)) {
//								echo "IGNORATA<br>";
								
								$num_confi++;
							}
							else {
//								echo "DUPLICATA<br>";
								
								$num_confd++;
								$insert = true;
							}
						}
						else if($tipo_gest=="ADD_USER") {
							// AGGIUNGI ASSOCIAZIONE UTENTE/QUERY, aggiungi solo associazione utente/query
						}
						else {
							// IGNORA, non si fa niente (neanche aggiungere associazione utente/query)
							
							$add_user = false;
							
							$num_ignora++;
						}
						
						if($insert===false && $add_user===true) {
							// Controllo se esiste già l'associazione tra utente e query
							$res_user = $db->execute($stmt_user, array($id_query, $to_user));
							if($row_user = $db->fetch_array($stmt_user)) {
								$add_user = false;
							}
						}
					}
					else {
						$insert = true;
					}
					
					if($insert===true) {
//						echo "INSERT<br>";
//						$campi = array_merge($fieldsValue, $filtro_array);
						$campi = $fieldsValue;
						foreach($campi as $k => $v) {
							if(isset($filtro_array[$k]))
								$campi[$k] = $filtro_array[$k];
							else
								$campi[$k] = "";
						}
						
						// ID_QUERY
						// @todo Recupero valore sequenziale per id query
						$max_id++;
						$id_query = $max_id;
						
//						$id_query = getSequence("TABQUERY");
						
						$campi['ID_QUERY'] = $id_query;
						
//						echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
						
						$res = $db->execute($stmt_ins, $campi);
						
						if(!$res) {
							$errors = true;
							$add_user = false;
						}
					}
					
					if($add_user===true) {
						// Aggiunta associazione utente
						$campi_user = $fieldsValue_user;
						$campi_user['ID_QUERY'] = $id_query;
						$campi_user['USER_NAME'] = $to_user;
						
//						echo "CAMPI USER:<pre>"; print_r($campi_user); echo "</pre>";
						
						$res_user = $db->execute($stmt_ins_user, $campi_user);
					}
				}
			}
			
//			echo "NUM CONF: $num_conf - DUPLICATE: $num_confd - IGNORATE: $num_confi<br>";
			if($num_conf>0) {
//				if($num_confd>0)
					$messageContext->addMessage("WARNING", $num_confd." query duplicate su ".$num_conf." query confrontate");
//				else if($num_confi>0)
//					$messageContext->addMessage("WARNING", $num_confi." ignorate su ".$num_conf." query confrontate");
			}
			
			if($num_ignora>0)
				$messageContext->addMessage("WARNING", $num_ignora." query ignorate");
			
			if($num_over>0)
				$messageContext->addMessage("WARNING", $num_over." query sovrascritte");
			
			if($num_dupli>0)
				$messageContext->addMessage("WARNING", $num_dupli." query duplicate");
		}
		
		if($errors===false) {
			$messageContext->addMessage("SUCCESS","Migrazione dei filtri delle query in DB eseguita con successo.");
		}
		else {
			$messageContext->addMessage("ERROR","Errore durante la migrazione dei filtri delle query in DB.");
		}
		
		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onError($azione, "DEFAULT", "", "", true);
	}