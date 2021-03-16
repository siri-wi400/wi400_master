<?
		
	$new_filter = wi400Detail::getDetailValue("SAVE_DETAIL",'FILTER_NEW');
	$overwrite_filter = wi400Detail::getDetailValue("SAVE_DETAIL",'FILTER_OVERWRITE');
	
	$wi400List = wi400Session::load(wi400Session::$_TYPE_LIST, $_REQUEST['IDLIST']);
	$saveDetail = new wi400Detail("SAVE_DETAIL");
	
	$myField = new wi400InputText('FILTER_NEW');
	$myField->addValidation('required');
	$myField->setLabel(_t('LIST_FILTER_CREATE'));
	$myField->setInfo(_t('LIST_FILTER_NAME'));
	$myField->setSize(20);
	$myField->setMaxLength(20);
	$myField->setOnChange("checkFilterName(this)");
	$myField->setValue($new_filter);
	$saveDetail->addField($myField);
	
//	echo "AZIONE: ".$actionContext->getAction()." - FORM: ".$actionContext->getForm()."<br>";

	$mySelect = new wi400InputSelect('FILTER_OVERWRITE');
	$mySelect->setLabel(_t('LIST_FILTER_OVERWRITE'));
	$mySelect->setFirstLabel(_t('LIST_FILTER_NOONE'));
//	$mySelect->setOnChange("checkFilterOverWrite(this)");
	$mySelect->setOnChange("checkFilterOverWrite(this);doSubmit('".$actionContext->getAction()."', '".$actionContext->getForm()."')");

	foreach ($wi400List->getCustomFilters() as $key => $value){
		$mySelect->addOption($key);	
	}
	$mySelect->setValue($overwrite_filter);
	$saveDetail->addField($mySelect);
	
	$myField = new wi400InputCheckbox('FILTER_CONFIG');
	$myField->setLabel(_t('LIST_FILTER_CONFIG'));
	$saveDetail->addField($myField);
	
//	if(isset($_SESSION["user_admin"]) && $_SESSION["user_admin"]==true) {
		$myField = new wi400InputCheckbox('FILTER_GEN');
		$myField->setLabel(_t('FILTRO_GENERICO'));
		if(!(isset($_SESSION["user_admin"]) && $_SESSION["user_admin"]==true))
			$myField->setReadonly(true);
		if(substr($overwrite_filter, 0, 1)=="*") {
			$myField->setChecked(true);
		}
		$saveDetail->addField($myField);
//	}

	$saveDetail->addParameter("IDLIST", $_REQUEST['IDLIST']);
	
	$saveDetail->dispose();
	
	$myButton = new wi400InputButton("FILTER_ADD_BUTTON");
	$myButton->setScript('passFilterName()');
	$myButton->setLabel(_t('SAVE'));
	$buttonsBar[] = $myButton;

	$myButton = new wi400InputButton("FILTER_REMOVE_BUTTON");
	$myButton->setScript('closeLookUp()');
	$myButton->setLabel(_t('CANCEL'));
	$buttonsBar[] = $myButton;
?>
<script>
function checkFilterOverWrite(which){
	if (which.value != ""){
		document.getElementById("FILTER_NEW").value = "";
	}
}

function checkFilterName(which){
	var overWriteObj = document.getElementById("FILTER_OVERWRITE");
	if (which.value != ""){
		overWriteObj.value = "";
		overWriteObj.disabled = true;
		if (overWriteObj.options.length > 0){
			for (var i=0; i<overWriteObj.options.length; i++){
 				if (overWriteObj.options[i].value == which.value){
 					overWriteObj.options[i].selected = true;
 					overWriteObj.disabled = false;
 					checkFilterOverWrite(overWriteObj);
 				}
 			}
		}
	}else{
		overWriteObj.disabled = false;
	}
}


function passFilterName(){
	var overWriteObj = document.getElementById("FILTER_OVERWRITE");
	var newNameObj = document.getElementById("FILTER_NEW");
	var listConfigObj = document.getElementById("FILTER_CONFIG");
	var listGenObj = document.getElementById("FILTER_GEN");
	
	var fileName = "";
	if (overWriteObj.value != ""){
		fileName = overWriteObj.value;
	}else if (newNameObj.value != ""){
		fileName = newNameObj.value;
	}else{
		alert(yav_config.FILTER_SEL);
		return;
	}

	var parentObj = getParentObj();
	var fileNameObj = parentObj.document.getElementById("<?= $_REQUEST["IDLIST"] ?>_FILTER_NAME");
	fileNameObj.value = fileName;

	var filterConfigObj = parentObj.document.getElementById("<?= $_REQUEST["IDLIST"] ?>_FILTER_CONFIG");
	if (listConfigObj.checked){
		filterConfigObj.value = "save";
	}else{
		filterConfigObj.value = "";
	}

	var filterGenObj = parentObj.document.getElementById("<?= $_REQUEST["IDLIST"] ?>_FILTER_GEN");
	if (listGenObj.checked){
		filterGenObj.value = "save";
	}else{
		filterGenObj.value = "";
	}
	
	parentObj.doSearch("<?= $_REQUEST["IDLIST"] ?>", "SAVE", "SAVE");
	closeLookUp();
}
</script>