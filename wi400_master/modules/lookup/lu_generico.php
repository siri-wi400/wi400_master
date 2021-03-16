<?php

	require_once $moduli_path."/list/wi400List.php"; 

//	$parameters = $lookUpContext->getParameters();

//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";

	$file = "";
	if(isset($_REQUEST['FILE']))
		$file = $_REQUEST["FILE"];
	else if(isset($_REQUEST['TABLE_NAME']))
		$file = $_REQUEST["TABLE_NAME"];
	
	$campo = "";
	if(isset($_REQUEST['CAMPO']))
		$campo = $_REQUEST["CAMPO"];
	else if(isset($_REQUEST['KEY_FIELD_NAME']))
		$campo = $_REQUEST['KEY_FIELD_NAME'];
	
	$descrizione = "";
	if(isset($_REQUEST["DESCRIZIONE"]))
		$descrizione = $_REQUEST["DESCRIZIONE"];
	else if(isset($_REQUEST['COLUMN']))
		$descrizione = $_REQUEST['COLUMN'];
	
	$fields ="";
	if(isset($_REQUEST['LU_FIELDS']) && $_REQUEST['LU_FIELDS']!="")
		$fields = $_REQUEST['LU_FIELDS'];
	
	if(isset($_REQUEST['LU_SELECT']) && $_REQUEST['LU_SELECT']!="") {
		$campi_extra = explode("|", $_REQUEST['LU_SELECT']);
	
		if(isset($_REQUEST['LU_AS_TITLES']) && $_REQUEST['LU_AS_TITLES']!="")
			$titles_campi_extra = explode("|",$_REQUEST['LU_AS_TITLES']);
		else
			$titles_campi_extra = explode("|", $_REQUEST['LU_SELECT']);
	
		if(isset($_REQUEST['LU_AS_TYPES']) && $_REQUEST['LU_AS_TYPES']!="")
			$type_campi_extra = explode("|",$_REQUEST['LU_AS_TYPES']);
		
		if(isset($_REQUEST['LU_AS_ALIGN']) && $_REQUEST['LU_AS_ALIGN']!="")
			$align_campi_extra = explode("|",$_REQUEST['LU_AS_ALIGN']);
	}
	
	$from = $file;
	if(isset($_REQUEST['LU_FROM']) && $_REQUEST['LU_FROM']!="")
		$from .= $_REQUEST['LU_FROM'];
	if(isset($_REQUEST['LU_FROM_BASE64']) && $_REQUEST['LU_FROM_BASE64']!="")
		$from .= base64_decode($_REQUEST['LU_FROM_BASE64']);
		
	$where = "";
	if(isset($_REQUEST['LU_WHERE']) && $_REQUEST['LU_WHERE']!="")
		$where = $_REQUEST['LU_WHERE'];

	// Sostituazione parametri JSON
	$where = substituteRequestParmLookup($where, $_REQUEST);
	
	if(isset($_REQUEST["FILTER_SQL"]) AND $_REQUEST["FILTER_SQL"]!="") {
		if($where!="")
			$where .= " AND ";
		$where .= $_REQUEST["FILTER_SQL"];
	}
//	echo "WHERE: $where<br>";

	if(isset($_REQUEST['LU_ORDER']) && $_REQUEST['LU_ORDER']!="")
		$order = $_REQUEST['LU_ORDER'];
	else
		$order = "$campo ASC";
	
	$miaLista = new wi400List("GENERIC_LOOKUP".str_replace(array("/","$"),"_",$file), True);
	
	if(isset($_REQUEST['QUERY_BETWEEN']) && $_REQUEST['QUERY_BETWEEN']==false)
		$miaLista->setPagBetween(false);

	$miaLista->setPassKey("$campo");
	if (isset($_REQUEST["DESCRIZIONE"])){
		$miaLista->setPassDesc($_REQUEST["DESCRIZIONE"]);	
	}
	
	$group_by = "";
	if(isset($_REQUEST['LU_GROUP']) && $_REQUEST['LU_GROUP']!="") {
		$group_by = $_REQUEST['LU_GROUP'];
	}
	else if (isset($_REQUEST["GROUP_BY"])){
		$group_by = $_REQUEST['GROUP_BY'];
	}
	
	if ($fields!="") {
		$miaLista->setField($fields);
	}
	
	if (!isset($_REQUEST["DIRECT_SQL"])){
		$miaLista->setFrom($from);
		$miaLista->setWhere($where);
		$miaLista->setGroup($group_by);
		$miaLista->setOrder($order);
	}
	else {
		$miaLista->setQuery(base64_decode($_REQUEST["DIRECT_SQL"]));
		
		// DEFAULT true: filtro aggiunto normalmente (direttamente sulla query)
		// false: per permette di filtrare campi non esistenti in tabella (ridenominati AS), viene creata una query con WITH, da impostare per query libere ($wi400List->setQuery();) tranne che per query con WITH
		if(isset($_REQUEST["AUTOFILTER"]) && $_REQUEST["AUTOFILTER"]=="N")
			$miaLista->setAutoFilter(false);
	}
	
	if(isset($_REQUEST['UNION_ALL']) && $_REQUEST['UNION_ALL']) {
		$union = $_REQUEST['UNION_ALL'];
		
		//showArray($union);
		$arr_sql_union = array();
		foreach ($union as $index => $gruppo) {
			$arr_sql_union[] = "select '".$gruppo."' as GRUPPO, '$index' as KEY FROM SYSIBM".$settings['db_separator']."SYSDUMMY1";
		}
		
		$miaLista->setQuery(implode(" union all ", $arr_sql_union));
		
		if(isset($_REQUEST["AUTOFILTER"]) && $_REQUEST["AUTOFILTER"]=="N")
			$miaLista->setAutoFilter(false);
	}
	
//	echo "SQL:".$miaLista->getSql()."<br>";
//	echo "SQL:".$miaLista->getQuery()."<br>";

	if(isset($_REQUEST['CALC_TOT_ROWS']))
		$miaLista->setCalculateTotalRows($_REQUEST['CALC_TOT_ROWS']);

	$des_campo = _t('CODE');
	if(isset($_REQUEST["LU_DES_CAMPO"]) && $_REQUEST["LU_DES_CAMPO"]) {
		$des_campo = $_REQUEST["LU_DES_CAMPO"];
	}
	
	$miaLista->addCol(new wi400Column("$campo",$des_campo));
	
	if(isset($descrizione) && $descrizione!="") {
		$miaLista->addCol(new wi400Column("$descrizione",_t('DESCRIPTION')));
	}
	
	if(isset($campi_extra) && !empty($campi_extra)) {
		for($i=0; $i<count($campi_extra); $i++) {
			
			$type = "";
			if(isset($type_campi_extra[$i]) && $type_campi_extra[$i]!="")
				$type = $type_campi_extra[$i];
			
			$align = "left";
			if(isset($align_campi_extra[$i]) && $align_campi_extra[$i]!="")
				$align = $align_campi_extra[$i];
			
			$miaLista->addCol(new wi400Column($campi_extra[$i], $titles_campi_extra[$i], $type, $align));
		}
	}
	
	$key = $campo;
	if(isset($_REQUEST['LU_KEY']) && $_REQUEST['LU_KEY']!="") {
		$keys = explode("|", $_REQUEST['LU_KEY']);
		foreach($keys as $key) {
			$miaLista->addKey($key);
		}
	}else {
		$miaLista->addKey("$key");
	}
	
	$des_filt = $descrizione;
	if(isset($_REQUEST['LU_FILTER'])) {
		$des_filt = $_REQUEST['LU_FILTER'];
	}
//	echo "FILTER:$des_filt<br>";
	
	if(isset($descrizione) && $descrizione!="") {
		$mioFiltro = new wi400Filter("$des_filt", _t('DESCRIPTION'), "STRING");
		$mioFiltro->setFast(true);
		$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		if(isset($_REQUEST['LU_FILTER_SQL_KEY'])) {
			$mioFiltro->setSqlKey($_REQUEST['LU_FILTER_SQL_KEY']);
		}
		$miaLista->addFilter($mioFiltro);
	}
	
	$mioFiltro = new wi400Filter("$campo", _t('CODE'), "STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
	if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
		$str = addslashes($_REQUEST["ONCHANGE"]);
		$miaLista->setPassKeyJsFunction($str);
	}
	
	if(isset($_REQUEST['CAMPO']) && isset($_REQUEST['SPECIAL_VALUE'])) {
		$colonne = $miaLista->getCols();
		$num_colonne = count($colonne);
		
		$union_string = "UNION ALL VALUES ";
		$values = array();
		foreach($_REQUEST['SPECIAL_VALUE'] as $value => $desc) {
			$union = array();
			$union[] = $value;
			$union[] = $desc;
			for($i=3; $i<=$num_colonne; $i++) {
				$union[] = "";
			}
			$values[] = "('".implode("', '", $union)."')";
		}
		$union_string .= implode(",", $values);
		
		// select * non va bene! devo settargli io le colonne
		if(!$fields) {
			$miaLista->setField(implode(", ", array_keys($colonne)));
		}
		/*if(isset($_REQUEST['LU_ORDER']) && (isset($_REQUEST['LU_GROUP']) || isset($_REQUEST['GROUP_BY']))) {
			$miaLista->setGroup($miaLista->getGroup()." ".$union_string);
		}else if($where) {
			$miaLista->setWhere($miaLista->getWhere()." ".$union_string);
		}else {
			$miaLista->setFrom($miaLista->getFrom()." ".$union_string);
		}*/
		$miaLista->setQuery($miaLista->getSql()." ".$union_string);
	}
	
	listDispose($miaLista);
	
	if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
		$myButton = new wi400InputButton("BUTTON_MULTI_SELECT_LOOKUP");
		$myButton->setLabel("Fatto");
		$myButton->setAction("CLOSE");
		$myButton->setForm("CLOSE_LOOKUP");
		$myButton->dispose();
	}
	
