<<?php 
// XML di ingresso
$ean = $_POST['EAN'];
// Chiamo il Web Services
	try{
		$curl_connection =
		curl_init('http://indicod-ecr.it/gepir/search/gtin/?trade=undefined&code='.$ean);
		//set options
		curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl_connection, CURLOPT_USERAGENT,
				"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
		curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
		//set data to be posted
		//curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
		//perform our request
		$result = curl_exec($curl_connection);
		//show information regarding the request
		//print_r(curl_getinfo($curl_connection));
		//echo curl_errno($curl_connection) . '-' .
		curl_error($curl_connection);
		//close the connection
		curl_close($curl_connection);
		$inizio = strpos($result, '<div class="gepirResults">');
		$fine   = strpos($result, '<div class="gepirSource">');
		$start = 0;
		$dati = substr($result, $inizio-1, $fine-$inizio + 1);
		$dati = str_replace(array("<br>","<br/>", "\r\n"), array("","",""), $dati);
		$find = strpos($dati,'span class="name">',$start);
		$findu = strpos($dati,'<',$find+20);
		$name = substr($dati, $find+18, $findu-($find+18));
		$find = strpos($dati,'span class="address">',$start);	
		$findu = strpos($dati,'<',$find+22);
		$address = substr($dati, $find+21, $findu-($find+21));
		
		if ($inizio > 0) {
			$ritorno = "!*!INIZIO!*!STATO:0!*!MESSAGGIO:OK!*!NAME:$name!*!ADDRESS:$address";
		} else {
			$ritorno = "!*!INIZIO!*!STATO:1!*!MESSAGGIO:".substr($result, $inizio-1, $fine-$inizio + 1);
		}	
	}
	catch (SoapFault $exception) 
	{ 
		$ritorno = "!*!INIZIO!*!STATO:99!*!MESSAGGIO:".$exception->getMessage();
	}
	echo $ritorno."!*!";
?>