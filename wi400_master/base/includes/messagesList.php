<?
	if (isset($messageContext) && sizeof($messageContext->getMessages("NOT_FIELD"))>0){
?>
	<div id="messagesList" style="visibility:hidden;overflow:hidden;position:absolute;display:none;"><br>
<?		
		$isFirstMessage = true;
		$messagesArray = array_reverse($messageContext->getMessages());
		foreach ($messagesArray as $messageKey => $messageObj){
			if ($messageObj[2] == ""){
				if ($isFirstMessage){
					$isFirstMessage = false;
				}
?>
				<div class="messageLabel_<?= $messageObj[0] ?>"><?= $messageObj[1] ?></div>
<?
			}
		}
		
	?>
	<br></div>
<?
	}
	$messageContext->removeMessages();
?>