<?php 

	if($actionContext->getForm()=="DEFAULT") {
/*		
		if($csv_exp===true || $csv_limit===true) {
			if($csv_exp===true)
				$msg = "Raggiunto limite di esportazione in XLS/XLSX. Eseguire l'esportazione in formato CSV.";
			else if($csv_limit===true)
				$msg = "Raggiunto limite di esportazione in CSV. Esportazione interrotta.";
?>
			<script>
				alert(<?= $msg?);
//				closeLookUp();
			</script>
<?php
		}
*/
//		if($wi400List->getExportBatch()===true && empty($wi400List->getSubfile())) {
//		if($wi400List->getExportBatch()===true) {
		if((isset($settings['export_list_batch']) && $settings['export_list_batch']===true) || $wi400List->getExportBatch()===true) {
			$export->set_export_batch(true);
		}
		
		$export->viewDefault($_REQUEST['EXP_LIST'], true, true, true, true);
		
		$myButton = new wi400InputButton("EXPORT_BUTTON");
//		$myButton->setScript("exportListFile('".$_REQUEST['IDLIST']."')");
		$myButton->setAction("EXPORTLIST");
		$myButton->setForm("EXPORT");
		$myButton->addParameter("EXP_LIST", $_REQUEST['EXP_LIST']);
		$myButton->setLabel(_t("ESPORTA"));
		$buttonsBar[] = $myButton;
		
		$myButton = new wi400InputButton("PREVIEW_BUTTON");
		$myButton->setAction("EXPORTLIST");
		$myButton->setForm("EXPORT");
		$myButton->addParameter("GET_PREVIEW", "1");
		$myButton->addParameter("EXP_LIST", $_REQUEST['EXP_LIST']);
		$myButton->setLabel("Preview");
		$buttonsBar[] = $myButton;
			
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel(_t("ANNULLA"));
		$buttonsBar[] = $myButton;
		
?>
		<script>
			jQuery('input[name="FORMAT"]').change(function() {
				var pdfRadio = jQuery('input[value="pdf"]:checked');
				var buttonPreview = jQuery('#PREVIEW_BUTTON');
				if(pdfRadio.length) 
					buttonPreview.css("display", "inline-block");
				else 
					buttonPreview.css("display", "none");
			});
		</script>
<?php 
	}
	else if ($actionContext->getForm() == "EXPORT") {
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setScript('history.back()');
		$myButton->setLabel(_t("INDIETRO"));
		$buttonsBar[] = $myButton;
	
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel(_t("CHIUDI"));
		$buttonsBar[] = $myButton;

		downloadDetail($TypeImage, $filename, $temp, _t("ESPORTAZIONE_COMPLETATA"));
	}

?>