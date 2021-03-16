<?php

	if (isset($_REQUEST["TITLE"])) {
		$actionContext->setLabel($_REQUEST["TITLE"]);
	}
	else {
		if (isset($_REQUEST["FILE"])) {
			$tabella = $_REQUEST["FILE"];
			$descrizione = $db->getTableDescription($tabella);
			$actionContext->setLabel(_t('TABLE_ELEMENTS').$tabella. " ".$descrizione);
		}
	}

?>