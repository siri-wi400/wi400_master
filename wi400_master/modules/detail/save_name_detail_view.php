<?
	$idDetail = "";
	$customValues = array();
	if (isset($_GET['IDDETAIL'])){
		// Recupero dati dettaglio
		$idDetail = $_GET['IDDETAIL'];
		$customValues = wi400Detail::loadCustomValues($idDetail);
	}else{
		echo "ERRORE GRAVE";
		exit();
	}
	
	$saveDetail = new wi400Detail("SAVE_DETAIL");
	
	$myField = new wi400InputText('FILTER_NEW');
	$myField->addValidation('required');
	$myField->setLabel("Salva con nome:");
	$myField->setInfo("Nome identificativo");
	$myField->setSize(20);
	$myField->setMaxLength(20);
	$myField->setOnChange("checkFilterName(this)");
	$saveDetail->addField($myField);

	$mySelect = new wi400InputSelect('FILTER_OVERWRITE');
	$mySelect->setLabel("Sovrascrivi esistente:");
	$mySelect->setFirstLabel("Nessuno");
	$mySelect->setOnChange("checkFilterOverWrite(this)");

	foreach ($customValues as $key => $value){
		$mySelect->addOption($key);	
	}
	$saveDetail->addField($mySelect);
	$saveDetail->dispose();
	
	$myButton = new wi400InputButton("FILTER_ADD_BUTTON");
	$myButton->setScript('passDetailName()');
	$myButton->setLabel("Salva");
	$buttonsBar[] = $myButton;

	$myButton = new wi400InputButton("FILTER_REMOVE_BUTTON");
	$myButton->setScript('closeLookUp()');
	$myButton->setLabel("Annulla");
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


function passDetailName(){
	var overWriteObj = document.getElementById("FILTER_OVERWRITE");
	var newNameObj = document.getElementById("FILTER_NEW");
	
	var fileName = "";
	if (overWriteObj.value != ""){
		fileName = overWriteObj.value;
	}else if (newNameObj.value != ""){
		fileName = newNameObj.value;
	}else{
		alert("Inserire un nome o selezionarne uno esistente!");
		return;
	}

	var parentObj = getParentObj();
	var fileNameObj = parentObj.document.getElementById("<?= $idDetail ?>_DETAIL_SAVE");
	fileNameObj.value = fileName;
	parentObj.doSubmit("SAVE_DETAIL","SAVE&IDDETAIL=<?= $idDetail ?>");
	closeLookUp();
}
</script>