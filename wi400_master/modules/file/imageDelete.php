<?php

    if (isset($_REQUEST["IMG_CODE"]) && $_REQUEST["IMG_CODE"]!="" && $actionContext->getForm() == "DELETE"){
    	global $root_path;
    	
    	// Cancello da db
		$stmtdelete = $db->prepare("DELETE", "OBJ_IMG", array("IMG_CODE"), null); 
		$result = $db->execute($stmtdelete, array("".$_REQUEST["IMG_CODE"]));
		
		// Cancello da filesystem
		$myPath = new wi400Image('pathImage');
		if (isset($_REQUEST["OBJ_TYPE"])){
			$myPath->setObjType($_REQUEST["OBJ_TYPE"]);
		}
		if (isset($_REQUEST["IMG_TYPE"])){
			$myPath->setImgType($_REQUEST["IMG_TYPE"]);
		}
		
	    $handler = opendir($_SERVER['DOCUMENT_ROOT'].$myPath->getImagePath());
	    while ($file = readdir($handler)) {
	        if (!is_dir($file) && 
	        	( strpos($file,"_".$_REQUEST["IMG_CODE"].".")>-1 
	        		|| strpos($file,$_REQUEST["IMG_CODE"].".") === 0 )){
	        	unlink($_SERVER['DOCUMENT_ROOT'].$myPath->getImagePath().$file);
	        }
	    }
	    closedir($handler);
    }
    
    $idWi400Image = "";
    if (isset($_REQUEST["IMG_CONTAINER"])) {
		$id = explode("_", $_REQUEST['IMG_CONTAINER']);
		$idWi400Image = $id[2];
    }
    else {
    	$idWi400Image = "detailImage";
    }
    $myImage = new wi400Image($idWi400Image);
	$myImage->setManager(true);
	$myImage->setWidth(150);
	$myImage->setShowContenitore(true);
	if (isset($_REQUEST["SIZE_CONTENITORE"])) {
		$myImage->setSizeContenitore($_REQUEST['SIZE_CONTENITORE']);
	} 
	if (isset($_REQUEST["IMG_COUNT"])) {
		$myImage->setMaxCount($_REQUEST['IMG_COUNT']);
	}
	if (isset($_REQUEST["CONFIRM_DELETE"])){
		$myImage->setConfirmDelete($_REQUEST["CONFIRM_DELETE"]);
	}
	if (isset($_REQUEST["OBJ_CODE"])){
		$myImage->setObjCode($_REQUEST["OBJ_CODE"]);
	}
	if (isset($_REQUEST["OBJ_TYPE"])){
		$myImage->setObjType($_REQUEST["OBJ_TYPE"]);
	}
	if (isset($_REQUEST["IMG_TYPE"])){
		$myImage->setImgType($_REQUEST["IMG_TYPE"]);
	}
	if(isset($_REQUEST['IMG_COLS_NUM'])) {
		$myImage->setColsNum($_REQUEST["IMG_COLS_NUM"]);
	}
	$myImage->dispose();
?>