<?php

	require_once 'monitor_email_commons.php';
	
	$azione = $actionContext->getAction();
	
//	echo "GATEWAY: ".$actionContext->getGateway()."<br>";
	
	$off = 1;
	if(!in_array($actionContext->getForm(), array("ATC_PRV", "NEW_DEST_DET", "NEW_CONTENTS_DET"))) {
		$off = 2;
		$history->addCurrent();
	}
/*	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	$last_action = "";
	$last_form = "";
	if(!empty($steps) && count($steps)>=$off) {
		$last_step = $steps[count($steps)-$off];
//		echo "LAST STEP: $last_step<br>";
			
		$last_action_obj = $history->getAction($last_step);
		if (isset($last_action_obj)) {
			$last_action = $last_action_obj->getAction();
			$last_form = $last_action_obj->getForm();
		}
	}
//	echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";
*/	
	$steps = $history->getSteps();
//	echo "STEPS:<pre>"; print_r($steps); echo "</pre>";
	
	$array_steps = get_history_steps($off, $steps);
	
	$first_action = $array_steps['FIRST_ACTION'];
	$first_form = $array_steps['FIRST_FORM'];
	
	$last_action = $array_steps['LAST_ACTION'];
	$last_form = $array_steps['LAST_FORM'];
//	echo "LAST_ACTION: $last_action - LAST FORM: $last_form<br>";

	$last_step = $array_steps['LAST_STEP'];
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
//	echo "DETAIL:<pre>"; print_r(wi400Detail::getDetailValues($azione."_SRC")); echo "</pre>";
	
	if(in_array($actionContext->getForm(), array("DEFAULT", "EMAIL_LIST"))) {
		if(isset($_REQUEST['ID_EMAIL'])) {
			wi400Detail::cleanSession($azione."_SRC");
			wi400Detail::cleanSession($azione."_NEW_EMAIL_DET");
				
			$id_array = array($_REQUEST['ID_EMAIL']);
		}
		else {
			$id_array = array();
			if(wi400Detail::getDetailValue($azione."_SRC","ID_SRC")!="")
				$id_array = wi400Detail::getDetailValue($azione."_SRC","ID_SRC");
		}
//		echo "ID SRC:<pre>"; print_r($id_array); echo "</pre>";
		
		$data_inv_ini = wi400Detail::getDetailValue($azione."_SRC",'DATA_INV_INI');
		$data_inv_fin = wi400Detail::getDetailValue($azione."_SRC",'DATA_INV_FIN');
//		echo "DATA INVIO - INI: $data_inv_ini - FIN: $data_inv_fin<br>";
		
		$data_ris_ini = wi400Detail::getDetailValue($azione."_SRC",'DATA_RIS_INI');
		$data_ris_fin = wi400Detail::getDetailValue($azione."_SRC",'DATA_RIS_FIN');
//		echo "DATA RISPEDIZIONE - INI: $data_ris_ini - FIN: $data_ris_fin<br>";
		
		$id_ini = wi400Detail::getDetailValue($azione."_SRC",'ID_INI');
		$id_fin = wi400Detail::getDetailValue($azione."_SRC",'ID_FIN');
//		echo "ID INI: $id_ini - ID FIN: $id_fin<br>";
/*
		if(!empty($id_array) || $id_ini!="") {
			$data_inv_ini = "";
			$data_inv_fin = "";
		}
*/		
		$user_array = array();
		if(wi400Detail::getDetailValue($azione."_SRC","USER_SRC")!="")
			$user_array =  wi400Detail::getDetailValue($azione."_SRC",'USER_SRC');
//		echo "USR SRC:<pre>"; print_r($user_array); echo "</pre>";
		
		// Subject
/*	
		$sbj_option = "";
		if(isset($_REQUEST['SUBJECT_SRC_OPTION'])) {
//			unset($_SESSION[$azione.'_SUBJECT_SRC_OPTION']);
			$sbj_option = $_REQUEST['SUBJECT_SRC_OPTION'];
			$_SESSION[$azione.'_SUBJECT_SRC_OPTION'] = $sbj_option;
		}
		else if(isset($_SESSION[$azione.'_SUBJECT_SRC_OPTION'])) {
			$sbj_option = $_SESSION[$azione.'_SUBJECT_SRC_OPTION'];
		}
*/
		$sbj_option = get_text_condition("SUBJECT_SRC", $azione);
		$subject = wi400Detail::getDetailValue($azione."_SRC",'SUBJECT_SRC');
//		echo "SBJ OPTION: $sbj_option - SUBJECT: $subject<br>";
		
		// Tipo conversione
		$tipo_conv = wi400Detail::getDetailValue($azione."_SRC",'TPCONV');
//		echo "TIPO CONV: $tipo_conv<br>";
		
		// Modello conversione
		$mod_conv = array();
		if(wi400Detail::getDetailValue($azione."_SRC", "MODCONV")!="")
			$mod_conv = wi400Detail::getDetailValue($azione."_SRC",'MODCONV');
//		echo "MODELLO CONV:<pre>"; print_r($mod_conv); echo "</pre>";

		$mod_cls = array();
		if(wi400Detail::getDetailValue($azione."_SRC", "MODCLS_SRC")!="")
			$mod_cls = wi400Detail::getDetailValue($azione."_SRC", "MODCLS_SRC");
//		echo "CLASSI CONV:<pre>"; print_r($mod_cls); echo "</pre>";

		// Nome allegato
/*		
		$atc_option = "";
		if(isset($_REQUEST['ALLEGATO_SRC_OPTION'])) {
//			unset($_SESSION[$azione.'_ALLEGATO_SRC_OPTION']);
			$atc_option = $_REQUEST['ALLEGATO_SRC_OPTION'];
			$_SESSION[$azione.'_ALLEGATO_SRC_OPTION'] = $atc_option;
		}
		else if(isset($_SESSION[$azione.'_ALLEGATO_SRC_OPTION'])) {
			$atc_option = $_SESSION[$azione.'_ALLEGATO_SRC_OPTION'];
		}
*/		
		$atc_option = get_text_condition("ALLEGATO_SRC", $azione);
		$atc_name = wi400Detail::getDetailValue($azione."_SRC",'ALLEGATO_SRC');
//		echo "ALLEGATO OPTION: $atc_option - ALLEGATO: $atc_name<br>";

		// Mittente
/*		
		if($email_sel_option===true) {
			$mit_option = "";
			if(isset($_REQUEST['MITTENTE_SRC_OPTION'])) {
//				unset($_SESSION[$azione.'_MITTENTE_SRC_OPTION']);
				$mit_option = $_REQUEST['MITTENTE_SRC_OPTION'];
				$_SESSION[$azione.'_MITTENTE_SRC_OPTION'] = $mit_option;
			}
			else if(isset($_SESSION[$azione.'_MITTENTE_SRC_OPTION'])) {
				$mit_option = $_SESSION[$azione.'_MITTENTE_SRC_OPTION'];
			}
		}
*/		
		if($email_sel_option===true)
			$mit_option = get_text_condition("MITTENTE_SRC", $azione);
		$mittente = wi400Detail::getDetailValue($azione."_SRC",'MITTENTE_SRC');
//		echo "MIT OPTION: $mit_option - MITTENTE: $mittente<br>";
		
		// Destinatario
/*		
		if($email_sel_option===true) {
			$dest_option = "";
			if(isset($_REQUEST['DESTINATARIO_SRC_OPTION'])) {
//				unset($_SESSION[$azione.'_DESTINATARIO_SRC_OPTION']);
				$dest_option = $_REQUEST['DESTINATARIO_SRC_OPTION'];
				$_SESSION[$azione.'_DESTINATARIO_SRC_OPTION'] = $dest_option;
			}
			else if(isset($_SESSION[$azione.'_DESTINATARIO_SRC_OPTION'])) {
				$dest_option = $_SESSION[$azione.'_DESTINATARIO_SRC_OPTION'];
			}
		}
*/		
		if($email_sel_option===true)
			$dest_option = get_text_condition("DESTINATARIO_SRC", $azione);
		$destinatario = wi400Detail::getDetailValue($azione."_SRC",'DESTINATARIO_SRC');
//		echo "DEST OPTION: $dest_option - DESTINATARIO: $destinatario<br>";
		
		$ris_invio = wi400Detail::getDetailValue($azione."_SRC",'RIS_INVIO');
//		echo "RISULTATO INVIO: $ris_invio<br>";

		$check_zip = get_switch_bool_value($azione."_SRC", "ZIP_SRC");
/*		
		$zip_src = "N";
		if($check_zip!=false)
			$zip_src = "S";
*/		
		$zip_src = get_switch_value($azione."_SRC", "ZIP_SRC");
//		echo "<font color='blue'>ZIP</font> - CHECK: "; var_dump($check_zip); echo " - VALUE: "; var_dump($zip_src); echo "<br>";
/*
		$check_contents = get_switch_bool_value($azione."_SRC", "CONTENTS_SRC");
		
		$contents_src = get_switch_value($azione."_SRC", "CONTENTS_SRC");
//		echo "CHECK CONTENTS: $check_contents<br>";
*/
		$contents_src = wi400Detail::getDetailValue($azione."_SRC",'CONTENTS_SRC');
		
		if(!isset($settings['enable_mpx']) || $settings['enable_mpx']===true) {
			$check_mpx = get_switch_bool_value($azione."_SRC", "INVIO_MPX_SRC");
/*			
			$mpx_src = "N";
			if($check_mpx!=false)
				$mpx_src = "S";
*/			
			$mpx_src = get_switch_value($azione."_SRC", "INVIO_MPX_SRC");
//			echo "CHECK MPX: $check_mpx<br>";
		}
		
//		$prova_sel = get_checkbox_values($azione."_PROVA", $last_step, $azione."_DEFAULT", $tipo_dest_array);
//		echo "PROVA:<pre>"; print_r($prova_sel); echo "</pre>";
//		echo "SESSION PROVA:<pre>"; print_r($_SESSION[$azione."_PROVA"]); echo "</pre>";
/*		
		$obj = wi400Detail::getDetailField($azione."_SRC", "PROVA");
//		echo "CHECKBOX:<pre>"; var_dump($obj); echo "</pre>";		
		$prova_sel = $obj->getValue();
		echo "PROVA:<pre>"; print_r($prova_sel); echo "</pre>";
*/		
		$prova_sel = wi400Detail::getDetailValue($azione."_SRC", "PROVA");
//		echo "PROVA:<pre>"; print_r($prova_sel); echo "</pre>";
	}
	else if(in_array($actionContext->getForm(), array(
		"ATC_LIST", "DEST_LIST", "CONTENTS_LIST", 
		"NEW_ATC_DET", "NEW_DEST_DET", "NEW_CONTENTS_DET",
		"EMAIL_DET", "MPX_DET"
	))) {
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_LIST");
		
		$cod_id = $keyArray['ID'];
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		unset($_SESSION[$azione.'_SUBJECT_SRC_OPTION']);
		unset($_SESSION[$azione.'_DESTINATARIO_SRC_OPTION']);
		
//		if($messageContext->getSeverity()=="SUCCESS")
//			$actionContext->gotoAction($azione, "EMAIL_LIST", "", true);
	}
	else if($actionContext->getForm()=="EMAIL_LIST") {
		$actionContext->setLabel("Lista e-mails");
		
		wi400Detail::cleanSession($azione."_EMAIL_DET");
		wi400Detail::cleanSession($azione."_NEW_EMAIL_DET");
		
		wi400Detail::cleanSession($azione."_MPX_DET");
		
		$select = "select a.*";
		$from = " from FPDFCONV a";
		
		$where_array = array();

		// Periodo invio
		if($data_inv_ini!="" && $data_inv_fin!="") {
			$data_ini = dateToTimestamp($data_inv_ini, true);
			$data_fin = dateToTimestamp($data_inv_fin, true);
			
			$where_array[] = "substr(char(a.MAIINS), 1, 10) between '$data_ini' and '$data_fin'";
		}
		
		// Periodo rispedizione
		if($data_ris_ini!="" && $data_ris_fin!="") {
			$data_ini = dateToTimestamp($data_ris_ini, true);
			$data_fin = dateToTimestamp($data_ris_fin, true);
			
			$where_array[] = "substr(char(a.MAIELA), 1, 10) between '$data_ini' and '$data_fin'";
		}
		
		// ID specifici
		if(!empty($id_array))
			$where_array[] = "a.ID in ('".implode("', '", $id_array)."')";
		
		// ID range
		if($id_ini!="" && $id_fin!="") {
			$id_range = array();
			
			$cod_id = substr($id_ini, 0, 1);
			$num_id_ini = substr($id_ini, 1);
			$num_id_fin = substr($id_fin, 1);
//			echo "COD ID: $cod_id - NUM ID INI: $num_id_ini - NUM ID FIN: $num_id_fin<br>";

			$where_array[] = "substr(a.ID, 1, 1)='$cod_id'";
			$where_array[] = "substr(a.ID, 2) between $num_id_ini and $num_id_fin";
		}
		
		// Utenti specifici
		if(!empty($user_array))
			$where_array[] = "a.MAIUSR in ('".implode("', '", $user_array)."')";

		// Oggetto
		if($subject!="") {
			$where_array[] = where_text_condition($sbj_option, $subject, "a.MAISBJ");
		}
		
		// Mittente
		if($mittente!="") {
			if($email_sel_option===true)
				$where_array[] = where_text_condition($mit_option, $mittente, "a.MAIFRM");
			else
				$where_array[] = "a.MAIFRM='$mittente'";
		}
		
		// Risultato invio (Riuscito/Fallito)
		if($ris_invio!="") {
			if($ris_invio=="SUCCESS")
				$where_array[] = "a.MAIERR='000'";
			else if($ris_invio=="ERROR")
//				$where_array[] = "a.MAIERR<>'000'";
				$where_array[] = "a.MAIERR not in ('".implode("', '", $array_not_err)."')";
			else if($ris_invio=="NOTINV")
				$where_array[] = "a.MAIRIS=0";
		}
		
		// Allegati
		if($tipo_conv!="" || !empty($mod_conv) || !empty($mod_cls) || (isset($zip_src) && $zip_src=="S") || $atc_name!="") {
			$select_atc = "select count(b.ID)";
			$from_atc = " from FEMAILAL b";
			$where_atc = "";
			$where_array_atc = array();
			
			$where_array_atc[] = "a.ID=b.ID";
			
			// Tipo conversione
			if($tipo_conv!="")
				$where_array_atc[] = "b.TPCONV='$tipo_conv'";
			
			// Modello conversione
			if(!empty($mod_conv))
				$where_array_atc[] = "b.MAIMOD in ('".implode("', '", $mod_conv)."')";
			
			// Classi di conversione
			if(!empty($mod_cls)) {
				$sql_cls = "select MODNAM from SIR_MODULI where MODCLS in ('".implode("', '", $mod_cls)."')";
//				echo "SQL CLS: $sql_cls<br>";
				
				$res_cls = $db->query($sql_cls, false, 0);

				$mod_conv_array = array();
				while($row_cls = $db->fetch_array($res_cls)) {
					$mod_conv_array[] = $row_cls['MODNAM'];
				}
//				echo "MOD CONV ARRAY:<pre>"; print_r($mod_conv_array); echo "</pre>";
				
				$where_array_atc[] = "b.CONV='S'";
				$where_array_atc[] = "b.TPCONV='PDF'";
				$where_array_atc[] = "b.MAIMOD in ('".implode("', '", $mod_conv_array)."')";
			}
			
			// Presenza allegati compressi
			if(isset($zip_src) && $zip_src=="S")
				$where_array_atc[] = "b.FILZIP='S'";
			
			// Nome allegato
			if($atc_name!="") {
				$where_atc_name_cond = "(".where_text_condition($atc_option, $atc_name, "b.MAIATC")." or ".
					where_text_condition($atc_option, $atc_name, "b.MAIPAT")." or ".
					where_text_condition($atc_option, $atc_name, "b.MAINAM")." )";
				
				$where_array_atc[] = $where_atc_name_cond;
			}
			
			$where_atc = " where ".implode(" and ", $where_array_atc);
			
			$sql_atc = $select_atc.$from_atc.$where_atc;
//			echo "SQL ATC: $sql_atc<br>";
			
			$where_array[] = "($sql_atc)>0";
		}
		
		// Destinatario
		if($destinatario!="") {
			$select_dest = "select count(c.ID)";
			$from_dest = " from FEMAILDT c";
			$where_dest = "";
			$where_array_dest = array();
				
			$where_array_dest[] = "a.ID=c.ID";
				
			if($email_sel_option===true)
				$where_array_dest[] = where_text_condition($dest_option, $destinatario, "c.MAITOR");
			else 
				$where_array_dest[] = "c.MAITOR='$destinatario'";
					
			$where_dest = " where ".implode(" and ", $where_array_dest);
				
			$sql_dest = $select_dest.$from_dest.$where_dest;
//			echo "SQL DEST: $sql_dest<br>";
				
			$where_array[] = "($sql_dest)>0";
		}
		
		// Presenza Allegati con Contenuti Extra		
		if (isset($contents_src) && $contents_src!="") {
			$select_cnts = "select count(e.ID)";
			$from_cnts = " from FEMAILCT e";
			$where_cnts = "";
			$where_array_cnts = array();
				
			$where_array_cnts[] = "a.ID=e.ID";
			
			$where_cnts = " where ".implode(" and ", $where_array_cnts);
			
			$sql_cnts = $select_cnts.$from_cnts.$where_cnts;
//			echo "SQL CONTENTS: $sql_cnts<br>";
			
			if($contents_src=="S")
				$where_array[] = "($sql_cnts)>0";
			else if($contents_src=="N")
				$where_array[] = "($sql_cnts)=0";
		}
		
		// Presenza Invio MPX
		if((!isset($settings['enable_mpx']) || $settings['enable_mpx']===true) && (isset($mpx_src) && $mpx_src=="S")) {
			$select_mpx = "select count(d.ID)";
			$from_mpx = " from FMPXPARM d";
			$where_mpx = " where a.ID=d.ID";
			
			$sql_mpx = $select_mpx.$from_mpx.$where_mpx;
//			echo "SQL MPX: $sql_mpx<br>";
				
			$where_array[] = "(a.MAIMPX='S' or ($sql_mpx)>0)";
		}
		
		$where = "";
		if(!empty($where_array))
			$where = " where ".implode(" and ", $where_array);
		
		$sql = $select.$from.$where;
		echo "SQL: $sql<br>";
//die();		
		subfileDelete($azione."_LIST");
		
		$subfile = new wi400Subfile($db, $azione."_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("MONITOR_EMAIL_LIST");
		$subfile->setModulo("email");
		
		$subfile->setSql($sql);
	}
	else if($actionContext->getForm()=="ATC_LIST") {
		$actionContext->setLabel("Lista allegati");
		
		wi400Detail::cleanSession($azione."_ATC_DET");
		wi400Detail::cleanSession($azione."_NEW_ATC_DET");
		
		$sql = "select * from FEMAILAL where ID='$cod_id'";
		echo "SQL: $sql<br>";
		
		subfileDelete($azione."_ATC_LIST");
		
		$subfile = new wi400Subfile($db, $azione."_ATC_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("MONITOR_EMAIL_ATC_LIST");
		$subfile->setModulo("email");
		
		$subfile->setSql($sql);
	}
	else if($actionContext->getForm()=="DEST_LIST") {
		$actionContext->setLabel("Lista destinatari");
		
		wi400Detail::cleanSession($azione."_DEST_DET");
		wi400Detail::cleanSession($azione."_NEW_DEST_DET");
/*		
		$sql = "select * from FEMAILDT where ID='$id'";
		
		subfileDelete("MONITOR_EMAIL_LIST");
		
		$subfile = new wi400Subfile($db, "MONITOR_EMAIL_DEST_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("MONITOR_EMAIL_DEST_LIST");
		$subfile->setModulo("email");
		
		$subfile->setSql($sql);
*/
	}
	else if($actionContext->getForm()=="CONTENTS_LIST") {
		$actionContext->setLabel("Lista contenuti");
	
		wi400Detail::cleanSession($azione."_CONTENTS_DET");
		wi400Detail::cleanSession($azione."_NEW_CONTENTS_DET");
/*	
		$sql = "select * from FEMAILCT where ID='$id'";
		echo "SQL: $sql<br>";
	
		subfileDelete($azione."_CONTENTS_LIST");
	
		$subfile = new wi400Subfile($db, $azione."_ATC_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("MONITOR_EMAIL_ATC_LIST");
		$subfile->setModulo("email");

		$subfile->setSql($sql);
*/		
	}
	else if($actionContext->getForm()=="EMAIL_DET") {
		$actionContext->setLabel("Dettaglio e-mail");
		
		$sql = "select * from FPDFCONV where ID='$cod_id'";
		echo "SQL: $sql<br>";
		$result = $db->singleQuery($sql);
		if($result) {
			$row = $db->fetch_array($result);
		}
	}
	else if($actionContext->getForm()=="NEW_EMAIL_DET") {
		$actionContext->setLabel("Nuova e-mail");
	}
	else if($actionContext->getForm()=="ATC_DET") {
		$actionContext->setLabel("Dettaglio allegato");
		
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_ATC_LIST");
		
		$cod_id = $keyArray['ID'];
		$atc = $keyArray['MAIATC'];
		
		$sql = "select * from FEMAILAL a where ID='$cod_id' and MAIATC='$atc'";
		echo "SQL: $sql<br>";
		$result = $db->singleQuery($sql);
		if($result) {
			$row = $db->fetch_array($result);
		}
	}
	else if($actionContext->getForm()=="NEW_ATC_DET") {
		$actionContext->setLabel("Nuovo allegato");
	}
	else if($actionContext->getForm()=="DEST_DET") {
		$actionContext->setLabel("Dettaglio destinatario");
		
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_DEST_LIST");
		
		$cod_id = $keyArray['ID'];
		$to = $keyArray['MAITOR'];
		
		$sql = "select * from FEMAILDT a where ID='$cod_id' and MAITOR='$to'";
		echo "SQL: $sql<br>";
		$result = $db->singleQuery($sql);
		if($result) {
			$row = $db->fetch_array($result);
		}
	}
	else if($actionContext->getForm()=="NEW_DEST_DET") {
		$actionContext->setLabel("Nuovo destinatario");
	}
	else if($actionContext->getForm()=="CONTENTS_DET") {
		$actionContext->setLabel("Dettaglio contenuti");
	
		$keyArray = array();
		$keyArray = getListKeyArray($azione."_CONTENTS_LIST");
	
		$cod_id = $keyArray['ID'];
		$tipo = $keyArray['UCTTYP'];
	
		$sql = "select * from FEMAILCT a where ID='$cod_id' and UCTTYP='$tipo'";
		echo "SQL: $sql<br>";
		$result = $db->singleQuery($sql);
		if($result) {
			$row = $db->fetch_array($result);
		}
	}
	else if($actionContext->getForm()=="NEW_CONTENTS_DET") {
		$actionContext->setLabel("Nuovo contenuto");
	}
	else if($actionContext->getForm()=="ATC_PRV") {
		$keyArray = array();
		$keyArray = explode("|",$_REQUEST['DETAIL_KEY']);		
		echo "KEY ARRAY:<pre>"; print_r($keyArray); echo "</pre>";
/*		
		switch($_REQUEST['COLUMN_KEY']) {
			case "MAIATC":
				$file = $keyArray[1];
				break;
			case "MAIPAT":
				$file = $keyArray[2];
				break;
			case "MAINAM":
				$file = $keyArray[3];
				
				$file_path = dirname($file);
//				echo "FILE PATH: $file_path<br>";
				if($file_path==".") {
					$file_path = dirname($atc_param[2]);
					$file = $file_path."/".$file;
				}
				break;
		}
*/		
		$atc = $keyArray[1];
		$file_path = $keyArray[2];
		$file_name = $keyArray[3];
		
		$atc_array = array(
			"MAIATC" => $atc,
			"MAIPAT" => $file_path,
			"MAINAM" => $file_name
		);
		
		$col_key = $_REQUEST['COLUMN_KEY'];
		switch($col_key) {
			case "MAIATC":
				$file = $atc_array[$col_key];
				break;
			case "MAIPAT":
				$file = $atc_array[$col_key];
				break;
			case "MAINAM":
/*				
				$file = $keyArray[$col_key];
		
				$file_path = dirname($file);
//				echo "FILE PATH: $file_path<br>";
				if($file_path==".") {
					$file_path = dirname($atc_param[2]);
					$file = $file_path."/".$file;
				}
*/				
				$file = wi400invioConvert::get_file_rename($atc_array);
				break;
		}
//		echo "FILE: $file<br>";
		
		$temp = "";
		$TypeImage = "";
		$file_parts = pathinfo($file);
		if(isset($file_parts['extension']))
			$TypeImage = strtolower($file_parts['extension']);
	}
/*	
	else if($actionContext->getForm()=="ARC_PRV") {
		$keyArray = array();
		$keyArray = explode("|",$_GET['DETAIL_KEY']);
	
//		echo "KEY ARRAY:<pre>"; print_r($keyArray); echo "</pre>";

		$file = $keyArray[1];
//		echo "FILE: $file<br>";
	
		$temp = "";
		$TypeImage = "";
		$file_parts = pathinfo($file);
		if(isset($file_parts['extension']))
			$TypeImage = strtolower($file_parts['extension']);
	}
*/
	else if($actionContext->getForm()=="MPX_DET") {
		$actionContext->setLabel("Impostazoni MPX");
		
		$sql = "select * from FMPXPARM where ID='$cod_id'";
		echo "SQL: $sql<br>";
		$result = $db->singleQuery($sql);
		if($result) {
			$row_mpx = $db->fetch_array($result);
//			echo "ROW MPX:<pre>"; print_r($row_mpx); echo "</pre>";
		}
	}