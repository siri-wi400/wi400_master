<?php
function SWITCH_SINO_OUTPUT_RPG($id, $field, $value, $obj=Null, $exit="") {
	$result = "N";
	if (isset($value) && $value =="1") {
		$result = "S";
	}
	return $result;
}
function SWITCH_SINO_OUTPUT_PHP($id, $field, $value, $obj, $exit="") {
	
	$myField = new wi400InputSwitch($id);
	$myField->setLabel($field->getLabel());
	$myField->setOnLabel(_t('LABEL_YES'));
	$myField->setOffLabel(_t('LABEL_NO'));
	$myField->setChecked($value=="S");
	
	return $myField;
}
function GIWI_IMAGE_PHP($id, $field, $value, $obj, $exit="") {
    global $GIWI_DATI_FIELDS;

    //showArray($GIWI_DATI_FIELDS);
    //showArray($obj);
    //echo __FUNCTION__;
    //showArray($obj->getFunctionParm());
    //echo "ssaaaa";
    //$ca= $obj->getClientAttributes($exit)->getParametri();
    //foreach ($ca as $key => $value) {
    //    echo $value;
    //    echo "<br>TROVATO:".$GIWI_DATI_FIELDS[$value];
    //}
    
    $myImage = new wi400Image('detailImage');
	$myImage->setManager(true);
	$myImage->setAddImage(true);
	$myImage->setShowContenitore(true);
	$myImage->setSizeContenitore(150);
	$myImage->setObjCode("PROVA");
	$myImage->setObjType("ORD");
	
	return $myImage;
}
function GIWI_VUOTO($id, $field, $value, $obj, $exit="") {
	
	$myField = new wi400Text($id);
	return $myField;
}

function GIWI_MAPS_OUTPUT($id, $field, $value, $obj, $exit="") {
	global $settings;
	
	$field->setSize(strlen($value));
	
	$customTool = new wi400CustomTool("CONSOLE_GIWI400", 'MAPPA');
	$customTool->addParameter('INDIRIZZO', base64_encode($value));
	$customTool->setIco("themes/common/images/mappa.png");
	$customTool->setToolTip("Google maps");
	$customTool->setTarget('WINDOW', 1000, 700);
	//$customTool->setScript("openLookupGiwi400('".$key."')");
	$field->addCustomTool($customTool);
	
	return $field;
}