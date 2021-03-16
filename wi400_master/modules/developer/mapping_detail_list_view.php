<?php

	if($form == 'DEFAULT') {
		
		$sql = '';
		$sql_union = array();
		$tabella = "SYSIBM/SYSDUMMY1";
		
		foreach($_SESSION['DEVELOPER_MAPPING_OBJ'] as $dati => $tipo) {
			list($curr_azione, $id) = explode("|", $dati);
			
			$sql_union[] = "SELECT '$id' as OBJ, '$tipo' as TYPE, '$curr_azione' as AZIONE from $tabella";
		}
		
		if($sql_union) {
			$miaLista = new wi400List($azione."_LIST", true);
			
			$miaLista->setIncludeFile('developer', 'mapping_detail_list_functions.php');
			
			$sql = implode(" UNION ALL ", $sql_union);
			
			//showArray($sql);
			
			$miaLista->setFrom("(".$sql.") as x");
			
			$mappa_col = new wi400Column("MODIFICA", "Mappa", "", "CENTER");
			$mappa_col->setDecorator("ICON_MAPPING");
			$mappa_col->setDefaultValue('EVAL:$row["OBJ"]."|".$row["TYPE"]."|".$row["AZIONE"]');
			$mappa_col->setSortable(false);
			$mappa_col->setExportable(false);
			$mappa_col->setActionListId("MODIFICA");
			
			$stato_col = new wi400Column('IS_MAPPING', "Stato");
			$stato_col->setDefaultValue('EVAL:checkIsMapping($row["OBJ"], $row["TYPE"], $row["AZIONE"])');
			$colStyle = array();
			$colStyle[] = array('EVAL:$row["IS_MAPPING"]=="Mappato"','wi400_grid_green');
			$colStyle[] = array('EVAL:$row["IS_MAPPING"]=="Da mappare"','wi400_grid_red');
			$stato_col->setStyle($colStyle);
			
			$dettaglio_col = new wi400Column("ABILITAZIONI", "Abilitazioni", "", "CENTER");
			$dettaglio_col->setDecorator("ICONS");
			$dettaglio_col->setDefaultValue('SEARCH');
			$dettaglio_col->setSortable(false);
			$dettaglio_col->setExportable(false);
			$dettaglio_col->setActionListId("SEARCH");
			
			$miaLista->setCols(array(
				$mappa_col,
				$dettaglio_col,
				new wi400Column('OBJ', 'Oggetto'),
				new wi400Column('TYPE', 'Tipo'),
				new wi400Column('AZIONE', 'Azione'),
				$stato_col
			));
			
			$miaLista->addKey('OBJ');
			$miaLista->addKey('TYPE');
			$miaLista->addKey('AZIONE');
			
			$action = new wi400ListAction();
			$action->setId("MODIFICA");
			$action->setLabel("Modifica");
			$action->setAction('ABILITAZIONI_CAMPI_DETAIL');
			$action->setForm("MAP_DETAIL");
			$action->setGateway('FROM_DEVELOPER');
			$miaLista->addAction($action);
			
			$action = new wi400ListAction();
			$action->setId("SEARCH");
			$action->setLabel("Abilitazioni");
			$action->setAction('ABILITAZIONI_CAMPI_DETAIL');
			$action->setForm("UTENTI");
			$action->setGateway('FROM_DEVELOPER');
			$miaLista->addAction($action);
			
			
			
			$miaLista->dispose();
			
		}
		
		
	}