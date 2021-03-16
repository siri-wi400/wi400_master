<?php
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	// Just for ajax response
	$pageDefaultDecoration = "clean_";
    
	$fieldId = $_REQUEST["FIELD_ID"];
	if(isset($_REQUEST['DETAIL_ID']) && $_REQUEST['DETAIL_ID']) {
		$fieldObj = wi400Detail::getDetailField($_REQUEST["DETAIL_ID"], $fieldId);
		$decodeParameters = $fieldObj->getDecode();
	
		if($_REQUEST["DESC"]) {
			$decodeParameters['LABEL'] = $fieldObj->getLabel();
			$decodeParameters['BYDESC'] = 'YES';
			$decodeKey = base64_encode(md5(serialize($decodeParameters)));
			$decodeParameters['KEYID']=$decodeKey;
		}
	} else {
		$colonna = $_REQUEST['COLONNA'];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['LIST_ID']);
		//$cols = $wi400List->getCols();
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
		//col = $cols[$colonna];
		$col = $wi400List->getCol($colonna);
		$fieldObj = $col->getInput();
		$decodeParameters	= $fieldObj->getDecode();
	}
	
	if($_REQUEST["DESC"]) {
		if (isset($decodeParameters['COLUMN'])) {
			$decodeParameters['KEY_COLUMN'] = $decodeParameters['COLUMN'];
		}
		$decodeParameters['QUERY_MASK'] = '%##FIELD##%';
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
	
	$fieldValue 	  = $_REQUEST["FIELD_VALUE"];
	$maxResult 	  	  = $_REQUEST["FIELD_MAX_RESULT"];
	
	$decodeType = "table";
	if (isset($decodeParameters["TYPE"])){
		$decodeType = $decodeParameters["TYPE"];
	}
	if (isset($_REQUEST['START']) && strtoupper($_REQUEST['START'])=='TRUE') {
		$decodeParameters['QUERY_MASK']='##FIELD##%';
	}
	if (!isset($decodeParameters['QUERY_MASK'])) {
		$decodeParameters['QUERY_MASK']=$settings['ajax_complete_mask_field_default'];
	}
	$decodeParameters['CASE']=True;
	if (isset($_REQUEST['CASE']) && strtoupper($_REQUEST['CASE'])=='FALSE') {
		$fieldValue = strtoupper($fieldValue);
		$decodeParameters['CASE']=False;
	}
	//require_once $base_path.'/package/'.$settings['package'].'/decodingclass/'.$decodeType.".php";
	require_once p13nPackage($decodeType);

	$decodeClass = new $decodeType();
	$decodeClass->setFieldId($fieldId);
	$decodeClass->setMaxResult($maxResult);
	$decodeClass->setFieldValue($fieldValue);
	$decodeClass->setDecodeParameters($decodeParameters);
	if (method_exists($decodeClass, "complete")) {	
		$decodeResult = $decodeClass->complete();
	}
} else {
	die("not Ajax Request");
}
?>