<?php
if (!isset($_POST['call'])){
?>
<html>
<head></head>
<BODY>
<form method=post target="result">
    	<TABLE class=border cellSpacing=0 cellPadding=0 width="100%" align=center border=0>
				<TR>
				<TD>
					<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>
						<TR>
						<TD class=back2 align=right width=10%>Utente: </td>
						<td class=back>
						<input name=user value="">
						</td>
						</tr>
						<TR>
						<TD class=back2 align=right width=10%>Password: </td>
						<td class=back>
							<input name=password value="">
						</td>
						</tr>
						<TR>
						<TD class=back2 align=right width=10%>XML input: </td>
						<td class=back>
							<textarea name="xmlinput" rows="9" cols="70"></textarea>
						</td>
						</tr>
					<tr>
						<td>
							<legend>Metodo WS</legend>						
							 <fieldset>
							  Genera OTM<input type="radio" name="metodo" value="otm" checked/>
							</fieldset>
						</td>
						</tr>
						</TABLE>
			<input type=submit value="Chiama Web_Services AS400" name=call class=back2>
			</form>
</BODY>
</html>
<?php 
}else{
	ini_set("soap.wsdl_cache_enabled", "1");
	$selected_radio = $_POST['metodo'];
//	echo "AZIONE: $selected_radio<br>";
	// Recupero il path da cui sto operando
	$name = explode('/',$_SERVER['REQUEST_URI']);
	if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
		$server = $_SERVER['HTTP_X_FORWARDED_HOST'];
		$server = substr($server,0,strlen($server)-3);
	} else {
		$server = $_SERVER['SERVER_ADDR'];
	}
	
	$path = $server.":".$_SERVER['SERVER_PORT'];
	$tot = count($name)-1;
	for($i=0; $i<$tot;$i++) {
		$path .= $name[$i]."/";
	}
	try{
		$xml_in = utf8_encode($_POST['xmlinput']);
//		echo "USER: ".$_POST['user']." - PASSWORD: ".$_POST['password']."<br>";
//		echo "XML_IN: $xml_in<br>";
		// Controllo il tipo di metodo richiesto
		$url = "http://".$path."wi400Ws_otm.wsdl";
//		echo "URL: $url<br>";
    	$client = new SoapClient($url);
		if ($selected_radio=='otm'){
 			$xml_out = $client->getOTM($_POST['user'], $_POST['password'], $xml_in);
    	}
		echo $xml_out;
	}
	catch (SoapFault $exception) 
	{ 
		echo $exception->getMessage();
	}
}
?>
