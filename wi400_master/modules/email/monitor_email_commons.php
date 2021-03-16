<?php

	$settings['enable_mpx'] = true;		//@todo RIMUOVERE

	$idUser = $_SESSION['user'];
	$timeStamp = getDb2Timestamp();
	
	$is_operatore = check_operatore();
	$is_operatore = true;				//@todo RIMUOVERE
	
	$email_sel_option = false;
	
	$enable_mpx = false;
	if(!isset($settings['enable_mpx']) || $settings['enable_mpx']===true)
		$enable_mpx = true;
/*
	$des_option = array(
		"INCLUDE"=>_t("CONTIENE"),
		"START"=>_t("INIZIA_PER"),
		"EQUAL"=>_t("UGUALE_A"),
		"NOT_INCLUDE" => _t("NON_CONTIENE"),
		"NOT_START" => _t("NON_INIZIA_PER"),
		"NOT_EQUAL" => _t("DIVERSO_DA"),
		"EMPTY" => _t("VUOTO"),
		"NOT_EMPTY" => _t("NON_VUOTO")
	);
*/	
//	$des_option = get_text_condition_array();
	
	$modelli_array = get_modelli_list();
//	echo "MODELLI ARRAY:<pre>"; print_r($modelli_array); echo "</pre>";

	$tipo_dest_array = array(
		"TO" => "TO",
		"CC" => "CC",
		"BCC" => "BCC",
		"RPYTO" => "Reply To",
		"CONTO" => "Confirm Reading TO"
	);
	
	$tipo_dest_colors = array(
		"TO" => "wi400_grid_green",
		"CC" => "wi400_grid_yellow",
		"BCC" => "wi400_grid_orange",
		"RPYTO" => "wi400_grid_blue",
		"CONTO" => "wi400_grid_red"
	);

	$ris_invio_array = array(
		"SUCCESS" => "Riusciti",
		"ERROR" => "Fallitti",
		"NOTINV" => "Non inviati"
	);
	
	$tipo_conv_array = array(
		"PDF" => "PDF",
		"BODY" => "Corpo dell'e-mail",
		"ACTION" => "Azione"
	);
	
	$tipo_contents_array = array(
		"BODY" => "Corpo dell'e-mail",
		"XML" => "Codice XML"
	);
	
	$pres_cnts_array = array(
		"S" => "Presenti",
		"N" => "Non presenti",
	);
	
	$tipo_contents_colors = array(
		"BODY" => "wi400_grid_green",
		"XML" => "wi400_grid_yellow",
	);
	
	// E-mail editor
	$email_editor_params = array(
		"TITLE" => "E-mail",
		"ENABLE_FROM" => false,
		"EMAIL_LOOK_UP" => true,
		"ENABLE_BCC" => true,
		"ENABLE_ATC" => false,
		"MODIFY_ATC" => true,
		"ENABLE_LOG" => false,
		"EMAIL_SOURCE" => false,
		"TO" => array(),
		"CC" => array(),
		"BCC" => array(),
		"SUBJECT" => "",
		"ATC" => array(),
	);
	
	$array_not_err = array('000', 'MPX', 'EMA', 'CNV');
	
	$des_icons_array = array(
		"DETTAGLIO" => "Dettaglio",
		"ALLEGATI" => "Allegati",
		"DESTINATARI" => "Destinatari",
		"INOLTRA" => "Inoltro",
		"ESEGUI" => "Esecuzione",
		"CONVERTI" => "Conversione",
		"PRINT" => "Stampa",	
		"CONTENTS" => "Contenuti"
	);
	
	$type_icons_array = array(
		"DETTAGLIO" => "SEARCH",
		"ALLEGATI" => "PAPERCLIP",
		"DESTINATARI" => "BOOK",
		"INOLTRA" => "SEND_MAIL",
		"ESEGUI" => "PLAY",
		"CONVERTI" => "CONVERT",
		"PRINT" => "PRINT",
		"CONTENTS" => "FOLDER"
	);
	
	$action_icons_array = array(
		"DETTAGLIO",
		"ALLEGATI",
		"DESTINATARI",
		"INOLTRA",
		"ESEGUI",
		"CONVERTI",
		"PRINT",
		"CONTENTS"
	);
	
	$atc_icons_array = array(
		"DETTAGLIO",
		"CONVERTI",
		"PRINT",
		"CONTENTS"
	);
/*	
	function where_text_condition($option, $value, $campo) {
		$filterWhere = "";
		
//		echo "OPTION: $option - VALUE: $value - CAMPO: $campo<br>";
	
		if(in_array($value, array("EMPTY", "NOT_EMPTY")) || (isset($value) && $value!="")) {
			$valueToSearch = strtoupper($value);
	
			$filterWhere = "UPPER($campo)";
	
			if (in_array($option,array("EQUAL","EMPTY")))
				$filterWhere .= " = ";
			else if (in_array($option,array("START","INCLUDE")))
				$filterWhere .= " LIKE ";
			else if (in_array($option,array("NOT_START","NOT_INCLUDE")))
				$filterWhere .= " NOT LIKE ";
			else if (in_array($option,array("NOT_EQUAL","NOT_EMPTY")))
				$filterWhere .= " <> ";
	
			$filterWhere .= "'";
	
			if (in_array($option,array("INCLUDE","NOT_INCLUDE")))
				$filterWhere .= "%";
			else if (in_array($option,array("EMPTY","NOT_EMPTY"))) {
				$valueToSearch = "";
			}
	
			$filterWhere .= $valueToSearch;
	
			if (in_array($option,array("START","INCLUDE","NOT_START","NOT_INCLUDE")))
				$filterWhere .= "%";
	
			$filterWhere .= "'";
		}
	
		return $filterWhere;
	}
*/	
	function get_modelli_list() {
		global $base_path, $settings;
	
		$path = $base_path."/package/".$settings['package'].'/persconv';
		$dir = opendir("$path");
		 
		$array_modelli = array();
		
		$array_modelli["*DEFAULT"] = "*DEFAULT";
		
		while($file = readdir($dir)) {
			if(is_file("$path/$file") && strncmp($file,"wi400SpoolCvt_",14)==0) {
				$fileName = basename($file, ".cls.php");
				$model = substr($fileName,14);
				
				$array_modelli[$model] = $model;
			}
		}
		
		return $array_modelli;
	}
	
	function check_operatore() {
		global $db, $settings;
		
		$is_operatore = false;
		
		if(!isset($settings['mail_tab_abil']) ||
			(isset($settings['mail_tab_abil']) && in_array("JPROFADF", $settings['mail_tab_abil']))
		) {
			$sql_operatore = "select * from ".$settings['lib_architect']."/JPROFADF where NMPRAD='".$_SESSION['user']."' and DSPRAD like 'SIRI - %'";
			$result_operatore = $db->singleQuery($sql_operatore);
			if($row_operatore = $db->fetch_array($result_operatore)) {
				$is_operatore = true;
			}
		}
		
		return $is_operatore;
	}
/*	
	function hidden_fields() {
		$hiddenField = new wi400InputHidden("SUBJECT_SRC_OPTION");
		$hiddenField->setValue($_REQUEST['SUBJECT_SRC_OPTION']);
		$hiddenField->dispose();
		
		$hiddenField = new wi400InputHidden("MITTENTE_SRC_OPTION");
		$hiddenField->setValue($_REQUEST['MITTENTE_SRC_OPTION']);
		$hiddenField->dispose();
		
		$hiddenField = new wi400InputHidden("DESTINATARIO_SRC_OPTION");
		$hiddenField->setValue($_REQUEST['DESTINATARIO_SRC_OPTION']);
		$hiddenField->dispose();
	}
*/