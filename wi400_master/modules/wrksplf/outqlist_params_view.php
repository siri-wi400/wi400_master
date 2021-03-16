<?php

	if($actionContext->getForm()=="DEFAULT") {
		$subfile = new wi400Subfile($db, $azione."_LIST", $settings['db_temp'], 10);
		$array = array();
		$array['OUTQNAME']=$db->singleColumns("1", "10", "", "OutQ" );
		$array['OUTQLIB']=$db->singleColumns("1", "10", "", "Libreria" );
		$array['OUTQDESCR']=$db->singleColumns("1", "50", "", "Descrizione" );
//		$array['OUTQSPOOL']=$db->singleColumns("3", "7", "0", "Numero Spool" );
		$array['DUPLEX']=$db->singleColumns("1", "1", "", "Duplex" );
		$array['DRIVER']=$db->singleColumns("1", "20", "", "Driver di stampa" );
		
		$subfile->inz($array);
		
		require_once $routine_path."/os400/wi400Os400Object.cls.php";
//		require_once $routine_path."/os400/wi400Os400Spool.cls.php";
		
		$list = new wi400Os400Object("*OUTQ");
		$list->getList();
		
		$sql_par = "select * from FP2OPARM where PROUTQ=?";
		$stmt_par = $db->singlePrepare($sql_par, 0, true);
		
		while ($obj_read = $list->getEntry()) {		
//			$dati = wi400Os400Spool::getOutqInfo(str_pad($obj_read['NAME'], 10).$obj_read['LIBRARY']);	
			$duplex = "";
			$driver = "";
			
			$res_par = $db->execute($stmt_par, array($obj_read['NAME']));
			if($row_par = $db->fetch_array($stmt_par)) {
				$duplex = $row_par['PRDUPX'];
				$driver = $row_par['PRDRIV'];
			}
				
			$dati = array( 
				$obj_read['NAME'],
				$obj_read['LIBRARY'],
				$obj_read['DESCRIP'],
//				$dati['QSPBCP'],
				$duplex,
				$driver
		    );   	
			
			$subfile->write($dati);
		}
		
		$subfile->finalize();
		
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("OUTQNAME, OUTQLIB");
		
		$miaLista->setSelection("MULTIPLE");
		
//		$cols = getColumnListFromTable($subfile->getTableName(), $settings['db_temp']);

		// Duplex
		$duplex_col = new wi400Column("DUPLEX", "Duplex");
		$duplex_col->setAlign('center');
		$duplex_col->setHeaderCallBack("setUpdateStatus(UPDATE_STATUS_ON)");
		
		// Azione di spunta colonna
		$duplex_col->setHeaderAction($azione);
		$duplex_col->setHeaderForm("CHECK_ALL");
		$duplex_col->setHeaderIco(array("uncheck.png","check.png"));
		$duplex_col->setHeaderCallBack("setUpdateStatus(UPDATE_STATUS_ON)");
		
		$inputField = new wi400InputCheckbox("DUP_COL");
		$inputField->setCheckUpdate(True);
		$inputField->setValue("S");
		$inputField->setUncheckedValue("N");
		
		$duplex_col->setInput($inputField);
		$duplex_col->setSortable(false);

		// Driver
		$driver_col = new wi400Column("DRIVER","Driver<br>di stampa");
		$driver_col->setHeaderCallBack("setUpdateStatus(UPDATE_STATUS_ON)");
		
		$inputField = new wi400InputText("DRIVER_STAMPA");
		$inputField->setSize(20);
		$inputField->setMaxLength(20);
		$inputField->setCheckUpdate(True);
		
		$driver_col->setInput($inputField);
		
		$cols = array(
			new wi400Column("OUTQNAME", "OutQ"),
			new wi400Column("OUTQLIB", "Libreria"),
			new wi400Column("OUTQDESCR", "Descrizione"),
//			new wi400Column("OUTQSPOOL", "Numero Spool", "INTEGER", "right"),
			new wi400Column("DUPLEX", "Duplex"),
			$duplex_col,
			$driver_col,	
		);
		
		$miaLista->setCols($cols);
		// Numero lavoro lo voglio a Destra
		
		// aggiunta chiavi di riga
		$miaLista->addKey("OUTQNAME");
		$miaLista->addKey("OUTQLIB");
		
		// Aggiunta filtri
		$toListFlt = new wi400Filter("OUTQNAME");
		$toListFlt->setDescription("Coda di stampa");
		$toListFlt->setFast(true);
		$miaLista->addFilter($toListFlt);
		
		// Aggiunta azioni
		$action = new wi400ListAction();
		$action->setAction("SPOOLLIST");
		$action->setLabel("Visualizza contenuto");
		$miaLista->addAction($action);
		
		// Salva modifiche
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("SAVE");
		$action->setLabel("Salva modifiche");
		$action->setConfirmMessage("Salvare?");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		// Stampa di prova
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("PRINT_PROVA");
		$action->setLabel("Stampa di prova");
		$action->setConfirmMessage("Eseguire stampa di prova?");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		listDispose($miaLista);
	}