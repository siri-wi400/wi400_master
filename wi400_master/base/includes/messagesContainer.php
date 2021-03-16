<?php
	//if (isset($messageContext) && sizeof($messageContext->getMessages("NOT_FIELD"))>0){
		if ($pageDefaultDecoration != "login_") {
?>
			<tr><td valign="top" align="right" id="messageAreaContainer">
<?
		}
?>
				<div id="messageArea" onClick="resizeMessageArea()" class='messageArea_<?= $messageContext->getSeverity('NOT_FIELD')?>' style="display:none;"></div>
<?
		if ($pageDefaultDecoration != "login_") {
?>
			</td></tr>
<?
		} // End if login
	//}// End if isset
?>