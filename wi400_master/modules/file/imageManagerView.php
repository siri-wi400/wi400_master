<?php
	
	$imageDetail = new wi400Detail("IMAGE_DETAIL");
	$myField = new wi400InputFile("image");
	$myField->setLabel("Immagine");
	$myField->setSize(35);
	$myField->addValidation('required');
	$myField->setInfo('Sceglire un immagine dal computer');
	$imageDetail->addField($myField);
	
	if (isset($_REQUEST['OBJ_CODE'])){
		$imageDetail->addParameter("OBJ_CODE",$_REQUEST['OBJ_CODE']);
		
	}
	if (isset($_REQUEST['OBJ_TYPE'])){
		$imageDetail->addParameter("OBJ_TYPE",$_REQUEST['OBJ_TYPE']);
	}
	if (isset($_REQUEST['IMG_TYPE'])){
		$imageDetail->addParameter("IMG_TYPE",$_REQUEST['IMG_TYPE']);
	}
	if (isset($_REQUEST['IMG_CONTAINER'])){
		$imageDetail->addParameter("IMG_CONTAINER",$_REQUEST['IMG_CONTAINER']);
	}
	if (isset($_REQUEST['IMG_COUNT'])){
		$imageDetail->addParameter("IMG_COUNT",$_REQUEST['IMG_COUNT']);
	}
	if (isset($_REQUEST['SIZE_CONTENITORE'])){
		$imageDetail->addParameter("SIZE_CONTENITORE", $_REQUEST['SIZE_CONTENITORE']);
	}
	
	if (isset($_REQUEST['CONFIRM_DELETE'])){
		$imageDetail->addParameter("CONFIRM_DELETE",$_REQUEST['CONFIRM_DELETE']);
	}
	
	$colsNum = 1;
	if(isset($_REQUEST["IMG_COLS_NUM"]) && !empty($_REQUEST["IMG_COLS_NUM"]))
		$colsNum = $_REQUEST["IMG_COLS_NUM"];
	$imageDetail->addParameter("IMG_COLS_NUM", $colsNum);
	
	$myButton = new wi400InputButton("UPLOAD_BUTTON");
	$myButton->setAction("IMAGEMANAGER");
	$myButton->setForm("UPLOAD");
	$myButton->setValidation(true);
	$myButton->setLabel("Carica");
	$buttonsBar[] = $myButton;
		
	$myButton = new wi400InputButton("CLOSE_BUTTON");
	$myButton->setScript('closeLookUp()');
	$myButton->setLabel("Chiudi");
	$buttonsBar[] = $myButton;
	
	$imageDetail->dispose();
	
	
	if ($reloadImage){

		// Ricarica immagini
		echo "<script>";
		//echo "var parentObj = getParentObj();";
		echo 'wi400top.reloadImage("'. $_REQUEST["OBJ_CODE"].'", "'.$_REQUEST["OBJ_TYPE"].'", "'.$_REQUEST["IMG_TYPE"].'", "'.$_REQUEST['IMG_CONTAINER'].'", '.$_REQUEST["CONFIRM_DELETE"].', '.$colsNum.', '.$_REQUEST['IMG_COUNT'].', '.$_REQUEST['SIZE_CONTENITORE'].');';
		echo "</script>";
		
	}
?>