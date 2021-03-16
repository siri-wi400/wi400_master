<?php
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	$idWidget = $_REQUEST["ID_WIDGET"];
	$dati = explode("-", $idWidget);
	$widget = strtolower($dati[0]);
	$progressivo = $dati[1];
	$row = rtvAzione($widget);
	
	//require_once $moduli_path."/".$row['MODULO']."/widget/".$widget."_widget.cls.php";
	require_once p13n("modules/".$row['MODULO']."/widget/".$widget."_widget.cls.php");
	
	$classe = strtoupper($widget."_WIDGET");
	$object = new $classe($progressivo);
	if(isset($_REQUEST['ON_ERROR'])) $object->setRemoveColor(true);
	
	$result = $object->run();
	$parametri = $object->getParameters();
	$body = $object->getHtmlBody();
	$color = $object->getColor($result);
	$decodeResult = array_merge($parametri, array("RESULT"=>$result, "BODY" => $body, 'COLOR' => $color));
} else {
	die("not Ajax Request");
}
?>