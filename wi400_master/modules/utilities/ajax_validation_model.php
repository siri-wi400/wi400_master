<?php
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	// Verifico se ho errori da segnalare
	$decodeResult = true;
	$string_error = "";
	if ($messageContext->getSeverity() == "ERROR" ){
		$string_error = "Errori validazione";
		if(isset($_REQUEST['NAME_ACTION']) && $_REQUEST['NAME_ACTION'] == 'actionList') {
			$string_error = "";
			foreach($messageContext->getMessages() as $mess) {
				$string_error .= $mess[1]."\n";
			}
			$messageContext->removeMessages();
		}
		$decodeResult = false;
	}
} else {
	die("not Ajax Request");
}