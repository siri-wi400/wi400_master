<?php 

	require_once 'query_tool_commons.php';
 	
	if($settings['xmlservice_driver']=="PDO") {				
		unset($_SESSION['pdo_resolve_file']);
		unset($_SESSION['pdo_resolve_subst']);
	}
	
	$azione = $actionContext->getAction();
//	echo "FORM: ".$actionContext->getForm()."<br>";

	$idDetail = $azione."_SRC";
	$idDetailMarkers = $azione."_MARKERS_SRC";

//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
	
	if($actionContext->getForm()=="DEFAULT") {
		$history->addCurrent();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
//		wi400Detail::cleanSession($idDetail);
		wi400Detail::cleanSession($idDetailMarkers);
	}
	else if($actionContext->getForm()=="DEFAULT_LIBERO") {
		$_SESSION[$azione."_QUERY_LIBERA"] = true;
		
		$actionContext->gotoAction($azione, "DEFAULT", "", true);
	}
	else if($actionContext->getForm()=="RETURN_DEFAULT") {
		wi400Detail::cleanSession($idDetail);
		
		$_SESSION[$azione."_QUERY_LIBERA"] = false;
		
		$actionContext->gotoAction($azione, "DEFAULT", "", true);
	}
	else if($actionContext->getForm()=="CLEAN") {
		wi400Detail::cleanSession($idDetail);
		wi400Detail::cleanSession($idDetailMarkers);
		
		$actionContext->gotoAction($azione, "DEFAULT", "", true);
	}
	
	if(!in_array($actionContext->getForm(), array("DEFAULT_LIBERO", "RETURN_DEFAULT", "CLEAN"))) {
		$query_libera = false;
		if(isset($_SESSION[$azione."_QUERY_LIBERA"])) {
			$query_libera = $_SESSION[$azione."_QUERY_LIBERA"];
		}
	}
	
	if($actionContext->getForm()=="MARKERS_DEL") {
		wi400Detail::cleanSession($azione."_MARKERS_DET");
		
		$actionContext->gotoAction($azione, "MARKERS", "", true);
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
	else if(!in_array($actionContext->getForm(), array("DEFAULT_LIBERO", "RETURN_DEFAULT", "CLEAN"))) {
		$hidden_markers = 0;
		$markers = array();
		if(in_array($actionContext->getForm(),array("DEFAULT","EXECUTE"))) {
//			echo "<font color='red'>QUERY INDIRIZZATA</font><br>";

			if(isset($_SESSION[$idDetail.'_LOAD_DETAIL'])) {
				if($actionContext->getForm()=="EXECUTE") {
					$actionContext->gotoAction($azione, "DEFAULT", "", true);
				}
			
//				echo "<font color='green'>LOAD DETAIL</font><br>";
			
//				echo "SESSION:<pre>"; print_r($_SESSION[$azione.'_SRC_LOAD_DETAIL']); echo "</pre>";

				$des_query = $_SESSION[$idDetail."_ID_LOAD_DETAIL"];
			}
			else {
				$des_query = trim(wi400Detail::getDetailValue($idDetail,"DES_QUERY"));
			}
//			echo "DES QUERY: $des_query<br>";
		
//			$markers = array();
			foreach($array_campi as $campo => $val) {
				$field = strtolower($campo);
				$field_2 = strtolower($campo)."_2";
			
				$$field = "";
				$$field_2 = "";
					
				if(isset($_SESSION[$idDetail.'_LOAD_DETAIL'][$campo])) {
					$field_obj = $_SESSION[$idDetail.'_LOAD_DETAIL'][$campo];
		
					$$field = $field_obj->getValue();
					
					$query_libera = false;
					$_SESSION[$azione."_QUERY_LIBERA"] = false;
				}
				else {
					if(!is_null(wi400Detail::getDetailValue($idDetail,$campo)))
						$$field = trim(wi400Detail::getDetailValue($idDetail,$campo));
				}
				
				if($hide_query===true)
					$$field = str_replace(array('<br>','</br>', "\r\n", "\n", "\r"), " ", $$field);
				
				$$field_2 = $$field;
				
				$$field = replace_comments($$field);
	
				$markers = check_markers($$field, $markers);
		
//				echo "$campo 2: ".$$field_2."<br>";
//				echo "$campo: ".$$field."<br>";
			}
			
			$sql_query = "";
			if(trim($select)=="") {
//				echo "<font color='blue'>QUERY LIBERA</font><br>";

				if(isset($_SESSION[$idDetail.'_LOAD_DETAIL']["SQL_QUERY"])) {
					$field_obj = $_SESSION[$idDetail.'_LOAD_DETAIL']["SQL_QUERY"];
						
					$sql_query = $field_obj->getValue();
					
					if($sql_query!="") {
						$query_libera = true;
						$_SESSION[$azione."_QUERY_LIBERA"] = true;
					}
				}
				else {
					$sql_query = wi400Detail::getDetailValue($idDetail,"SQL_QUERY");
				}
				
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
			}
			
			// Descrizioni Titoli
			if(isset($_SESSION[$idDetail.'_LOAD_DETAIL']["DES_TITOLI"])) {
				$field_obj = $_SESSION[$idDetail.'_LOAD_DETAIL']["DES_TITOLI"];
				
				$check_des_titoli = false;
				if($field_obj!="") {
					$check_des_titoli = $field_obj->getChecked();
				}
			}
			else {
				$check_des_titoli = get_switch_bool_value($idDetail, "DES_TITOLI", true);
			}
//			echo "CHECK DES TITOLI: $check_des_titoli<br>";

			// Font Catalogo
			if(isset($_SESSION[$idDetail.'_LOAD_DETAIL']["FONT_CATALOGO"])) {
				$field_obj = $_SESSION[$idDetail.'_LOAD_DETAIL']["FONT_CATALOGO"];
			
				$check_font_catalogo = false;
				if($field_obj!="") {
					$check_font_catalogo = $field_obj->getChecked();
				}
			}
			else {
				$check_font_catalogo = get_switch_bool_value($idDetail, "FONT_CATALOGO");
			}
//			echo "CHECK FONT CATALOGO: $check_font_catalogo<br>";

			// Totali colonne
			if(isset($_SESSION[$idDetail.'_LOAD_DETAIL']["TOTALI_STR"])) {
				$field_obj = $_SESSION[$idDetail.'_LOAD_DETAIL']["TOTALI_STR"];
			
				$totali_str = $field_obj->getValue();
			}
			else {
				$totali_str = wi400Detail::getDetailValue($idDetail, "TOTALI_STR");
			}
//			echo "TOTALI: $totali_str<br>";
			
			if(isset($_SESSION[$idDetail.'_LOAD_DETAIL'])) {
				unset($_SESSION[$idDetail.'_LOAD_DETAIL']);
			}
		
//			wi400Detail::cleanSession($idDetail);
//			wi400Detail::cleanSession($idDetailMarkers);
		}
	
//		echo "<font color='green'>MARKERS IN STRING:</font><pre>"; print_r($markers); echo "</pre>";

//		echo "QUERY LIBERA: $query_libera<br>";

		if($actionContext->getForm()=="EXECUTE") {
			if($query_libera===false && ($select=="" || $from=="")) {
				$actionContext->gotoAction($azione, "DEFAULT", "", true);
			}
		}
		
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
				foreach($array_campi as $campo => $val) {
					$field = strtolower($campo);
				
					$$field = "";
				}
			}
		}
	
		if(!empty($markers)) {
			$marker_values = array();
			
			foreach($markers as $mark) {
				$field_id = get_marker_field_id($mark);			
//				echo "FIELD ID: $field_id<br>";

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
			
//			echo "<font color='green'>MARKER VALUES:</font><pre>"; print_r($marker_values); echo "</pre>";
			
			if(count($markers)>$hidden_markers && count($marker_values)==$hidden_markers) {
				$marker_values = array();
			}
		
//			echo "<font color='green'>MARKER VALUES:</font><pre>"; print_r($marker_values); echo "</pre>";
		
			if($actionContext->getForm()=="EXECUTE" && empty($marker_values)) {
				$actionContext->gotoAction($azione, "DEFAULT", "", true);
			}
		}
	}
		
	if($actionContext->getForm()=="EXECUTE") {
		// Impostazione formato tabella
		$sql_query_val = $sql_query;
		if(!empty($marker_values))
			$sql_query_val = replace_markers($sql_query, $marker_values);
//		echo "SQL QUERY REPLACED: $sql_query_val<br>";

//		$queries = explode(";\x0d\x0a", $sql_query_val);
		$queries = explode(";", $sql_query_val);
//		echo "QUERIES:<pre>"; print_r($queries); echo "</pre>";
		
		foreach($queries as $key => $value) {
//			$value = trim($value);
			if($value !="") {
				$res = $db->query($value);
				
				if(!$res) {	
					$messageContext->addMessage("ERROR","Query errata: ".$value);
//					$messageContext->addMessage("ERROR","Query errata");
				}
				else {	
//					$messageContext->addMessage("SUCCESS","Query OK: ".$value);
					$messageContext->addMessage("SUCCESS","Query OK");
				}
			}
		}
			
		// Metto a video il risultato dell'ultima query
		if ($res) {
			if($riga = $db->fetch_array($res)) {		
				$col_token = join("_", array_keys($riga));
//				echo "COL_TOKEN: $col_token<br>";
				
//				echo "SESSION: ".$_SESSION["analisi_query_columns_list"]."<br>";
				
				if(!isset($_SESSION["analisi_query_columns_list"]) || $_SESSION["analisi_query_columns_list"]!=$col_token){			
					$_SESSION["analisi_query_columns_list"] = $col_token;
					
					$listConfig = wi400File::getUserFile("list", $azione."_LIST.lst");
//					echo "LIST_CONFIG: $listConfig<br>";
					
					if(file_exists($listConfig)) {
//						echo "<font color='red'>UNLINK</font><br>";
						
						unlink($listConfig);
					}
				}
			}
			else {
				// Se si tratta di un UPDATE o INSERT visualizzo numero di righe trattate
				// Controllo se è un SELECT.
				$isUpdate=False;
				if (strtoupper(substr($value, 0, 6))=="UPDATE" || strtoupper(substr($value, 0, 6))=="INSERT" ) {
					$isUpdate=True;
				}
				$isCreate=False;
				if (strtoupper(substr($value, 0, 6))=="CREATE") {
					$isCreate=True;
				}
				if (!$isUpdate) {
					if ($isCreate==False) {
						$messageContext->addMessage("INFO", "Non è stata trovata alcuna riga");
					} else {
						// Altri casi ...
					}
				} else {
					$messageContext->addMessage("INFO", "Righe trattate:".$db->num_rows($res));
				}
			}
		}
	}