<?php
if (!isset($_POST['call'])){
?>
<!DOCTYPE frameset PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<script>
function doSubmit(){
	top.xmlresult.location.href = "./loading.html";
	setTimeout(function(){

		var xmlform = document.getElementById("xmlform");
		xmlform.target = "xmlresult";
		xmlform.submit();
		},1000);
}
</script>
</head>
<BODY>
<form id="xmlform" method="POST">
		<input type="hidden" name="call" value="call">
    	<TABLE class=border cellSpacing=0 cellPadding=0 width="100%" align=center border=0>
				<TR>
				<TD>
					<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>
						<TR>
						<TD class=back2 align=right width=10%>Consumer: </td>
						<td class=back>
						<input name=consumer value="">
						</td>
						<TD class=back2 align=right width=10%>Contesto: </td>
						<td class=back>
							<input name=contesto value="">
						</td>
						</tr>
						<TR>
						<TD class=back2 align=right width=10%>XML input: </td>
						<td class=back>
							<textarea name="xmlinput" rows="9" cols="70"></textarea>
						</td>
						<TD class=back2 align=right width=10%>Regole: </td>
						<td class=back>
							<input name=regole value="">
						</td>
						</tr>
						<tr>
						<td>
							<legend>Metodo WS</legend>						
							 <fieldset>
							  Get<input type="radio" name="metodo" value="get" checked/>
							  Set<input type="radio" name="metodo" value="set"/>
							</fieldset>
						</td>
						<TD class=back2 align=right width=10%>Get Destinatari:</td>
						<td class=back>
							<input type="checkbox" name=getdestinatari value="True">
						</td>
						</tr>
						</TABLE>
						</td>
						</tr>
						</TABLE>
			<input type="button" value="Chiama Web_Services AS400"  class="back2" onClick="doSubmit()">
			</form>
</BODY>
</html>
<?php 
}else{
	ini_set("soap.wsdl_cache_enabled", "0");
	$selected_radio = $_POST['metodo'];
    // Recupero il path da cui sto operando
	//$name = explode('/',$_SERVER['REQUEST_URI']);
	//$path="10.10.1.60:89/WI400/soap/wsdl";
	//$tot = count($name)-1;
	//for($i=0; $i<$tot;$i++) {
	//	$path .= $name[$i]."/";
	//}
	// TODO: Inserire il percorso completo
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
	$path = $path. "wsdl/";
	try{
		$xml = utf8_encode($_POST['xmlinput']);
		// Controllo il tipo di metodo richiesto
		if ($selected_radio=='get')
		{
	    $client = new SoapClient("http://".$path."/wi400WsSiri.wsdl");
		if (isset($_POST['getdestinatari'])){
			$xml = $client->getDestinatari($_POST['consumer'], $_POST['contesto'], $xml,$_POST['regole']);
		} else {
			$xml = $client->getArticolo($_POST['consumer'], $_POST['contesto'], $xml,$_POST['regole']);
		}} elseif ($selected_radio=='set') {
	    	$client = new SoapClient("http://".$path."/wi400WsSet.wsdl");
			$xml = $client->setMovimenti($_POST['consumer'], $_POST['contesto'], $xml,$_POST['regole']);
		}
		header('Content-Type: text/xml');
		echo $xml;
	}
	catch (SoapFault $exception) 
	{ 
		echo $exception->getMessage();
	}
}
?>
