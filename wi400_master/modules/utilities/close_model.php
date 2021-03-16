<?php

//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";
/*
	if($actionContext->getForm()=="CLOSE_LOOKUP_RELOAD_LIST") {
		$reloadAction = "";
		$idList = "";
	}
	else if($actionContext->getForm()=="SEL_ACTION") {
		$reloadAction = "";
		$reloadForm = "";
		$idList = "";
	}
*/
	if(isset($_REQUEST['CLEAN_DETAIL']) && $_REQUEST['CLEAN_DETAIL']!="") {
		wi400Detail::cleanSession($_REQUEST['CLEAN_DETAIL']);
	}
	
	if(isset($_REQUEST['SUBFILE_DELETE']) && $_REQUEST['SUBFILE_DELETE']!="") {
		subfileDelete($_REQUEST['SUBFILE_DELETE']);
	}
	
	$history = false;
	if(isset($_REQUEST['IS_FROM_HISTORY']) && $_REQUEST['IS_FROM_HISTORY']!="") {
		$history = true;
	}