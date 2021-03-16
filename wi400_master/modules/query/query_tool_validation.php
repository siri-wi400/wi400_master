<?php

//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
	
	$query_admin_level = "";
	
	if(in_array("QUERY_ADMIN", $_SESSION ["WI400_GROUPS_BACKUP"]))
		$query_admin_level = "QUERY_ADMIN";
	
	if(in_array("QUERY_USER", $_SESSION ["WI400_GROUPS_BACKUP"]))
		$query_admin_level = "QUERY_USER";
	
	if(in_array("QUERY_FILTRO", $_SESSION ["WI400_GROUPS_BACKUP"]))
		$query_admin_level = "QUERY_FILTRO";
	
	if(in_array("QUERY_MARKER", $_SESSION ["WI400_GROUPS_BACKUP"]))
		$query_admin_level = "QUERY_MARKER";
		
//	echo "QUERY ADMIN LEVEL: $query_admin_level<br>";
				
	if(!in_array($query_admin_level, array("QUERY_FILTRO", "QUERY_MARKER"))) {
		if($actionContext->getForm()=="DEFAULT") {
			validation_default_query_filtro();
		}
		else if($actionContext->getForm()=="MARKERS") {
			validation_query_markers();
		}
	}
/*	
	if($actionContext->getAction()=="QUERY_TOOL_DB" && $actionContext->getForm()=="DEFAULT") {
		validation_default_db();
		die("HERE");
	}
	
	function validation_default_db() {
		require_once p13nPackage("common");
	
		if(isset($_POST['ID_QUERY']) && !empty($_POST['ID_QUERY'])) {
			$where_query = "ID_QUERY in (select ID_QUERY from USERQUERY where USER_NAME='".$_SESSION['user']."' and STATO='1')";
			$where_query .= " and STATO='1'";
	
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
			$decodeClass->setFieldValue($_POST['ID_QUERY']);
			$decodeRes = $decodeClass->decode();
	
			if($decodeRes===false) {
				$messageContext->addMessage("ERROR", "Valore non valido", "ID_QUERY", true);
			}
		}
	}
*/	
	function validation_default_query_filtro() {
		global $messageContext;
		
		$sql_query = "";
		if(isset($_POST['SQL_QUERY']) && trim($_POST['SQL_QUERY'])!="") {
			$sql_query = $_POST['SQL_QUERY'];
		}
		
		$select = "";
		if(isset($_POST['SELECT']) && trim($_POST['SELECT'])!="") {
			$select = $_POST['SELECT'];
		}
		
		$from = "";
		if(isset($_POST['FROM']) && trim($_POST['FROM'])!="") {
			$from = $_POST['FROM'];
		}
		
		if($sql_query=="" && ($select=="" || $from=="")) {
			$messageContext->addMessage("ERROR", _t('QRY_SYNTAX_ERR'));
		}
	}
	
	function validation_query_markers() {
		global $messageContext, $moduli_path;
		
		require $moduli_path.'/query/query_tool_common_markers.php';		// require invece di require_once, pechè a causa del require_once qui non funziona il require_once in query_tool_commons.php dopo
		
		$tipo = $_POST['TIPO_MARKER'];
		
		if(isset($_POST['SELECT_STR']) && $_POST['SELECT_STR']!="") {
			if(!in_array($tipo, $val_select_array)) {
				$messageContext->addMessage("ERROR", "Valorizzare 'Selezione di valori ', solo per marker indicati come '*SELEZIONE*'");
			}
		}
		else {
			if(in_array($tipo, $val_select_array)) {
				$messageContext->addMessage("ERROR", "Valorizzare 'Selezione di valori ', in caso di marker indicati come '*SELEZIONE*'");
			}
		}
		
		$not_required = "";
		if(isset($_POST['NOT_REQUIRED']) && $_POST['NOT_REQUIRED']!="") {
			$not_required = $_POST['NOT_REQUIRED'];
		}
		
		if($not_required!="" && in_array($tipo, $no_apici_array)) {
//			$messageContext->addMessage("ERROR", "Selezionare 'Campo Vuoto Ammesso', solo per marker non numerici");
//			$messageContext->addMessage("ERROR", "Selezionare 'Campo Vuoto Ammesso', solo per marker richiedenti valori in formato testo");
			$messageContext->addMessage("ERROR", "Selezionare 'Campo Vuoto Ammesso', solo per marker richiedenti uso di apici nella query");
		}
		
		$not_hidden = "";
		if(isset($_POST['NOT_HIDDEN']) && $_POST['NOT_HIDDEN']!="") {
			$not_hidden = $_POST['NOT_HIDDEN'];
		}
		
		if($not_hidden!="" && !in_array($tipo, $hidden_array)) {
			$messageContext->addMessage("ERROR", "Selezionare 'Mostra campo nascosto', solo per marker indicati come '*NASCOSTO*'");
		}
	}