<?php

	if(in_array($actionContext->getForm(), array("DEFAULT", "SEARCH"))) {
//		echo "POST:<pre>"; print_r($_POST); echo "</pre>"; die();
	
		validation_search();
	}
	
	function validation_search() {
		global $messageContext;
/*		
		if((isset($_POST['SEARCH_FILE']) && trim($_POST['SEARCH_FILE'])!="" && strpos($_POST['SEARCH_FILE'], "*")===false) && (
			(isset($_POST['SEARCH_DIR']) && trim($_POST['SEARCH_DIR'])!="") ||
			(isset($_POST['SUBDIR_SPAN_INI']) && trim($_POST['SUBDIR_SPAN_INI'])!="") ||
			(isset($_POST['SUBDIR_ARRAY']) && !empty($_POST['SUBDIR_ARRAY']))				
		) ) {
			$messageContext->addMessage("ERROR", "Controllare o le directories e/o subdirectories o direttamente un file");
		}
*/		
	}
