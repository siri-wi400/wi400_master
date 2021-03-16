<?php

	error_reporting(E_ALL);
	
//	ini_set("display_errors", true);

	require_once $routine_path.'/classi/wi400Map.cls.php';

	$history->addCurrent();
		
	if ($actionContext->getForm() == "DEFAULT"){
		$keyArray = getListKeyArray("POSTI_ARTICOLO");
		$posto = $keyArray['POSTO'];
		
		$actionContext->setLabel("Mappa deposito-Posto ".$posto);

		$deposito = wi400Detail::getDetailValue("SEARCH_ARTICOLI_DEPOSITO","deposito");
				
		$mappa = new wi400Map($deposito, "DEPOSITO");	
		$point = new wi400MapPoint($posto, "PALLET");
		$mappa->addPoint($point);
	}
	if ($actionContext->getForm()=="POSTI_ARTICOLO"){
		$actionContext->setLabel("Mappa deposito");
		
		$wi400List = new wi400List();
		$wi400List =  getList("POSTI_ARTICOLO");
		
		$query = "SELECT ".$wi400List->getField()." FROM ".$wi400List->getFrom()." ";
		$query = $query.$wi400List->getWhere();	
		$result = $db->query($query);
		
		$deposito = wi400Detail::getDetailValue("SEARCH_ARTICOLI_DEPOSITO","deposito");
				
		$mappa = new wi400Map($deposito, "DEPOSITO");
			
		while ($row = $db->fetch_array($result)){
			$point = new wi400MapPoint($row['POSTO'], "PALLET");
			$mappa->addPoint($point);	           
		}
	}
	if ($actionContext->getForm()=="DETTAGLIO_ROLL"){
		$actionContext->setLabel("Mappa articoli del roll");
		
		$wi400List = new wi400List();
		$wi400List =  getList("DETTAGLIO_ARTICOLI_ROLL");
		
		$deposito = wi400Detail::getDetailValue("SEARCH_ROLL","deposito");
			
		$query = "SELECT ".$wi400List->getField()." FROM ".$wi400List->getFrom()." ";
		$query = $query.$wi400List->getWhere();	
		$result = $db->query($query);
		
		$mappa = new wi400Map($deposito, "DEPOSITO");
			
		while ($row = $db->fetch_array($result)){
			$point = new wi400MapPoint($row['POSTO_PICKING'], "PALLET");
			$point->setText($row['POSTO_PICKING']."\r\n".$row['ARTICOLO']."\r\n".$row['DESART']);
			$mappa->addPoint($point);	           
		}
	}
	if ($actionContext->getForm()=="PRESENZE_LIST"){
		$keyArray = getListKeyArray("TRACCIA_PRESENZE_LIST");
		$posto = $keyArray["POSTO"];	
	
		$actionContext->setLabel("Mappa deposito-Posto ".$posto);
		
		$mappa = new wi400Map($keyArray["DEPOSITO"], "DEPOSITO");
		$point = new wi400MapPoint($posto, "PALLET");
		$point->setText($posto);
		$mappa->addPoint($point);
	}
	if ($actionContext->getForm()=="DETTAGLIO_ROLL_DEPOSITO"){
		$actionContext->setLabel("Utilizzo bay in deposito");
		
		$wi400List = new wi400List();
		$wi400List =  getList("DETTAGLIO_ROLL_DEPOSITO");
		$tabella = $wi400List->getFrom();
		
		$query = "SELECT RAGZOD, RAGCOR, RAGBAY, RAGCDP, RAGCDA, sum(ragcpr) AS COLLI 
			, count(*) AS COUNT FROM $tabella,FRAGROLR WHERE RAGNRA = ORDINE AND RAGNRO = NUMROL AND RAGSTA <> '0'
 			GROUP BY RAGZOD, RAGCOR, RAGBAY, RAGCDP, RAGCDA  ORDER BY COUNT DESC, COLLI DESC";	
 		$result = $db->query($query);
	
 		$deposito = wi400Detail::getDetailValue("SEARCH_ROLL","deposito");	
	
 		$mappa = new wi400Map($deposito, "DEPOSITO");	
	
 		$first = True;
	
 		while ($row = $db->fetch_array($result)){
			if ($first){
		    	$coeff = round(255 / $row['COUNT']);
		    	$first = false;
		    }
		     
		    $postoPicking = $row['RAGZOD']."-".$row['RAGCOR']."-".$row['RAGBAY']."-".$row['RAGCDP'];
			$point = new wi400MapPoint($postoPicking, "PALLET");
			$point->setText($postoPicking."\r\n".$row['RAGCDA'].".\r\n Colli $row[COLLI] Passaggi $row[COUNT]");
			$temp = round($row['COUNT']*$coeff);
			$point->setCustomColor(255, 255-$temp, 0);
			$mappa->addPoint($point);	           
		}
	}
	if ($actionContext->getForm() == "NAVIGAZIONE_MAPPA"){
		
 		$deposito = wi400Detail::getDetailValue("NAVIGAZIONE_MAPPA","deposito");	
	
 		$mappa = new wi400Map($deposito, "DEPOSITO");
	}
/*	
	else if($actionContext->getForm()=="SATURAZIONE_MAGAZZINO") {
		$deposito = wi400Detail::getDetailValue("SATURAZIONE_MAGAZZINO_SRC","DEPOSITO");
		
		$keyArray = getListKeyArray($_POST['IDLIST']);
		
		$posto = $keyArray['ZONA']."-".$keyArray['CORRIDOIO']."-".$keyArray['BAY']."-".$keyArray['POST'];
		
		$mappa = new wi400Map($deposito, "DEPOSITO");	
		$point = new wi400MapPoint($posto, "PALLET");
		$mappa->addPoint($point);
	}
*/
	else if($actionContext->getForm()=="SATURAZIONE_MAGAZZINO_SEL") {
		// Azione corrente
		$actionContext->setLabel("Visualizza posti selezionati su mappa");
		
		$deposito = wi400Detail::getDetailValue("SATURAZIONE_MAGAZZINO_SRC","DEPOSITO");
		
		$idList = $_POST['IDLIST'];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['IDLIST']);
		
		// Recupero degli elementi selezionati
		$rowsSelectionArray = $wi400List->getSelectionArray();
		
//		echo "SEL:<pre>"; print_r($rowsSelectionArray); echo "</pre><br>";
		
		$mappa = new wi400Map($deposito, "DEPOSITO");
		
		foreach($rowsSelectionArray as $key => $val) {
			$keyArray = array();
			$keyArray = explode("|",$key);
			$posto = $keyArray[2]."-".$keyArray[3]."-".$keyArray[4]."-".$keyArray[5];
			
//			echo "ART: ".$keyArray[0]." - POSTO: $posto<br>";
			
			$point = new wi400MapPoint($posto, "PALLET");
			$point->setText($posto."\r\n".$keyArray[0].".\r\n$keyArray[1]");
			$mappa->addPoint($point);
			
		}
	}
	else if($actionContext->getForm()=="SATURAZIONE_MAGAZZINO_ALL") {
		// Azione corrente
		$actionContext->setLabel("Visualizza tutti i posti su mappa");
		
		$deposito = wi400Detail::getDetailValue("SATURAZIONE_MAGAZZINO_SRC","DEPOSITO");
		
		$idList = $_POST['IDLIST'];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['IDLIST']);
		
		$sql = $wi400List->getSql();
		
		// Recupero della query
		// ***************************************************
		// FILTRO
		// ***************************************************
		
		$filterWhere = "";
		$hasFilter = "false";
		
		foreach ($wi400List->getFilters() as $filter){
			if ($filter->getValue() != ""){
				
				// Segnalo la presenza di filtri
				if (!$filter->getFast()) $hasFilter = "true";
				
				if ($filterWhere != ""){
					$filterWhere = $filterWhere." AND ";
				}else{
					$filterWhere = " WHERE ";
				}
				
				$valueToSearch = $filter->getValue();
					
				$option = $filter->getOption();
				$filterKey = $filter->getkey();
				if ($filter->getType() == "STRING" &&
						!$filter->getCaseSensitive()===true){
					$filterKey = "UPPER(".$filterKey.")";
				}
				$filterWhere = $filterWhere.$filterKey;
				
				if ($filter->getType() == "STRING"){
					if ($option == "EQUAL") $filterWhere = $filterWhere." = ";
					if ($option == "START" || $option == "INCLUDE") $filterWhere = $filterWhere." LIKE ";
					$filterWhere = $filterWhere."'";
					if ($option == "INCLUDE") $filterWhere = $filterWhere."%";
					$filterWhere = $filterWhere.$valueToSearch;
					if ($option == "START" || $option == "INCLUDE") $filterWhere = $filterWhere."%";
					$filterWhere = $filterWhere."'";
				}else if ($filter->getType() == "NUMERIC"){
					$filterWhere = $filterWhere.$option.$valueToSearch;
				}else if ($filter->getType() == "CHECK_STRING"){
					$filterWhere = $filterWhere." ".$valueToSearch." ";
				}else if ($filter->getType() == "CHECK_NUMERIC"){
//					$filterWhere = $filterWhere." = ".$valueToSearch;
					$filterWhere = $filterWhere." ".$valueToSearch." ";
				}else if ($filter->getType() == "LOOKUP"){
					$filterWhere = $filterWhere." = ";
					$filterWhere = $filterWhere."'";
					$filterWhere = $filterWhere.$valueToSearch;
					$filterWhere = $filterWhere."'";
				}
			}
		}
		
		$sql .= $filterWhere;
			
		$result = $db->query($sql);
		
		$mappa = new wi400Map($deposito, "DEPOSITO");
		
		while($row = $db->fetch_array($result)) {
			$posto = $row['ZONA']."-".$row['CORRIDOIO']."-".$row['BAY']."-".$row['POST'];
			
			$point = new wi400MapPoint($posto, "PALLET");
			$point->setText($posto."\r\n".$row['ARTICOLO'].".\r\n$row[DES_ART]");
			$mappa->addPoint($point);
		}
	}
	
?>