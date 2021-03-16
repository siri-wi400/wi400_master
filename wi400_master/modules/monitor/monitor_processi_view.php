<?php

	if($form == 'DEFAULT') {
		$miaLista = new wi400List($azione."_LIST", true);//!$isFromHistory);
		$miaLista->setSubfile($subfile);
		$miaLista->setSelection("MULTIPLE");
		
		$styleCond = array();
		$styleCond[] = array('EVAL:$row["PROSID"]==session_id()', 'wi400_grid_orange');
		$sessione_col = new wi400Column('PROSID', 'Sessione');
		$sessione_col->setStyle($styleCond);
		
		$dettaglio = new wi400Column("DETTAGLIO_LOG", "", "", "CENTER");
		$dettaglio->setDecorator("ICONS");
		$dettaglio->setDefaultValue("SEARCH");
		$dettaglio->setSortable(false);
		$dettaglio->setExportable(false);
		$dettaglio->setActionListId($azione."_DET");

		$miaLista->setCols(array(
			$dettaglio,
			$sessione_col,
			new wi400Column('PROPID', 'Id php'),
			new wi400Column('PROJOA', 'Id AS400'),
			new wi400Column('ATV_AS400', 'Lavoro attivo'),
			new wi400Column('STATUS_AS400', 'Stato lavoro'),
			new wi400Column('PROJAD', 'Id DB'),
			new wi400Column('ATV_DB', 'Lavoro attivo'),
			new wi400Column('STATUS_DB', 'Stato lavoro'),
			new wi400Column('PROUSR', 'Utente'),
			new wi400Column('PROURL', 'Ambiente'),
			new wi400Column('PROAZI', 'Azione'),
			new wi400Column('PROFRM', 'Form'),
			new wi400Column('PROTIF', 'First click', 'TIMESTAMP'),
			new wi400Column('PROTIL', 'Last click', 'TIMESTAMP'),
			new wi400Column('PROTIE', 'End click', 'TIMESTAMP'),
			new wi400Column('PROSTA', 'Stato'),
			
		));
		
		$miaLista->addKey('PROSID');
		$miaLista->addKey('PROPID');
		$miaLista->addKey('PROJOA');
		$miaLista->addKey('PROJAD');
		$miaLista->addKey('PROAZI');
		$miaLista->addKey('PROUSR');
		
		$miaLista->addColGroup('AS400', '<center>AS400</center>', 'GREEN');
		$miaLista->addColGroup('DB', '<center>DB</center>', 'YELLOW');
		
		foreach($miaLista->getCols() as $id => $col) {
			if(in_array($id, array('PROJOA', 'ATV_AS400', 'STATUS_AS400'))) {
				$col->setGroup('AS400');
			}
			if(in_array($id, array('PROJAD', 'ATV_DB', 'STATUS_DB'))) {
				$col->setGroup('DB');
			}
		}
		
		// Dettaglio articolo
		$action = new wi400ListAction();
		$action->setId($azione."_DET");
		$action->setAction('DEVELOPER_DOC');
		$action->setForm("DETAIL");
		$action->setGateway('ESCAPE_PID');
		$action->setLabel("Dettaglio");
		$action->setSelection("SINGLE");
		$action->setTarget('WINDOW', 1100, 700);
		$miaLista->addAction($action);
		
		// Dettaglio articolo
		$action = new wi400ListAction();
		$action->setLabel("Attività");
		$action->setAction($azione);
		$action->setForm("ATTIVITA");
		$action->setSelection("MULTIPLE");
		$action->setTarget('WINDOW', 1100, 700);
		$miaLista->addAction($action);
		
		$miaLista->dispose();
	}else if($form == 'ATTIVITA') {
		$detail = new wi400Detail($azione.'_OPERAZIONI', true);
		
		$myField = new wi400InputSwitch("KILL_JOB_PHP");
		$myField->setLabel("Kill job php");
		//$myField->setChecked($exit=="E");
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");
		$detail->addField($myField);
		
		$myField = new wi400InputSwitch("KILL_JOB_AS400");
		$myField->setLabel("Kill job AS400");
		//$myField->setChecked($exit=="E");
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");
		$detail->addField($myField);
		
		$myField = new wi400InputSwitch("KILL_JOB_DB");
		$myField->setLabel("Kill job DB");
		//$myField->setChecked($exit=="E");
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");
		$detail->addField($myField);
		
		$myField = new wi400InputSwitch("KILL_SESSION");
		$myField->setLabel("Kill sessione");
		//$myField->setChecked();
		$myField->setOnLabel("SI");
		$myField->setOffLabel("NO");
		$detail->addField($myField);
		
		$myButton = new wi400InputButton('UPDATE_BUTTON');
		$myButton->setLabel(_t('APPLY'));
		$myButton->setAction($azione);
		$myButton->setForm("ESEGUI_ATTIVITA");
		$myButton->setValidation(true);
		$myButton->setConfirmMessage('Sicuro di voler proseguire?');
		$detail->addButton($myButton);
		
		$myButton = new wi400InputButton('CANCEL_BUTTON');
		$myButton->setLabel(_t('CANCEL'));
		$myButton->setAction('CLOSE');
		$myButton->setForm("CLOSE_WINDOW");
		$detail->addButton($myButton);
		
		$detail->dispose();
		
		echo "<br>";
		
		//Dettaglio con le righe selezionate
		$actionDetail = new wi400Detail($azione.'_LIST_DET', true);
		
		$tableDetail = new wi400Table("SELEZIONI");
		$tableDetail->setLabel('Processi selezionati');
		
		$tableDetail->setCols(array(
			new wi400Column('PROSID', 'Sessione'),
			new wi400Column('PROPID', 'Id php'),
			new wi400Column('PROJOA', 'Id AS400'),
			new wi400Column('PROJAD', 'Id DB'),
			new wi400Column('PROUSR', 'Utente'),
			/*new wi400Column('PROSTA', 'Stato'),
			new wi400Column('PROTIF', 'First click', 'TIMESTAMP'),
			new wi400Column('PROTIL', 'Last click', 'TIMESTAMP'),
			new wi400Column('PROTIE', 'End click', 'TIMESTAMP'),
			
			new wi400Column('PROAZI', 'Azione'),
			new wi400Column('PROFRM', 'Form'),
			new wi400Column('ATV_AS400', 'Lavoro attivo'),
			new wi400Column('STATUS_AS400', 'Stato lavoro'),
			new wi400Column('ATV_DB', 'Lavoro attivo'),
			new wi400Column('STATUS_DB', 'Stato lavoro')*/
		));
		
		foreach($rowsSelectionArray as $key => $value){
			$keyArray = get_list_keys_num_to_campi($wi400List, explode("|",$key));
			//showArray($keyArray);
						
			$tab_row = array(
				$keyArray['PROSID'],
				$keyArray['PROPID'],
				$keyArray['PROJOA'],
				$keyArray['PROJAD'],
				$keyArray['PROUSR']
			);
				
			$tableDetail->addRow($tab_row);
		}
		
		$actionDetail->addField($tableDetail);
		
		$actionDetail->dispose();
		
	}