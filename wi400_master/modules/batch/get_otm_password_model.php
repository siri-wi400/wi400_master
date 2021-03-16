<?php 
ob_end_clean();
$otm = new wi400Otm();
$scadenza = "";
if (isset($batchcContext->scadenza) && $batchContext->scadenza!="") {
	$scadenza = $batchcContext->scadenza;
}
$key = $otm->getOtmPassword($batchContext->user, "TEXT", $batchContext->parmOtm, $scadenza);
echo "OTMKEY=".$key."\r\n";
?>