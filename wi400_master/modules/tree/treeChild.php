<?php
	
	if (!isset($_REQUEST["IDTREE"]) 
		|| $_REQUEST["IDTREE"] == ""
			|| !wi400Session::exist(wi400Session::$_TYPE_TREE, $_REQUEST["IDTREE"]) ){
				echo "ERRORE GRAVE:treeChild";
				exit();
	}
	
	$treeChild = wi400Session::load(wi400Session::$_TYPE_TREE, $_REQUEST["IDTREE"]);
	
	$getChildParameters = explode ("|",$_REQUEST["TREE_PARAMETERS"]);
	
	if ($actionContext->getForm() == "CLOSE_NODE"){
		
		$treeChild->closeNode($getChildParameters[0], $getChildParameters[1], 
				$getChildParameters[2], $getChildParameters[3], $getChildParameters[4]);
		
	}else{
		
		$treeChild->getChild($getChildParameters[0], $getChildParameters[1], 
				$getChildParameters[2], $getChildParameters[3], $getChildParameters[4]);
	}
?>