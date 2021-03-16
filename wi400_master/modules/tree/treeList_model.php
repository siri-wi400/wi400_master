<?php

	$treeMerc = "";
	$hiddenFields = array();
	
	if (isset($_REQUEST["IDLIST"])){
		
		// Recupero Lista in sessione
		$treeList = getList($_REQUEST["IDLIST"]);
		
		// Recupero albero associato alla lista
		$treeMerc = $treeList->getTree();
		
		// Recupero dalla sessione eventuale apertura
		if (wi400Session::exist(wi400Session::$_TYPE_TREE, $treeMerc->getId())){
			$treeMerc = wi400Session::load(wi400Session::$_TYPE_TREE, $treeMerc->getId());
		}
		
		if ($treeMerc->getJsSelectionFunction() == ""){
			$treeMerc->setJsSelectionFunction("filterByNodeAndLevel");
		}
		
		foreach($treeMerc->getFilterLevels() as $filter){
			$hiddenFields[] = new wi400InputHidden("FAST_FILTER_".$filter);
		}
		
		$hiddenFields[] = new wi400InputHidden($_REQUEST["IDLIST"]."_SEARCH");
		
	}else if (isset($_REQUEST["IDTREE"])){
		
		// Recupero albero dalla sessione
		$treeMerc = wi400Session::load(wi400Session::$_TYPE_TREE, $_REQUEST["IDTREE"]);
		
		if ($treeMerc->getJsSelectionFunction() == ""){
			$treeMerc->setJsSelectionFunction("passNodeField");
		}
		
	}else{
		echo "ERRORE GRAVE!";
		exit;
	}
	
	$actionContext->setLabel($treeMerc->getDescription());
?>
