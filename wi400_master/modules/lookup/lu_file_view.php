<?php

	$spacer = new wi400Spacer();

	if(($azione=="LU_FILE" && $actionContext->getForm()=="DEFAULT") || $azione=="LU_FILE_LIB") {
		$miaLista = new wi400List($azione."_LIBLIST", !$isFromHistory);

		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("LIBNAME");
		
		$miaLista->setSelection('SINGLE');

		$cols = getColumnListFromTable($subfile->getTableName(), $settings['db_temp']);

		$miaLista->setCols($cols);

		// aggiunta chiavi di riga
		$miaLista->addKey("LIBNAME");
		
		$miaLista->addParameter("CAMPO", $campo);

		// Aggiunta filtri
		$toListFlt = new wi400Filter("LIBNAME");
		$toListFlt->setDescription("Libreria");
		$toListFlt->setFast(true);
//		$toListFlt->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($toListFlt);

		$toListFlt = new wi400Filter("LIBDESCR");
		$toListFlt->setDescription("Descrizione");
//		$toListFlt->setFast(true);
		$toListFlt->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($toListFlt);

		// Aggiunta azioni
		if($azione=="LU_FILE") {
			$action = new wi400ListAction();
			$action->setAction($azione);
			$action->setForm("FILES");
			$action->setLabel("Visualizza tabelle fisiche");
			$miaLista->addAction($action);
		}
		else if($azione=="LU_FILE_LIB") {
			$miaLista->setShowMenu(false);
			
			$miaLista->setPassKey(true);
			$miaLista->setPassDesc("TABLE_SCHEMA");
		}
		
		// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
		if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
			$str = addslashes($_REQUEST["ONCHANGE"]);
			$miaLista->setPassKeyJsFunction($str);
		}
		
		listDispose($miaLista);
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}
	else if(($azione=="LU_FILE" && $actionContext->getForm()=="FILES") || $azione=="LU_FILE_LIST") {
		if($azione=="LU_FILE") {
			$ListDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DETAIL");
			$ListDetail->setColsNum(2);
			
			$labelDetail = new wi400Text("LIBRERIA");
			$labelDetail->setLabel("Libreria");
			$labelDetail->setValue($libreria);
			$ListDetail->addField($labelDetail);
			
			$ListDetail->dispose();
			
			$spacer->dispose();
		}
		
		$miaLista = new wi400List($azione."_TABLELIST", True);
		$miaLista->setSubfile($subfile);

		$miaLista->setFrom($subfile->getTable());
		
		if($azione=="LU_FILE")
			$miaLista->setOrder("TABLENAME DESC");
		else if($azione=="LU_FILE_LIST")
			$miaLista->setOrder("TABLENAME ASC");
		
		$miaLista->setSelection('MULTIPLE');

		$cols = getColumnListFromArray('TABLELIST');

		$miaLista->setCols($cols);

		// aggiunta chiavi di riga
		$miaLista->addKey("TABLENAME");
		
		$miaLista->addParameter("LIBRERIA", $libreria);
		$miaLista->addParameter("CAMPO", $campo);
		
		// Aggiunta filtri
		$listFlt = new wi400Filter("TABLENAME");
		$listFlt->setDescription("Nome Tabella");
		$listFlt->setFast(True);
		$miaLista->addFilter($listFlt);
		
		// Aggiunta azioni
		if($azione=="LU_FILE") {
			$action = new wi400ListAction();
			$action->setAction($azione);
			$action->setForm("IMPORT");
			$action->setLabel("Seleziona files");
			$miaLista->addAction($action);
			
			$action = new wi400ListAction();
			$action->setAction($azione);
			$action->setForm("IMPORT_LIB");
			$action->setLabel("Seleziona files con libreria");
			$miaLista->addAction($action);
		}
		else if($azione=="LU_FILE_LIST") {
			$miaLista->setShowMenu(false);
				
			$miaLista->setPassKey(true);
			$miaLista->setPassDesc("TABLE_NAME");
		}
		
		// Verifico se mi è stato passato in $_REQUEST un eventuale onchange
		if (isset($_REQUEST["ONCHANGE"]) AND $_REQUEST["ONCHANGE"] != ""){
			$str = addslashes($_REQUEST["ONCHANGE"]);
			$miaLista->setPassKeyJsFunction($str);
		}
		
		listDispose($miaLista);
/*
		$myButton = new wi400InputButton("IMPORT_BUTTON");
		$myButton->setAction($azione);
		$myButton->setForm("IMPORT");
		$myButton->setLabel("Seleziona");
		$buttonsBar[] = $myButton;
		
		$myButton = new wi400InputButton("IMPORT_BUTTON");
		$myButton->setAction($azione);
		$myButton->setForm("IMPORT_LIB");
		$myButton->setLabel("Seleziona con libreria");
		$buttonsBar[] = $myButton;
*/		
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setScript('history.back()');
		$myButton->setLabel("Indietro");
		$buttonsBar[] = $myButton;
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}
	else if($actionContext->getForm()=="CLOSE_WINDOW") {
		close_window();
	}
	else if(in_array($actionContext->getForm(),array("IMPORT","IMPORT_LIB"))) {
?>	
	<script>
		passValue("<?=$files?>", "<?=$campo?>", true, ", ");
		closeLookUp();
	</script>
<?	
	}