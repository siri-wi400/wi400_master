<?php 
// XML di ingresso
$url="http://ec.europa.eu/taxation_customs/vies/services/checkVatService?wsdl";
		
// Chiamo il Web Services
	try{

		$client = new SoapClient($url, array('trace' => 1));
		echo "<pre>";
		//print_r($client->__getFunctions());
		$risposta = $client->checkVat(array("countryCode"=>"IT", "vatNumber"=>"03723210278"));
		print_r($risposta);
		echo "</pre>";
		
		echo "====== REQUEST HEADERS =====" . PHP_EOL;
		var_dump($client->__getLastRequestHeaders());
		echo "========= REQUEST ==========" . PHP_EOL;
		var_dump($client->__getLastRequest());
		echo "========= RESPONSE =========" . PHP_EOL;
		var_dump($response);
	}
	catch (SoapFault $exception) 
	{ 
		// Gestione errore
		echo $exception->faultstring;
	}
	
?>