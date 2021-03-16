<?php
  /**
   * @todo : Gestire cancellazione file presenti
   * 		 Cancellazione automatica dei lavori che non dovrebbero più essere presenti nel sistema
   *         Controllare output di ritorno del comando 
   */
	if($actionContext->getForm()=="DEFAULT") {
		
		// Inizializzazione lista
		$miaLista = new wi400List($azione."_LIST", !$isFromHistory);
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("RUNNING DESC");
		$miaLista->setSelection("SINGLE");
		
		$miaLista->setCalculateTotalRows('FALSE');		
		
		$miaLista->setCols(array(
				new wi400Column("RRN","RRN", "","","", False),
				new wi400Column("PID","Pid PHP"),
				new wi400Column("PIDSTS","Stato Pid"),
				new wi400Column("PIDACT","Active Pid"),
				new wi400Column("JOBINFO","Job"),
				new wi400Column("JOBINFOSTS","Stato Job"),
				new wi400Column("JOBINFOACT","Active Job"),
				new wi400Column("DBINFO","DB INFO"),
				new wi400Column("DBINFOSTS","Stato DB"),
				new wi400Column("DBINFOACT","Active DB"),
				new wi400Column("USER","Utente"),
				new wi400Column("SESSIONE","Sessione"),
				new wi400Column("DATA_CREAZIONE","Inizio"),
				new wi400Column("RUNNING","Run", "", "right")
		));
		
		$miaLista->addKey("RRN");
		$colStyle = array();
		$colStyle[] = array('EVAL:$row["ITSME"]=="1"','wi400_grid_green');
		
		foreach($miaLista->getCols() as $col) {
			$col->setStyle($colStyle);
		}
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("KILL_PROCESS");
		$action->setConfirmMessage("Sei sicuro di volere cancellare tutti i processi attivi?");
		$action->setLabel("Cancella tutti i processi");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);

		
		$miaLista->dispose();
	}

?>