<?php

	$azione = $actionContext->getAction();
//	echo "AZIONE: $azione<br>";

	$idDetail = $azione."_SRC";
//	echo "DETAIL: $idDetail<br>";

	$idDetailMarkers = $azione."_MARKERS_DEF";
//	echo "DETAIL MARKERS: $idDetailMarkers<br>";

//	echo "GATEWAY: ".$actionContext->getGateway()."<br>";
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";

	wi400Detail::cleanSession($idDetailMarkers);
	
	if($actionContext->getGateway()!="QUERY_TOOL_DB_MARKERS") {
		unset($_SESSION['ID_QUERY_DB_DETAIL_MARKERS_DEF']);
	}
	
	if($actionContext->getGateway()=="QUERY_MANAGER_DB") {
		wi400Detail::cleanSession($idDetail);
		
		$idList = $actionContext->getGateway()."_QUERY_LIST";
//		echo "IDLIST: $idList<br>";
		
		$keyArray = array();
		$keyArray = getListKeyArray($idList);
		
		$id_query = $keyArray['ID_QUERY'];
//		$des_query = $keyArray['DES_QUERY'];
//		echo "ID QUERY: $id_query<br>";
//		echo "DES_QUERY: $des_query<br>";
	
		$fieldObj = new wi400InputText("ID_QUERY");
		$fieldObj->setValue($id_query);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
/*
	else if($actionContext->getGateway()=="QUERY_TOOL_DB_SEL") {
		wi400Detail::cleanSession($idDetail);
		
		$idList = $azione."_QUERY_SEL_LIST";
//		echo "IDLIST: $idList<br>";
		
		$keyArray = array();
		$keyArray = getListKeyArray($idList);
		
		$id_query = $keyArray['ID_QUERY'];
		$des_query = $keyArray['DES_QUERY'];
//		echo "ID QUERY: $id_query<br>";
//		echo "DES_QUERY: $des_query<br>";
		
		$fieldObj = new wi400InputText("ID_QUERY");
		$fieldObj->setValue($id_query);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
*//*	
	else if($actionContext->getGateway()=="QUERY_TOOL_DB_LOOKUP") {
		wi400Detail::cleanSession($idDetail);

		if(isset($_REQUEST['ID_QUERY']) && !empty($_REQUEST['ID_QUERY'])) {
			$id_query = $_REQUEST['ID_QUERY'];
			echo "ID QUERY: $id_query<br>";
//			echo "DES_QUERY: $des_query<br>";
	
			$fieldObj = new wi400InputText("ID_QUERY");
			$fieldObj->setValue($id_query);
			wi400Detail::setDetailField($idDetail, $fieldObj);
		}
	}
*/
	else if(in_array($actionContext->getGateway(), array("QUERY_TOOL_DB_SAVE_NEW", "QUERY_TOOL_DB_MULTI"))) {
		wi400Detail::cleanSession($idDetail);
		
		$id_query = $_REQUEST['ID_QUERY'];
		
		$fieldObj = new wi400InputText("ID_QUERY");
		$fieldObj->setValue($id_query);
		wi400Detail::setDetailField($idDetail, $fieldObj);
	}
	else if($actionContext->getGateway()=="QUERY_TOOL_DB_MARKERS") {
		wi400Detail::cleanSession($idDetail);
		
		unset($_SESSION[$azione."_QUERY_LIBERA"]);

		$id_query = $_REQUEST['ID_QUERY'];
//		$id_query = 500;
//		echo "ID QUERY: $id_query<br>";
		
		$fieldObj = new wi400InputText("ID_QUERY");
		$fieldObj->setValue($id_query);
		wi400Detail::setDetailField($idDetail, $fieldObj);

		// Detail dei marker di default
//		$idDetailDef = $_REQUEST['ID_DETAIL_MARKERS_DEF'];
		$idDetailDef = $_SESSION['ID_QUERY_DB_DETAIL_MARKERS_DEF'];
//		echo "ID_DETAIL_MARKERS_DEF: $idDetailDef<br>";

		$fields = wi400Detail::getDetailFields($idDetailDef);
		wi400Detail::setDetailFields($idDetailMarkers, $fields);
	}