<?php
$form = $actionContext->getForm();
$azione = $actionContext->getAction();
if($form == "DEFAULT") {
	wi400Detail::cleanSession("LOCKS_SRC");
	wi400Detail::cleanSession("LOCKS_DETAIL");
	$actionContext->gotoAction($azione, "DETAIL", "", True);
}
if($form == "DETAIL") {
	$searchAction = new wi400Detail($azione."_DETAIL", false);
	// Societa
	// Sblocco porta
	$myField= new wi400InputCheckbox('SOLOLOCK');
	$myField->setLabel("Solo lock della sessione");
	$myField->setOnChange("risottomettiForm('DETAIL')");
	$myField->setValue("1");
	$myField->setChecked(false);
	if (isset($_REQUEST['SOLOLOCK']) && $_REQUEST['SOLOLOCK']== '1')
		$myField->setChecked(true);
	$searchAction->addField($myField);
	//
	$searchAction->dispose();
	
	$miaLista = new wi400List("LOCKS_LIST",true);
	$miaLista->setFrom("TABLOCK");
	$miaLista->setWhere("LOCKSTA = '1'");
	$miaLista->setOrder("LOCKTIM ASC");
	if (isset($_REQUEST['SOLOLOCK']) && $_REQUEST['SOLOLOCK']=="1") {
		$sessione = wi400Detail::getDetailValue("LOCKS_SRC", "SESSIONE");
		if ($sessione =="") {
			$sessione = session_id();
		}
		$miaLista->setWhere("LOCKSTA = '1' AND LOCKSES='$sessione'");
	}
	
	
	$miaLista->setShowMenu(false);
	
	$miaLista->setSelection("MULTIPLE");
	
	$miaLista->setCalculateTotalRows("true");
	$miaLista->setShowMenu(true);
	$miaLista->setCols(array(
							new wi400Column("LOCKCON",_t('CONTEXT')),
							new wi400Column("LOCKKEY",_t('LABEL_KEY')),
							new wi400Column("LOCKSES",_t('SESSION')),
							new wi400Column("LOCKUSR",_t('USER')),
							new wi400Column("LOCKTIM",_t('DATE'),"TIMESTAMP"),
							new wi400Column("LOCKTYP",_t('TYPE'))
							)
						);
	
	// aggiunta chiavi di riga
	$miaLista->addKey("LOCKCON");
	$miaLista->addKey("LOCKKEY");
	
	
	
	$mioFiltro = new wi400Filter("MDADSA",_t('LABEL_KEY'),"STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	
	// Aggiunta azioni
	$action = new wi400ListAction();
	$action->setAction("LOCKS");
	$action->setForm("DELETE");
	$action->setSelection("MULTIPLE");
	$action->setLabel(_t('DELETE_SEL'));
	$miaLista->addAction($action);
	
	listDispose($miaLista);
}
