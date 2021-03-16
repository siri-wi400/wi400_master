<?php

	if($actionContext->getForm()=="DEFAULT") {
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		
		$miaLista->setFrom("FLOGCONV");
		$miaLista->setOrder("LOGELA DESC");
	
		// Verifico se l'utente fa parte del gruppo spool_admin
		$sql = "select WI400_GROUPS from sir_users where user_name='". $_SESSION['user']."'";
		$result = $db->singleQuery($sql);
		$user = $db->fetch_array($result);
		if (strpos($user['WI400_GROUPS'],"SPOOL_ADMIN")===false) {
			$miaLista->setWhere("LOGUSR='$_SESSION[user]'");
		}
	
//		echo "SQL: ".$miaLista->getSql()."<br>";
		
		$miaLista->setSelection("MULTIPLE");
		
		$cols = getColumnListFromTable("FLOGCONV");
		
		// Colonna link stampa
		$stmpCol = new wi400Column("STMPCOL", "Stampa");
		
		$imgIco = new wi400Image("IMG");
//		$imgIco->setUrl("tag-image.gif");
		$imgIco->setUrl("printer.gif");
		
		$imgCond = array();
		$imgCond[] = array('EVAL:1==1', $imgIco->getHtml());
		
		$stmpCol->setDefaultValue($imgCond);
		
		$stmpCol->setDetailAction("LOGCONV_STAMPA", "STAMPA_SEL");
		$stmpCol->setSortable(False);
		$stmpCol->setExportable(False);
		
		array_unshift($cols, $stmpCol);		
		
		$miaLista->setCols($cols);
		
		$col_ins = $miaLista->getCol("LOGINS");
		$col_ins->setFormat("COMPLETE_TIMESTAMP");
		
		$col_ela = $miaLista->getCol("LOGELA");
		$col_ela->setFormat("COMPLETE_TIMESTAMP");
		
		$col_file = $miaLista->getCol("LOGNOM");
		$col_file->setDetailAction($azione, "DOWNLOAD_FILE");
//		$col_file->addDetailKey("LOGPTH");
//		$col_file->addDetailKey("LOGNOM");
		
		$col_time_stampa = $miaLista->getCol("LOGSTT");
		$col_time_stampa->setFormat("COMPLETE_TIMESTAMP");
		
		$miaLista->addKey("LOGUSR");
		$miaLista->addKey("LOGJOB");
		$miaLista->addKey("LOGNBR");
		$miaLista->addKey("LOGDTA");
		$miaLista->addKey("LOGPTH");
		$miaLista->addKey("LOGNOM");
		$miaLista->addKey("LOGMOD");
		$miaLista->addKey("LOGID");
		
		// Utente
		$mioFiltro = new wi400Filter("LOGUSR","Utente","STRING");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		// Dati utente
		$mioFiltro = new wi400Filter("LOGDTA","Dati utente","STRING");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		// ID ultima conversione
		$mioFiltro = new wi400Filter("LOGID","ID ultima conversione","STRING");
		$mioFiltro->setFast(true);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("LOGMOD","Modulo","STRING");
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("LOGOUT", "OutQ Stampa", "STRING");
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("LOGNOM", "File", "STRING");
		$miaLista->addFilter($mioFiltro);
		
		for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
			$miaLista->addKey("LOGKY".$i);
			
			$mioFiltro = new wi400Filter("LOGKY".$i, "Chiave Ricerca $i","STRING");
			$miaLista->addFilter($mioFiltro);
		}
		
		for($i=1; $i<=$settings['modelli_pdf_user_keys']; $i++) {
			$miaLista->addKey("LOGKU".$i);
			
			$mioFiltro = new wi400Filter("LOGKU".$i, "Chiave Utente $i", "STRING");
			$miaLista->addFilter($mioFiltro);
		}
		
		// Stampa
		$action = new wi400ListAction();
		$action->setAction("LOGCONV_STAMPA");
		$action->setForm("STAMPA_SEL");
		$action->setLabel("Stampa");
		$action->setSelection("MULTIPLE");
		$action->setTarget("WINDOW");
		$action->setConfirmMessage("Stampare?");
		$miaLista->addAction($action);
		
		// Stampa Tutto
		$action = new wi400ListAction();
		$action->setAction("LOGCONV_STAMPA");
		$action->setForm("STAMPA_SEL_TUTTO");
		$action->setLabel("Stampa Tutto");
		$action->setSelection("NONE");
		$action->setTarget("WINDOW");
		$action->setConfirmMessage("Stampare TUTTO?");
		$miaLista->addAction($action);
		
		// Rimozione log
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("REMOVE");
		$action->setLabel("Cancella");
		$action->setSelection("MULTIPLE");
		$action->setConfirmMessage("Cancellare?");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}
	else if($actionContext->getForm()=="DOWNLOAD_FILE") {
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
		
		downloadDetail($TypeImage, $filename, $temp, "Esportazione completata");						
	}