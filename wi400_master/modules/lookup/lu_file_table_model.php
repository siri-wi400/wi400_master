<?php
	
	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()=="DEFAULT") {
		if(isset($_REQUEST["TITLE"])) {
			$actionContext->setLabel($_REQUEST["TITLE"]);
		}
		else {
			$actionContext->setLabel("Struttura delle tabelle");
		}
		
		$source = $_REQUEST['SOURCE'];		
		$from = $_REQUEST['FROM'];
//		echo "<font color='red'>FROM FIELD:</font> $from<br>";
		
		$search = array("from","join","inner","left","right","on", ",");
		$from = str_replace($search,"",$from);
//		echo "FROM: $from<br>";
		
		$files = explode(" ", $from);
//		echo "<font color='blue'>FILES:</font> "; print_r($files); echo "</pre>";
		
		subfileDelete($azione."_LIST");
			
		$subfile = new wi400Subfile($db, "LU_FILE_TABLE_LIST", $settings['db_temp'], 20);
		$subfile->setConfigFileName("LU_FILE_TABLE_LIST");
		$subfile->setModulo('lookup');
		
		$subfile->addParameter("FILES", $files, true);
		
		$subfile->setSql("*AUTOBODY");
/*	
		if(!empty($files)) {
			foreach($files as $file) {
				
				$sql_query = "select * from $file";
				echo "SQL: $sql_query<br>";
				
				// Impostazione formato tabella
				$res = $db->singleQuery($sql_query);
				
				if(!$res)	
					$messageContext->addMessage("ERROR","Query errata: ".$sql_query);
				else {	
					$riga = $db->fetch_array($res);
					echo "ROW:<pre>"; print_r($riga); echo "</pre>";
					
					$campi = array_keys($riga);
					echo "CAMPI:<pre>"; print_r($campi); echo "</pre>";
				}
				
			}
		}
*/
	}
	else if($actionContext->getForm()=="IMPORT") {
		$idList = $_REQUEST["IDLIST"];
//		echo "IDLIST: $idList<br>";
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$rowsSelectionArray = $wi400List->getSelectionArray();
//		echo "ROW SEL:<pre>"; print_r($rowsSelectionArray); echo "</pre>";
		
		$campi = implode(", ", array_keys($rowsSelectionArray));
//		echo "CAMPI: $campi<br>";
/*		
		$source = $_REQUEST['SOURCE'];
		$from = $_REQUEST['FROM'];
*/
		$params = $wi400List->getParameters();
//		echo "PARAMS:<pre>"; print_r($params); echo "</pre>";
		
		$source = $params['SOURCE'];
//		echo "SOURCE: $source<br>";
		
//		$_SESSION[$source.'_CAMPI'] = $campi;
	
//		$actionContext->gotoAction($azione, "CLOSE_WINDOW");
	}