<?php 

	require_once 'query_tool_commons.php';
	require_once 'query_tool_db_commons.php';
 	
	if(isset($settings['xmlservice_driver']) && $settings['xmlservice_driver'] =="PDO") {				
		unset($_SESSION['pdo_resolve_file']);
		unset($_SESSION['pdo_resolve_subst']);
	}
	
	$azione = $actionContext->getAction();
	
//	echo "AZIONE: $azione<br>";
//	echo "FORM: ".$actionContext->getForm()."<br>";
//	echo "GATEWAY: ".$actionContext->getGateway()."<br>";
	
//	echo "<pre>"; print_r($actionContext); echo "</pre>";

	$idDetail = $azione."_SRC";
	$idDetailMarkers = $azione."_MARKERS_SRC";

//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";

//	echo "UTENTE: $idUser<br>";

	$id_query = wi400Detail::getDetailValue($idDetail, 'ID_QUERY');
//	echo "ID QUERY: $id_query<br>";
	
	$des_query = wi400Detail::getDetailValue($idDetail, 'DES_QUERY');
//	echo "DES_QUERY: $des_query<br>";

	$note_query = wi400Detail::getDetailValue($idDetail, 'NOTE');
//	echo "NOTE: $note_query<br>";

	$area_query = wi400Detail::getDetailValue($idDetail, 'AREA');
//	echo "AREA: $area_query<br>";
	
	$funz_query = wi400Detail::getDetailValue($idDetail, 'FUNZIONE');
//	echo "FUNZIONE: $funz_query<br>";

	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	if($actionContext->getForm()=="DEFAULT") {
		if(!in_array($actionContext->getGateway(), array("QUERY_TOOL_DB_MARKERS")) 
			&& !in_array("QUERY_TOOL_DB_PIN_DEFAULT", $steps)
		) {
			$history->addCurrent();
		}
	}
	
	if($actionContext->getForm()=="DEFAULT") {
//		$_SESSION[$azione."_QUERY_LIBERA"] = false;
		
		if($id_query!="")
			wi400Detail::cleanSession($idDetail);
		
		wi400Detail::cleanSession($idDetailMarkers);
		wi400Detail::cleanSession($azione."_SAVE_DET");
		
		if($actionContext->getGateway()=="")
			wi400Detail::cleanSession($azione."_MARKERS_DEF");
	}
	else if($actionContext->getForm()=="DEFAULT_LIBERO") {
		$_SESSION[$azione."_QUERY_LIBERA"] = true;
		
//		$actionContext->gotoAction($azione, "DEFAULT", "", true);
		$actionContext->gotoAction($azione, "DEFAULT", $actionContext->getGateway(), true);
	}
	else if($actionContext->getForm()=="RETURN_DEFAULT") {
		wi400Detail::cleanSession($idDetail);
		
		$_SESSION[$azione."_QUERY_LIBERA"] = false;
		
//		$actionContext->gotoAction($azione, "DEFAULT", "", true);
		$actionContext->gotoAction($azione, "DEFAULT", $actionContext->getGateway(), true);
	}
	else if($actionContext->getForm()=="CLEAN") {
		wi400Detail::cleanSession($idDetail);
		wi400Detail::cleanSession($idDetailMarkers);
		
//		$actionContext->gotoAction($azione, "DEFAULT", "", true);
		$actionContext->gotoAction($azione, "DEFAULT", $actionContext->getGateway(), true);
	}
	
	if(!in_array($actionContext->getForm(), array("DEFAULT_LIBERO", "RETURN_DEFAULT", "CLEAN"))) {
//		echo "SESSION QUERY LIBERA: "; var_dump($_SESSION[$azione."_QUERY_LIBERA"]); echo "<br>";
		
		$query_libera = false;
		if(isset($_SESSION[$azione."_QUERY_LIBERA"])) {
			$query_libera = $_SESSION[$azione."_QUERY_LIBERA"];
		}
		
//		echo "QUERY LIBERA: "; var_dump($query_libera); echo "<br>";
	}
	
	if($actionContext->getForm()=="MARKERS_DEL") {
		wi400Detail::cleanSession($azione."_MARKERS_DET");
		
//		$actionContext->gotoAction($azione, "MARKERS", "", true);
		$actionContext->gotoAction($azione, "MARKERS", $actionContext->getGateway(), true);
	}
	else if($actionContext->getForm()=="MARKERS") {
		$actionContext->setLabel("Markers");
	}
	else if($actionContext->getForm()=="ADD_MARKER") {
		$in_campo = wi400Detail::getDetailValue($azione."_MARKERS_DET", "IN_CAMPO");
		if(empty($in_campo))
			$in_campo = "SQL_QUERY";
		
		$marker = get_marker_code($azione."_MARKERS_DET");	
//		echo "IN CAMPO: $in_campo - MARKER: $marker<br>";
	}
	else if(!in_array($actionContext->getForm(), array("DEFAULT_LIBERO", "RETURN_DEFAULT", "CLEAN", "SAVE_SEL"))) {
		$fieldsValue = getDs("TABQUERY");
//		echo "TAB FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
		
		$hidden_markers = 0;
		$markers = array();
		if(in_array($actionContext->getForm(),array("DEFAULT","EXECUTE","SAVE"))) {
//			echo "<font color='red'>QUERY INDIRIZZATA</font><br>";
			
			if($id_query!="") {
				if($actionContext->getForm()=="EXECUTE") {
//					$actionContext->gotoAction($azione, "DEFAULT", "", true);
				}
				
//				echo "<font color='green'>LOAD QUERY</font><br>";
				
				$sql = "select * from TABQUERY where ID_QUERY=$id_query and STATO='1'";
				$res = $db->singleQuery($sql);
				if($array_campi = $db->fetch_array($res)) {
					$des_query = trim($array_campi['DES_QUERY']);
					$note_query = trim($array_campi['NOTE']);
					$area_query = trim($array_campi['AREA']);
					$funz_query = trim($array_campi['FUNZIONE']);
				}
			}
			else {
				$des_query = trim(wi400Detail::getDetailValue($idDetail, "DES_QUERY"));
				$note_query = trim(wi400Detail::getDetailValue($idDetail, "NOTE"));
				$area_query = trim(wi400Detail::getDetailValue($idDetail, "AREA"));
				$funz_query = trim(wi400Detail::getDetailValue($idDetail, "FUNZIONE"));
				
				$array_campi = getDs("TABQUERY");
			}
			
			$fieldsValue['ID_QUERY'] = $id_query;
			
			$fieldsValue['DES_QUERY'] = $des_query;
//			echo "DES QUERY: $des_query<br>";

			$fieldsValue['NOTE'] = $note_query;
//			echo "NOTE: $note_query<br>";

			$fieldsValue['AREA'] = $area_query;
//			echo "AREA: $area_query<br>";
			
			$fieldsValue['FUNZIONE'] = $funz_query;
//			echo "FUNZIONE: $funz_query<br>";
			
//			echo "ARRAY CAMPI:<pre>"; print_r($array_campi); echo "</pre>";
		
//			$markers = array();
			foreach($array_campi as $key => $val) {
//				echo "KEY: $key<br>";
				
				if(!in_array($key, array("SELECT_FLD", "FROM_FLD", "WHERE_FLD", "GROUP_FLD", "ORDER_FLD")))
					continue;
				
				$campo = $key;
				if(array_key_exists($key, $field_names_array))
					$campo = $field_names_array[$key];
//				echo "CAMPO: $key - CHIAVE: $campo<br>";
				
				$field = strtolower($campo);
				$field_2 = strtolower($campo)."_2";
			
				$$field = "";
				$$field_2 = "";
/*					
				if($id_query!="") {
					$$field = $val;
					
					$query_libera = false;
					$_SESSION[$azione."_QUERY_LIBERA"] = false;
				}
				else {
					if(!is_null(wi400Detail::getDetailValue($idDetail,$campo)))
						$$field = trim(wi400Detail::getDetailValue($idDetail,$campo));
				}
*/
				if(!is_null(wi400Detail::getDetailValue($idDetail, $campo))) {
					$$field = trim(wi400Detail::getDetailValue($idDetail, $campo));
				}
				else if($id_query!="") {
					$$field = $val;
						
//					$query_libera = false;
//					$_SESSION[$azione."_QUERY_LIBERA"] = false;
				}
				
				$fieldsValue[$key] = $$field;
				
				if($hide_query===true)
					$$field = str_replace(array('<br>','</br>', "\r\n", "\n", "\r"), " ", $$field);
				
				$$field_2 = $$field;
				
				$$field = replace_comments($$field);
	
				$markers = check_markers($$field, $markers);
				
				$marker_values = get_marker_values($markers, $idDetailMarkers, $hidden_markers);				
//				echo "<font color='green'>MARKER VALUES:</font><pre>"; print_r($marker_values); echo "</pre>";
		
//				echo "$campo 2: ".$$field_2."<br>";
//				echo "$campo: ".$$field."<br>";
				
				if(!empty($marker_values))
					$$field = replace_markers($$field, $marker_values);
				
//				echo "$campo REPLACED: ".$$field."<br>";
			}
			
//			echo "<font color='green'>MARKER VALUES:</font><pre>"; print_r($marker_values); echo "</pre>";
			
			$sql_query = "";
			if(trim($select)=="" || (trim($select)!="" && $query_libera===true)) {
//				echo "<font color='blue'>QUERY LIBERA</font><br>";
/*
				if($id_query!="") {
					$sql_query = $array_campi['SQL_QUERY'];
					
					if($sql_query!="") {
						$query_libera = true;
						$_SESSION[$azione."_QUERY_LIBERA"] = true;
					}
				}
				else {
					$sql_query = wi400Detail::getDetailValue($idDetail, "SQL_QUERY");
				}
*/
				if(!is_null(wi400Detail::getDetailValue($idDetail, "SQL_QUERY"))) {
					$sql_query = wi400Detail::getDetailValue($idDetail, "SQL_QUERY");
				}
				else if($id_query!="") {
					$sql_query = $array_campi['SQL_QUERY'];

					$query_libera = false;
					$_SESSION[$azione."_QUERY_LIBERA"] = false;
					if($sql_query!="") {
//						echo "<font color='red'>QUERY LIBERA</font><br>";
						$query_libera = true;
						$_SESSION[$azione."_QUERY_LIBERA"] = true;
					}
				}
				
				$fieldsValue["SQL_QUERY"] = $sql_query;
				
				if($hide_query===true)
					$sql_query = str_replace(array('<br>','</br>', "\r\n", "\n", "\r"), " ", $sql_query);
				
				$sql_query_2 = $sql_query;
				
				$sql_query = replace_comments($sql_query);
				
				if($sql_query!="") {
//					$markers = array();
					$markers = check_markers($sql_query, $markers);
//					echo "SQL_QUERY 2: $sql_query_2<br>";
//					echo "SQL_QUERY: $sql_query<br>";
				}
				
				$marker_values = get_marker_values($markers, $idDetailMarkers, $hidden_markers);				
//				echo "<font color='green'>MARKER VALUES:</font><pre>"; print_r($marker_values); echo "</pre>";
			}
			else {
				$query_libera = false;
				$_SESSION[$azione."_QUERY_LIBERA"] = false;
			}
			
			// Descrizioni Titoli - Font Catalogo - Totali colonne
/*			
			if($id_query!="") {
				$check_des_titoli = false;
				if(!empty($array_campi['DES_TITOLI'])) {
					$check_des_titoli = true;
				}
				
				$check_font_catalogo = false;
				if(!empty($array_campi['FONT_CAT'])) {
					$check_font_catalogo = true;
				}
				
				$totali_str = $array_campi['TOTALI_STR'];
			}
			else {
				$check_des_titoli = get_switch_bool_value($idDetail, "DES_TITOLI", true);
				$check_font_catalogo = get_switch_bool_value($idDetail, "FONT_CATALOGO");
				$totali_str = wi400Detail::getDetailValue($idDetail, "TOTALI_STR");
			}
*/
			$check_des_titoli = false;
//			if(!is_null(get_switch_bool_value($idDetail, "DES_TITOLI"))) {
			if(!is_null(wi400Detail::getDetailField($idDetail, "DES_TITOLI"))) {
				$check_des_titoli = get_switch_bool_value($idDetail, "DES_TITOLI", true);
			}
			else if($id_query!="") {
				$check_des_titoli = false;
				if(!empty($array_campi['DES_TITOLI'])) {
					$check_des_titoli = true;
				}
			}
//			echo "CHECK DES TITOLI: "; var_dump($check_des_titoli); echo "<br>";
				
			$check_font_catalogo = false;
//			if(!is_null(get_switch_bool_value($idDetail, "FONT_CATALOGO"))) {
			if(!is_null(wi400Detail::getDetailField($idDetail, "FONT_CATALOGO"))) {
				$check_font_catalogo = get_switch_bool_value($idDetail, "FONT_CATALOGO");
			}
			else if($id_query!="") {
				$check_font_catalogo = false;
				if(!empty($array_campi['FONT_CAT'])) {
					$check_font_catalogo = true;
				}
			}

			$totali_str = "";
			if(!is_null(wi400Detail::getDetailValue($idDetail, "TOTALI_STR"))) {
				$totali_str = wi400Detail::getDetailValue($idDetail, "TOTALI_STR");
			}
			else if($id_query!="") {
				$totali_str = $array_campi['TOTALI_STR'];
			}
			
			$fieldsValue["DES_TITOLI"] = $check_des_titoli;
			$fieldsValue["FONT_CAT"] = $check_font_catalogo;
			$fieldsValue["TOTALI_STR"] = $totali_str;
			
//			echo "CHECK DES TITOLI: $check_des_titoli<br>";
//			echo "CHECK FONT CATALOGO: $check_font_catalogo<br>";
//			echo "TOTALI: $totali_str<br>";
		
//			wi400Detail::cleanSession($idDetail);
//			wi400Detail::cleanSession($idDetailMarkers);
		}
		
//		echo "1 - ID QUERY: $id_query<br>";
		
//		echo "TAB FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
	
//		echo "<font color='green'>MARKERS IN STRING:</font><pre>"; print_r($markers); echo "</pre>";

//		echo "QUERY LIBERA: "; var_dump($query_libera); echo "<br>";
//		echo "SQL: $sql_query<br>";
//		echo "SELECT: $select<br>";

		if($actionContext->getForm()=="EXECUTE") {
//			echo "SELECT: $select - FROM: $from<br>";
			
			if($query_libera===false && ($select=="" || $from=="")) {
//				$actionContext->gotoAction($azione, "DEFAULT", "", true);
				$actionContext->gotoAction($azione, "DEFAULT", $actionContext->getGateway(), true);
			}
		}
		
//		echo "2 - ID QUERY: $id_query<br>";
		
		if(($actionContext->getForm()=="EXECUTE" && $query_libera===false) || 
			($actionContext->getForm()=="DEFAULT" && $query_libera===true && $sql_query=="" && $select!="")
		) {
			$sql_query = "SELECT $select FROM $from";
					
			if(isset($where) && trim($where)!="")
				$sql_query .= " WHERE $where";
			if(isset($group_by) && trim($group_by)!="")
				$sql_query .= " GROUP BY $group_by";
			if(isset($order_by) && trim($order_by)!="")
				$sql_query .= " ORDER BY $order_by";
					
//			echo "SQL COMPOSTA: $sql_query<br>";
			
			if($query_libera===true) {
				$sql_query_2 = $sql_query;
				
				foreach($array_campi as $campo => $val) {
					if(in_array($campo, array("ID_QUERY", "DES_QUERY", "NOTE", "AREA", "FUNZIONE")))
						continue;
					
					$field = strtolower($campo);
				
					$$field = "";
				}
			}
		}
		
//		echo "3 - ID QUERY: $id_query<br>";
		
		if($actionContext->getForm()!="SAVE") {
			if(!empty($markers)) {
/*				
				$marker_values = array();
				
				foreach($markers as $mark) {
					$field_id = get_marker_field_id($mark);			
//					echo "FIELD ID: $field_id<br>";
	
					if(!is_null(wi400Detail::getDetailValue($idDetailMarkers, $field_id))) {
						$marker_values[$mark] = wi400Detail::getDetailValue($idDetailMarkers, $field_id);
					}
					else {
						$parts = get_marker_parts($mark);
						$tipo = $parts['TIPO'];
						
						$val = replace_val_set_markers($tipo);
						
						if($val!==false) {
							$marker_values[$mark] = $val;
						}
					}
				}
				
//				echo "<font color='green'>MARKER VALUES:</font><pre>"; print_r($marker_values); echo "</pre>";
				
				if(count($markers)>$hidden_markers && count($marker_values)==$hidden_markers) {
					$marker_values = array();
				}
*/
//				$marker_values = get_marker_values($markers, $idDetailMarkers, $hidden_markers);				
//				echo "<font color='green'>MARKER VALUES:</font><pre>"; print_r($marker_values); echo "</pre>";
			
				if($actionContext->getForm()=="EXECUTE" && empty($marker_values)) {
//					$actionContext->gotoAction($azione, "DEFAULT", "", true);
					$actionContext->gotoAction($azione, "DEFAULT", $actionContext->getGateway(), true);
				}
			}
		}
	}
	
//	echo "INT - ID QUERY: $id_query<br>";
		
	if($actionContext->getForm()=="EXECUTE") {
		// Impostazione formato tabella
		$sql_query_val = $sql_query;
//		echo "SQL QUERY: $sql_query_val<br>";
		
		if(!empty($marker_values))
			$sql_query_val = replace_markers($sql_query, $marker_values);
//		echo "SQL QUERY REPLACED: $sql_query_val<br>";

//		$queries = explode(";\x0d\x0a", $sql_query_val);
		$queries = explode(";", $sql_query_val);
//		echo "QUERIES:<pre>"; print_r($queries); echo "</pre>";

		$query_exe = false;
		
		foreach($queries as $key => $value) {
//			$value = trim($value);
// 			echo "VALUE: $value<br>";
			if($value !="") {
				$res = $db->query($value);
				
				if(!$res) {	
					$messageContext->addMessage("ERROR","Query errata: ".$value);
//					$messageContext->addMessage("ERROR","Query errata");
				}
				else {	
//					$messageContext->addMessage("SUCCESS","Query OK: ".$value);
					$messageContext->addMessage("SUCCESS","Query OK");
					
					$query_exe = true;
				}
			}
		}
		
		if($query_exe===true) {
			if($id_query!="") {
				// Timestamp ultima esecuzione
				$keyUpdt = array("ID_QUERY" => $id_query, "USER_NAME" => $_SESSION['user']);
			
				$fieldsUpdtValue = array();
			
				$fieldsUpdtValue['TMSEXE'] = getDb2Timestamp();
			
//				echo "UPDATE - FIELDS:<pre>"; print_r($fieldsUpdtValue); echo "</pre>";
			
				$stmt_updt = $db->prepare("UPDATE", "USERQUERY", $keyUpdt, array_keys($fieldsUpdtValue));
					
				$res_updt = $db->execute($stmt_updt, $fieldsUpdtValue);
			}
		}
		
		$is_select_query = true;
		if($query_libera===true) {
			foreach($not_sel_query as $tipo_q) {
//				echo "TIPO QUERY:$tipo_q<br>";
				if(strncmp(strtoupper(trim($value)), $tipo_q, strlen($tipo_q))==0) {
//					echo "NO SELECT<br>";
					$is_select_query = false;
					break;
				}
			}
		}
		
//		echo "IS SELECT QUERY: "; var_dump($is_select_query); echo "<br>";
		
		if($is_select_query===false) {
			$messageContext->addMessage("INFO", "La query non è di selezione: nessun risultato visibile");
			$riga = false;
		}
		else {
			// Metto a video il risultato dell'ultima query
			if($riga = $db->fetch_array($res)) {		
				$col_token = join("_", array_keys($riga));
//				echo "COL_TOKEN: $col_token<br>";
				
//				echo "SESSION: ".$_SESSION["analisi_query_columns_list"]."<br>";
				
				if(!isset($_SESSION["analisi_query_columns_list"]) || $_SESSION["analisi_query_columns_list"]!=$col_token){			
					$_SESSION["analisi_query_columns_list"] = $col_token;
					
					$listConfig = wi400File::getUserFile("list", $azione."_LIST.lst");
//					echo "LIST_CONFIG: $listConfig<br>";
					
					if(file_exists($listConfig)) 
//						echo "<font color='red'>UNLINK</font><br>";
						
						unlink($listConfig);
				}
			}
			else {
				$messageContext->addMessage("INFO", "Non è stata trovata alcuna riga");
			}
		}
	}
	else if($actionContext->getForm()=="SAVE_SEL") {
		$des_query = trim(wi400Detail::getDetailValue($azione."_SAVE_DET", "DES_QUERY"));
		if($des_query=="")
			$des_query = trim(wi400Detail::getDetailValue($idDetail, "DES_QUERY"));
		
		$note_query = trim(wi400Detail::getDetailValue($azione."_SAVE_DET", "NOTE"));
		if($note_query=="")
			$note_query = trim(wi400Detail::getDetailValue($idDetail, "NOTE"));
		
		$area_query = trim(wi400Detail::getDetailValue($azione."_SAVE_DET", "AREA"));
		if($area_query=="")
			$area_query = trim(wi400Detail::getDetailValue($idDetail, "AREA"));
		
		$funz_query = trim(wi400Detail::getDetailValue($azione."_SAVE_DET", "FUNZIONE"));
		if($funz_query=="")
			$funz_query = trim(wi400Detail::getDetailValue($idDetail, "FUNZIONE"));
		
		$check_overwrite = get_switch_bool_value($azione."_SAVE_DET", "OVERWRITE", true);
	}
	else if($actionContext->getForm()=="SAVE") {
		$des_query = trim(wi400Detail::getDetailValue($azione."_SAVE_DET", "DES_QUERY"));
		$note_query = trim(wi400Detail::getDetailValue($azione."_SAVE_DET", "NOTE"));
		$area_query = trim(wi400Detail::getDetailValue($azione."_SAVE_DET", "AREA"));
		$funz_query = trim(wi400Detail::getDetailValue($azione."_SAVE_DET", "FUNZIONE"));
		
		$check_overwrite = get_switch_bool_value($azione."_SAVE_DET", "OVERWRITE", true);
		
		$fieldsValue['DES_QUERY'] = $des_query;
		$fieldsValue['NOTE'] = $note_query;
		$fieldsValue['AREA'] = $area_query;
		$fieldsValue['FUNZIONE'] = $funz_query;
		$fieldsValue['STATO'] = "1";
		$fieldsValue['USERMOD'] = $idUser;
		$fieldsValue['DATMOD'] = $date;
		$fieldsValue['ORAMOD'] = $hour;
		
		// Controllo esistenza filtro in DB
		$error = false;
		if($id_query!="" && $check_overwrite===true) {
//			echo "UPDATE<br>";

			$insert = false;
			
			$keyUpdt = array("ID_QUERY" => "?");
				
			$fieldsValue_updt = $fieldsValue;
			unset($fieldsValue_updt['ID_QUERY']);
			unset($fieldsValue_updt['USERINS']);
			unset($fieldsValue_updt['DATINS']);
			unset($fieldsValue_updt['ORAINS']);
//			echo "TAB FIELDS:<pre>"; print_r($fieldsValue_updt); echo "</pre>";
				
			$stmt_updt = $db->prepare("UPDATE", "TABQUERY", $keyUpdt, array_keys($fieldsValue_updt));
			
			$campi = $fieldsValue_updt;
			$campi[] = $id_query;
//			echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
				
			$res = $db->execute($stmt_updt, $campi);
			
			if(!$res)
				$error = true;
		}
		else {
//			echo "INSERT<br>";

			$insert = true;

			$fieldsValue['USERINS'] = $idUser;
			$fieldsValue['DATINS'] = $date;
			$fieldsValue['ORAINS'] = $hour;
			
			$stmt_ins = $db->prepare("INSERT", "TABQUERY", null, array_keys($fieldsValue));				
			
			// ID_QUERY
			$sql_max = "select max(ID_QUERY) MAX_ID from TABQUERY";
			$res_max = $db->singleQuery($sql_max);
			$max_id = 0;
			if($row_id = $db->fetch_array($res_max))
				$max_id = $row_id['MAX_ID'];
			
			$id_query = $max_id+1;
			
//			$id_query = getSequence("TABQUERY");
			
			$campi = $fieldsValue;
			$campi['ID_QUERY'] = $id_query;
//			echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
			
			$res = $db->execute($stmt_ins, $campi);
			
			if(!$res) {
				$error = true;
			}
			else {
//				echo "INSERT USER<br>";
				
				$fieldsValue_user = getDs("USERQUERY");
//				echo "USER FIELDS:<pre>"; print_r($fieldsValue_user); echo "</pre>";
					
				$stmt_ins_user = $db->prepare("INSERT", "USERQUERY", null, array_keys($fieldsValue_user));
				
				$campi_user = $fieldsValue_user;
				$campi_user['ID_QUERY'] = $id_query;
				$campi_user['ID_FOLDER'] = -1;
				$campi_user['USER_NAME'] = $idUser;
				$campi_user['STATO'] = "1";
				$campi_user['USERINS'] = $idUser;
				$campi_user['DATINS'] = $date;
				$campi_user['ORAINS'] = $hour;
				$campi_user['USERMOD'] = $idUser;
				$campi_user['DATMOD'] = $date;
				$campi_user['ORAMOD'] = $hour;
//				echo "USER CAMPI:<pre>"; print_r($campi_user); echo "</pre>";
				
				$res_ins = $db->execute($stmt_ins_user, $campi_user);
				
				if(!$res_ins)
					$error = true;
			}
		}
					
		if($error===true)
			$messageContext->addMessage("ERROR", "Errore durante il salvataggio della query.");
		else {
			$messageContext->addMessage("SUCCESS", "Salvataggio della query eseguito con successo.");

			wi400Detail::cleanSession($azione."_SAVE_DET");
		}
		
//		$actionContext->onSuccess($azione, "DEFAULT");
//		$actionContext->onError($azione, "DEFAULT", "", "", true);
		if($insert===false)
			$actionContext->onSuccess("CLOSE", "CLOSE_WINDOW_MSG");
		else
			$actionContext->onSuccess($azione, "CLOSE_WINDOW", "", $azione."_SAVE_NEW&ID_QUERY=$id_query");
		$actionContext->onError($azione, "SAVE_SEL", "", "", true);
	}
	else if($actionContext->getForm()=="DELETE") {
		$error = false;
		if($id_query!="") {
			// UPDATE
			$keyUpdt = array("ID_QUERY" => "?", "STATO" => "1");
			$fieldsValue_updt = array(
				"STATO" => "0",
				"USERMOD" => $idUser,
				"DATMOD" => $date,
				"ORAMOD" => $hour
			);
	
			$stmt_updt = $db->prepare("UPDATE", "TABQUERY", $keyUpdt, array_keys($fieldsValue_updt));
	
			$stmt_updt_user = $db->prepare("UPDATE", "USERQUERY", $keyUpdt, array_keys($fieldsValue_updt));
	
			$campi = $fieldsValue_updt;
			$campi[] = $id_query;
//			echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
	
			$res_updt = $db->execute($stmt_updt, $campi);
	
			if(!$res_updt) {
				$error = true;
			}
			else {
				$res_updt_user = $db->execute($stmt_updt_user, $campi);
	
//				if(!$res_updt_user)
//					$error = true;
			}
			
			if($error===false) {
				$messageContext->addMessage("SUCCESS", "Eliminazione della query avvenuta con successo");
					
				wi400Detail::cleanSession($idDetail);
			}
			else {
				$messageContext->addMessage("ERROR", "Errore durante l'eliminazione della query");
			}
		}
		else {
			$messageContext->addMessage("WARNING", "La query non è eliminabile in quanto non è salvata.");
		}
	
//		$actionContext->onSuccess($azione, "DEFAULT");
		$actionContext->onSuccess($azione, "DEFAULT", "", $actionContext->getGateway());
		
//		$actionContext->onError($azione, "DEFAULT", "", "", true);
		$actionContext->onError($azione, "DEFAULT", "", $actionContext->getGateway(), true);
	}
/*
	else if($actionContext->getForm()=="CHECK") {
		require_once p13nPackage("common");
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "TABQUERY",
			'COLUMN' => "DES_QUERY",
			'KEY_FIELD_NAME' => "ID_QUERY",
			'FILTER_SQL' => $where_query,
			'AJAX' => true
		);
		
		$decodeClass = new common();
		$decodeClass->setDecodeParameters($decodeParameters);
		$decodeClass->setFieldValue($id_query);
		$decodeRes = $decodeClass->decode();
		
		if($decodeRes===false) {
			// Non è una query valida
			$field = wi400Detail::getDetailField($idDetail, 'ID_QUERY');
			$field->setFieldMessage(_t("VALORE_DI").$this->getFieldLabel()._t("NON_VALIDO"));
		}
		
		$actionContext->gotoAction($azione, "DEFAULT", "", true);
	}
*/
	else if($actionContext->getForm()=="QUERY_SEL") {
		$actionContext->setLabel("Seleziona query");
		
		$from = "TABQUERY";
		$where = "STATO='1'";
		
		$where .= " and ID_QUERY in (select ID_QUERY from USERQUERY where USER_NAME='$idUser' and STATO='1')";
	}
	else if($actionContext->getForm()=="CLOSE_WINDOW") {
//		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW", "", true);
		$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
	}
	else if($actionContext->getForm() == "AJAX_TREE") {
		//$user = $_SESSION['user'];
		$cont = 0;
		$html = array();
		$query = "SELECT * FROM FOLDQUERY WHERE USER_NAME=? AND ID_PARENT=? AND STATO='1'";
		$stmt_folder = $db->prepareStatement($query);
	
		$query = "SELECT A.*, B.DES_QUERY FROM userquery A LEFT JOIN tabquery B on A.ID_QUERY=B.ID_QUERY
					WHERE USER_NAME=? AND A.STATO='1' AND A.ID_FOLDER=?";
		$stmt_user = $db->prepareStatement($query);
	
		function creaNodo($id, $pId, $name, $idParent) {
			global $html, $cont;
				
			$nodo = array("id" => $id, "pId" => $pId, "name" => $name, "isParent" => ($idParent ? "true" : "false"), "id_query_fold" => $id);
			$html[] = $nodo;
			if(intval($id) > $cont) {
				$cont = intval($id);
			}
			//$cont++;
		}
	
		function getFolder($foldParent) {
			global $stmt_folder, $db;
				
			$cartelle = array();
			$rs = $db->execute($stmt_folder, array($_SESSION['user'], $foldParent));
			while($row = $db->fetch_array($stmt_folder)) {
				$cartelle[] = $row;
			}
				
			return $cartelle;
		}
	
		function creaLivello($userPar, $foldParent) {
			global $db, $stmt_user;
				
			$rs = $db->execute($stmt_user, array($_SESSION['user'], $userPar));
			$pId = $userPar == -1 ? 0 : $userPar;
			while($row = $db->fetch_array($stmt_user)) {
				creaNodo($row['ID_QUERY'], $pId, $row['DES_QUERY'], false);
			}
				
			foreach(getFolder($foldParent) as $row) {
				//echo $row['ID_FOLDER']."___<BR/>";
				creaNodo($row['ID_FOLDER'], $row['ID_PARENT'], $row['DESFOLD'], true);
	
				creaLivello($row['ID_FOLDER'], $row['ID_FOLDER']);
			}
		}
	
		creaLivello(-1, 0);
	
		echo json_encode(array($html, $cont+1));
	
		die();
	}
	else if($actionContext->getForm() == "SAVE_TREE") {
//		showArray($_REQUEST['data']);
	
		$rs = $db->query("DELETE FROM FOLDQUERY WHERE USER_NAME='".$_SESSION['user']."'");
		if($rs) {
			$dati = $_REQUEST['data'];
			$field = getDs("FOLDQUERY");
			//showArray($field);
			$stmt_folder = $db->prepare("INSERT", "FOLDQUERY", null, array_keys($field));
				
			$where = array("ID_QUERY" => "?");
			$stmt_user = $db->prepare("UPDATE", "USERQUERY", $where, array("ID_FOLDER"));
				
			function saveNodo($nodi) {
				global $stmt_folder, $stmt_user, $db;
	
				foreach ($nodi as $key => $nodo) {
					if($nodo['isParent'] == "true") {
						$field['ID_FOLDER'] = $nodo['id_query_fold'];
						$field['USER_NAME'] = $_SESSION['user'];
						$field['DESFOLD'] = $nodo['name'];
						$field['ID_PARENT'] = $nodo['pId'] == "" ? 0 : $nodo['pId'];
						$field['STATO'] = '1';
						$field['USERINS'] = $_SESSION['user'];
						$field['DATAINS'] = date('Ymd');
						$field['ORAINS'] = date('His');
						$field['USERMOD'] = $_SESSION['user'];
						$field['DATMOD'] = date('Ymd');
						$field['ORAMOD'] = date('His');
	
						$db->execute($stmt_folder, $field);
	
						if(isset($nodo['children'])) {
							saveNodo($nodo['children']);
						}
					}else {
						$idNod = -1;
						if($nodo['pId'] != "") {
							$idNod = $nodo['pId'];
						}
	
						$db->execute($stmt_user, array($idNod, $nodo['id_query_fold']));
					}
				}
			}
				
				
			saveNodo($dati);
		}
	
	
		die();
	}
	
//	echo "FINE - ID QUERY: $id_query<br>";