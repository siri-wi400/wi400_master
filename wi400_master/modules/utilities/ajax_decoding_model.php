<?php
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	// Just for ajax response
	$pageDefaultDecoration = "clean_";

	$fieldId 			= $_REQUEST["FIELD_ID"];
	//Controllo se è un campo multiplo
	if(strpos($fieldId, "new_") !== false) {
		//Recupero l'ID padre
		$arr = explode("_", $fieldId);
		unset($arr[count($arr)-1]);
		unset($arr[0]);
		$fieldId = implode("_", $arr);
	}
	if(isset($_REQUEST['DETAIL_ID']) && $_REQUEST['DETAIL_ID']) {
		$fieldObj = wi400Detail::getDetailField($_REQUEST["DETAIL_ID"], $fieldId);
	}else {
		$colonna = $_REQUEST['COLONNA'];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['LIST_ID']);
		//$cols = $wi400List->getCols();
		//$col = $cols[$colonna];
		$col = $wi400List->getCol($colonna);
		$fieldObj = $col->getInput();
		if (isset($_REQUEST['WI400_LIST_KEY'])) {
			$list_key=array();
			$keys = $wi400List->getKeys();
			$valore = explode("|", $_REQUEST['WI400_LIST_KEY']);
			$ii=0;
			foreach ($keys as $kkk => $vvv) {
				$list_key[$kkk]=$valore[$ii];
				$ii++;
			}
			$_REQUEST['WI400_LIST_KEY_ARRAY']=$list_key;
		}
	}
	$decodeParameters	= $fieldObj->getDecode();
	$decodeParameters['LABEL'] = $fieldObj->getLabel();
	$fieldValue 		= $_REQUEST["FIELD_VALUE"];
	$label				= $decodeParameters["LABEL"];
	$decodeType = "table";
	if (isset($decodeParameters["TYPE"])){
		$decodeType = $decodeParameters["TYPE"];
	}
	// Cerco eventuali decode Parameters
	if (isset($decodeParameters['JS_PARAMETERS'])) {
		foreach ($decodeParameters['JS_PARAMETERS'] as $key => $value) {
			// Se Dettaglio
			if(isset($_REQUEST['DETAIL_ID']) && $_REQUEST['DETAIL_ID']) {
				$decodeParameters[$value]=$_REQUEST[$key];
			} else {
				// Recupero dai pezzi della lista
				$lista= $_REQUEST['LIST_ID'];
				$row= $_REQUEST['ROW_NUMBER'];
				$decodeParameters[$value]=$_REQUEST[$lista."-".$row."-".$key];
			}
		}
	}
	//require_once $base_path.'/package/'.$settings['package'].'/decodingclass/'.$decodeType.".php";
	require_once p13nPackage($decodeType);

	$decodeClass = new $decodeType();
	$decodeClass->setFieldId($fieldId);
	$decodeClass->setFieldLabel($label);
	$decodeClass->setFieldValue($fieldValue);
	$decodeClass->setDecodeParameters($decodeParameters);
	
	$decodeResult = $decodeClass->decode();
	
	$fieldMessage = $decodeClass->getFieldMessage();
	$decodeFields = $decodeClass->decodeFields();
} else {
	die("not Ajax Request");
}
?>