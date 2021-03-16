<?php
$azione = $actionContext->getAction();
require_once "developer_auth.php";
if ($actionContext->getForm()=="DETAIL") {
	$gateway = wi400Detail::getDetailValue("JOBLOG_VIEWER_SRC", "FROM_GATEWAY");
	if (isset($_REQUEST['GATEWAY'])) {
		$gateway = $_REQUEST['GATEWAY'];
	}
	if ($gateway=="DEVELOPER_DOCX") {
		$jobComplete = wi400Detail::getDetailValue("JOBLOG_VIEWER_SRC", "JOB");
		$formato = wi400Detail::getDetailValue("JOBLOG_VIEWER_SRC", "FORMATO");
	}
	if ($gateway=="DEVELOPER_DOCS") {
		$jobComplete = wi400Detail::getDetailValue("JOBLOG_VIEWER_SRC", "JOBS");
		$formato = wi400Detail::getDetailValue("JOBLOG_VIEWER_SRC", "FORMATOS");
	}
	$jobInfo = explode("/", $jobComplete);
	$jobname=   $jobInfo[2];    //job
	$jobuser=   $jobInfo[1];    //user
	$jobnumber= $jobInfo[0];    //nbr
}
if ($actionContext->getForm()=="CHANGE") {
	$gateway = $_REQUEST['GATEWAY'];
	if ($gateway=="DEVELOPER_DOCX") {
		$jobComplete = wi400Detail::getDetailValue("JOBLOG_VIEWER_SRC", "JOB");
		$formato = wi400Detail::getDetailValue("JOBLOG_VIEWER_SRC", "FORMATO");
	}
	if ($gateway=="DEVELOPER_DOCS") {
		$jobComplete = wi400Detail::getDetailValue("JOBLOG_VIEWER_SRC", "JOBS");
		$formato = wi400Detail::getDetailValue("JOBLOG_VIEWER_SRC", "FORMATOS");
	}
	$newFormato = "";
	if ($formato=="E") {
		$newFormato="R";
	} else {
		$newFormato="E";
	}
	if ($gateway=="DEVELOPER_DOCX") {
		$int = new wi400InputText("FORMATO");
		$int->setValue($newFormato);
		wi400Detail::setDetailField("JOBLOG_VIEWER_SRC",$int);
	}
	if ($gateway=="DEVELOPER_DOCS") {
		$int = new wi400InputText("FORMATOS");
		$int->setValue($newFormato);
		wi400Detail::setDetailField("JOBLOG_VIEWER_SRC",$int);
	}
	$actionContext->gotoAction($azione, "DETAIL", "", True, True);
}