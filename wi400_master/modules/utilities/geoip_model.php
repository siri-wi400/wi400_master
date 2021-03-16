<?php

	if($actionContext->getForm()=="DEFAULT") {
		// Azione corrente
		$actionContext->setLabel("GeoIP");
		
//		require_once $moduli_path.'/utilities/usage.php';

		//This is a Demo API KEY, exclusively for www.phpclasses.org only
		define("API_KEY","F_ad249ddcf27dbcd8d868a59f2298a8d8"); 
		
		require_once $moduli_path.'/utilities/GeoIpApiConnector.inc.php';
		
		$GeoIpApiConnector=new GeoIpApiConnector(API_KEY);
		//$infoArray=$GeoIpApiConnector->getInformationArray($ip);
		
		$nome_nazione = $GeoIpApiConnector->getCountryName($ip);
		$nome_capitale = $GeoIpApiConnector->getCapitalName($ip);
		$codice_nazione = $GeoIpApiConnector->getCountryCode($ip);
		$regione = $GeoIpApiConnector->getRegionName($ip);
		$citta = $GeoIpApiConnector->getCityName($ip);
		$latitudine = $GeoIpApiConnector->getLatitude($ip);
		$longitudine = $GeoIpApiConnector->getLongitude($ip);
		$pin_code = $GeoIpApiConnector->getPinCode($ip);
		$dma_code = $GeoIpApiConnector->getDmaCode($ip);
		$area_code = $GeoIpApiConnector->getAreaCode($ip);
		$moneta = $GeoIpApiConnector->getCurrencyName($ip);
		$cambio = $GeoIpApiConnector->getCurrentConversionRate($ip);
		$prefisso = $GeoIpApiConnector->getCallingCode($ip);
		$ora_locale = $GeoIpApiConnector->getLocalTime($ip);
		$temperatura = $GeoIpApiConnector->getCurrentTemperature($ip);
		$tipo_account = $GeoIpApiConnector->getAccountType($ip);
		$tot_richieste = $GeoIpApiConnector->getTotalRequestsMade($ip);
		$richieste_rimanenti = $GeoIpApiConnector->getRemainingRequests($ip);
	}

?>