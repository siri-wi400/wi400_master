<?php
global $actionContext;
 
if($actionContext->getAction()=="IMPORT_XLS") {
	if($actionContext->getForm()=="DEFAULT") {
?>
		<input type='button' onClick="doListAction('IMPORT_XLS_PARAMS_LIST',1)" value="Importa" class="detail-button" />
<?php 
	}
	else {
?>
		<input type='button' onClick="doListAction('IMPORT_XLS_PARAMS_LIST',1)" value="Conferma" class="detail-button" />
<?php 
	}
}