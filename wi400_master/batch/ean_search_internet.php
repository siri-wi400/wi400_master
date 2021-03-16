<?php
// XML di ingresso
//$ean = $_POST['EAN'];
$ean = "0800897116460";
// Chiamo il Web Services
try{
	//$curl =	curl_init('http://www.ean-search.org/perl/ean-search.pl?q='.$ean);
	$url = 'http://www.ean-search.org/perl/ean-search.pl?q='.$ean;
	$curl = curl_init();
	
	// setup headers - used the same headers from Firefox version 2.0.0.6
	// below was split up because php.net said the line was too long. :/
	$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
	$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
	$header[] = "Cache-Control: max-age=0";
	$header[] = "Connection: keep-alive";
	$header[] = "Keep-Alive: 300";
	$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	$header[] = "Accept-Language: en-us,en;q=0.5";
	$header[] = "Pragma: "; //browsers keep this blank.
	
	$proxy ="cvl:baldoria@10.0.69.12:3128";
	curl_setopt($curl, CURLOPT_URL, $url);
	//set options
	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3'); 
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header); 
	curl_setopt($curl, CURLOPT_PROXY, $proxy);
	curl_setopt($curl, CURLOPT_REFERER, 'http://www.google.com'); 
	curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate'); 
	curl_setopt($curl, CURLOPT_AUTOREFERER, true); 
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 10); 
	//set data to be posted
	//curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
	//perform our request
	$result = curl_exec($curl);
	echo $result;
	die();
	//show information regarding the request
	//print_r(curl_getinfo($curl_connection));
	//echo curl_errno($curl_connection) . '-' .
	curl_error($curl);
	//close the connection
	curl_close($curl);
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