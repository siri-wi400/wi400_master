<?php
	
	$spacer = new wi400Spacer();
	
	if($actionContext->getForm()=="DEFAULT") {
		$ListDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DETAIL");
		$ListDetail->setColsNum(2);
		
		$labelDetail = new wi400Text("FILES");
		$labelDetail->setLabel("Files");
		$labelDetail->setValue(implode("<br>",$files));
		$ListDetail->addField($labelDetail);
		
		$ListDetail->dispose();
		
		$spacer->dispose();
		
		$miaLista = new wi400List($azione."_".$actionContext->getForm()."_LIST", !$isFromHistory);
			
		$miaLista->setSubfile($subfile);
		$miaLista->setFrom($subfile->getTable());
		$miaLista->setOrder("NOME_FILE");
		$miaLista->setSelection('MULTIPLE');
//		$miaLista->setSelection('SINGLE');
	
//		$miaLista->setPassKey("ID");
		
		$miaLista->setCols(array(
			new wi400Column("ID","ID Campo"),
			new wi400Column("REMARKS","REMARKS"),
//			new wi400Column("DES_CAMPO","Descrizione Campo"),
			new wi400Column("NOME_FILE","ID File"),
			new wi400Column("DES_FILE","Descrizione File"),
			new wi400Column("DATA_TYPE","DATA_TYPE","INTEGER","right"),
			new wi400Column("DATA_TYPE_STRING","DATA_TYPE_STRING"),
			new wi400Column("NUM_SCALE","NUM_SCALE"),
			new wi400Column("LENGTH_PRECISION","LENGTH_PRECISION","INTEGER","right"),
			new wi400Column("VIDEO_LENGTH","VIDEO_LENGTH","INTEGER","right"),
			new wi400Column("BUFFER_LENGTH","BUFFER_LENGTH","INTEGER","right"),
			new wi400Column("HEADING","HEADING"),
			new wi400Column("COLUMN_DEFAULT","COLUMN_DEFAULT"),
			new wi400Column("IS_NULLABLE","IS_NULLABLE"),
			new wi400Column("DATETIME_CODE","DATETIME_CODE"),
			new wi400Column("CCSID","CCSID")
		));
		
		$miaLista->addKey("ID");
		
		$mioFiltro = new wi400Filter("ID","ID Campo","STRING");
		$mioFiltro->setFast(true);
		$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($mioFiltro);
		
		$mioFiltro = new wi400Filter("DES_CAMPO","Descrizione Campo","STRING");
		$mioFiltro->setFast(true);
		$mioFiltro->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
		$miaLista->addFilter($mioFiltro);
		
		$miaLista->addParameter("SOURCE", $source);
		$miaLista->addParameter("FROM", $from);
		
		if($azione=="LU_FILE_TABLE") {
			$action = new wi400ListAction();
			$action->setAction($azione);
			$action->setForm("IMPORT");
			$action->setLabel("Seleziona campi");
			$miaLista->addAction($action);
		}
		else if($azione=="LU_FILE_TABLE_LIST") {
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
/*		
		$hiddenField = new wi400InputHidden("SOURCE");
		$hiddenField->setValue($source);
		$hiddenField->dispose();
		
		$hiddenField = new wi400InputHidden("FROM");
		$hiddenField->setValue($from);
		$hiddenField->dispose();
		
		$myButton = new wi400InputButton("IMPORT_BUTTON");
		$myButton->setAction($azione);
		$myButton->setForm("IMPORT");
		$myButton->setLabel("Seleziona");
		$buttonsBar[] = $myButton;
*/		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}
	else if($actionContext->getForm()=="CLOSE_WINDOW") {
		close_window();
	}
	else if($actionContext->getForm()=="IMPORT"){
?>	
	<script>
		passValue("<?=$campi?>", "<?=$source?>", true, ", ");
		closeLookUp();
	</script>
<?	
	}