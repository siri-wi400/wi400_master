<?php

	if ($actionContext->getForm() == "DELETE"){
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_GET['IDLIST']);
	
		foreach($wi400List->getSelectionArray() as $key => $value){
			$keyArray = explode("|",$key);
	    	endLock($keyArray[0],$keyArray[1]);
	    	$messageContext->addMessage("SUCCESS",_t('LOCK_OBJ').$keyArray[1]._t('LOCK_REMOVED'));
		}
		
		
	}