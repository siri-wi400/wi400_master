<?php
 
	// XML di ingresso
	$piva = $_GET['piva'];
	$country = $_GET['country'];
	
	$url="http://ec.europa.eu/taxation_customs/vies/services/checkVatService?wsdl";

	// Chiamo il Web Services
	try {
		$client = new SoapClient($url, array('trace' => 1));
		//echo "<pre>";
		//print_r($client->__getFunctions());
		$risposta = $client->checkVat(array("countryCode"=>trim($country), "vatNumber"=>trim($piva)));
		//print_r($risposta);
		//echo "</pre>";
		
		//echo "====== REQUEST HEADERS =====" . PHP_EOL;
		//var_dump($client->__getLastRequestHeaders());
		//echo "========= REQUEST ==========" . PHP_EOL;
		//var_dump($client->__getLastRequest());
		//echo "========= RESPONSE =========" . PHP_EOL;
		//var_dump($response);

		echo $risposta->valid."|".$risposta->name."|".$risposta->address;
	}
	catch (SoapFault $exception) { 
		// Gestione errore
		//echo var_dump($exception);
		echo $exception->faultstring;
	}