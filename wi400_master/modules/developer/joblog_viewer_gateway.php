<?php 
if($actionContext->getGateway()=="DEVELOPER_DOCX") {
	// Reperisco i dati
	$init_gateway = wi400Detail::getDetailValue("DEVELOPER_DOC_SRC", "FROM_GATEWAY");
	if ($init_gateway=="MONITOR_PROCESSI") {
		$key = getListKeyArray('MONITOR_PROCESSI_LIST');
		$job = $key['PROJOA'];
	} else {
		$jobInfo = getJobInfo(True);
		$job = $jobInfo['NBR']."/".$jobInfo['USR']."/".$jobInfo['JOB'];
	}
	$int = new wi400InputText("JOB");
	$int->setValue($job);
	wi400Detail::setDetailField("JOBLOG_VIEWER_SRC",$int);
	
	$int = new wi400InputText("FORMATO");
	$int->setValue("E");
	wi400Detail::setDetailField("JOBLOG_VIEWER_SRC",$int);
	
	$gat = new wi400InputText("FROM_GATEWAY");
	$gat->setValue($actionContext->getGateway());
	wi400Detail::setDetailField("JOBLOG_VIEWER_SRC",$gat);

}
if($actionContext->getGateway()=="DEVELOPER_DOCS") {
	// Reperisco i dati
	// Verifico qual'è il Gateway di entrata
	$init_gateway = wi400Detail::getDetailValue("DEVELOPER_DOC_SRC", "FROM_GATEWAY");	
	if ($init_gateway=="MONITOR_PROCESSI") {
		$key = getListKeyArray('MONITOR_PROCESSI_LIST');
		$job = $key['PROJAD'];
	} else {
		$query ="SELECT JOB_NAME AS JOB FROM TABLE(QSYS2.ACTIVE_JOB_INFO('YES' ,'','*','')) AS X";
		$result = $db->query($query);
		$row = $db->fetch_array($result);
		$job = $row['JOB'];
	}
	$int = new wi400InputText("JOBS");
	$int->setValue($job);
	wi400Detail::setDetailField("JOBLOG_VIEWER_SRC",$int);
	
	$int = new wi400InputText("FORMATOS");
	$int->setValue("E");
	wi400Detail::setDetailField("JOBLOG_VIEWER_SRC",$int);
	
	$gat = new wi400InputText("FROM_GATEWAY");
	$gat->setValue($actionContext->getGateway());
	wi400Detail::setDetailField("JOBLOG_VIEWER_SRC",$gat);  
}
?>
