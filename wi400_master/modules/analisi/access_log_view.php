<?php
	$spacer = new wi400Spacer(); 

	if($actionContext->getForm()=="DEFAULT") {
		
		/*$filtri = new wi400Iframe();
		$filtri->setUrl($appBase."index.php?t=GRIDSRCH&IDLIST=ACCESS_LOG_LIST&DECORATION=lookup&&LOOKUP_PARENT=lookup0&WAIT_LOADING=true");
		$filtri->dispose();*/
		$mio = new wi400Detail("ACCESS_LOG_DETAIL", False);
		$mio->isEditable(true);
		
		// CAMPO1
		$myField = new wi400InputText('CAMPO1');
		$myField->setLabel("Campo 1");
		$myField->addValidation('required');
		$myField->setMaxLength(40);
		$myField->setReadOnly(True);
		$myField->setValue("INPUT DA MODIFICARE");
		$mio->addField($myField);
		// CAMPO2 
		$myField = new wi400Text('CAMPO2');
		$myField->setLabel("Campo 2");
		$myField->setFormat("PERC_GRAPH");
		$myField->setValue("Testo fisso modificabile");
		$mio->addField($myField);
		
		$mio->setReloadAction("ACCESS_LOG");
		$mio->setReloadForm("RELOAD_AJAX");
		$mio->setReloadTimeout(700);
		$mio->dispose();
		
		
		//
		echo '<div class="panel-container" style="width: 100%;"><div class="panel-left" style="width: 70%; float:left;">';
		$miaLista = new wi400List($azione."_LIST", false);
		
		$where = "USER_NAME=ZSUTE";
		
		$miaLista->setFrom("ZSLOG , $users_table ");
		$miaLista->setWhere($where);
		$miaLista->setOrder("ZSUTE, ZTIME DESC", True);
		//$miaLista->setStaticOrder("ZSUTE, ZTIME DESC");
		$miaLista->setSelection('SINGLE');
		$miaLista->setAutoUpdateList(True);
		$miaLista->setCallBackFunction("validationRow", "functionValidationAccessLog");
		$miaLista->setCallBackFunction("validation", "functionValidationAccessLog");
		$miaLista->setCallBackFunction("reload", "functionReloadAccessLog");
		$miaLista->setCallBackFunction("afterFetch", "functionAfterFetchAccessLog");
		$miaLista->setCallBackFunction("inputCell", "functionInputCellLog");
		$miaLista->setIncludeFile("analisi", "access_log_functions.php");
		
		$esito_col = new wi400Column("ZSESI","Esito log");
		$esito_cond = array();
		$esito_cond[] = array('EVAL:$row["ZSESI"]=="OK"', 'wi400_grid_green');
		$esito_cond[] = array('EVAL:$row["ZSESI"]=="KO"', 'wi400_grid_red');
		$esito_col->setStyle($esito_cond);
		
		$sess_col = new wi400Column("ZFRE", "ID session");
		$sess_col->setActionListId("SESS_ACTION");
		
		$active_sess_col = new wi400Column("ACTIVE_SESS", "Ultima attivit&agrave;");
		$active_sess_col->setDefaultValue('EVAL:getLastTimeMod($row[\'ZFRE\'])');
		// LUCA ESPERIMENTO RIMOZIONE RIGA
		$col = new wi400Column("ELIMINA RIGA", "Elimina");
		$inputField = new wi400InputCheckbox("ELIMINA_RIGA_I");
		$inputField->setValue("1");
		//$inputField->setOnClick("removeListRow(this, '')");
		//$inputField->setUncheckedValue("0");
		$col->setInput($inputField);
		$col->setSortable(false);
		//$col->setDisableAutoUpdate(True);
		// LUCA ESPERIMENTO RICODIFICA AJAX CELLA
		$inputField = new wi400InputText("ADDQTA");
		$inputField->setSize(11);
		$inputField->setMaxLength(11);
		$inputField->setMask("1234567890");
		$inputField->addValidation("partita_iva");
		$inputField->setOnChange("updateListRow(this, '')");
		$inputField->setOnBlur("updateListRow(this, '')");
		
				
		$orderedQtaColumn = new wi400Column("TEST");
		$orderedQtaColumn->setAlign("right");
		$orderedQtaColumn->setSortable(false);
		$orderedQtaColumn->setDescription("Modifica Qtà");
		$orderedQtaColumn->setExportable(false);
		$orderedQtaColumn->setInput($inputField);
		//$orderedQtaColumn->setWriteUniqueId(True);
		$esito_cond = array();
		$esito_cond[] = array('EVAL:$row["TEST"]=="1"', 'wi400_grid_green');
		$esito_cond[] = array('EVAL:$row["TEST"]=="2"', 'wi400_grid_red');
		$esito_cond[] = array('EVAL:$row["TEST"]=="3"', 'wi400_grid_yellow');
		$esito_cond[] = array('EVAL:$row["TEST"]=="4"', 'wi400_grid_orange');
		$orderedQtaColumn->setStyle($esito_cond);
		$orderedQtaColumn->setDefaultValue("");
		$orderedQtaColumn->setReadonly('EVAL:$row["TEST"]=="S"||$row["TEST"]=="E"');
		
		// TEST COLONNA DATE
		$inpDate = new wi400InputText("ADDDATA");
		$inpDate->addValidation("date");
		$colDate = new wi400Column("TEST_DATA");
		$colDate->setFormat("DATE");
		$colDate->setDescription("Test Data");
		$colDate->setInput($inpDate);
		// FINE COLONNA DATE
		$miaLista->setCols(array(
			new wi400Column("ZSUTE","Codice utente"),
			$colDate,
			$orderedQtaColumn,
			$col,	
			new wi400Column("FIRST_NAME","Nome utente"),
			new wi400Column("LAST_NAME","Cognome utente"),
			$esito_col,
			new wi400Column("ZSIP", "Indirizzo IP"),
			new wi400Column("ZTIME", "Data di log", "COMPLETE_TIMESTAMP"),
			$sess_col,
			$active_sess_col
		));
		
		
		// Aggiunta chiavi di listaù
		$miaLista->addKey("ZSUTE");
		$miaLista->addKey("FIRST_NAME");
		$miaLista->addKey("LAST_NAME");
		//$miaLista->addKey("ZSESI");
		//$miaLista->addKey("ZSIP");
		//$miaLista->addKey("ZTIME");
		//$miaLista->addKey("ZFRE");
		
		$miaLista->setBreakKey("ZSUTE");
		//$miaLista->setShowTabletNumPages(False);
		
		// Aggiunta filtri rapidi
		// Utente
		$mioFiltro = new wi400Filter("ZSUTE","Codice utente","STRING"); 
		$mioFiltro->setFast(true);    
		$miaLista->addFilter($mioFiltro);
		
		// Aggiunta Filtri avanzati
		$mioFiltro = new wi400Filter("ZSESI","Esito log","SELECT","");
		$filterValues = array();
		$filterValues["ZSESI='OK'"] = "OK";
		$filterValues["ZSESI='KO'"] = "KO";
		$mioFiltro->setSource($filterValues);
		$miaLista->addFilter($mioFiltro);
		// Filtro di tipo data
		$mioFiltro = new wi400Filter("ZTIME","Data","DATE","");
		$filterValues = array();
		$miaLista->addFilter($mioFiltro);
		
		// Percentuale accessi utenti
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("PERCENTUALE_ACCESSI");
		$action->setLabel("Percentuale accessi utenti");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		// Andamento accessi utente
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("ANDAMENTO_ACCESSI");
		$action->setLabel("Andamento accessi utenti");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		// GeoIP
		$action = new wi400ListAction();
		$action->setAction("GEOIP");
		$action->setLabel("GeoIP");
		$action->setTarget("WINDOW",1000,500, "false");
		$action->setGateway("ACCESS_LOG");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Sessione
		$action = new wi400ListAction("ACCESS_LOG_DET", "LIST");
		$action->setId("SESS_ACTION");
		$action->setLabel("Dettaglio sessione");
		$action->setGateway("ACCESS_LOG");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$miaLista->setShowHeadFilter(False);
		$miaLista->setTopNavigationBar(True);
		$miaLista->setBlockScrollHeader(True);
		
		$myButton = new wi400InputButton('SEARCH_BUTTON');
		$myButton->setLabel("Aggiorna");
		$myButton->setScript("doReloadDetail('ACCESS_LOG_DETAIL')");
		$myButton->setValidation(True);
		$myButton->dispose();
		
		//showArray($miaLista->getFieldsForSelect());
		//showArray($miaLista->getColumnsOrder());
		//$miaLista->setCanExport(True);
		//$miaLista->setCanManage(False);
		//$miaLista->setCanReload(True);
		$miaLista->dispose();
        echo  '</div><div class="right"  style="width: 30%; float:left;">Questo è il campo!';
		//$miaLista->dispose();
		echo '</div></div>';
		//echo "<script> jQuery(\".panel-left\").resizable({handleSelector: \".splitter\",resizeHeight: false});</script>";
		
/*		
		$myButton = new wi400InputButton('BATCH_EXPORT');
		$myButton->setLabel("Esportazione Lista Batch");
		$myButton->setAction($azione);
		$myButton->setForm("EXPORT");
		$myButton->setValidation(true);
		$myButton->setCheckUpdate(true);
		$myButton->dispose();
*/
	}
	else if($actionContext->getForm()=="PERCENTUALE_ACCESSI") {
		$graphDetail = new wi400Detail("PERCENTUALE_ACCESSI_DETAIL");
		$graphDetail->isEditable(true);
		$graphDetail->setColsNum(2);
				
		// Data inizio
		$myField = new wi400InputText('DATA_INI');
		$myField->addValidation('required');
		if(!isset($data_ini) || empty($data_ini)) {
			$data = mktime(0,0,0,1,1,date("Y"));
			$myField->setValue(dateModelToView(date("Ymd", $data)));
		}
		else
			$myField->setValue($data_ini);
		$myField->addValidation('date');
		$myField->setLabel("Da data");
		$graphDetail->addField($myField);
		
		// Data fine
		$myField = new wi400InputText('DATA_FIN');
		$myField->addValidation('required');
		if(!isset($data_fin) || empty($data_fin))
			$myField->setValue(dateModelToView(date("Ymd")));
		else
			$myField->setValue($data_fin);
		$myField->addValidation('date');
		$myField->setLabel("A data");
		$graphDetail->addField($myField);
		
		// Ricarica
		$myButton = new wi400InputButton('RELOAD_BUTTON');
		$myButton->setLabel("Genera grafico");
		$myButton->setAction($azione);
		$myButton->setForm("PERCENTUALE_ACCESSI");
		$graphDetail->addButton($myButton);
		
		// Download grafico
		$myButton = new wi400InputButton('DOWNLOAD_BUTTON');
		$myButton->setLabel("Download grafico");
		$myButton->setScript("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=$azione&f=DOWNLOAD_GRAFICO', 'Download grafico')");
		$graphDetail->addButton($myButton);
		
		$graphDetail->dispose();
		
		$spacer->dispose();
		
		if($data_ini!="" && $data_fin!="") {
			// Output del grafico senza link nell'immagine
			
			// Get the handler to prevent the library from sending the
			// image to the browser
			$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
			 
			// Stroke image to a file and browser
			 
			// Default is PNG so use ".png" as suffix
			$graphFile = wi400File::getUserFile("tmp", $filename);
		
			$graph->img->Stream($graphFile);
	
//			echo "<img name='myimage' id='myimage' src='".$appBase."index.php?DECORATION=clean&t=FILEDWN&CONTEST=tmp&FILE_NAME=".$filename."' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";
			$link = create_file_download_link($filename, "tmp");
			echo "<img name='myimage' id='myimage' src='".$link."' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";

			// Output del grafico con link nell'immagine
/*			
			// Get the handler to prevent the library from sending the
			// image to the browser
			$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
			 
			// Stroke image to a file and browser
			 
			// Default is PNG so use ".png" as suffix
			$graphFile = wi400File::getUserFile("tmp", $filename);
		
			$graph->img->Stream($graphFile);
	
			echo  $graph->GetHTMLImageMap("myimagemap001");
			echo "<img name='myimage' id='myimage' ISMAP USEMAP=\"#myimagemap001\" src='".$appBase."index.php?DECORATION=clean&t=FILEDWN&CONTEST=tmp&FILE_NAME=".$filename."' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";
*/
		}
	}
	else if($actionContext->getForm()=="ANDAMENTO_ACCESSI") {
		$graphDetail = new wi400Detail("ANDAMENTO_ACCESSI_DETAIL");
		$graphDetail->isEditable(true);
		$graphDetail->setColsNum(2);
		
		// Utente
		$myField = new wi400InputText('UTENTE');
		$myField->setLabel("Codice Utente");
		$myField->addValidation('required');
		$myField->setMaxLength(20);
		$myField->setCase("UPPER");
		if(isset($user) && $user!="")
			$myField->setValue($user);
		else
			$myField->setValue($_SESSION['user']);
		$myField->setInfo('Inserire il codice utente');
		
		$myLookUp =new wi400LookUp("LU_GENERICO");
		$myLookUp->addParameter("FILE",$users_table);
		$myLookUp->addParameter("CAMPO","USER_NAME");
		$myLookUp->addParameter("DESCRIZIONE","EMAIL");
		$myField->setLookUp($myLookUp);
		
		$graphDetail->addField($myField);
		
		$myField = new wi400Text('VUOTO');
		$myField->setValue("");
		$graphDetail->addField($myField);
		
		// Data inizio
		$myField = new wi400InputText('DATA_INI');
		$myField->addValidation('required');
		if(!isset($data_ini) || empty($data_ini)) {
			$data = mktime(0,0,0,1,1,date("Y"));
			$myField->setValue(dateModelToView(date("Ymd", $data)));
		}
		else
			$myField->setValue($data_ini);
		$myField->addValidation('date');
		$myField->setLabel("Da data");
		$graphDetail->addField($myField);
		
		// Data fine
		$myField = new wi400InputText('DATA_FIN');
		$myField->addValidation('required');
		if(!isset($data_fin) || empty($data_fin))
			$myField->setValue(dateModelToView(date("Ymd")));
		else
			$myField->setValue($data_fin);
		$myField->addValidation('date');
		$myField->setLabel("A data");
		$graphDetail->addField($myField);
		
		// Ricarica
		$myButton = new wi400InputButton('RELOAD_BUTTON');
		$myButton->setLabel("Genera grafico");
		$myButton->setAction($azione);
		$myButton->setForm("ANDAMENTO_ACCESSI");
		$graphDetail->addButton($myButton);
		
		// Download grafico
		$myButton = new wi400InputButton('DOWNLOAD_BUTTON');
		$myButton->setLabel("Download grafico");
		$myButton->setScript("openWindow(_APP_BASE + APP_SCRIPT + '?DECORATION=lookUp&t=$azione&f=DOWNLOAD_GRAFICO', 'Download grafico')");
		$graphDetail->addButton($myButton);
		
		$graphDetail->dispose();
		
		$spacer->dispose();
		
		if($user!="" && $data_ini!="" && $data_fin!="") {
			// Output del grafico senza link nell'immagine	
			
			// Get the handler to prevent the library from sending the
			// image to the browser
			$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
			 
			// Stroke image to a file and browser
			 
			// Default is PNG so use ".png" as suffix
			$graphFile = wi400File::getUserFile("tmp", $filename);
			
			$graph->img->Stream($graphFile);
		
//			echo "<img name='myimage' id='myimage' src='".$appBase."index.php?DECORATION=clean&t=FILEDWN&CONTEST=tmp&FILE_NAME=".$filename."' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";
			$link = create_file_download_link($filename, "tmp");
			echo "<img name='myimage' id='myimage' src='".$link."' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";

			// Output del grafico con link nell'immagine
/*			
			// Get the handler to prevent the library from sending the
			// image to the browser
			$gdImgHandler = $graph->Stroke(_IMG_HANDLER);
			 
			// Stroke image to a file and browser
			 
			// Default is PNG so use ".png" as suffix
			$graphFile = wi400File::getUserFile("tmp", $filename);
			
			$graph->img->Stream($graphFile);
			
			echo  $graph->GetHTMLImageMap("myimagemap002");
			echo "<img name='myimage' id='myimage' ISMAP USEMAP=\"#myimagemap002\" src='".$appBase."index.php?DECORATION=clean&t=FILEDWN&CONTEST=tmp&FILE_NAME=".$filename."' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";
*/
		}
	}
	else if($actionContext->getForm()=="DOWNLOAD_GRAFICO") {
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
		
		$temp = "tmp";
		$TypeImage = "png.gif";
				
		downloadDetail($TypeImage, $filename, $temp, "Esportazione completata");
	} 	else if($actionContext->getForm()=="RELOAD_AJAX") {
		wi400Detail::setDetailValue('ACCESS_LOG_DETAIL', "CAMPO2", substr(time(), -2));
		wi400Detail::setDetailValue('ACCESS_LOG_DETAIL', "CAMPO1", time());
	}
/*	
	else if($actionContext->getForm()=="EXPORT") {
		require_once $routine_path."/classi/wi400Batch.cls.php";
		$batch = new wi400Batch("QPGMR", "QPGMR");
		$batch->setAction("EXPORT_LIST_BATCH");
		$id = $batch->getId();
		// Recupero la lista
		$source = wi400Session::getFileName(wi400Session::$_TYPE_LIST, $azione."_LIST");
		$batch->duplicateFileBatch($source, $azione."_LIST", wi400Session::$_TYPE_LIST);
		// Recupero un eventuale subfile
		if ( $wi400List->getSubfile() != null){
			$source = wi400Session::getFileName(wi400Session::$_TYPE_SUBFILE, $wi400List->getSubfile());
			$batch->duplicateFileBatch($source, $wi400List->getSubfile(), wi400Session::$_TYPE_SUBFILE);
		}	
		$result_batch = $batch->call($connzend);
	}
*/	
	removeListRowJs();
?>
<script>
function keepMeAlive(imgName) {
	   myImg = document.getElementById(imgName);
	   if (myImg) myImg.src = myImg.src.replace(/?.*$/, '?' + Math.random());
}
window.setInterval("keepMeAlive('keepAliveIMG')", 100000);
</script>