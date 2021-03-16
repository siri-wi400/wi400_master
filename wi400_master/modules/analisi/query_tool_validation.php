<?php
if(!in_array("QUERY_FILTRO", $_SESSION ["WI400_GROUPS_BACKUP"])) {
	if($actionContext->getForm()=="DEFAULT") {
		if((!isset($_POST['SELECT']) || trim($_POST['SELECT'])=="") || 
			(!isset($_POST['FROM']) || trim($_POST['FROM'])=="")
		) {
			$messageContext->addMessage("ERROR", _t('QRY_SYNTAX_ERR'));
		}
	}
}
?>