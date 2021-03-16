<?php

	require_once $moduli_path."/list/wi400List.php"; 

//	$parameters = $lookUpContext->getParameters();

//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";

//	$file = $_REQUEST["FILE"];
	$campo = $_REQUEST["CAMPO"];
	
	$descrizione = "";
	if(isset($_REQUEST["DESCRIZIONE"])) {
		$descrizione = $_REQUEST["DESCRIZIONE"];
	}

	$miaLista = new wi400List("SQL_LOOKUP", True);
	
	$miaLista->setPassKey("$campo");
	if (isset($_REQUEST["DESCRIZIONE"])){
		$miaLista->setPassDesc($_REQUEST["DESCRIZIONE"]);
	}
	
	$select ="*";
	if(isset($_REQUEST['LU_SELECT']) && $_REQUEST['LU_SELECT']!="")
		$select = $_REQUEST['LU_SELECT'];
	
	$from = "TABQUERY";
	if(isset($_REQUEST['LU_FROM']) && $_REQUEST['LU_FROM']!="")
		$from = $_REQUEST['LU_FROM'];
	
	if(isset($_REQUEST['LU_FROM_BASE64']) && $_REQUEST['LU_FROM_BASE64']!="") {
		$from = base64_decode($_REQUEST['LU_FROM_BASE64']);
	
		if(isset($_REQUEST['IS_SERIALIZED']) && $_REQUEST['IS_SERIALIZED']!="")
			$from = unserialize($from);
	}
	
	$where ="";
	if(isset($_REQUEST['LU_WHERE']) && $_REQUEST['LU_WHERE']!="")
		$where = $_REQUEST['LU_WHERE'];
	
	if(isset($_REQUEST['LU_ORDER']) && $_REQUEST['LU_ORDER']!="")
		$order = $_REQUEST['LU_ORDER'];
	else
		$order = "$campo ASC";
	
	$sql = $with."select ".$select." from ".$from;
	if($where!="")
		$sql .= " where ".$where;
	if($order!="")
		$sql .= " order by ".$order;
	
	if(isset($_REQUEST['SQL_BASE64']) && $_REQUEST['SQL_BASE64']!="") {
		$sql = base64_decode($_REQUEST['SQL_BASE64']);
		
		if(isset($_REQUEST['IS_SERIALIZED']) && $_REQUEST['IS_SERIALIZED']!="")
			$sql = unserialize($sql);
	}

	$miaLista->setQuery($sql);
	
//	echo "SQL:".$miaLista->getQuery()."<br>";

	if(isset($_REQUEST['CALC_TOT_ROWS']))
		$miaLista->setCalculateTotalRows($_REQUEST['CALC_TOT_ROWS']);
/*	
	$det_col = new wi400Column("DETTAGLIO", "Dettaglio<br>Query", "STRING", "center");
	$det_col->setActionListId("DETTAGLIO");
	$det_col->setDefaultValue("SEARCH");
	$det_col->setDecorator("ICONS");
	$det_col->setSortable(false);
	$det_col->setExportable(false);
	
	$miaLista->addCol($det_col);
*/
	$miaLista->addCol(new wi400Column("$campo",_t('CODE')));
	
	if(isset($descrizione) && $descrizione!="") {
		$miaLista->addCol(new wi400Column("$descrizione",_t('DESCRIPTION')));
	}
	
	if(isset($campi_extra) && !empty($campi_extra)) {
		for($i=0; $i<count($campi_extra); $i++) {
			$type = "";
			if(isset($type_campi_extra[$i]) && $type_campi_extra[$i]!="")
				$type = $type_campi_extra[$i];
			$miaLista->addCol(new wi400Column($campi_extra[$i], $titles_campi_extra[$i], $type));
		}
	}
	
	// Aggiunta chiavi di riga
	$miaLista->addKey("$campo");
	
	$des_filt = $descrizione;
	if(isset($_REQUEST['LU_FILTER'])) {
		$des_filt = $_REQUEST['LU_FILTER'];
	}
//	echo "FILTER:$des_filt<br>";
	
	$mioFiltro = new wi400Filter("$des_filt", _t('DESCRIPTION'), "STRING");
	$mioFiltro->setFast(true);
	$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
	$miaLista->addFilter($mioFiltro);
	
	$mioFiltro = new wi400Filter("$campo", _t('CODE'), "STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
/*	
	$action = new wi400ListAction();
	$action->setId("DETTAGLIO");
	$action->setAction("QUERY_TOOL_DB");
	$action->setForm("DEFAULT");
	$action->setGateway("QUERY_MANAGER_DB");
	$action->setLabel("Dettaglio Query");
	$action->setSelection("SINGLE");
	$miaLista->addAction($action);
*/	
	// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
	if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
		$str = addslashes($_REQUEST["ONCHANGE"]);
		$miaLista->setPassKeyJsFunction($str);
	}
	
	listDispose($miaLista);