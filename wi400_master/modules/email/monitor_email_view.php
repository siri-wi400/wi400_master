<?php

	$spacer = new wi400Spacer();
	
	if(in_array($actionContext->getForm(), array("EMAIL_LIST", "ATC_LIST", "DEST_LIST", "CONTENTS_LIST"))) {
		$ListDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET");
		$ListDetail->setColsNum(2);
		
		if($actionContext->getForm()=="EMAIL_LIST") {
			if($where=="") {
				$fieldDetail = new wi400Text("SELEZIONI");
				$fieldDetail->setLabel("Selezioni");
				$fieldDetail->setValue("TUTTE LE E-MAIL");
				$ListDetail->addField($fieldDetail);
			}
			else {			
				if(isset($data_inv_ini) && $data_inv_ini!="") {
					$fieldDetail = new wi400Text("PERIODO_INVIO");
					$fieldDetail->setLabel("Periodo invio");
					$fieldDetail->setValue("Dal $data_inv_ini al $data_inv_fin");
					$ListDetail->addField($fieldDetail);
				}
				
				if(isset($data_ris_ini) && $data_ris_ini!="") {
					$fieldDetail = new wi400Text("PERIODO_RIS");
					$fieldDetail->setLabel("Periodo rispedizione");
					$fieldDetail->setValue("Dal $data_ris_ini al $data_ris_fin");
					$ListDetail->addField($fieldDetail);
				}
				
				if(isset($id_array) && !empty($id_array)) {
					$fieldDetail = new wi400Text("ID_SEL");
					$fieldDetail->setLabel("ID");
					$fieldDetail->setValue(implode("<br>", $id_array));
					$ListDetail->addField($fieldDetail);
				}
				
				if(isset($id_ini) && $id_ini!="") {
					$fieldDetail = new wi400Text("ID_RANGE");
					$fieldDetail->setLabel("ID");
					$fieldDetail->setValue("Dal $id_ini al $id_fin");
					$ListDetail->addField($fieldDetail);
				}
				
				if(isset($user_array) && !empty($user_array)) {
					$fieldDetail = new wi400Text("USER");
					$fieldDetail->setLabel("Utenti");
					
					if(!isset($settings['mail_tab_abil']) || 
						(isset($settings['mail_tab_abil']) && in_array("JPROFADF", $settings['mail_tab_abil']))
					) {
						$sql_user = "select DSPRAD from ".$settings['lib_architect']."/JPROFADF	where NMPRAD=?";
						$stmt_user = $db->singlePrepare($sql_user);
						
						$des_utenti = array();
						foreach($user_array as $val) {
							$result_user = $db->execute($stmt_user, array($val));
							$row_user = $db->fetch_array($stmt_user);
							$des_utenti[] = $val." - ".$row_user['DSPRAD'];
						}
						$fieldDetail->setValue(implode("<br>", $des_utenti));
					}
					else {
//						$fieldDetail->setValue(implode("<br>", $user_array));

						$sql_user = "select FIRST_NAME!!' '!!LAST_NAME as DES_USER from $users_table where USER_NAME=?";
						$stmt_user = $db->singlePrepare($sql_user);
						
						$des_utenti = array();
						foreach($user_array as $val) {
							$result_user = $db->execute($stmt_user, array($val));
							$row_user = $db->fetch_array($stmt_user);
							$des_utenti[] = $val." - ".$row_user['DES_USER'];
						}
						$fieldDetail->setValue(implode("<br>", $des_utenti));
					}
					$ListDetail->addField($fieldDetail);
				}
				
				// Subject
				if(in_array($sbj_option, array("EMPTY", "NOT_EMPTY")) || (isset($subject) && $subject!="")) {
					$labelDetail = new wi400Text("SUBJECT");
					$labelDetail->setLabel("Oggetto");
					$labelDetail->setValue(get_text_condition_des($sbj_option, $subject));
					$ListDetail->addField($labelDetail);
				}
				
				if(isset($tipo_conv) && $tipo_conv!="") {
					$fieldDetail = new wi400Text("TPCONV");
					$fieldDetail->setLabel("Tipo conversione");
					$fieldDetail->setValue($tipo_conv);
					$ListDetail->addField($fieldDetail);
				}
				
				if(isset($mod_conv) && !empty($mod_conv)) {
					$fieldDetail = new wi400Text("MODCONV");
					$fieldDetail->setLabel("Modello conversione");
					$fieldDetail->setValue(implode("<br>", $mod_conv));
					$ListDetail->addField($fieldDetail);
				}
				
				if(isset($mod_cls) && !empty($mod_cls)) {
					$fieldDetail = new wi400Text("MODCONV");
					$fieldDetail->setLabel("Classe di conversione");
					$fieldDetail->setValue(implode("<br>", $mod_cls));
					$ListDetail->addField($fieldDetail);
				}
				
				// Nome allegato
				if(in_array($atc_option, array("EMPTY", "NOT_EMPTY")) || (isset($atc_name) && $atc_name!="")) {
					$labelDetail = new wi400Text("ALLEGATO");
					$labelDetail->setLabel("Nome allegato");
					$labelDetail->setValue(get_text_condition_des($atc_option, $atc_name));
					$ListDetail->addField($labelDetail);
				}
				
				if($email_sel_option===true) {
					// Mittente
					if(in_array($mit_option, array("EMPTY", "NOT_EMPTY")) || (isset($mittente) && $mittente!="")) {
						$labelDetail = new wi400Text("MITTENTE");
						$labelDetail->setLabel("Mittente");
						$labelDetail->setValue(get_text_condition_des($mit_option, $mittente));
						$ListDetail->addField($labelDetail);
					}
					
					// Destinatario
					if(in_array($dest_option, array("EMPTY", "NOT_EMPTY")) || (isset($destinatario) && $destinatario!="")) {
						$labelDetail = new wi400Text("DESTINATARI");
						$labelDetail->setLabel("Destinatari");
						$labelDetail->setValue(get_text_condition_des($dest_option, $destinatario));
						$ListDetail->addField($labelDetail);
					}
				}
				else {
					if(isset($mittente) && $mittente!="") {
						$labelDetail = new wi400Text("MITTENTE");
						$labelDetail->setLabel("Mittente");
						$labelDetail->setValue($mittente);
						$ListDetail->addField($labelDetail);
					}
					
					if(isset($destinatario) && $destinatario!="") {
						$labelDetail = new wi400Text("DESTINATARI");
						$labelDetail->setLabel("Destinatari");
						$labelDetail->setValue($destinatario);
						$ListDetail->addField($labelDetail);
					}
				}
				
				if(isset($ris_invio) && $ris_invio!="") {
					$fieldDetail = new wi400Text("RIS_INVIO");
					$fieldDetail->setLabel("Risultato invio");
					$fieldDetail->setValue($ris_invio_array[$ris_invio]);
					$ListDetail->addField($fieldDetail);
				}
				
//				if((isset($contents_src) && $contents_src=="S")) {
				if((isset($contents_src) && $contents_src!="")) {	
					$fieldDetail = new wi400Text("CONTENTS");
					$fieldDetail->setLabel("Contentuti extra");
//					$fieldDetail->setValue(_t('LABEL_YES'));
					$fieldDetail->setValue($pres_cnts_array[$contents_src]);
					$ListDetail->addField($fieldDetail);
				}
				
				if((isset($zip_src) && $zip_src=="S")) {
					$fieldDetail = new wi400Text("ZIP");
					$fieldDetail->setLabel("Presenza allegati compressi");
					$fieldDetail->setValue(_t('LABEL_YES'));
					$ListDetail->addField($fieldDetail);
				}
				
				if($enable_mpx===true && (isset($mpx_src) && $mpx_src=="S")) {
					$fieldDetail = new wi400Text("MPX_INVIO");
					$fieldDetail->setLabel("Presenza Impostazioni MPX");
					$fieldDetail->setValue(_t('LABEL_YES'));
					$ListDetail->addField($fieldDetail);
				}
			}
		}
		else {
			$fieldDetail = new wi400Text("ID_SEL");
			$fieldDetail->setLabel("ID");
			$fieldDetail->setValue($cod_id);
			$ListDetail->addField($fieldDetail);
		}
		
		$ListDetail->dispose();
		
		$spacer->dispose();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$searchAction = new wi400Detail($azione."_SRC", true);
		$searchAction->setTitle('Parametri');
		$searchAction->isEditable(true);
		$searchAction->setSaveDetail(true);
		
		// Data invio iniaziale
		$myField = new wi400InputText('DATA_INV_INI');
		$myField->setLabel('Data invio iniziale');
		$myField->addValidation('date');
		if(!isset($data_inv_ini))
			$myField->setValue(dateModelToView(date("Ymd")));
		else 
			$myField->setValue($data_inv_ini);
//		$myField->setSaveFile(false);				// @todo TEST del funzionamento di setSaveFile()
//		$myField->setOnChange("doSubmit('$azione', 'DEFAULT')");		// @todo TEST del funzionamento di onChange su campo data
		$searchAction->addField($myField);
		
		// Data invio finale
		$myField = new wi400InputText('DATA_INV_FIN');
		$myField->setLabel('Data invio finale');
		$myField->addValidation('date');
		if(!isset($data_inv_fin))
			$myField->setValue(dateModelToView(date("Ymd")));
		else 
			$myField->setValue($data_inv_fin);
		$searchAction->addField($myField);
		
		// Data rispedizione iniaziale
		$myField = new wi400InputText('DATA_RIS_INI');
		$myField->setLabel('Data rispedizione iniziale');
		$myField->addValidation('date');
		if(!isset($data_ris_ini) || trim($data_ris_ini)=="")
			$myField->setValue("");
		else
			$myField->setValue($data_ris_ini);
		$searchAction->addField($myField);
		
		// Data rispedizione finale
		$myField = new wi400InputText('DATA_RIS_FIN');
		$myField->setLabel('Data rispedizione finale');
		$myField->addValidation('date');
		if(!isset($data_ris_fin) || trim($data_ris_fin)=="")
			$myField->setValue("");
		else
			$myField->setValue($data_ris_fin);
		$searchAction->addField($myField);
		
		// ID specifici
		$myField = new wi400InputText('ID_SRC');
		$myField->setLabel('ID');
		$myField->setShowMultiple(true);
//		$myField->setOnChange("multiFieldAddRemove('ADD','ID_SRC', null, true)");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($id_array);
		$searchAction->addField($myField);
		
		// Da ID
		$myField = new wi400InputText('ID_INI');
		$myField->setLabel('Da ID');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($id_ini);
		$searchAction->addField($myField);
		
		// A ID
		$myField = new wi400InputText('ID_FIN');
		$myField->setLabel('A ID');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($id_fin);
		$searchAction->addField($myField);
		
		// Utente
		$myField = new wi400InputText('USER_SRC');
		$myField->setLabel("Utente");
		$myField->setShowMultiple(true);
//		$myField->setOnChange("multiFieldAddRemove('ADD','USER_SRC', null, true)");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($user_array);
		$myField->setInfo(_t('USER_CODE_INFO'));
		
		if(!isset($settings['mail_tab_abil']) ||
			(isset($settings['mail_tab_abil']) && in_array("JPROFADF", $settings['mail_tab_abil']))
		) {
			$decodeParameters = array(
				'TYPE' => 'common',
				'TABLE_NAME' => $settings['lib_architect']."/JPROFADF",
				'COLUMN' => 'DSPRAD',
				'KEY_FIELD_NAME' => 'NMPRAD',
//				'FILTER_SQL' => "DSPRAD not like 'SIRI - %'",
				'AJAX' => true
			);
			$myField->setDecode($decodeParameters);
			
			$myLookUp = new wi400LookUp("LU_GENERICO");
//			$myLookUp->addParameter("FILE",$settings['lib_architect']."/JPROF01L");
			$myLookUp->addParameter("FILE",$settings['lib_architect']."/JPROFADF");		// JPROFADF file fisico di indice JPROF01L
			$myLookUp->addParameter("CAMPO","NMPRAD");
			$myLookUp->addParameter("DESCRIZIONE","DSPRAD");
//			$myLookUp->addParameter("LU_WHERE","DSPRAD not like 'SIRI - %'");
//			$myLookUp->addParameter("ONCHANGE","multiFieldAddRemove('ADD','USER_SRC', null, true)");
//			$myLookUp->addParameter("QUERY_BETWEEN", false);
			$myField->setLookUp($myLookUp);
		}
		else {
			$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'LAST_NAME',
				'TABLE_NAME' => 'SIR_USERS',
				'KEY_FIELD_NAME' => 'USER_NAME',
				'ALLOW_NEW' => True,
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
			);
			$myField->setDecode($decodeParameters);
				
			$myLookUp =new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$users_table);
			$myLookUp->addParameter("CAMPO","USER_NAME");
			$myLookUp->addParameter("DESCRIZIONE","EMAIL");
			$myLookUp->addParameter("LU_SELECT", "FIRST_NAME|LAST_NAME");
			$myLookUp->addParameter("LU_AS_TITLES", "Nome|Cognome");
			$myField->setLookUp($myLookUp);
			$myField->setAutoFocus(True);
		}
		
		$searchAction->addField($myField);
		
		// Subject
		$myField = new wi400InputText('SUBJECT_SRC');
		$myField->setLabel("Oggetto");
		$myField->setSelOption(true);
		$myField->setMaxLength(60);
		$myField->setSize(60);
		$myField->setValue($subject);
		$searchAction->addField($myField);
		
		// Tipo di conversione
		$mySelect = new wi400InputSelect('TPCONV');
		$mySelect->setLabel("Tipo conversione");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($tipo_conv_array);
		$mySelect->setValue($tipo_conv);
		$searchAction->addField($mySelect);
/*		
		// Modello di conversione
		$mySelect = new wi400InputSelect('MODCONV');
		$mySelect->setLabel("Modello conversione");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($modelli_array);
		$mySelect->setValue($mod_conv);
		$searchAction->addField($mySelect);		
//		echo "MOD CONV OPTIONS:<pre>"; print_r($mySelect->getOptions()); echo "</pre>";
*/
		// Modello di conversione
		$myField = new wi400InputText('MODCONV');
		$myField->setLabel("Modello conversione");
		$myField->setShowMultiple(true);
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setValue($mod_conv);
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "SIR_MODULI",
			'COLUMN' => 'MODDES',
			'KEY_FIELD_NAME' => 'MODNAM',
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "SIR_MODULI");
		$myLookUp->addParameter("CAMPO", "MODNAM");
		$myLookUp->addParameter("DESCRIZIONE", "MODDES");
//		$myLookUp->addField("MODCONV");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Classe di conversione
/*
		$mySelect = new wi400InputSelect('MODCLS_SRC');
		$mySelect->setLabel("Classe di conversione");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($classi_conv_array);
		$mySelect->setValue($mod_cls);
		$searchAction->addField($mySelect);
*/
		$myField = new wi400InputText('MODCLS_SRC');
		$myField->setLabel("Classe di conversione");
//		$myField->addValidation("required");
		$myField->setShowMultiple(true);
		$myField->setValue($mod_cls);
		$myField->setCase("UPPER");
		$myField->setMaxLength(100);
		$myField->setSize(20);
//		$myField->setInfo('Inserire la Classe di conversione');
/*
		$path_classi = $base_path."/package/".$settings['package'].'/persconv';
		
		$myLookUp = new wi400LookUp("LU_DIR_LIST");
		$myLookUp->addParameter("FILE_PATHS", $path_classi);
		$myLookUp->addParameter("FILE_TYPES", "php");
		$myLookUp->addParameter("FULL_PATH", false);
		$myLookUp->addParameter("SHOW_INFO", false);
		$myLookUp->addParameter("LU_SELECT", "substr(FILE, 15) as CLASSE");
		$myLookUp->addParameter("LU_CAMPO", "CLASSE");
		$myLookUp->addParameter("LU_CAMPO_LABEL", "Classe<br>Conversione");
		$myField->setLookUp($myLookUp);
*/
		$myLookUp = new wi400LookUp("LU_MODELLI_CONV_PDF");
		$myField->setLookUp($myLookUp);
		
		$searchAction->addField($myField);
		
		// Nome allegato
		$myField = new wi400InputText('ALLEGATO_SRC');
		$myField->setLabel("Nome Allegato");
		$myField->setSelOption(true);
		$myField->setMaxLength(60);
		$myField->setSize(60);
		$myField->setValue($atc_name);
		$searchAction->addField($myField);
		
		// Mittente
		$myField = new wi400InputText('MITTENTE_SRC');
		$myField->setLabel("Mittente");
		$myField->addValidation('email');				//@todo SISTEMARE VALIDAZIONE EMAIL
		$myField->setSelOption($email_sel_option);		//@todo o setSelOption(true) o addValidation('email')
		$myField->setMaxLength(64);
		$myField->setSize(64);
		$myField->setValue($mittente);
		
		if(!isset($settings['mail_tab_abil']) ||
			(isset($settings['mail_tab_abil']) && in_array("JPROFADF", $settings['mail_tab_abil']))
		) {
			$myLookUp = new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$settings['lib_architect']."/JPROFADF");
			$myLookUp->addParameter("LU_FROM"," left join $users_table on USER_NAME=NMPRAD");
			$myLookUp->addParameter("CAMPO","EMAIL");
			$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
//			$myLookUp->addParameter("LU_WHERE","DSPRAD like 'SIRI - %'");
			$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");		
			$myLookUp->addParameter("LU_SELECT","DSPRAD");
			$myLookUp->addParameter("LU_AS_TITLES","Nome utente");		
			$myField->setLookUp($myLookUp);
		}
		
		$searchAction->addField($myField);
		
		// Destinatario
		$myField = new wi400InputText('DESTINATARIO_SRC');
		$myField->setLabel("Destinatario");
		$myField->addValidation('email');				//@todo SISTEMARE VALIDAZIONE EMAIL
		$myField->setSelOption($email_sel_option);		//@todo o setSelOption(true) o addValidation('email')
		$myField->setMaxLength(64);
		$myField->setSize(64);
		$myField->setValue($destinatario);
		
		if(!isset($settings['mail_tab_abil']) ||
			(isset($settings['mail_tab_abil']) && in_array("JPROFADF", $settings['mail_tab_abil']))
		) {
			$myLookUp = new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$settings['lib_architect']."/JPROFADF");
			$myLookUp->addParameter("LU_FROM"," left join $users_table on USER_NAME=NMPRAD");
			$myLookUp->addParameter("CAMPO","EMAIL");
			$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
//			$myLookUp->addParameter("LU_WHERE","DSPRAD like 'SIRI - %'");
			$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");	
			$myLookUp->addParameter("LU_SELECT","DSPRAD");
			$myLookUp->addParameter("LU_AS_TITLES","Nome utente");		
			$myField->setLookUp($myLookUp);
		}
		
		$searchAction->addField($myField);
		
		// Risultato invio (Riusciti/Falliti)
		$mySelect = new wi400InputSelect('RIS_INVIO');
		$mySelect->setLabel("Risultato invio");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($ris_invio_array);
		$mySelect->setValue($ris_invio);
		$searchAction->addField($mySelect);
/*		
		// Presenza contenuti
		$myField = new wi400InputSwitch("CONTENTS_SRC");
		$myField->setLabel("Presenza allegati con contentuti extra");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_contents);
		$myField->setValue(1);
		$searchAction->addField($myField);
*/		
		// Presenza contenuti
		$mySelect = new wi400InputSelect('CONTENTS_SRC');
		$mySelect->setLabel("Contentuti extra");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($pres_cnts_array);
		$mySelect->setValue($contents_src);
		$searchAction->addField($mySelect);
		
		// Presenza allegati compressi
		$myField = new wi400InputSwitch("ZIP_SRC");
		$myField->setLabel("Presenza allegati compressi");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		$myField->setChecked($check_zip);
//		$myField->setValue(1);
//		$myField->setValue("S");
		$searchAction->addField($myField);
		
		if($enable_mpx===true) {
			// Invio MPX
			$myField = new wi400InputSwitch("INVIO_MPX_SRC");
			$myField->setLabel("Presenza Impostazioni MPX");
			$myField->setOnLabel(_t('LABEL_YES'));
			$myField->setOffLabel(_t('LABEL_NO'));
			$myField->setChecked($check_mpx);
			$myField->setValue(1);
			$searchAction->addField($myField);
		}
/*		
		$mySelect = new wi400InputSelectCheckBox('PROVA');
		$mySelect->setLabel("Prova");
		$mySelect->setMultiple(true);
		$mySelect->setColsNumber(3);
		$mySelect->setOptions($tipo_dest_array);
		$mySelect->setValue($prova_sel);
		$searchAction->addField($mySelect);
*/		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Seleziona");
		$myButton->setAction($azione);
		$myButton->setForm("EMAIL_LIST");
		$myButton->setValidation(true);
		$searchAction->addButton($myButton);
		
		$myButton = new wi400InputButton('NEW_BUTTON');
		$myButton->setLabel("Nuova e-mail");
		$myButton->setAction($azione);
		$myButton->setForm("NEW_EMAIL_DET");
		$searchAction->addButton($myButton);
		
		$searchAction->dispose();
		
//		echo "DETAIL:<pre>"; print_r($searchAction); echo "</pre>";
	}
	else if($actionContext->getForm()=="EMAIL_LIST") {
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("ID desc");
		
		echo "SQL: ".$miaLista->getSql()."<br>";
		
		$miaLista->setSelection("MULTIPLE");
		
//		$miaLista->setCalculateTotalRows('FALSE');

		$miaLista->setIncludeFile(rtvModuloAzione($azione),"monitor_email_functions.php");
/*		
		$miaLista->setAutoUpdateList(True);		
		$miaLista->setCallBackFunction("updateRow", "functionUpdateRow_email");
*/
		$id_col = new wi400Column("ID","ID");

		$num_inv_col = new wi400Column("MAIRIS", "Numero<br>invii", "INTEGER", "right");
		$num_inv_cond = array();
		$num_inv_cond[] = array('EVAL:$row["MAIRIS"]>1', "wi400_grid_yellow");
		$num_inv_cond[] = array('EVAL:$row["MAIRIS"]==0', "wi400_grid_red");
		$num_inv_col->setStyle($num_inv_cond);
		
		$err_cond = array();
		$err_cond[] = array('EVAL:$row["MAIERR"]==""', "");
		$err_cond[] = array('EVAL:check_error($row["MAIERR"], "'.implode("|", $array_not_err).'", false)', "wi400_grid_green");
		$err_cond[] = array('EVAL:check_error($row["MAIERR"], "'.implode("|", $array_not_err).'", true)', "wi400_grid_red");
		
		$err_col = new wi400Column("MAIERR", "Codice<br>errore", "STRING", "center");
		$err_col->setStyle($err_cond);
		
		$err_msg_col = new wi400Column("MAIDER", "Messaggio di errore");
		$err_msg_col->setStyle($err_cond);

		$email_col = new wi400Column("MAIEMA", "Invio<br>E-mail", "STRING", "center");
/*
		$email_cond = array();
		$email_cond[] = array('EVAL:$row["MAIEMA"]<>"S"', "wi400_grid_red");
		$email_col->setStyle($email_cond);
*/				
		$email_col->setDecorator("YES_NO_ICO");
/*
		$inputField = new wi400InputCheckbox("SPUNTAE");
		$inputField->setUncheckedValue("N");
		$inputField->setValue("S");
		$email_col->setInput($inputField);
*/
		$mpx_col = new wi400Column("MAIMPX", "Invio<br>MPX", "STRING", "center");
		$mpx_col->setShow($enable_mpx);
/*
		$mpx_cond = array();
		$mpx_cond[] = array('EVAL:$row["MAIMPX"]=="S"', "wi400_grid_green");
		$mpx_col->setStyle($mpx_cond);
*/		
		$mpx_col->setDecorator("YES_NO_ICO");
		
		$mpx_cond = array();
		$mpx_cond[] = array('EVAL:$row["MAIMPX"]=="S"', "MPX_DETAIL");
		$mpx_cond[] = array('EVAL:1==1', "");
		$mpx_col->setActionListId($mpx_cond);
		
		$job_col = new wi400Column("MAIJOB", "Job");
		$job_col->setShow(false);
		
		$num_job_col = new wi400Column("MAINBR", "Numero<br>Job");
		$num_job_col->setShow(false);
		
		$num_job_col->setDetailAction("LOGCONV", "DEFAULT");
		$num_job_col->addDetailKey("MAINBR");
		
		$stato_col = new wi400Column("MAISTA", "Stato<br>record", "STRING", "center");
		$stato_col->setShow(false);

		$env_col = new wi400Column("MAIAMB", "Ambiente<br>generazione", "STRING", "center");
		$env_col->setShow(false);
		
		$window_col = new wi400Column("MAIWDW", "Window", "STRING", "center");
		$window_col->setShow(false);
		
		$free_col = new wi400Column("MAILIB", "Campo libero per usi futuri");
		$free_col->setShow(false);
		
		$icon_cols = array();
		foreach($action_icons_array as $key) {
			$val = $des_icons_array[$key];
			
			$col = new wi400Column($key, $val, "STRING", "center");
			$col->setActionListId($key);
			
			$cond = array();
			if($key=="CONTENTS")
				$cond[] = array('EVAL:check_email_contents($row["ID"])', $type_icons_array[$key]);
			else
				$cond[] = array('EVAL:1==1', $type_icons_array[$key]);
			
			$col->setDefaultValue($cond);		
			$col->setDecorator("ICONS");
			$col->setExportable(false);
			$col->setSortable(false);
			
			$icon_cols[$key] = $col;
		}
		
		$cols_1 = array($id_col);
		$cols_1 = array_merge($cols_1, $icon_cols);
/*		
		$prova_col = new wi400Column("PROVA_COL", "Prova");
		$prova_col->setDefaultValue('EVAL:prova()');
*/		
		$cols_2 = array(
//			$id_col,
			new wi400Column("MAIUSR", "Utente"),
			new wi400Column("DES_USR", "Descrizione Utente"),
			new wi400Column("MAISBJ", "Oggetto"),
			$err_col,
			$err_msg_col,
			$num_inv_col,
			new wi400Column("MAIFRM", "E-mail mittente"),
			new wi400Column("MAIALI", "Alias mittente"),
			new wi400Column("MAIINS", "Data inserimento", "COMPLETE_TIMESTAMP"),
			new wi400Column("MAIELA", "Data elaborazione", "COMPLETE_TIMESTAMP"),
			$email_col,
			$mpx_col,
			$job_col,
			$num_job_col,
			$stato_col,
			$env_col,
			$window_col,
			$free_col,
//				$prova_col
		);
		
		$list_cols = array_merge($cols_1, $cols_2);
		
		$miaLista->setCols($list_cols);
		
		// Chiavi di riga
		$miaLista->addKey("NREL");
		$miaLista->addKey("ID");
//		$miaLista->addKey("MAINBR");
		
//		hidden_fields();

		// Filtri
		$myFilter = new wi400Filter("ID","ID");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("MAIUSR","Utente");
		$myFilter->setFast(true);
		$myFilter->setCase('UPPER');
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("DES_USR","Descrizione Utente");
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("MAIFRM","E-mail mittente");
		$myFilter->addValidation('email');
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("MAISBJ","Oggetto");
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		// Errori
/*		
		$mioFiltro = new wi400Filter("MAIERR","Presenza errore","CHECK_STRING","<>'000'");
//		$mioFiltro->setLogicalOperator(wi400Filter::$LOGICAL_OPERATOR_OR);
		$miaLista->addFilter($mioFiltro);
*/
		$myFilter = new wi400Filter("MAIERR","Presenza errore","SELECT","");
		$filterValues = array(
			"(MAIERR<>'000' and MAIERR<>'')" => _t("SI"),
			"(MAIERR='000' or MAIERR='')" => _t("NO"),
		);
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		// Numero invii
		$myFilter = new wi400Filter("MAIRIS", "Numero invii", "NUMERIC");
		$miaLista->addFilter($myFilter);
		
		// E-mail
/*		
		$mioFiltro = new wi400Filter("MAIEMA","Invio E-mail","CHECK_STRING","='S'");
//		$mioFiltro->setLogicalOperator(wi400Filter::$LOGICAL_OPERATOR_OR);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("MAIEMA","NO Invio E-mail","CHECK_STRING","<>'S'");
		$mioFiltro->setId("NO_EMA");
//		$mioFiltro->setLogicalOperator(wi400Filter::$LOGICAL_OPERATOR_OR);
		$miaLista->addFilter($mioFiltro);
*/		
		$myFilter = new wi400Filter("MAIEMA","Invio E-mail","SELECT","");
		$filterValues = array(
			"MAIEMA='S'" => _t("SI"),
			"MAIEMA<>'S'" => _t("NO")
		);
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		// MPX
		if($enable_mpx===true) {
/*			
			$mioFiltro = new wi400Filter("MAIMPX","Invio MPX","CHECK_STRING","='S'");
//			$mioFiltro->setLogicalOperator(wi400Filter::$LOGICAL_OPERATOR_OR);
			$miaLista->addFilter($mioFiltro);
*/
			$myFilter = new wi400Filter("MAIMPX","Invio MPX","SELECT","");
			$filterValues = array(
				"MAIMPX='S'" => _t("SI"),
				"MAIMPX<>'S'" => _t("NO")
			);
			$myFilter->setSource($filterValues);
			$miaLista->addFilter($myFilter);
		}
		
		// Dettaglio
		$action = new wi400ListAction();
		$action->setId("DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("EMAIL_DET");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Allegati
		$action = new wi400ListAction();
		$action->setId("ALLEGATI");
		$action->setAction($azione);
		$action->setForm("ATC_LIST");
		$action->setLabel("Allegati");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Destinatari
		$action = new wi400ListAction();
		$action->setId("DESTINATARI");
		$action->setAction($azione);
		$action->setForm("DEST_LIST");
		$action->setLabel("Destinatari");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Contenuti Extra
		$action = new wi400ListAction();
		$action->setId("CONTENTS");
		$action->setAction($azione);
		$action->setForm("CONTENTS_LIST");
		$action->setLabel("Contenuti");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Log PDF archiviati
		$action = new wi400ListAction();
		$action->setAction("LOGCONV");
		$action->setForm("DEFAULT");
		$action->setLabel("Log PDF archiviati");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Nuova e-mail
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_EMAIL_DET");
		$action->setLabel("Nuova e-mail");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		// @todo E-mail editor
		$action = new wi400ListAction();
		$action->setAction("EMAIL_EDITOR");
		$action->setForm("DEFAULT");
		$action->setLabel("Email editor (WIP)");
		$action->setTarget("WINDOW");
		$action->setSelection("NONE");
		$action->addParameter("EMAIL_EDITOR_PARAMS", $email_editor_params);
		$miaLista->addAction($action);
		
		if($is_operatore===true) {
			// Elimina e-mail
			$action = new wi400ListAction();
			$action->setAction("EMAIL_DELETE");
			$action->setForm("DELETE_EMAIL");
			$action->setLabel("Elimina e-mails");
			$action->setSelection("MULTIPLE");
			$action->setConfirmMessage("Eliminare?");
			$miaLista->addAction($action);
		}
		
		// Invio E-mail
		$action = new wi400ListAction();
		$action->setAction("EMAIL_INVIO");
		$action->setForm("ESECUZIONE");
		$action->setLabel("ESECUZIONE");
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage("Eseguire?");
		$miaLista->addAction($action);
		
		// Invio E-mail (singola)
		$action = new wi400ListAction();
		$action->setId("ESEGUI");
		$action->setAction("EMAIL_INVIO");
		$action->setForm("ESECUZIONE");
		$action->setLabel("ESECUZIONE");
		$action->setSelection("SINGLE");
		$action->setConfirmMessage("Eseguire?");
		$action->setShow(false);
		$miaLista->addAction($action);
		
		// Inoltro E-mail
		$action = new wi400ListAction();
		$action->setId("INOLTRA");
		$action->setAction("EMAIL_INOLTRO");
		$action->setForm("DEFAULT");
		$action->setLabel("Inoltro e-mail");
		$action->setTarget("WINDOW");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Conversione
		$action = new wi400ListAction();
		$action->setAction("EMAIL_CONVERT");
		$action->setForm("CONVERTI_TUTTO");
		$action->setLabel("Conversione");
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage("Convertire?");
		$miaLista->addAction($action);
		
		// Conversione File
		$action = new wi400ListAction();
		$action->setId("CONVERTI");
		$action->setAction("EMAIL_CONVERT");
		$action->setForm("CONVERTI_TUTTO");
		$action->setLabel("Conversione File");
		$action->setSelection("SINGLE");
		$action->setConfirmMessage("Convertire?");
		$action->setShow(false);
		$miaLista->addAction($action);
		
		if(!isset($settings['enable_stampa']) || $settings['enable_stampa']===true) {
			// Stampa
			$action = new wi400ListAction();
			$action->setAction("EMAIL_STAMPA_SEL");
			$action->setForm("STAMPA_TUTTO");
			$action->setLabel("Stampa");
			$action->setSelection("MULTIPLE");
			$action->setTarget("WINDOW");
			$action->setConfirmMessage("Stampare?");
			$miaLista->addAction($action);
			
			// Stampa File
			$action = new wi400ListAction();
			$action->setId("PRINT");
			$action->setAction("EMAIL_STAMPA_SEL");
			$action->setForm("STAMPA_TUTTO");
			$action->setLabel("Stampa File");
			$action->setSelection("SINGLE");
			$action->setTarget("WINDOW");
			$action->setConfirmMessage("Stampare?");
			$action->setShow(false);
			$miaLista->addAction($action);
		}
		
		if($enable_mpx===true) {
			// Impostazioni MPX
			$action = new wi400ListAction();
			$action->setId("MPX_DETAIL");
			$action->setAction($azione);
			$action->setForm("MPX_DET");
			$action->setLabel("Impostazioni MPX");
			$action->setSelection("SINGLE");
			$miaLista->addAction($action);
			
			// Invio MPX
			$action = new wi400ListAction();
			$action->setAction("EMAIL_INVIO");
			$action->setForm("INVIO_MPX");
			$action->setLabel("Invio MPX");
			$action->setSelection("SINGLE");
			$action->setConfirmMessage("Inviare ad MPX?");
			$miaLista->addAction($action);
		}
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="ATC_LIST") {
		$miaLista = new wi400List($azione."_ATC_LIST", !$isFromHistory);
		
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("MAIATC");
		
		echo "SQL: ".$miaLista->getSql()."<br>";
		
		$miaLista->setSelection("MULTIPLE");
		
//		$miaLista->setCalculateTotalRows('FALSE');

		$miaLista->setIncludeFile(rtvModuloAzione($azione),"monitor_email_functions.php");

		$icon_cols = array();
		foreach($atc_icons_array as $key) {
			$val = $des_icons_array[$key];
			
			$col = new wi400Column($key, $val, "STRING", "center");
			$col->setActionListId($key);
				
			$cond = array();
			if($key=="DETTAGLIO")
				$cond[] = array('EVAL:1==1', $type_icons_array[$key]);
			if($key=="CONTENTS")
//				$cond[] = array('EVAL:($row["TPCONV"]=="BODY" && check_email_contents($row["ID"]))', $type_icons_array[$key]);
				$cond[] = array('EVAL:(check_email_contents($row["ID"]))', $type_icons_array[$key]);
			else
				$cond[] = array('EVAL:$row["TPCONV"]!="BODY"', $type_icons_array[$key]);
				
			$col->setDefaultValue($cond);
			$col->setDecorator("ICONS");
			$col->setExportable(false);
			$col->setSortable(false);
				
			$icon_cols[$key] = $col;
		}
		
		$atc_col = new wi400Column("MAIATC", "Allegato");
//		$atc_col->setDetailAction($azione."&CAMPO=atc", "ATC_PRV");
		$atc_col->setDetailAction($azione, "ATC_PRV");
//		$atc_col->setDetailUrlEncode(true);
		$atc_col->addDetailKey("ID");
		$atc_col->addDetailKey("MAIATC");
		$atc_col->addDetailKey("MAIPAT");
		$atc_col->addDetailKey("MAINAM");
		$miaLista->addCol($atc_col);
		
		$pat_col = new wi400Column("MAIPAT", "Path file convertito");
//		$pat_col->setDetailAction($azione."&CAMPO=pat", "ATC_PRV");
		$pat_col->setDetailAction($azione, "ATC_PRV");
//		$pat_col->setDetailUrlEncode(true);
		$pat_col->addDetailKey("ID");
		$pat_col->addDetailKey("MAIATC");
		$pat_col->addDetailKey("MAIPAT");
		$pat_col->addDetailKey("MAINAM");
		$miaLista->addCol($pat_col);
		
//		$nam_col = new wi400Column("MAINAM", "Ridenominazione allegato", "FILE_PATH_TO_FILE_NAME");
		$nam_col = new wi400Column("MAINAM", "Ridenominazione allegato");
//		$nam_col->setDetailAction($azione."&CAMPO=nam", "ATC_PRV");
		$nam_col->setDetailAction($azione, "ATC_PRV");
//		$nam_col->setDetailUrlEncode(true);
		$nam_col->addDetailKey("ID");
		$nam_col->addDetailKey("MAIATC");
		$nam_col->addDetailKey("MAIPAT");
		$nam_col->addDetailKey("MAINAM");
		$miaLista->addCol($nam_col);
/*				
//		$arc_col = new wi400Column("FILEARC", "File archiviato", "FILE_PATH_TO_FILE_NAME");
		$arc_col = new wi400Column("FILEARC", "File archiviato");
//		$arc_col->setDetailAction($azione."&CAMPO=nam", "ATC_PRV");
		$arc_col->setDetailAction($azione, "ARC_PRV");
//		$arc_col->setDetailUrlEncode(true);
		$arc_col->addDetailKey("ID");
		$arc_col->addDetailKey("FILEARC");
		$miaLista->addCol($arc_col);
*/				
		$ex_atc_col = new wi400Column("EX_ATC", "Esistenza<br>allegato", "STRING", "center");
		$ex_atc_col->setDecorator("YES_NO_ICO");
		
		$ex_pat_col = new wi400Column("EX_PAT", "Esistenza<br>file<br>convertito", "STRING", "center");
		$ex_pat_col->setDecorator("YES_NO_ICO");
		
		$ex_nam_col = new wi400Column("EX_NAM", "Esistenza<br>allegato<br>ridenominato", "STRING", "center");
		$ex_nam_col->setDecorator("YES_NO_ICO");
/*		
		$ex_arc_col = new wi400Column("EX_ARC", "Esistenza<br>file<br>archiviato", "STRING", "center");
		$ex_arc_col->setDecorator("YES_NO_ICO");
*/		
		$atc_size_col = new wi400Column("ATC_FILE_SIZE", "Dimensione<br>allegato", "STRING", "right");
		$atc_size_col->setDefaultValue('EVAL:format_size($row["ATC_DIM"], null, true)');
		$atc_size_col->setSortable(false);
		
		$pat_size_col = new wi400Column("PAT_FILE_SIZE", "Dimensione<br>file<br>convertito", "STRING", "right");
		$pat_size_col->setDefaultValue('EVAL:format_size($row["PAT_DIM"], null, true)');
		$pat_size_col->setSortable(false);
		
		$nam_size_col = new wi400Column("NAM_FILE_SIZE", "Dimensione<br>allegato<br>ridenominato", "STRING", "right");
		$nam_size_col->setDefaultValue('EVAL:format_size($row["NAM_DIM"], null, true)');
		$nam_size_col->setSortable(false);
/*		
		$arc_size_col = new wi400Column("ARC_FILE_SIZE", "Dimensione<br>file<br>archiviato", "STRING", "right");
		$arc_size_col->setDefaultValue('EVAL:format_size($row["ARC_DIM"], null, true)');
		$arc_size_col->setSortable(false);
*/		
		$zip_col = new wi400Column("FILZIP", "Zippare<br>allegato", "STRING", "center");
		$zip_col->setDecorator("YES_NO_ICO");
/*		
		$to_arc_col = new wi400Column("TO_ARC", "Da archiviare", "STRING", "center");
		$to_arc_col->setDecorator("YES_NO_ICO");
		
		$archived_col = new wi400Column("ARCHIVED", "Archiviato", "STRING", "center");
		$archived_col->setDecorator("YES_NO_ICO");
*/		
		$stampato_col = new wi400Column("MAISTO", "Stampato", "STRING", "center");
		$stampato_col->setDecorator("YES_NO_ICO");
		
		$conv_col = new wi400Column("CONV", "Convertire<br>allegato", "STRING", "center");
		$conv_col->setDecorator("YES_NO_ICO");
		
		$cols = array(
//			new wi400Column("ID","ID"),
			$atc_col,
			$ex_atc_col,
//			new wi400Column("ATC_DIM", "Dim.(Bytes)<br>allegato", "INTEGER", "right"),
			$atc_size_col,
//			new wi400Column("CONV", "Convertire<br>allegato", "STRING", "center"),
			$conv_col,
			new wi400Column("TPCONV", "Tipo<br>conversione"),
			new wi400Column("MAIMOD", "Modello<br>conversione"),
			new wi400Column("MAIARG", "Argomento<br>conversione"),
			$pat_col,
			$ex_pat_col,
//			new wi400Column("PAT_DIM", "Dim.(Bytes)<br>file convertito", "INTEGER", "right"),	
			$pat_size_col,
			$nam_col,
			$ex_nam_col,
//			new wi400Column("NAM_DIM", "Dim.(Bytes)<br>allegato ridenominato", "INTEGER", "right"),
			$nam_size_col,
			$zip_col,
			
//			$to_arc_col,		
//			$archived_col,	
/*				
			$arc_col,
			$ex_arc_col,	
//			new wi400Column("ARC_DIM", "Dim.(Bytes)<br>file archiviato", "INTEGER", "right"),
			$arc_size_col,
*/				
			$stampato_col,
			new wi400Column("MAIOUT", "Coda di stampa"),
			new wi400Column("MAISTT", "Data e ora<br>di stampa", "COMPLETE_TIMESTAMP")
		);
		
		$cols = array_merge($icon_cols, $cols);
		
		$miaLista->setCols($cols);
		
		// Chiavi di riga
		$miaLista->addKey("NREL");
		$miaLista->addKey("ID");
		$miaLista->addKey("MAIATC");
		
//		hidden_fields();

		// Filtri
		$myFilter = new wi400Filter("TPCONV","Tipo conversione","SELECT","");
//		$myFilter->setFast(true);
		$filterValues = array();
		foreach($tipo_conv_array as $key => $val) {
			$filterValues["TPCONV='$key'"] = $val;
		}
//		echo "FILTERS:"; print_r($filterValues); echo "<br>";
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("MAIMOD","Modello conversione","LOOKUP");
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "SIR_MODULI",
			'COLUMN' => 'MODDES',
			'KEY_FIELD_NAME' => 'MODNAM',
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myFilter->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "SIR_MODULI");
		$myLookUp->addParameter("CAMPO", "MODNAM");
		$myLookUp->addParameter("DESCRIZIONE", "MODDES");
		$myFilter->setLookUp($myLookUp);
		
		$miaLista->addFilter($myFilter);
		
		$mioFiltro = new wi400Filter("CONV","E-mails da convertire","CHECK_STRING","='S'");
//		$mioFiltro->setLogicalOperator(wi400Filter::$LOGICAL_OPERATOR_OR);
		$miaLista->addFilter($mioFiltro);
		
		// Dettaglio
		$action = new wi400ListAction();
		$action->setId("DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("ATC_DET");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Nuovo allegato
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_ATC_DET");
		$action->setLabel("Nuovo allegato");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		// Elimina allegato
		$action = new wi400ListAction();
		$action->setAction("EMAIL_DELETE");
		$action->setForm("DELETE_ATC");
		$action->setLabel("Elimina allegati");
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage("Eliminare?");
		$miaLista->addAction($action);
		
		// Converti
		$action = new wi400ListAction();
		$action->setAction("EMAIL_CONVERT");
		$action->setForm("CONVERTI_SEL");
		$action->setLabel("Converti");
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage("Convertire i file selezionati?");
		$miaLista->addAction($action);
		
		// Converti tutto
		$action = new wi400ListAction();
		$action->setAction("EMAIL_CONVERT");
		$action->setForm("CONVERTI_TUTTO");
		$action->setLabel("Converti tutto");
		$action->setSelection("NONE");
		$action->setConfirmMessage("Convertire tutto?");
		$miaLista->addAction($action);
		
		// Converti File
		$action = new wi400ListAction();
		$action->setId("CONVERTI");
		$action->setAction("EMAIL_CONVERT");
		$action->setForm("CONVERTI_SEL");
		$action->setLabel("Converti File");
		$action->setSelection("SINGLE");
		$action->setConfirmMessage("Convertire il file selezionato?");
		$action->setShow(false);
		$miaLista->addAction($action);
		
		if(!isset($settings['enable_stampa']) || $settings['enable_stampa']===true) {
			// Stampa
			$action = new wi400ListAction();
			$action->setAction("EMAIL_STAMPA_SEL");
			$action->setForm("STAMPA_SEL");
			$action->setTarget("WINDOW");
			$action->setLabel("Stampa");
			$action->setSelection("MULTIPLE");
			$action->setConfirmMessage("Stampare i files selezionati?");
			$miaLista->addAction($action);
			
			// Stampa Tutto
			$action = new wi400ListAction();
			$action->setAction("EMAIL_STAMPA_SEL");
			$action->setForm("STAMPA_TUTTO");
			$action->setTarget("WINDOW");
			$action->setLabel("Stampa Tutto");
			$action->setSelection("NONE");
			$action->setConfirmMessage("Stampare Tutto?");
			$miaLista->addAction($action);
			
			// Stampa File
			$action = new wi400ListAction();
			$action->setId("PRINT");
			$action->setAction("EMAIL_STAMPA_SEL");
			$action->setForm("STAMPA_SEL");
			$action->setTarget("WINDOW");
			$action->setLabel("Stampa");
			$action->setSelection("SINGLE");
			$action->setConfirmMessage("Stampare il file selezionato?");
			$action->setShow(false);
			$miaLista->addAction($action);
		}
		
		// Contenuti Extra
		$action = new wi400ListAction();
		$action->setId("CONTENTS");
		$action->setAction($azione);
		$action->setForm("CONTENTS_LIST");
		$action->setLabel("Contenuti");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Log PDF archiviati
		$action = new wi400ListAction();
		$action->setAction("LOGCONV");
		$action->setForm("DEFAULT");
		$action->setLabel("Log PDF archiviati");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="DEST_LIST") {
		$miaLista = new wi400List($azione."_DEST_LIST", !$isFromHistory);
/*		
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
*/		
		$select = "a.*";
		
		$matpto_cond = array();
		foreach($tipo_dest_array as $key => $val) {
			$matpto_cond[] = "when MATPTO='$key' then '$val'";
		}
		$select .= ", (case ".implode(" ", $matpto_cond)." end) as DES_TPTO";
		
		$miaLista->setField($select);
		$miaLista->setFrom("FEMAILDT a");
		$miaLista->setWhere("ID='$cod_id'");		
		$miaLista->setOrder("MATPTO desc, MAITOR");
		
		echo "SQL: ".$miaLista->getSql()."<br>";
		
		$miaLista->setSelection("MULTIPLE");
		
//		$miaLista->setCalculateTotalRows('FALSE');

//		$tipo_dest_col = new wi400Column("MATPTO", "Tipo<br>destinatario");
		
		$tipo_dest_cond = array();
		foreach($tipo_dest_colors as $key => $val) {
			$tipo_dest_cond[] = array('EVAL:$row["MATPTO"]=="'.$key.'"', $val);
		}
		
//		$tipo_dest_col->setStyle($tipo_dest_cond);
		
		$des_tipo_dest_col = new wi400Column("DES_TPTO", "Tipo<br>destinatario");
/*		
		$tipo_dest_values_cond = array();
		foreach($tipo_dest_array as $key => $val) {
			$tipo_dest_values_cond[] = array('EVAL:$row["MATPTO"]=="'.$key.'"', $val);
		}
		
		$des_tipo_dest_col->setDefaultValue($tipo_dest_values_cond);
*/
		$des_tipo_dest_col->setStyle($tipo_dest_cond);
		
		$dettaglio_col = new wi400Column("DETTAGLIO", $des_icons_array["DETTAGLIO"], "STRING", "center");
		$dettaglio_col->setActionListId("DETTAGLIO");
		
		$cond = array();
		$cond[] = array('EVAL:1==1', $type_icons_array["DETTAGLIO"]);
		
		$dettaglio_col->setDefaultValue($cond);
		$dettaglio_col->setDecorator("ICONS");
		$dettaglio_col->setExportable(false);
		$dettaglio_col->setSortable(false);
		
		$miaLista->setCols(array(
			$dettaglio_col,
//			new wi400Column("ID","ID"),
			new wi400Column("MAITOR", "E-mail destinatario"),
			new wi400Column("MAIALI", "Alias destinatario"),
//			$tipo_dest_col,
			$des_tipo_dest_col
		));
		
		// Chiavi di riga
		$miaLista->addKey("NREL");
		$miaLista->addKey("ID");
		$miaLista->addKey("MAITOR");
		
//		hidden_fields();

		// Filtri
		$myFilter = new wi400Filter("MAITOR","E-mail destinatario");
		$myFilter->addValidation('email');
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);

		$myFilter = new wi400Filter("MATPTO","Tipo destinatario","SELECT","");
		$filterValues = array();
		foreach($tipo_dest_array as $key => $val) {
			$filterValues["MATPTO='$key'"] = $val;
		}
//		echo "FILTERS:"; print_r($filterValues); echo "<br>";
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("MAIALI","Alias destinatario");
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
		
		// Dettaglio
		$action = new wi400ListAction();
		$action->setId("DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("DEST_DET");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Nuovo destinatario
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_DEST_DET");
		$action->setLabel("Nuovo destinatario");
		$action->setSelection("NONE");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);
		
		// Elimina destinatario
		$action = new wi400ListAction();
		$action->setAction("EMAIL_DELETE");
		$action->setForm("DELETE_DEST");
		$action->setLabel("Elimina destinatari");
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage("Eliminare?");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="CONTENTS_LIST") {
		$miaLista = new wi400List($azione."_CONTENTS_LIST", !$isFromHistory);
/*
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
*//*
		$select = "a.*";
		
		$matpto_cond = array();
		foreach($tipo_contents_array as $key => $val) {
			$matpto_cond[] = "when UCTTYP='$key' then '$val'";
		}
		$select .= ", (case ".implode(" ", $matpto_cond)." end) as DES_TIPC";
		
		$miaLista->setField($select);
*/		
		$miaLista->setFrom("FEMAILCT a");
		$miaLista->setWhere("ID='$cod_id'");
		$miaLista->setOrder("UCTTYP");
	
		echo "SQL: ".$miaLista->getSql()."<br>";
	
		$miaLista->setSelection("MULTIPLE");
	
//		$miaLista->setCalculateTotalRows('FALSE');

		$dettaglio_col = new wi400Column("DETTAGLIO", $des_icons_array["DETTAGLIO"], "STRING", "center");
		$dettaglio_col->setActionListId("DETTAGLIO");
	
		$cond = array();
		$cond[] = array('EVAL:1==1', $type_icons_array["DETTAGLIO"]);
	
		$dettaglio_col->setDefaultValue($cond);
		$dettaglio_col->setDecorator("ICONS");
		$dettaglio_col->setExportable(false);
		$dettaglio_col->setSortable(false);
		
		$tipo_cnt_cond = array();
		foreach($tipo_contents_colors as $key => $val) {
			$tipo_cnt_cond[] = array('EVAL:$row["UCTTYP"]=="'.$key.'"', $val);
		}
/*		
		$des_tipo_cnt_col = new wi400Column("DES_TIPC", "Tipo<br>contenuto");
		$des_tipo_cnt_col->setStyle($tipo_cnt_cond);
*/
		$tipo_col = new wi400Column("UCTTYP", "Tipo<br>contenuto");
		$tipo_col->setStyle($tipo_cnt_cond);
		
		$miaLista->setCols(array(
			$dettaglio_col,
//			new wi400Column("ID","ID"),
			new wi400Column("UCTTYP", "Tipo<br>contenuto"),
			$tipo_col,
//			$des_tipo_cnt_col,
//			new wi400Column("UCTRIG", "Riga<br>contenuto"),
			new wi400Column("UCTKEY", "Contenuto"),
		));
	
		// Chiavi di riga
		$miaLista->addKey("NREL");
		$miaLista->addKey("ID");
		$miaLista->addKey("UCTTYP");
//		$miaLista->addKey("UCTRIG");
	
//		hidden_fields();

		// Filtri
/*	
		$myFilter = new wi400Filter("UCTTYP","Tipo contenuto");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
*/		
		$myFilter = new wi400Filter("UCTTYP","Tipo contenuti","SELECT","");
//		$myFilter->setFast(true);
		$filterValues = array();
		foreach($tipo_contents_array as $key => $val) {
			$filterValues["UCTTYP='$key'"] = $val;
		}
//		echo "FILTERS:"; print_r($filterValues); echo "<br>";
		$myFilter->setSource($filterValues);
		$miaLista->addFilter($myFilter);
		
		$myFilter = new wi400Filter("UCTKEY","Contenuto");
		$myFilter->setFast(true);
		$myFilter->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($myFilter);
	
		// Dettaglio
		$action = new wi400ListAction();
		$action->setId("DETTAGLIO");
		$action->setAction($azione);
		$action->setForm("CONTENTS_DET");
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
	
		// Nuovo destinatario
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("NEW_CONTENTS_DET");
		$action->setLabel("Nuovo contenuto");
		$action->setSelection("NONE");
		$action->setTarget("WINDOW");
		$miaLista->addAction($action);
	
		// Elimina destinatario
		$action = new wi400ListAction();
		$action->setAction("EMAIL_DELETE");
		$action->setForm("DELETE_CONTENTS");
		$action->setLabel("Elimina contenuti");
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage("Eliminare?");
		$miaLista->addAction($action);
	
		listDispose($miaLista);
	}
	else if(in_array($actionContext->getForm(), array("EMAIL_DET", "NEW_EMAIL_DET"))) {
		$idDetail = $azione."_".$actionContext->getForm();
		
		$actionDetail = new wi400Detail($idDetail, false);
		
		if(in_array($actionContext->getForm(),array("EMAIL_DET"))) {
			if(!existDetail($idDetail)) {
				$email = false;
				$mpx = false;
				
				// caricamento dei dati della chiamata recuperati dal subfile
				$actionDetail->setSource($row);
				
				if($row['MAIEMA']=="S")
					$email = true;
				
				if($row['MAIMPX']=="S")
					$mpx = true;
			}
		}
		
		$actionDetail->setSaveDetail(true);

		// @todo Nel caso di "Nuova E-mail" sarebbe da saltare l'ID in modo che venga assegnato automaticamente durante il salvataggio
		if($actionContext->getForm()=="EMAIL_DET") {
			// ID
			$myField = new wi400InputText('ID');
			$myField->setLabel('ID');
			$myField->addValidation('required');
			if($actionContext->getForm()=="EMAIL_DET") {
				$myField->setReadonly(true);
			}
			else {
				$myField->setInfo("L'ID deve essere LUNGO 10 caratteri e il PRIMO deve essere una LETTERA mentre gli ALTRI dei NUMERI");
			}
			$myField->setSize(10);
			$myField->setMaxLength(10);
			$actionDetail->addField($myField);
		}
		
		// Utente
		$myField = new wi400InputText('MAIUSR');
		$myField->setLabel("Utente");
		$myField->addValidation('required');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		$myField->setInfo(_t('USER_CODE_INFO'));
		
		if(!isset($settings['mail_tab_abil']) ||
			(isset($settings['mail_tab_abil']) && in_array("JPROFADF", $settings['mail_tab_abil']))
		) {
			$decodeParameters = array(
				'TYPE' => 'common',
				'TABLE_NAME' => $settings['lib_architect']."/JPROFADF",
				'COLUMN' => 'DSPRAD',
				'KEY_FIELD_NAME' => 'NMPRAD',
//				'FILTER_SQL' => "DSPRAD not like 'SIRI - %'",
				'AJAX' => true
			);
			$myField->setDecode($decodeParameters);
			
			$myLookUp = new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$settings['lib_architect']."/JPROFADF");
			$myLookUp->addParameter("CAMPO","NMPRAD");
			$myLookUp->addParameter("DESCRIZIONE","DSPRAD");
//			$myLookUp->addParameter("LU_WHERE","DSPRAD not like 'SIRI - %'");
			$myField->setLookUp($myLookUp);
		}
		else {
			$decodeParameters = array(
				'TYPE'=> 'common',
				'COLUMN' => 'LAST_NAME',
				'TABLE_NAME' => 'SIR_USERS',
				'KEY_FIELD_NAME' => 'USER_NAME',
				'ALLOW_NEW' => True,
				'AJAX' => true,
				'COMPLETE' => true,
				'COMPLETE_MIN' => 2,
				'COMPLETE_MAX_RESULT' => 15
			);
			$myField->setDecode($decodeParameters);
				
			$myLookUp =new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$users_table);
			$myLookUp->addParameter("CAMPO","USER_NAME");
			$myLookUp->addParameter("DESCRIZIONE","EMAIL");
			$myLookUp->addParameter("LU_SELECT", "FIRST_NAME|LAST_NAME");
			$myLookUp->addParameter("LU_AS_TITLES", "Nome|Cognome");
			$myField->setLookUp($myLookUp);
			$myField->setAutoFocus(True);
		}
		
		$actionDetail->addField($myField);
		
		// Mittente
		$myField = new wi400InputText('MAIFRM');
		$myField->setLabel("Mittente");
		$myField->addValidation('email');
		$myField->addValidation('required');
		$myField->setMaxLength(64);
		$myField->setSize(64);
		
		if(!isset($settings['mail_tab_abil']) ||
			(isset($settings['mail_tab_abil']) && in_array("JPROFADF", $settings['mail_tab_abil']))
		) {
			$myLookUp = new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$settings['lib_architect']."/JPROFADF");
			$myLookUp->addParameter("LU_FROM"," left join $users_table on USER_NAME=NMPRAD");
			$myLookUp->addParameter("CAMPO","EMAIL");
			$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
//			$myLookUp->addParameter("LU_WHERE","DSPRAD like 'SIRI - %'");
			$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");
		
			$myLookUp->addParameter("LU_SELECT","DSPRAD");
			$myLookUp->addParameter("LU_AS_TITLES","Nome utente");
		
			$myField->setLookUp($myLookUp);
		}
		
		$actionDetail->addField($myField);
		
		// Alias Mittente
		$myField = new wi400InputText('MAIALI');
		$myField->setLabel("Alias mittente");
//		$myField->addValidation('required');
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$actionDetail->addField($myField);
		
		// Subject
		$myField = new wi400InputText('MAISBJ');
		$myField->setLabel("Oggetto");
		$myField->addValidation('required');
		$myField->setMaxLength(60);
		$myField->setSize(60);
		$actionDetail->addField($myField);
		
		// Invio E-mail
		$myField = new wi400InputSwitch("INVIO_EMAIL");
		$myField->setLabel("Invio e-mail");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		if(isset($email))
			$myField->setChecked($email);
		$myField->setValue("S");
		$actionDetail->addField($myField);
		
		if($enable_mpx===true) {
			// Invio MPX
			$myField = new wi400InputSwitch("INVIO_MPX");
			$myField->setLabel("Invio MPX");
			$myField->setOnLabel(_t('LABEL_YES'));
			$myField->setOffLabel(_t('LABEL_NO'));
			if(isset($mpx))
				$myField->setChecked($mpx);
			$myField->setValue("S");
			$actionDetail->addField($myField);
		}
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction("EMAIL_SAVE");
		if($actionContext->getForm()=="EMAIL_DET")
			$myButton->setForm("UPDT_EMAIL");
		else if($actionContext->getForm()=="NEW_EMAIL_DET")
			$myButton->setForm("INS_EMAIL");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);		
		$actionDetail->addButton($myButton);
		
		if($actionContext->getForm()=="EMAIL_DET") {
			// Elimina
			$myButton = new wi400InputButton('CANCEL_BUTTON');
			$myButton->setLabel("Elimina");
			$myButton->setAction("EMAIL_DELETE");
			$myButton->setForm("DELETE_EMAIL");
			$myButton->setConfirmMessage("Eliminare?");
			$actionDetail->addButton($myButton);
		}
		
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction($azione);
//		$myButton->setForm("EMAIL_LIST");
		$myButton->setForm($last_form);
		$actionDetail->addButton($myButton);
		
		$actionDetail->dispose();
	}
	else if(in_array($actionContext->getForm(), array("ATC_DET", "NEW_ATC_DET"))) {
		$idDetail = $azione."_".$actionContext->getForm();
		
		$actionDetail = new wi400Detail($idDetail, false);
		
		if(in_array($actionContext->getForm(),array("ATC_DET"))) {
			if(!existDetail($idDetail)) {
				$conv = false;
				$zip = false;
				$stampato = false;
				
				// caricamento dei dati della chiamata recuperati dal subfile
				$actionDetail->setSource($row);
				
				if($row['CONV']=="S")
					$conv = true;
				
				if($row['FILZIP']=="S")
					$zip = true;
				
				if($row['MAISTO']=="S")
					$stampato = true;
			}
		}
		
		$actionDetail->setSaveDetail(true);

		// ID
		$myField = new wi400InputText('ID');
		$myField->setLabel('ID');
		$myField->setReadonly(true);
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setValue($cod_id);
		$actionDetail->addField($myField);
		
		// Allegato
		$myField = new wi400InputText('MAIATC');
		$myField->setLabel('Allegato');
		$myField->addValidation('required');
		$myField->setSize(100);
		$myField->setMaxLength(100);
		$myField->setInfo("L'intero path");
		$actionDetail->addField($myField);
		
		// Da convertire
		$myField = new wi400InputSwitch("CONVERSIONE");
		$myField->setLabel("Convertire allegato");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		if(isset($conv))
			$myField->setChecked($conv);
		$myField->setValue("S");
		$actionDetail->addField($myField);
		
		// Tipo di conversione
		$mySelect = new wi400InputSelect('TPCONV');
		$mySelect->setLabel("Tipo conversione");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($tipo_conv_array);
		$actionDetail->addField($mySelect);
/*
		// Modello di conversione
		$mySelect = new wi400InputSelect('MAIMOD');
		$mySelect->setLabel("Modello conversione");
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($modelli_array);
		$mySelect->setValue($mod_conv);
		$actionDetail->addField($mySelect);
//		echo "MOD CONV OPTIONS:<pre>"; print_r($mySelect->getOptions()); echo "</pre>";
*/
		// Modello di conversione
		$myField = new wi400InputText('MAIMOD');
		$myField->setLabel("Modello conversione");
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setCase("UPPER");
		
		$decodeParameters = array(
			'TYPE' => 'common',
			'TABLE_NAME' => "SIR_MODULI",
			'COLUMN' => 'MODDES',
			'KEY_FIELD_NAME' => 'MODNAM',
			'AJAX' => true,
			'COMPLETE' => true,
			'COMPLETE_MIN' => 2,
			'COMPLETE_MAX_RESULT' => 15
		);
		$myField->setDecode($decodeParameters);
		
		$myLookUp = new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE", "SIR_MODULI");
		$myLookUp->addParameter("CAMPO", "MODNAM");
		$myLookUp->addParameter("DESCRIZIONE", "MODDES");
//		$myLookUp->addField("MODCONV");
		$myField->setLookUp($myLookUp);
		
		$actionDetail->addField($myField);
		
		// Argomento conversione
		$myField = new wi400InputText('MAIARG');
		$myField->setLabel('Argomento conversione');
//		$myField->addValidation('required');
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setInfo("L'intero path");
		$actionDetail->addField($myField);
		
		// Path file convertito
		$myField = new wi400InputText('MAIPAT');
		$myField->setLabel('Path file convertito');
//		$myField->addValidation('required');
		$myField->setSize(100);
		$myField->setMaxLength(100);
		$myField->setInfo("L'intero path");
		$actionDetail->addField($myField);
		
		// Ridenominazione allegato
		$myField = new wi400InputText('MAINAM');
		$myField->setLabel('Ridenominazione allegato');
//		$myField->addValidation('required');
		$myField->setSize(100);
		$myField->setMaxLength(100);
		$myField->setInfo("Solo il file name");
		$actionDetail->addField($myField);
		
		// Da zippare
		$myField = new wi400InputSwitch("ZIP");
		$myField->setLabel("Zippare allegato");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		if(isset($zip))
			$myField->setChecked($zip);
		$myField->setValue("S");
		$actionDetail->addField($myField);
		
		// Stampato
		if(!isset($settings['enable_stampa']) || $settings['enable_stampa']===true) {
			$myField = new wi400InputSwitch("STAMPATO");
			$myField->setLabel("Stampato");
			$myField->setOnLabel(_t('LABEL_YES'));
			$myField->setOffLabel(_t('LABEL_NO'));
			if(isset($stampato))
				$myField->setChecked($stampato);
			$myField->setValue("S");
			$actionDetail->addField($myField);
		}
		else {
			$myField = new wi400Text('STAMPA');
			$myField->setLabel("Stampato");
			if(isset($row)) {
				if($row['MAISTO']=="S")
					$myField->setValue(_t('LABEL_YES'));
				else
					$myField->setValue(_t('LABEL_NO'));
			}
			$actionDetail->addField($myField);
		}
		
		// Coda di stampa
		$myField = new wi400InputText('MAIOUT');
		$myField->setLabel("Coda di stampa");
		$myField->setInfo(_t('SPOOL_OUTQ_INFO'));
		if(isset($outq) && $outq!="")
			$myField->setValue($outq);
		
		$myLookUp = new wi400LookUp("LU_OBJECT");
		$myLookUp->addField("OUTQ");
		$myLookUp->addParameter("OBJTYPE", "*OUTQ");
		$myField->setLookUp($myLookUp);
		
		$decodeParameters = array(
			'TYPE' => 'i5_object',
			'OBJTYPE' => '*OUTQ',
			'AJAX' => true
		);
		$myField->setDecode($decodeParameters);
		
		$actionDetail->addField($myField);
		
		// Data e ora di stampa
		$myField = new wi400Text('MAISTT');
		$myField->setLabel("Data e ora stampa");
		$myField->setValue(wi400_format_COMPLETE_TIMESTAMP($row['MAISTT']));
		$actionDetail->addField($myField);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction("EMAIL_SAVE");
		if($actionContext->getForm()=="ATC_DET")
			$myButton->setForm("UPDT_ATC");
		else if($actionContext->getForm()=="NEW_ATC_DET")
			$myButton->setForm("INS_ATC");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		
		if($actionContext->getForm()=="ATC_DET") {
			// Elimina
			$myButton = new wi400InputButton('CANCEL_BUTTON');
			$myButton->setLabel("Elimina");
			$myButton->setAction("EMAIL_DELETE");
			$myButton->setForm("DELETE_ATC");
			$myButton->setConfirmMessage("Eliminare?");
			$actionDetail->addButton($myButton);
		}
		
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction($azione);
		$myButton->setForm("ATC_LIST");
		$actionDetail->addButton($myButton);
		
		$actionDetail->dispose();
	}
	else if(in_array($actionContext->getForm(), array("DEST_DET", "NEW_DEST_DET"))) {
		$idDetail = $azione."_".$actionContext->getForm();
		
		$actionDetail = new wi400Detail($idDetail, false);
		
		if(in_array($actionContext->getForm(),array("DEST_DET"))) {
			if(!existDetail($idDetail)) {
				// caricamento dei dati della chiamata recuperati dal subfile
				$actionDetail->setSource($row);
			}
		}
		
		$actionDetail->setSaveDetail(true);

		// ID
		$myField = new wi400InputText('ID');
		$myField->setLabel('ID');
		$myField->setReadonly(true);
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setValue($cod_id);
		$actionDetail->addField($myField);
		
		// Destinatario
		$myField = new wi400InputText('MAITOR');
		$myField->setLabel("Destinatario");
		$myField->addValidation('email');
		$myField->addValidation('required');
		$myField->setMaxLength(64);
		$myField->setSize(64);
		
		if(!isset($settings['mail_tab_abil']) ||
			(isset($settings['mail_tab_abil']) && in_array("JPROFADF", $settings['mail_tab_abil']))
		) {
			$myLookUp = new wi400LookUp("LU_GENERICO");
			$myLookUp->addParameter("FILE",$settings['lib_architect']."/JPROFADF");
			$myLookUp->addParameter("LU_FROM"," left join $users_table on USER_NAME=NMPRAD");
			$myLookUp->addParameter("CAMPO","EMAIL");
			$myLookUp->addParameter("DESCRIZIONE","USER_NAME");
//			$myLookUp->addParameter("LU_WHERE","DSPRAD like 'SIRI - %'");
			$myLookUp->addParameter("LU_ORDER","USER_NAME ASC");
		
			$myLookUp->addParameter("LU_SELECT","DSPRAD");
			$myLookUp->addParameter("LU_AS_TITLES","Nome utente");
			
			$myField->setLookUp($myLookUp);
		}
		
		$actionDetail->addField($myField);
		
		// Alias Destinatario
		$myField = new wi400InputText('MAIALI');
		$myField->setLabel("Alias destinatario");
//		$myField->addValidation('required');
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$actionDetail->addField($myField);
		
		// Tipo destinatario
		$mySelect = new wi400InputSelect('MATPTO');
		$mySelect->setLabel("Tipo destinatario");
		$mySelect->addValidation('required');
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($tipo_dest_array);
		$actionDetail->addField($mySelect);
		
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction("EMAIL_SAVE");
		if($actionContext->getForm()=="DEST_DET")
			$myButton->setForm("UPDT_DEST");
		else if($actionContext->getForm()=="NEW_DEST_DET")
			$myButton->setForm("INS_DEST");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		
		if($actionContext->getForm()=="DEST_DET") {
			// Elimina
			$myButton = new wi400InputButton('CANCEL_BUTTON');
			$myButton->setLabel("Elimina");
			$myButton->setAction("EMAIL_DELETE");
			$myButton->setForm("DELETE_DEST");
			$myButton->setConfirmMessage("Eliminare?");
			$actionDetail->addButton($myButton);
		}
		
		if($actionContext->getForm()=="NEW_DEST_DET") {
			// Chiudi
			$myButton = new wi400InputButton('BACK_BUTTON');
			$myButton->setLabel("Chiudi");
//			$myButton->setScript('closeLookUp()');
			$myButton->setAction("CLOSE");
			$myButton->setForm("CLOSE_LOOKUP");
			$myButton->addParameter("CLEAN_DETAIL", $idDetail);
			$actionDetail->addButton($myButton);
		}
		else {
			// Annulla
			$myButton = new wi400InputButton('BACK_BUTTON');
			$myButton->setLabel("Annulla");
			$myButton->setAction($azione);
			$myButton->setForm("DEST_LIST");
			$actionDetail->addButton($myButton);
		}
		
		$actionDetail->dispose();
	}
	else if(in_array($actionContext->getForm(), array("CONTENTS_DET", "NEW_CONTENTS_DET"))) {
		$idDetail = $azione."_".$actionContext->getForm();
	
		$actionDetail = new wi400Detail($idDetail, false);
	
		if(in_array($actionContext->getForm(),array("CONTENTS_DET"))) {
			if(!existDetail($idDetail)) {
				// caricamento dei dati della chiamata recuperati dal subfile
				$actionDetail->setSource($row);
			}
		}
	
		$actionDetail->setSaveDetail(true);
	
		// ID
		$myField = new wi400InputText('ID');
		$myField->setLabel('ID');
		$myField->setReadonly(true);
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setValue($cod_id);
		$actionDetail->addField($myField);
		
		// Tipo contenuto
		$mySelect = new wi400InputSelect('UCTTYP');
		$mySelect->setLabel("Tipo contenuto");
		$mySelect->addValidation('required');
		$mySelect->setFirstLabel("Seleziona...");
		$mySelect->setOptions($tipo_contents_array);
		$actionDetail->addField($mySelect);
/*		
		// Numero riga
		$myField = new wi400InputText('UCTRIG');
		$myField->setLabel("Numero riga");
//		$myField->addValidation('required');
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setMask("0123456789");
		$actionDetail->addField($myField);
*/		
		// Contenuto
		$myField = new wi400InputTextArea('UCTKEY');
		$myField->setLabel("Contenuto");
		$myField->setSize(180);
		$myField->setRows(15);
		$actionDetail->addField($myField);
	
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction("EMAIL_SAVE");
		if($actionContext->getForm()=="CONTENTS_DET")
			$myButton->setForm("UPDT_CONTENTS");
		else if($actionContext->getForm()=="NEW_CONTENTS_DET")
			$myButton->setForm("INS_CONTENTS");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
	
		if($actionContext->getForm()=="CONTENTS_DET") {
			// Elimina
			$myButton = new wi400InputButton('CANCEL_BUTTON');
			$myButton->setLabel("Elimina");
			$myButton->setAction("EMAIL_DELETE");
			$myButton->setForm("DELETE_CONTENTS");
			$myButton->setConfirmMessage("Eliminare?");
			$actionDetail->addButton($myButton);
		}
	
		if($actionContext->getForm()=="NEW_CONTENTS_DET") {
			// Chiudi
			$myButton = new wi400InputButton('BACK_BUTTON');
			$myButton->setLabel("Chiudi");
//			$myButton->setScript('closeLookUp()');
			$myButton->setAction("CLOSE");
			$myButton->setForm("CLOSE_LOOKUP");
			$myButton->addParameter("CLEAN_DETAIL", $idDetail);
			$actionDetail->addButton($myButton);
		}
		else {
			// Annulla
			$myButton = new wi400InputButton('BACK_BUTTON');
			$myButton->setLabel("Annulla");
			$myButton->setAction($azione);
			$myButton->setForm("CONTENTS_LIST");
			$actionDetail->addButton($myButton);
		}
	
		$actionDetail->dispose();
	}
	else if($actionContext->getForm()=="MPX_DET") {
		$idDetail = $azione."_".$actionContext->getForm();
		
		$actionDetail = new wi400Detail($idDetail, false);
		
		if(!existDetail($idDetail)) {
			// caricamento dei dati della chiamata recuperati dal subfile
			$actionDetail->setSource($row_mpx);
			
			$test = false;
			
			if($row_mpx['TEST']=="S")
				$test = true;
		}
	
		$actionDetail->setSaveDetail(true);
	
		// ID
		$myField = new wi400InputText('ID');
		$myField->setLabel('ID');
		$myField->setReadonly(true);
		$myField->setSize(10);
		$myField->setMaxLength(10);
		$myField->setValue($cod_id);
		$actionDetail->addField($myField);
	
		// Test
		$myField = new wi400InputSwitch("MPX_TEST");
		$myField->setLabel("Test");
		$myField->setOnLabel(_t('LABEL_YES'));
		$myField->setOffLabel(_t('LABEL_NO'));
		if(isset($test))
			$myField->setChecked($test);
		$myField->setValue(1);
		$actionDetail->addField($myField);
	
		// Numero pagine PDF
		$myField = new wi400InputText('NUMPAG');
		$myField->setLabel("Numero pagine PDF");
//		$myField->addValidation('required');
		$myField->setMaxLength(4);
		$myField->setSize(4);
		$myField->setMask("0123456789");
		$actionDetail->addField($myField);
	
		// Formato stampa e spedizione
		$myField = new wi400InputText('WKPRID');
		$myField->setLabel("Formato stampa e spedizione");
//		$myField->addValidation('required');
		$myField->setMaxLength(10);
		$myField->setSize(10);
		$actionDetail->addField($myField);
	
		// Riga 1 indirizzo
		$myField = new wi400InputText('ADDR1');
		$myField->setLabel("Riga 1 indirizzo");
//		$myField->addValidation('required');
		$myField->setMaxLength(100);
		$myField->setSize(100);
		$actionDetail->addField($myField);
		
		// Riga 2 indirizzo
		$myField = new wi400InputText('ADDR2');
		$myField->setLabel("Riga 2 indirizzo");
//		$myField->addValidation('required');
		$myField->setMaxLength(100);
		$myField->setSize(100);
		$actionDetail->addField($myField);
		
		// Riga 3 indirizzo
		$myField = new wi400InputText('ADDR3');
		$myField->setLabel("Riga 3 indirizzo");
		$myField->addValidation('required');
		$myField->setMaxLength(100);
		$myField->setSize(100);
		$actionDetail->addField($myField);
		
		// CAP
		$myField = new wi400InputText('CAP');
		$myField->setLabel("CAP");
//		$myField->addValidation('required');
		$myField->setMaxLength(5);
		$myField->setSize(5);
		$myField->setMask("0123456789");
		$actionDetail->addField($myField);
		
		// Città
		$myField = new wi400InputText('CITTA');
		$myField->setLabel("Città");
//		$myField->addValidation('required');
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$actionDetail->addField($myField);
		
		// Provincia
		$myField = new wi400InputText('PROV');
		$myField->setLabel("Provincia");
//		$myField->addValidation('required');
		$myField->setMaxLength(2);
		$myField->setSize(2);
		$actionDetail->addField($myField);
		
		// Nazione
		$myField = new wi400InputText('NAZ');
		$myField->setLabel("Nazione");
//		$myField->addValidation('required');
		$myField->setMaxLength(2);
		$myField->setSize(2);
		$actionDetail->addField($myField);
		
		// Stato globale elaborazione
		$myField = new wi400InputText('GLOCOD');
		$myField->setLabel("Stato globale elaborazione");
//		$myField->addValidation('required');
		$myField->setMaxLength(3);
		$myField->setSize(3);
		$actionDetail->addField($myField);
		
		// Codice univoco ass. MPX
		$myField = new wi400InputText('SETID');
		$myField->setLabel("Codice univoco ass. MPX");
//		$myField->addValidation('required');
		$myField->setMaxLength(50);
		$myField->setSize(50);
		$actionDetail->addField($myField);
		
		// Codice elaborazione Set
		$myField = new wi400InputText('SETCOD');
		$myField->setLabel("Codice elaborazione Set");
//		$myField->addValidation('required');
		$myField->setMaxLength(3);
		$myField->setSize(3);
		$actionDetail->addField($myField);
		
		// Codice elaborazione PDF
		$myField = new wi400InputText('PDFCOD');
		$myField->setLabel("Codice elaborazione PDF");
//		$myField->addValidation('required');
		$myField->setMaxLength(3);
		$myField->setSize(3);
		$actionDetail->addField($myField);
		
		// Codice elaborazione Env
		$myField = new wi400InputText('ENVCOD');
		$myField->setLabel("Codice elaborazione Env");
//		$myField->addValidation('required');
		$myField->setMaxLength(3);
		$myField->setSize(3);
		$actionDetail->addField($myField);
	
		// Salva
		$myButton = new wi400InputButton('SAVE_BUTTON');
		$myButton->setLabel("Salva");
		$myButton->setAction("EMAIL_SAVE");
		if(isset($row_mpx) && !empty($row_mpx))
			$myButton->setForm("UPDT_MPX");
		else
			$myButton->setForm("INS_MPX");
		$myButton->setConfirmMessage("Salvare?");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
	
		 if(isset($row_mpx) && !empty($row_mpx)) {
			// Elimina
			$myButton = new wi400InputButton('CANCEL_BUTTON');
			$myButton->setLabel("Elimina");
			$myButton->setAction("EMAIL_DELETE");
			$myButton->setForm("DELETE_MPX");
			$myButton->setConfirmMessage("Eliminare?");
			$actionDetail->addButton($myButton);
		}
	
		// Annulla
		$myButton = new wi400InputButton('BACK_BUTTON');
		$myButton->setLabel("Annulla");
		$myButton->setAction($azione);
		$myButton->setForm("EMAIL_LIST");
		$actionDetail->addButton($myButton);
	
		$actionDetail->dispose();
	}
	else if($actionContext->getForm()=="ATC_PRV") {
		$campi = array(
			"REQUEST_PARAMS" => array("DETAIL_KEY", "COLUMN_KEY")
		);
		
		downloadDetail($TypeImage, $file, $temp, "Esportazione completata", "", "", $campi);
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}