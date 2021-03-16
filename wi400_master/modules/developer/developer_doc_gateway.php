<?php
	
	if($actionContext->getGateway()=="ESCAPE") {
	
		$int = new wi400InputText("SESSIONE");
		$int->setValue(session_id());
		wi400Detail::setDetailField("DEVELOPER_DOC_SRC",$int);
		
		$gat = new wi400InputText("FROM_GATEWAY");
		$gat->setValue("DEVELOPER_DOC");
		wi400Detail::setDetailField("DEVELOPER_DOC_SRC",$gat);
		
	}else if($actionContext->getGateway()=="ESCAPE_PID") {
		
		$key = getListKeyArray('MONITOR_PROCESSI_LIST');
		
		$_REQUEST['ACTION'] = $key['PROAZI'];
		
		// Reperisco i riferimenti della sessione sulla lista
		$int = new wi400InputText("SESSIONE");
		$int->setValue($key['PROSID']);
		wi400Detail::setDetailField("DEVELOPER_DOC_SRC",$int);
		
		$gat = new wi400InputText("FROM_GATEWAY");
		$gat->setValue("MONITOR_PROCESSI");
		wi400Detail::setDetailField("DEVELOPER_DOC_SRC",$gat);
	}
