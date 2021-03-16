<?php

	if($actionContext->getForm()=="DEFAULT") {
		$geoIpDetail = new wi400Detail($gateway.'_GEOIP_DETAIL');
		$geoIpDetail->setTitle(ucfirst($key));
		$geoIpDetail->setColsNum(2);
		
		$labelDetail = new wi400Text("IP_ADDRESS");
		$labelDetail->setLabel("Indirizzo IP:");
		$labelDetail->setValue($ip);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("COUNTRY_NAME");
		$labelDetail->setLabel("Nome Nazione:");
		$labelDetail->setValue($nome_nazione);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("CAPITAL_NAME");
		$labelDetail->setLabel("Capitale:");
		$labelDetail->setValue($nome_capitale);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("COUNTRY_CODE");
		$labelDetail->setLabel("Codice Nazione:");
		$labelDetail->setValue($codice_nazione);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("REGION_NAME");
		$labelDetail->setLabel("Regione:");
		$labelDetail->setValue($regione);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("CITY_NAME");
		$labelDetail->setLabel("Città:");
		$labelDetail->setValue($citta);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("LATITUDE");
		$labelDetail->setLabel("Latitudine:");
		$labelDetail->setValue($latitudine);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("LONGITUDE");
		$labelDetail->setLabel("Longitudine:");
		$labelDetail->setValue($longitudine);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("PIN_CODE");
		$labelDetail->setLabel("Codice PIN:");
		$labelDetail->setValue($pin_code);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("DMA_CODE");
		$labelDetail->setLabel("Codice DMA:");
		$labelDetail->setValue($dma_code);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("AREA_CODE");
		$labelDetail->setLabel("Codice Area:");
		$labelDetail->setValue($area_code);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("CURRENCY_NAME");
		$labelDetail->setLabel("Moneta:");
		$labelDetail->setValue($moneta);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("CURRENT_CONVERSION_RATE");
		$labelDetail->setLabel("Cambio:");
		$labelDetail->setValue($cambio);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("CALLING_CODE");
		$labelDetail->setLabel("Prefisso internazionale:");
		$labelDetail->setValue($prefisso);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("LOCAL_TIME");
		$labelDetail->setLabel("Ora locale:");
		$labelDetail->setValue($ora_locale);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("CURRENT_TEMPERATURE");
		$labelDetail->setLabel("Temperatura:");
		$labelDetail->setValue($temperatura);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("ACCOUNT_TYPE");
		$labelDetail->setLabel("Tipo di account:");
		$labelDetail->setValue($tipo_account);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("VUOTO");
		$labelDetail->setLabel("");
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("TOTAL_REQUESTS_MADE");
		$labelDetail->setLabel("Numero di richieste<br>eseguite nella giornata:");
		$labelDetail->setValue($tot_richieste);
		$geoIpDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("REMAINING_REQUESTS");
		$labelDetail->setLabel("Numero di richieste<br>rimanenti nella giornata:");
		$labelDetail->setValue($richieste_rimanenti);
		$geoIpDetail->addField($labelDetail);
		
		$geoIpDetail->dispose();
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	}

?>