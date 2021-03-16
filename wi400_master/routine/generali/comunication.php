<?php
function sendSmsAruba($text, $sender, $rcpt, $qty="n") {
	global $routine_path;
	/*$username='Sms47413';
	 //$password='6beef5ca';
	 $username = "Sms59722";
	 $password = "1234";
	 $url = 'http://admin.sms.aruba.it/sms/send.php';
	 //$url = 'http://admin.sms.aruba.it/sms/credit.php';
	 //$url = 'http://sms.arubabusiness.it/sms/send.php';
	 $fields_string="";
	 $fields = array(
	 'user' => $username,
	 'pass' => $password,
	 'rcpt' => $rcpt,
	 'data' => $text,
	 'sender' => $sender,
	 'qty' => $qty,
	 'operation'=> "TEXT"
	 );
	 //url-ify the data for the POST
	 foreach($fields as $key=>$value) {
	 $fields_string .= $key.'='.urlencode($value).'&';
	 }
	 $fields_string = substr($fields_string, 0, -1);
	 //open connection
	 $myget = $url."?".$fields_string;
	 die($myget);
	 $do = file_get_contents($myget);*/
	require_once $routine_path."/RSSDK/sendsms.php";
	
	$sms = new Sdk_SMS();
	$sms->sms_type = SMSTYPE_ALTA;
	$sms->add_recipient($rcpt);
	$sms->message = $text;
	$sms->sender = $sender;        // A phone number, or a registered alphanumeric sender
	$sms->set_immediate();        // Or sms->set_scheduled_delivery($unix_timestamp)
	//$sms->order_id = '999FFF111'; // Optional
	
	//echo 'About to send a message ' . $sms->count_smss() . ' SMSs long ';
	//echo 'to ' . $sms->count_recipients() . ' recipients </br>';
	
	if ($sms->validate()) {
		$res = $sms->send();
		if ($res['ok']) {
			//echo $res['sentsmss'] . ' SMS sent, order id is ' . $res['order_id'] . ' </br>';
		} else {
			//echo 'Error sending SMS: ' . $sms->problem() . ' </br>';
		}
	} else {
		//echo 'invalid SMS: ' . $sms->problem() . ' </br>';
	}
	//die();
}
function sendSmsWay($text, $sender, $rcpt, $qty="n") {
	$url="https://smsway.hes.it/Italtrans/WS/Sms.asmx?WSDL";
	$ritorno ="";
	$username='wsuser';
	$password='1t4!n5';
	// Chiamo il Web Services
	try{
		$opts = array(
				//'ssl' => array('ciphers'=>'RC4-SHA', 'verify_peer'=>false, 'verify_peer_name'=>false)
		);
		// SOAP 1.2 client
		$params = array ('encoding' => 'UTF-8', 'verifypeer' => false, 'verifyhost' => false, 'soap_version' => SOAP_1_2, 'trace' => 1, 'exceptions' => 1, "connection_timeout" => 180, 'stream_context' => stream_context_create($opts) );
		$client = new SoapClient($url, $params);

		$parameters= array(
				'username'=>$username,
				'password'=>$password,
				'priority'=>'3',
				'sender'=>'',
				'address'=>$rcpt,
				'body'=>$text,
				'valperiod'=>'',
				'encoding'=>'',
				'smsclass'=>'',
				'timetosend'=>'',
				'messageid'=>''
		);
		$xml = $client->SendEx($parameters);
		$dati = get_object_vars($xml);
		if (is_numeric($dati['SendExResult']) && $dati['SendExResult']>0) {
			$esito = "OK";
		} else {
			$esito = "KO";
		}
		$id_messaggio = $dati['SendExResult'];
		return array("ESITO"=> $esito, "ID_MESSAGGIO"=>$id_messaggio, "MSG"=> "");
		//return $dati['SendExResult'];
	}
		catch (SoapFault $exception)
	{
		$id_messaggio = "-999";
		$esito = "KO";
		return array("ESITO"=> $esito, "ID_MESSAGGIO"=>$id_messaggio, "MSG"=>$exception->getMessage());
		//return " -ERROR SOAP-".$exception->getMessage();
	}
}
function getSmsWayStatus($dati) {
	$url="https://smsway.hes.it/Italtrans/WS/Sms.asmx?WSDL";
	$ritorno ="";
	$username='wsuser';
	$password='1t4!n5';
	// Chiamo il Web Services
	try{
		$opts = array(
				'ssl' => array('ciphers'=>'RC4-SHA', 'verify_peer'=>false, 'verify_peer_name'=>false)
		);
		// SOAP 1.2 client
		$params = array ('encoding' => 'UTF-8', 'verifypeer' => false, 'verifyhost' => false, 'soap_version' => SOAP_1_2, 'trace' => 1, 'exceptions' => 1, "connection_timeout" => 180, 'stream_context' => stream_context_create($opts) );
		$client = new SoapClient($url, $params);
	
		$parameters= array(
				'username'=>$username,
				'password'=>$password,
				'MessageId'=>$dati
		);
		$xml = $client->RecvStatusPolling($parameters);
		$dati = get_object_vars($xml);
		return $dati['RecvStatusPollingResponse'];
	}
	catch (SoapFault $exception)
	{
		return " -ERROR SOAP-".$exception->getMessage();
	}
}
function sendSmsMessagenet($text, $sender, $rcpt, $qty="n") {
	$username='1838813';
	$password='QF8nU2H2';
	$url = 'https://api.messagenet.com/api/send_sms/';
	$fields_string="";
	$fields = array(
			'auth_userid' => $username,
			'auth_password' => $password,
			'destination' => $rcpt,
			'text' => $text,
			'sender' => $sender,
			'report' => '0',
			'format'=> "json"
	);
	//url-ify the data for the POST
	foreach($fields as $key=>$value) {
		$fields_string .= $key.'='.urlencode($value).'&';
	}
	$fields_string = substr($fields_string, 0, -1);
	$opts = array('http' =>
			array(
					'method'  => 'POST',
					'header'  => 'Content-Type: application/x-www-form-urlencoded',
					'content' => $field_strig
			)
	);
	$context  = stream_context_create($opts);
	//open connection
	$myget = $url."?".$fields_string;
	//die($myget);
	try{
		$do = file_get_contents($url, False, $opts);
		$dati = json_decode($do, True);
		if ($response['http_status']['value'] =="200") {
			$esito = "OK";
		} else {
			$esito = "KO";
		}
		return array("ESITO"=> $esito, "ID_MESSAGGIO"=>$response['sent'][0]['message_id'], "MSG"=> $response['message_status']['description']);
	}
		catch (Exception $e)
	{
		$id_messaggio = "-999";
		$esito = "KO";
		return array("ESITO"=> $esito, "ID_MESSAGGIO"=>$id_messaggio, "MSG"=>$e->getMessage());
		
	}
}
