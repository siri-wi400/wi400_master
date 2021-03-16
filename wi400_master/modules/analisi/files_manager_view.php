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
		$file_col->setDetailAction($azione, "FILE_PRV");
		
		$tipo_col = new wi400Column("TIPO","Tipo");
		$tipo_col_cond = array();
		$tipo_col_cond[] = array('EVAL:$row["TIPO"]=="ERROR"', 'wi400_font_red');
		$tipo_col_cond[] = array('EVAL:$row["TIPO"]=="DEBUG"', 'wi400_font_orange');
		$tipo_col_cond[] = array('EVAL:$row["TIPO"]=="EMAIL"', 'wi400_font_blue');
		$tipo_col_cond[] = array('EVAL:$row["TIPO"]=="SQL"', 'wi400_font_green');
		$tipo_col->setStyle($tipo_col_cond);
		
		$cols = array();
		$cols[] = $file_col;
		
		if($azione=="LOG_MANAGER")
			$cols[] = $tipo_col;
		
		$cols[] = new wi400Column("DIMENSIONE","Dimensione (Bytes)", "INTEGER", "right");
		
		$miaLista->setCols($cols);
		
		$miaLista->addKey("FILE_NAME");
		
		if($azione=="LOG_MANAGER") {
/*			
			$mioFiltro = new wi400Filter("TIPO","Tipo","STRING"); 
			$mioFiltro->setFast(true);    
			$miaLista->addFilter($mioFiltro);
*/			
			$myFilter = new wi400Filter("TIPO", "Tipo", "SELECT", "");
			$filterValues = array();
			foreach($log_files_paths as $key => $val) {
				$type = substr($key,4);
				$filterValues["TIPO='$type'"] = $type;
			}
//			echo "FILTERS:<pre>"; print_r($filterValues); echo "</pre><br>";
			$myFilter->setSource($filterValues);
			$miaLista->addFilter($myFilter);
		}

		$action = new wi400ListAction();
		$action->setAction("LOG_VIEWER");
		$action->setForm("DEFAULT");
		$action->setLabel("Visualizza file");
		$action->setSelection("SINGLE");
		$miaLista->addAction($action);
		
		$action = new wi400ListAction();
		$action->setAction($azione);
		$action->setForm("DELETE_FILES");
		$action->setLabel("Elimina files");
		$action->setSelection("MULTIPLE");
		$miaLista->addAction($action);
		
		$miaLista->dispose();
	}
	else if($actionContext->getForm()=="FILE_PRV") {
		$TypeImage = "";

		$file_parts = pathinfo($file_path);
		if(isset($file_parts['extension']))
			$TypeImage = strtolower($file_parts['extension']);
				
		downloadDetail($TypeImage, $file_path, "", "Esportazione completata");
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}