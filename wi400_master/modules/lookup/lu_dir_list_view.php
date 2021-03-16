<?php

$spacer = new wi400Spacer();

if($actionContext->getForm()=="DEFAULT") {
	$ListDetail = new wi400Detail("LU_DIR_LIST_DETAIL");
//	$ListDetail->setColsNum(2);
	
	if($show_info===true) {
		$labelDetail = new wi400Text("PATHS");
		$labelDetail->setLabel("Paths");
		$labelDetail->setValue(implode("<br>",$file_paths));
		$ListDetail->addField($labelDetail);
	
		if(!empty($file_types)) {
			$labelDetail = new wi400Text("TYPES");
			$labelDetail->setLabel("Types");
			$labelDetail->setValue(implode("<br>",$file_types));
			$ListDetail->addField($labelDetail);
		}
	}
	
	$ListDetail->dispose();
	
	$spacer->dispose();
	
	$select = "";
	if(isset($_REQUEST['LU_SELECT']))
		$select = $_REQUEST["LU_SELECT"];
	
	$miaLista = new wi400List("LU_DIR_LIST", !$isFromHistory);
	
	if($select!="")
		$miaLista->setField($select);
	
	$miaLista->setSubfile($subfile);
	$miaLista->setFrom($subfile->getTable());
	$miaLista->setOrder("TIPO asc, FILE asc");
	
	$miaLista->setSelection("MULTIPLE");

	$miaLista->setCalculateTotalRows('FALSE');
	
	$campo = "FILE";
	if(isset($_REQUEST['LU_CAMPO']))
		$campo = $_REQUEST["LU_CAMPO"];
	
	$campo_label = "File";
	if(isset($_REQUEST['LU_CAMPO_LABEL']))
		$campo_label = $_REQUEST["LU_CAMPO_LABEL"];

	$miaLista->setCols(array(
		new wi400Column($campo, $campo_label),
		new wi400Column("TIPO", "Tipo"),
		new wi400Column("DIMENSIONE", "Dimensione (Bytes)", "INTEGER", "right")
	));
	
	if($show_info===false) {
		$miaLista->removeCol("TIPO");
		$miaLista->removeCol("DIMENSIONE");
	}
	else {
		if($azione=="LOG_MANAGER") {
			$mioFiltro = new wi400Filter("TIPO","Tipo","STRING");
			$mioFiltro->setFast(true);
			$miaLista->addFilter($mioFiltro);
		}
	}
	
	$miaLista->addKey($campo);
	$miaLista->setPassKey("FILE");
	
	// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
	if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
		$str = addslashes($_REQUEST["ONCHANGE"]);
		$miaLista->setPassKeyJsFunction($str);
	}

	listDispose($miaLista);
}