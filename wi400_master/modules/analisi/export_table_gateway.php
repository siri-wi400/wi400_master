<?php

//	echo "GATEWAY: ".$actionContext->getGateway()."<br>";

	if($actionContext->getGateway()!="") {
		$idDetail = $actionContext->getAction()."_SRC";
		
		wi400Detail::cleanSession($idDetail);

		$tipo_exp = "excel2007";
		$des_titoli = true;
		$notifica = "NOTIFICA";
	}

	if($actionContext->getGateway()=="EXPORT_PRODSETAS") {
		$tabella = "FXLSGIAN";
		$notifica = "ALLEGATO";
//		$zip = true;
		
		$subject = "Esportazione prodotti per settore e assortimento";
		$body = "L'esportazione prodotti per settore e assortimento è stata eseguita con successo";
		$file_name = $subject;
		
		$popola = $actionContext->getGateway();
	}
	
//	echo "TABELLA: $tabella<br>";
	
	$fieldObj = new wi400InputText("TABELLA");
	$fieldObj->setValue($tabella);
	wi400Detail::setDetailField($idDetail, $fieldObj);
	
	if(isset($libreria) && $libreria!="") {
		$fieldObj = new wi400InputText("LIBRERIA");
		$fieldObj->setValue($libreria);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
	
	$fieldObj = new wi400InputText("TIPO_EXP");
	$fieldObj->setValue($tipo_exp);
	wi400Detail::setDetailField($idDetail, $fieldObj);
	
	if(isset($des_titoli) && $des_titoli==true) {
		$fieldObj = new wi400InputSwitch("DES_TITOLI");
		$fieldObj->setChecked(true);
//		$fieldObj->setValue(1);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
	
	if(isset($notifica) && $notifica!="") {
		$fieldObj = new wi400InputText("NOTIFICA");
		$fieldObj->setValue($notifica);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
	
	if(isset($zip) && $zip==true) {
		$fieldObj = new wi400InputSwitch("ZIP");
		$fieldObj->setChecked(true);
//		$fieldObj->setValue(1);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
	
	if(isset($subject) && $subject!="") {
		$fieldObj = new wi400InputText("SUBJECT");
		$fieldObj->setValue($subject);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
	
	if(isset($body) && $body!="") {
		$fieldObj = new wi400InputText("BODY");
		$fieldObj->setValue($body);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
	
	if(isset($file_name) && $file_name!="") {
		$fieldObj = new wi400InputText("FILE_NAME");
		$fieldObj->setValue($file_name);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
	
	if(isset($popola) && $popola!="") {
		$fieldObj = new wi400InputText("POPOLA");
		$fieldObj->setValue($popola);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}