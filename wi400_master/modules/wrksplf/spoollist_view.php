<?php

	$spacer = new wi400Spacer();

	if($actionContext->getForm()=="DEFAULT") {
		subfileDelete("WRKSPLF");

		$subfile = new wi400Subfile($db, "WRKSPLF", $settings['db_temp']);
		$subfile->setModulo('wrksplf');
		$subfile->setSql("*AUTOBODY");
		$subfile->addParameter("SPOOL_PARAMETER", $desc);
		
		$miaLista = new wi400List("WRKSPLF", False);
		
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("SPOOLDATA DESC");
		$miaLista->setSelection("MULTIPLE");

//		$cols = getColumnListFromTable($subfile->getTableName(), "PHPTEMP");
		$cols = getColumnListFromArray('WRKSPLF', 'wrksplf');
		$cols['SPOOLNUMBER']->setAlign('right');
		$cols['SPOOLDATA']->setFormat('DATE');

		$miaLista->setCols($cols);
	
		// aggiunta chiavi di riga
		$miaLista->addKey("SPOOLJOB");
		$miaLista->addKey("SPOOLUSER");
		$miaLista->addKey("SPOOLNUMBER");
		$miaLista->addKey("SPOOLNAME");
		$miaLista->addKey("SPOOLNBR");
		$miaLista->addKey("SPOOLUSRDATA");
		$miaLista->addKey("SPOOLMODULO");
		$miaLista->addKey("SPOOLPAGNBR");
		$miaLista->addKey("SPOOLOUTQ");
		$miaLista->addKey("OUTQNAME");
		$miaLista->addKey("OUTWRITEJ");
		
		// Aggiunta filtri
		$listFlt = new wi400Filter("SPOOLNAME");
		$listFlt->setDescription(_t('SPOOL_NAME'));
		$listFlt->setType("STRING");
		$miaLista->addFilter($listFlt);
		
		$listFlt = new wi400Filter("SPOOLUSRDATA");
		$listFlt->setDescription(_t('SPOOL_USRDTA'));
		$miaLista->addFilter($listFlt);
		
		$listFlt = new wi400Filter("SPOOLUSER");
		$listFlt->setDescription(_t('USER_CODE'));
		$miaLista->addFilter($listFlt);
		
		$listFlt = new wi400Filter("SPOOLJOB");
		$listFlt->setDescription(_t('JOB'));
		$miaLista->addFilter($listFlt);
		
		// Aggiunta azioni
		$action = new wi400ListAction();
		$action->setAction("TSPOOLVIEW");
		$action->setLabel(_t('DETAIL_VIEW'));
		$action->setSelection('SINGLE');
		$miaLista->addAction($action);
		
		// Conversione spool in formato PDF
		$action = new wi400ListAction();
		$action->setAction("TSPOOLPDF");
		$action->setLabel(_t('PDF_CONVERT'));
		$action->setSelection('SINGLE');
		$action->setTarget("WINDOW");		
		$miaLista->addAction($action);
		
		// Modifica attributi
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("MODIFICA_ATTRIBUTI");
		$action->setLabel(_t('PROP_CHANGE'));
		$action->setSelection('MULTIPLE');
		$action->setTarget("WINDOW", 1000, 600);		
		$miaLista->addAction($action);
		// Visualizza Messaggio
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("VISUALIZZA_MESSAGGIO");
		$action->setLabel("Visualizza Messaggio Writer");
		$action->setSelection('SINGLE');
		$action->setTarget("WINDOW", 800, 400);
		$miaLista->addAction($action);
		
		// Cancellazione spool
		$action = new wi400ListAction();
		$action->setAction("DELETESPOOL");
		$action->setLabel(_t('SPOOL_DELETE'));
		$action->setSelection('MULTIPLE');
		$action->setConfirmMessage(_t('SPOOL_DELETE_QUESTION'));
		$miaLista->addAction($action);
		
		getMicroTimeStep("inizio dispose");
		listDispose($miaLista);
		getMicroTimeStep("fine dispose");
	}
	else if($actionContext->getForm()=="VISUALIZZA_MESSAGGIO") {
		foreach($wi400List->getSelectionArray() as $key => $value)
		{
			$keyArray = explode("|",$key);
			if ($keyArray[10]!="") {
							$sql="SELECT CHAR(MESSAGE_TEXT) AS MSG                               
				 FROM                                                    
				TABLE(QSYS2.JOBLOG_INFO('".$keyArray[10]."')) A     
				  ORDER BY ORDINAL_POSITION DESC";
							$result = $db->singleQuery($sql);
							$row = $db->fetch_array($result);
							echo $row['MSG'];
			} else {
				echo "Nessun messaggio da visualizzare";
			}
			/*
			 * SELECT A.*,                                                       
char(message_text) FROM qsys2.message_queue_info A                
 WHERE                                                            
from_job = '462101/QPGMR/CHK_SOGLIA' and message_type='INQUIRY'   
and from_program ='QCLXERR'                                       
and message_file_name='QCPFMSG'                                   
and message_queue_name = 'QSYSOPR'                                
AND message_queue_library = 'QSYS'                                
			 */
		}
	}
	else if($actionContext->getForm()=="MODIFICA_ATTRIBUTI") {
		$actionDetail = new wi400Detail($azione.'_MOD_ATTR_DET', True);
		$actionDetail->setSaveDetail(True);

        if(count($rowsSelectionArray)==1) {
		    require_once $routine_path."/os400/wi400Os400Spool.cls.php";
		    $key =getListKeyArray("WRKSPLF");
		    $job = str_pad($key['SPOOLJOB'], 10).str_pad($key['SPOOLUSER'], 10).str_pad($key['SPOOLNUMBER'], 6); 
			$itemObj = wi400Os400Spool::getAttribute($job, $key['SPOOLNAME'],$key["SPOOLNBR"]);
        	/*
			$zsplfa = new wi400Routine( 'ZSPLFA', $connzend );
			$zsplfa->load_description ();
			$zsplfa->prepare ();
			$zsplfa->set ( 'SPOOLNAME',  $key['SPOOLNAME']);
			$job = str_pad($key['SPOOLJOB'], 10).str_pad($key['SPOOLUSER'], 10).str_pad($key['SPOOLNUMBER'], 6); 
			$zsplfa->set ( 'JOB', $job );
			$zsplfa->set ( 'NBR', $key["SPOOLNBR"]);
			$zsplfa->call ();
			$itemObj = $zsplfa->get( 'SPLFA' );
			*/
			$setValue=True;			
        }
			
		$mySelect = new wi400InputSelect('SAVE');
		$mySelect->setLabel(_t('SAVE'));
		$mySelect->setFirstLabel(_t('TYPE_SELECT'));
		$mySelect->addOption(_t('LABEL_YES'),"*YES");
		$mySelect->addOption(_t('LABEL_NO'),"*NO");
		if ($setValue) $mySelect->setValue($itemObj['QUSFXS']);
//		$mySelect->addValidation("required");
		$actionDetail->addField($mySelect);
		
		$myField = new wi400InputText('OUTQ');
		$myField->setLabel("OUTQ");
		$myField->setInfo(_t('SPOOL_OUTQ_INFO'));
		if ($setValue) $myField->setValue($itemObj['QUSFX5']);		
		
		$myLookUp =new wi400LookUp("LU_OBJECT");
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
		
		$myField = new wi400InputText('COPIES');
		$myField->setLabel(_t('COPIES'));
		$myField->setInfo(_t('COPIES_INFO'));	
		$myField->setMaxLength(5);
		$myField->setSize(5);
		$myField->addValidation('integer');
		if ($setValue) $myField->setValue($itemObj['QUSFX0']);			
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText('DAPAGINA');
		$myField->setLabel(_t('PAGE_FROM'));
		$myField->setInfo(_t('PAGE_FROM_INFO'));	
		$myField->setMaxLength(5);
		$myField->setSize(5);
		$myField->addValidation('integer');
		if ($setValue && $itemObj['QUSFXW']!=0) $myField->setValue($itemObj['QUSFXW']);			
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText('APAGINA');
		$myField->setLabel(_t('PAGE_TO'));
		$myField->setInfo(_t('PAGE_TO_INFO'));
		$myField->setMaxLength(5);
		$myField->setSize(5);
		if ($setValue && $itemObj['QUSFXX']!=0) $myField->setValue($itemObj['QUSFXX']);			
		$myField->addValidation('integer');
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText('USRDTA');
		$myField->setLabel(_t('SPOOL_USRDTA'));
		$myField->setInfo(_t('SPOOL_USRDTA_INFO'));	
		$myField->setMaxLength(10);
		if ($setValue) $myField->setValue($itemObj['QUSFXN']);			
		$myField->setSize(10);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputText('FORMTYPE');
		$myField->setLabel(_t('SPOOL_MODULE'));
		$myField->setInfo(_t('SPOOL_MODULE_INFO'));	
		$myField->setMaxLength(10);
		if ($setValue) $myField->setValue($itemObj['QUSFXM']);			
		$myField->setSize(10);
		$actionDetail->addField($myField);
		
		$myField = new wi400InputCheckbox("UPDATE_ALL");
		$myField->setLabel(_t('LABEL_ALL'));
		$myField->setChecked(false);
		$actionDetail->addField($myField);
		
		$myButton = new wi400InputButton('UPDATE_BUTTON');
		$myButton->setLabel(_t('APPLY'));
		$myButton->setAction($azione);
		$myButton->setForm("UPDATE_ATTRIBUTI");
		$myButton->setValidation(true);
		$actionDetail->addButton($myButton);
		
		$myButton = new wi400InputButton('CANCEL_BUTTON');
		$myButton->setLabel(_t('CANCEL'));
//		$myButton->setScript('closeLookUp()');
		$myButton->setAction($azione);
		$myButton->setForm("CLOSE_WINDOW");
		$actionDetail->addButton($myButton);
		
		$actionDetail->dispose();
		
		$spacer->dispose();
		
		$actionDetail = new wi400Detail($azione.'_LIST_DET', false);

		$tableDetail = new wi400Table("SPOOL_FIRST");
		$tableDetail->setLabel(_t('SPOOL_SELECTED'));
		
		$tableDetail->setCols(array(
			new wi400Column("SPOOLNAME",_t('SPOOL_NAME')),
			new wi400Column("SPOOLJOB",_t('JOB')),
			new wi400Column("SPOOLUSER",_t('USER_CODE')),
			new wi400Column("SPOOLNUMBER",_t('JOB_NUMBER'),"","right"),
			new wi400Column("SPOOLNBR",_t('SPOOL_NUMBER'),"","right"),
			new wi400Column("SPOOLOUTQ",_t('OUTQ_LIB')),
			new wi400Column("OUTQNAME","Outq"),
			new wi400Column("SPOOLPAGNBR",_t('PAGES'),"INTEGER","right"),
			new wi400Column("SPOOLMODULO",_t('MODULE')),
			new wi400Column("SPOOLUSRDATA",_t('SPOOL_USRDTA'))
		));
		
		foreach($rowsSelectionArray as $key => $value){
			$keyArray = array();
			$keyArray = explode("|",$key);
			
			$tab_row = array(
				$keyArray[3],
				$keyArray[0],
				$keyArray[1],
				$keyArray[2],
				$keyArray[4],
				$keyArray[8],
				$keyArray[9],
				$keyArray[7],
				$keyArray[6],
				$keyArray[5]
			);
			
			$tableDetail->addRow($tab_row);
		}
	
		$actionDetail->addField($tableDetail);
		
		$actionDetail->dispose();
	}
	else if($actionContext->getForm()=="CLOSE_WINDOW") {
		close_window();
	}
	
	// Scansione della directory routine/classi/pers e creazione delle opzioni basate sui file personalizzati presenti
	function createPersMenu($mySelect) {
	    global $base_path, $settings;
	
	    $path = $base_path."/package/".$settings['package'].'/persconv';
	    $dir = opendir("$path");
	    
	    while($file = readdir($dir)) {
	    	if(is_file("$path/$file") && strncmp($file,"wi400SpoolCvt_",14)==0) {
	        	$fileName = basename($file, ".cls.php"); 
	        	$model = substr($fileName,14);
				$mySelect->addOption($model, $model);
	        }
	    }
	}
	
?>
