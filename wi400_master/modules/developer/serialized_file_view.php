<?php
if($actionContext->getForm()=="DEFAULT") {
		// Inizializzazione lista
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("TIPO desc, FILE_NAME desc");
		$miaLista->setSelection("MULTIPLE");
		
		//		$miaLista->setCalculateTotalRows('FALSE');
		
		$file_col = new wi400Column("FILE_NAME","File");
		//$file_col->setDetailAction($azione, "FILE_PRV");
		$file_col->setActionListId('NAVIGA_OGGETTO');
		
		$cols = array();
		$cols[] = $file_col;
		
		if($azione=="LOG_MANAGER")
			$cols[] = $tipo_col;
		
		$cols[] = new wi400Column("DIMENSIONE","Dimensione (Bytes)", "INTEGER", "right");
		$cols[] = new wi400Column("HAS_SUBFILE","Subfile", "STRING", "left");
		$cols[] = new wi400Column("ATIME","Ultimo Accesso", "STRING", "left");
		$cols[] = new wi400Column("MTIME","Ultima Modifica", "STRING", "left");
		$cols[] = new wi400Column("CTIME","Ultimo Cambiamento", "STRING", "left");
		
		
		$miaLista->setCols($cols);
		
		$miaLista->addKey("FILE_NAME");
		$miaLista->addKey("NREL");
		//
		// Aggiunta filtri
		$listFlt = new wi400Filter("FILE_NAME");
		$listFlt->setDescription("Nome File");
		$listFlt->setFast(True);
		$miaLista->addFilter($listFlt);
		// Navigazione su oggetto
		$action = new wi400ListAction();
		$action->setId('NAVIGA_OGGETTO');
		$action->setAction("NAVIGATE_OBJECT");
		$action->setLabel("Naviga su Oggetto");
		$action->setTarget("WINDOW", 800, 600);
		$action->setForm("DETAIL");
		$action->setGateway("SERIALIZED_FILE");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		// Cancellazione di un oggetto
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setLabel("Cancella Oggetto");
		$action->setForm("DELETE_OBJECT");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		// Cancellazione di tutti gli oggetti
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setLabel("Cancella Tutto");
		$action->setForm("DELETE__ALL_OBJECT");
		$action->setSelection("NONE");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
	}