<?php
if($actionContext->getForm()=="WRITE") {
	echo "JSON:".json_encode(array("5250Complete"=>$complete, "5250html"=>$html, "5250dati" => $dati, "5250Value" => $value, "5250Error" => $errore, "5250Message" => $messaggio));
	die();
}else if($actionContext->getForm()=="READ") {
	echo "JSON:".json_encode(array("5250Complete"=>$complete, "5250html"=>$html, "5250dati" => $dati, "5250Value" => $value, "5250Error" => $errore, "5250Message" => $messaggio, '5250Extraction' => $id));
	die();
}
