<?
	//$wi400List = new wi400List();
	//if (wi400Session::exist(wi400Session::$_TYPE_LIST, $_GET['IDLIST'])) {	
	if (isset($_GET['IDLIST'])){
		//$wi400List =  $_SESSION[$_GET['IDLIST']];
		$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_GET['IDLIST']);
	}else{
		echo "ERRORE GRAVE";
		exit();
	}

	
	$azioniDetail = new wi400Detail("SEARCH_DETAIL".$wi400List->getIdList(),true);
	$azioniDetail->addParameter($wi400List->getIdList()."_SEARCH","true");
	$azioniDetail->addParameter($wi400List->getIdList()."_FILTER_NAME","");
	$azioniDetail->addParameter($wi400List->getIdList()."_FILTER_CONFIG","");
	$azioniDetail->addParameter($wi400List->getIdList()."_FILTER_GEN","");

	$filterImage = new wi400Image("FILTER_IMG");
	$filterImage->setUrl("search.png");
	$azioniDetail->addImage($filterImage);
	
	$logicalGroup = false;
	$isFirst = true;
	$fixField = "";
	foreach($wi400List->getFilters() as $filter){
		if (!$filter->getFast()){
			$fastFilter = $filter;
			if ($fixField == ""){
				$fixField = "FAST_FILTER_".$filter->getId();
			}
			$filterLabel = $fastFilter->getDescription();
			if (!$isFirst){
				if ($filter->getLogicalOperator() === wi400Filter::$LOGICAL_OPERATOR_OR){
					$logicalGroup = true;
				}else{
					$logicalGroup = false;
				}
				if ($logicalGroup){
					$filterLabel = "<span id='span_and_or' style='color:red'>(OR)</span> ".$filterLabel;
				}else{
					$filterLabel = "<span id='span_and_or' style='color:green'>(AND)</span> ".$filterLabel;
				}

			}else{
				$isFirst = false;
			}
			$text_obj = new wi400Text("FAST_FILTER_".$filter->getId(),$filterLabel,$fastFilter->getHtml());
			$text_obj->setHiddenFields(false);
			$text_obj->setLookUp($fastFilter->getLookUp());
			if($fastFilter->getFieldObj()) {
				$fieldObj = $fastFilter->getFieldObj();
				$text_obj->setDecode($fieldObj->getDecode());
			}
			$azioniDetail->addField($text_obj);
		}
	}

	$azioniDetail->dispose();
	
	$myButton = new wi400InputButton("FILTER_ADD_BUTTON");
	$myButton->setScript('doSearch("'.$wi400List->getIdList().'")');
	$myButton->setLabel(_t("APPLICA_FILTRI"));
	$myButton->setValidation(True);
	$buttonsBar[] = $myButton;
		
	$myButton = new wi400InputButton("FILTER_REMOVE_BUTTON");
	$myButton->setScript('doRemoveSearch("'.$wi400List->getIdList().'")');
	$myButton->setLabel(_t("RIMUOVI_FILTRI"));
	$buttonsBar[] = $myButton;

	$myButton = new wi400InputButton("FILTER_CANCEL_BUTTON");
	$myButton->setScript('closeLookUp()');
	$myButton->setLabel(_t("ANNULLA"));
	$buttonsBar[] = $myButton;
	
	$myButton = new wi400InputButton("FILTER_SAVE_BUTTON");
	$myButton->setScript('doSaveSearch("'.$wi400List->getIdList().'")');
	$myButton->setLabel(_t("SALVA_FILTRI"));
	$buttonsBar[] = $myButton;
	
	
?>
<script>
	window["AUTO_FOCUS_FIELD_ID"] = "<?= $fixField ?>";
</script>