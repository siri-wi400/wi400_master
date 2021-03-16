<?php 
/*
 * Link per documetazione API interfacciamento GoogleMap:http://code.google.com/p/php-google-map-api/
 */

   	error_reporting(E_ALL&E_NOTICE);
//	ini_set("display_errors", true);
    //require_once $routine_path.'/easyGoogleMap/EasyGoogleMap.class.php';
    include_once($routine_path."/googleMapAPI/GoogleMap.php");
    include_once($routine_path."/googleMapAPI/JSMin.php");
    //$settings['googleMapKey']="ABQIAAAAmiag7sjtDKGksv0q_TBc1BRLe-1ghZI0wrX9FytkuF0UrJ1rOhTBErks4J3adCnAKk4PkhFWP09yhg";
  	$history->addCurrent();
  	if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
		$server = $_SERVER['HTTP_X_FORWARDED_HOST'];
		$server = substr($server,0,strlen($server)-3);
	} else {
		$server = $_SERVER['SERVER_NAME'];
	}
    if ($server=='10.198.1.60') {
    	 $settings['google_map_key']="ABQIAAAAmiag7sjtDKGksv0q_TBc1BQdQxLwbjAps_9FJTxaygPoonPl5hSULheWX4v7aHj5asXQzcKVJln3uA";
    }
	
    $settings['google_map_key']="AIzaSyC6Q8iFoaZc3S4E_glvJIduvkNutN31zHU";
  	
    
// FROM ZEFIRO _________________________________________________________________________ 
    if ($actionContext->getForm()=="ZEF_GM01"){
	$actionContext->setLabel("Mappa Interlocutori");
		
	$MAP_OBJECT = new GoogleMapAPI(); 
	$MAP_OBJECT->_minify_js = True;
	$MAP_OBJECT->setHeight(600);
	$MAP_OBJECT->setWidth(800);
	
	$marker_web_location = "http://www.bradwedell.com/phpgooglemapapi/demos/img/";
	$icon_filename = $marker_web_location."triangle_icon.png";
	$icon_filename = "" ;
	 

	// Ciclo interlcutori ricevuti
	
	$interlocutori = explode(";", $_GET['int']);
	
    foreach ($interlocutori as $key=>$value) {
    $row['TIPO']=substr($value, 0 ,1);
    $row['CODICE']=substr($value,1);
		
		switch ($row ['TIPO']) {
			case 'F' :
				$sql = "select CDFORN3C, TPINTE3C, RAGSO13C, RAGSO23C, RAGSOB3C, INDIRI3C, LOCALI3C, FRAZIO3C, CAPCAP3C,
						PROVIN3C, CDNAZI3C, ININTE3C  from ANFOR3C§ ";
				$sql .= "where CDAZPR3C = '" . $_SESSION ['codice_azienda'] . "' ";
				$sql .= "and TPINTE3C = '" . $row ['TIPO'] . "' ";
				$sql .= "and CDFORN3C = '" . $row ['CODICE'] . "' ";
				$result = $db->singleQuery ( $sql );
				$anfor = $db->fetch_array ( $result );
				$address = $anfor['INDIRI3C']." ".$anfor['LOCALI3C']." ".$anfor['PROVIN3C']." " .$anfor['CDNAZI3C'] . " " .$anfor['CAPCAP3C'] ;
				$title = $anfor['CDFORN3C'] . " " . $anfor['RAGSO13C' ];
				$html = $title . "<br> " . $address ."<br>";
				$tooltip =  $title;
				break;
			case 'C' :
				$sql = "select CDCLIE7C, TPINTE7C, RAGSO17C, RAGSO27C, RAGSOB7C, INDIRI7C, LOCALI7C, FRAZIO7C, CAPCAP7C,
						PROVIN7C, CDNAZI7C, ININTE7C  from ANCLI7C§ ";
				$sql .= "where CDAZPR7C = '" . $_SESSION ['codice_azienda'] . "' ";
				$sql .= "and TPINTE7C = '" . $row ['TIPO'] . "' ";
				$sql .= "and CDCLIE7C = '" . $row ['CODICE'] . "' ";
				$result = $db->singleQuery ( $sql );
				$anfor = $db->fetch_array ( $result );
				$address = $anfor['INDIRI7C']." ".$anfor['LOCALI7C']." ".$anfor['PROVIN7C']." " .$anfor['CDNAZI7C'] . " " .$anfor['CAPCAP7C'] ;
				$title = $anfor['CDCLIE7C'] . " " . $anfor['RAGSO17C' ];
				$html = $title . "<br>" . $address . "<br>";
				$tooltip =  $title;
				break;
			default :
				$address='';
				break;
		}
		
		
	 $MAP_OBJECT->addMarkerByAddress($address, $title, $html, $tooltip, $icon_filename);
	 echo $html;
	 
	   /*
	    Parameters:
string   	$address:  	the map address to mark (street/city/state/zip)	
string   	$title:  	the title display in the sidebar	
string   	$html:  	the HTML block to display in the info bubble (if empty, title is used)	
string   	$tooltip:  	Tooltip to display (deprecated?)	
string   	$icon_filename:  	Web file location (eg http://somesite/someicon.gif) to use for icon	
string   	$icon_shadow_filename:  	Web file location (eg http://somesite/someicon.gif) to use for icon shadow
	    
	    */
		
	} //Fine Ciclo 
	 
    
    // Esempio: Imposta un percorso tra due indirizzi
	//$indirizzo2 = "78, Via Postumia Ovest, Olmi, TV 31048";
    //$MAP_OBJECT->addMarkerByAddress($indirizzo2,$row['CLIENTE']." ".$row['MEBRAG'],"", $row['CLIENTE'], $default_icon);
	//$MAP_OBJECT->addDirections($indirizzo, $indirizzo2, "map_directions", True);
    	
	
}   
// FROM ZEFIRO _________________________________________________________________________ 
// per visualizzate da lista (TO DO)
    if ($actionContext->getForm()=="ZEF_GM01_LIST"){
    	
   	$wi400List = new wi400List();
	$wi400List =  getList("GM_LIST_INTERLOCUTORI");
	$tabella = $wi400List->getFrom();
		
	$MAP_OBJECT = new GoogleMapAPI(); 
	$MAP_OBJECT->_minify_js = True;
	
	$query = "SELECT ".$wi400List->getField()." FROM ".$wi400List->getFrom()." ";
	$query = $query.$wi400List->getWhere();
	$actionContext->setLabel("Interlocutori");	
	$result = $db->query($query);
	
	$marker_web_location = "http://www.bradwedell.com/phpgooglemapapi/demos/img/";
	$icon_filename = $marker_web_location."triangle_icon.png";
	$icon_filename = "" ;
	 
	// (prepare ??)

	
	// Ciclo di lettura sulla tabella
	$first=True;
	while ($row = $db->fetch_array($result)){
		echo $row ['TIPO'];
		
		switch ($row ['TIPO']) {
			case 'F' :
				$sql = "select CDFORN3C, TPINTE3C, RAGSO13C, RAGSO23C, RAGSOB3C, INDIRI3C, LOCALI3C, FRAZIO3C, CAPCAP3C,
						PROVIN3C, CDNAZI3C, ININTE3C  from ANFOR3C§ ";
				$sql .= "where CDAZPR3C = '" . $_SESSION ['codice_azienda'] . "' ";
				$sql .= "and TPINTE3C = '" . $row ['TIPO'] . "' ";
				$sql .= "and CDFORN3C = '" . $row ['CODICE'] . "' ";
				$result = $db->singleQuery ( $sql );
				$anfor = $db->fetch_array ( $result );
				$address = $anfor['INDIRI3C']." ".$anfor['LOCALI3C']." ".$anfor['PROVIN3C']." " .$anfor['CDNAZI3C'] . " " .$anfor['CAPCAP3C'] ;
				$title = $anfor['CDFORN3C'] . " " . $anfor['RAGSO13C' ];
				$html = $title . "<br> " . $address;
				$tooltip =  $title;
				break;
			case 'C' :
				$sql = "select CDCLIE7C, TPINTE7C, RAGSO17C, RAGSO27C, RAGSOB7C, INDIRI7C, LOCALI7C, FRAZIO7C, CAPCAP7C,
						PROVIN7C, CDNAZI37, ININTE7C  from ANCLI7C§ ";
				$sql .= "where CDAZPR7C = '" . $_SESSION ['codice_azienda'] . "' ";
				$sql .= "and TPINTE7C = '" . $row ['TIPO'] . "' ";
				$sql .= "and CDCLIE7C = '" . $row ['CODICE'] . "' ";
				$result = $db->singleQuery ( $sql );
				$anfor = $db->fetch_array ( $result );
				$address = $anfor['INDIRI7C']." ".$anfor['LOCALI7C']." ".$anfor['PROVIN7C']." " .$anfor['CDNAZI7C'] . " " .$anfor['CAPCAP7C'] ;
				$title = $anfor['CDCLIE7C'] . " " . $anfor['RAGSO17C' ];
				$html = $title . "<br> " . $address;
				$tooltip =  $title;
				break;
			default :
				$address='';
				break;
		}
		
		
	 $MAP_OBJECT->addMarkerByAddress($address, $title, $html, $tooltip, $icon_filename);
	   /*
	    Parameters:
string   	$address:  	the map address to mark (street/city/state/zip)	
string   	$title:  	the title display in the sidebar	
string   	$html:  	the HTML block to display in the info bubble (if empty, title is used)	
string   	$tooltip:  	Tooltip to display (deprecated?)	
string   	$icon_filename:  	Web file location (eg http://somesite/someicon.gif) to use for icon	
string   	$icon_shadow_filename:  	Web file location (eg http://somesite/someicon.gif) to use for icon shadow
	    
	    */
		
	} //Fine Ciclo di lettura sulla tabella
	 
    
    // Esempio: Imposta un percorso tra due indirizzi
	//$indirizzo2 = "78, Via Postumia Ovest, Olmi, TV 31048";
    //$MAP_OBJECT->addMarkerByAddress($indirizzo2,$row['CLIENTE']." ".$row['MEBRAG'],"", $row['CLIENTE'], $default_icon);
	//$MAP_OBJECT->addDirections($indirizzo, $indirizzo2, "map_directions", True);
    	
	// operazioni finali di configurazione della mappa
	$MAP_OBJECT->setHeight(600);
	$MAP_OBJECT->setWidth(800);
	
	
	
	
}     
 //________________________________________________________________________________________
    
if ($actionContext->getForm()=="PRESENZE_PALLET"){
	$wi400List = new wi400List();
	$wi400List =  getList("TRACCIA_PRESENZE_PALLET_LIST");
	$tabella = $wi400List->getFrom();
	$gm = new EasyGoogleMap($settings['google_map_key']);
	$query = "SELECT ".$wi400List->getField()." FROM ".$wi400List->getFrom()." ";
	$query = $query.$wi400List->getWhere();
	$actionContext->setLabel("Destinazione dei lotti");	
	$result = $db->query($query);
	// Routine per reperimento cliente
	$rtlent = new wi400Routine('RTLENT', $connzend);
	$rtlent->load_description();
	$rtlent->prepare();
	// Routine per reperimento indirizzo
	$rtlfo1 = new wi400Routine('RTLFO1', $connzend);
	$rtlfo1->load_description();
	$rtlfo1->prepare();
	while ($row = $db->fetch_array($result)){
		    if ($row['NEGOZIO']!='9000'){ 
			    $rtlent->set('CODICE', $row['NEGOZIO']);
			    $rtlent->set('DATINV', date("Ymd"));
			    $rtlent->call();
			    $ente = $rtlent->get("ENTI");
			    $cliente = $ente['MAFINT'];
			    $descrizione = $ente['MAFDSE'];
		    } else {
		    	$cliente = $row['CLIENTE'];
		    	$descrizione = $row['DES_CLI']; 
		    }
		    $rtlfo1->set('CODFOR', $cliente);
		    $rtlfo1->set('DATINV', date("Ymd"));
		    $rtlfo1->call();
		    $anin = $rtlfo1->get("FORN");
		    $indirizzo = $anin['MEAIND']." ".$anin['MEACAP']." ".$anin['MEACOM']. " ITALY";
			$gm->SetMarkerIconStyle('FLAG');
			$gm->SetAddress($indirizzo, True);
			$gm->SetInfoWindowText($cliente." ".$descrizione);
			$gm->SetSideClick($descrizione);
	}
	$gm->SetMapZoom(8);
	$gm->SetMapWidth(800); # default = 300
	$gm->SetMapHeight(600); # default = 300	
}
if ($actionContext->getForm()=="TO_CLI_LST"){
   	$wi400List = new wi400List();
	$wi400List =  getList("TO_CLI_LST");
	$tabella = $wi400List->getFrom();
	//$gm = new EasyGoogleMap($settings['google_map_key']);
	$MAP_OBJECT = new GoogleMapAPI(); 
	$MAP_OBJECT->_minify_js = True;
	$query = "SELECT ".$wi400List->getField()." FROM ".$wi400List->getFrom()." ";
	$query = $query.$wi400List->getWhere();
	$actionContext->setLabel("Negozi interessati al TO");	
	$result = $db->query($query);
	// Routine per reperimento indirizzo
	$rtlfo1 = new wi400Routine('RTLFO1', $connzend);
	$rtlfo1->load_description();
	$rtlfo1->prepare();
	// Ciclo di lettura sulla tabella
	$first=True;
	$marker_web_location = "http://www.bradwedell.com/phpgooglemapapi/demos/img/";
	$default_icon = $marker_web_location."triangle_icon.png";
	while ($row = $db->fetch_array($result)){
		    $rtlfo1->set('CODFOR', $row['CLIENTE']);
		    $rtlfo1->set('DATINV', date("Ymd"));
		    $rtlfo1->call();
		    $anin = $rtlfo1->get("FORN");
		    $indirizzo = $anin['MEAIND']." ".$anin['MEACAP']." ".$anin['MEACOM']. " ITALIA";
		    $MAP_OBJECT->addMarkerByAddress($indirizzo,$row['CLIENTE']." ".$row['MEBRAG'],"", $row['CLIENTE'], $default_icon);
		    /*$gm->SetMarkerIconStyle('FLAG');
			$gm->SetAddress($indirizzo, True);
			$gm->SetInfoWindowText($row['CLIENTE']." ".$row['MEBRAG']);
			$gm->SetSideClick($row['MEBRAG']);*/
	}
	// Aggiungo il centro distributivo
    //$indirizzo = "78, Via Postumia Ovest, Olmi, TV 31048";
	//$gm->SetMarkerIconStyle('FLAG');
	//$gm->SetAddress($indirizzo, True);
	//$gm->SetInfoWindowText("SEDE CENTRALE");
	//$gm->SetSideClick("SEDE CENTRALE");	
	// operazioni finali di configurazione della mappa
	//$gm->SetMapZoom(8);
	//$gm->SetMapWidth(800); # default = 300
	//$gm->SetMapHeight(600); # default = 300
	$MAP_OBJECT->setHeight(600);
	$MAP_OBJECT->setWidth(800);
	
	//$gm->setCenter("78, Via Postumia Ovest, Olmi, TV 31048");
}
if ($actionContext->getForm()=="RIEPILOGO_VIAGGI_DETAIL"){
	$wi400List = new wi400List();
	$wi400List =  getList("RIEPILOGO_VIAGGI_DETAIL");
	$tabella = $wi400List->getFrom();
	//$gm = new EasyGoogleMap($settings['google_map_key']);
	$MAP_OBJECT = new GoogleMapAPI();
	$MAP_OBJECT->_minify_js = True;
	$query = "SELECT ".$wi400List->getField()." FROM ".$wi400List->getFrom()." ";
	$query = $query.$wi400List->getWhere();
	$actionContext->setLabel("Destinazioni del viaggio su mappa");	
	$result = $db->query($query);
	// Routine per reperimento cliente
	$rtlent = new wi400Routine('RTLENT', $connzend);
	$rtlent->load_description();
	$rtlent->prepare();
	// Routine per reperimento indirizzo
	$rtlfo1 = new wi400Routine('RTLFO1', $connzend);
	$rtlfo1->load_description();
	$rtlfo1->prepare();
	$marker_web_location = "http://10.10.1.60:89/upload/";
	$default_icon = $marker_web_location."triangle_icon.png";
	while ($row = $db->fetch_array($result)){
		    $rtlent->set('CODICE', $row['ENTE']);
		    $rtlent->set('DATINV', date("Ymd"));
		    $rtlent->call();
		    $ente = $rtlent->get("ENTI");
		    $rtlfo1->set('CODFOR', $ente['MAFINT']);
		    $rtlfo1->set('DATINV', date("Ymd"));
		    $rtlfo1->call();
		    $anin = $rtlfo1->get("FORN");
		    $indirizzo = $anin['MEAIND']." ".$anin['MEACAP']." ".$anin['MEACOM']. " ITALY";
		    $MAP_OBJECT->addMarkerByAddress($indirizzo,$row['ENTE']." ".$row['DES_ENTE'],"", $row['ENTE'], $default_icon);
			/*$gm->SetMarkerIconStyle('FLAG');
			$gm->SetAddress($indirizzo, True);
			$gm->SetInfoWindowText($row['ENTE']." ".$row['DES_ENTE']);
			$gm->SetSideClick($row['DES_ENTE']);*/
	}
	// Aggiungo il centro distributivo
    //$indirizzo = "78, Via Postumia Ovest, Olmi, TV 31048";
	//$gm->SetMarkerIconStyle('FLAG');
	//$gm->SetAddress($indirizzo, True);
	//$gm->SetInfoWindowText("SEDE CENTRALE");
	//$gm->SetSideClick("SEDE CENTRALE");	
	// operazioni finali di configurazione della mappa
	/*$gm->SetMapZoom(8);
	$gm->SetMapWidth(800); # default = 300
	$gm->SetMapHeight(600); # default = 300*/
	$MAP_OBJECT->setHeight(600);
	$MAP_OBJECT->setWidth(800);
}




?>