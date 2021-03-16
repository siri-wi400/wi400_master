<?php

	$azione = $actionContext->getAction();
//	echo "AZIONE: $azione<br>";
//	echo "FORM: ".$actionContext->getForm()."<br>";
	$database = $settings['database'];
	$campo = "FROM";
	if(isset($_REQUEST['CAMPO']) && $_REQUEST['CAMPO']!="") {
		$campo = $_REQUEST['CAMPO'];
	}
//	echo "CAMPO: $campo<br>";

	if(($azione=="LU_FILE" && $actionContext->getForm()=="DEFAULT") || $azione=="LU_FILE_LIB") {
		// Azione corrente
		$actionContext->setLabel("Lista librerie");
		
		$subfile = new wi400Subfile($db, $azione."_LIBLIST", $settings['db_temp'], 10);
		$subfile->setConfigFileName("LIBLIST");
		
		$array = array();
		$array['LIBNAME']=$db->singleColumns("1", "50", "", "Libreria" );
		$array['LIBDESCR']=$db->singleColumns("1", "50", "", "Descrizione" );

		$subfile->inz($array);
		
		// Eseguo le query in base ai database
		if ($database == 'GENERIC_PDO' || $database == 'GENERIC_PDO_GEN' || $database == 'DB2MYSQLI'){
			$sql = "select NAME,NULL AS DESCRI FROM SYS.DATABASES";
			$sql_stmt = $db->prepareStatement($sql);
			$sql_res = $db->execute($sql_stmt);
			
			while ($row = $db->fetch_array($sql_stmt)){
				$dati = array(
						$row['NAME'],
						$row['DESCRI']
				);
				$subfile->write($dati);
			}
		}
			
		if ($database == 'OCI_PDO_ORACLE'){
			$sql = "select distinct owner as NAME,NULL AS DESCRI from all_objects";
			$sql_stmt = $db->prepareStatement($sql);
			$sql_res = $db->execute($sql_stmt);
		
			while ($row = $db->fetch_array($sql_stmt)){
				$dati = array(
						$row['NAME'],
						$row['DESCRI']
				);
				$subfile->write($dati);
			}
		}

		if ($database == 'DB2AS400' || $database == 'DB2_ODBC'){
			require_once $routine_path."/os400/wi400Os400Object.cls.php";
	
			$list = new wi400Os400Object("*LIB");
			$list->getList();
	
			while ($obj_read = $list->getEntry()) {
				$dati = array( 
					$obj_read['NAME'],
					$obj_read['DESCRIP']
				);   	
			
				$subfile->write($dati);
			}
		}

		$subfile->finalize();
	}
	else if(($azione=="LU_FILE" && $actionContext->getForm()=="FILES") || $azione=="LU_FILE_LIST") {
		// Azione corrente
		$actionContext->setLabel("Lista files");
		
		$libreria = "";
		if($azione=="LU_FILE") {
			$keyArray = getListKeyArray($azione."_LIBLIST");
			$libreria = $keyArray['LIBNAME'];
			
			$actionContext->setLabel("Tabelle libreria: $libreria");
		}
		else {
			$actionContext->setLabel("Tabelle");
		}
//		echo "LIBRERIA: $libreria<br>";
		
		wi400Detail::cleanSession("GESTIONE_DATA_SI");
		
		// Eseguo le query in base ai database
		if ($database == 'DB2AS400' || $database == 'DB2_ODBC'){
			$sql = "select TABLE_NAME, TABLE_TEXT from SYSTABLES where SYSTEM_TABLE<>'Y' and 
				FILE_TYPE ='D' and TABLE_TYPE='P'";
			if($libreria!="") {
				$sql .= " and TABLE_SCHEMA='$libreria'";
			}
			$sql .= " group by TABLE_NAME, TABLE_TEXT";
		}
		
		if ($database == 'GENERIC_PDO' || $database == 'GENERIC_PDO_GEN' || $database == 'DB2MYSQLI'){
			$sql = "SELECT TABLE_NAME,TABLE_CATALOG as TABLE_TEXT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='dbo'";
			if($libreria!="") {
				$sql .= " and TABLE_CATALOG='$libreria'";
			}
		}
		
		if ($database == 'OCI_PDO_ORACLE'){
			$sql = "SELECT A.TABLE_NAME,cast(B.COMMENTS as varchar(50)) as TABLE_TEXT FROM ALL_CATALOG A,ALL_TAB_COMMENTS B 
				WHERE A.TABLE_NAME = B.TABLE_NAME AND A.OWNER = B.OWNER AND TABLE_TYPE='TABLE'";
			if($libreria!="") {
				$sql .= " and A.OWNER='$libreria'";
			}
		}
//		echo "SQL: $sql<br>";
		
		$subfile = new wi400Subfile($db, $azione."_TABLELIST", $settings['db_temp']);
		$subfile->setConfigFileName("TABLELIST");
		
		$subfile->setSql($sql);
		
		$subfile->addParameter("LIBRERIA", $libreria, True);
	}
	else if(in_array($actionContext->getForm(),array("IMPORT","IMPORT_LIB"))) {
		$idList = $_REQUEST["IDLIST"];
//		echo "IDLIST: $idList<br>";
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
		$rowsSelectionArray = $wi400List->getSelectionArray();
//		echo "ROW SEL:<pre>"; print_r($rowsSelectionArray); echo "</pre>";
		
		$params = $wi400List->getParameters();
//		echo "PARAMS:<pre>"; print_r($params); echo "</pre>";
		
		$libreria = $params['LIBRERIA'];
//		echo "LIBRERIA: $libreria<br>";
		
		$files = array_keys($rowsSelectionArray);
//		echo "FILES:<pre>"; print_r($files); echo "</pre>";
		
		if($actionContext->getForm()=="IMPORT_LIB") {
			foreach($files as $key => $file) {
				$files[$key] = $libreria."/".$file;
			}
		}
		
		$files = implode(", ", $files);
//		echo "FILES: $files<br>";
		
//		$_SESSION['FROM_CAMPI'] = $files;

//		$actionContext->gotoAction($azione, "CLOSE_WINDOW");
	}