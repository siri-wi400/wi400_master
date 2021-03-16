<?php
$spacer = new wi400Spacer();

if($actionContext->getForm()=="DEFAULT_2") {
	// Mostro specchietto con colonne che dovranno essere presenti sul subfile
/*	
	$stringa = "<table border = '1'><tr>";
	foreach ($customSubfile->import_get_column() as $key) {
		$stringa .= "<td>".$key."</td>";
	}
	$stringa .="</tr></tabled>";
	echo $stringa;
*/	
	$azioniDetail = new wi400Detail($azione."_CAMPI_IMP_DET", true);
	$azioniDetail->setTitle("Campi importazione");
	$azioniDetail->setColsNum(2);
	
	foreach($cols_values as $key => $des) {
		$myField = new wi400InputText($key);
		$myField->setLabel($key);
		$myField->setValue($des);
		$myField->addValidation('required');
		$myField->setSize(10);
		$myField->setMaxLength(100);
		$myField->setReadonly(true);
//		$myField->setType("HIDDEN");
		$azioniDetail->addField($myField);
	
		$c++;
	}
	
	$azioniDetail->dispose();
	
	$spacer->dispose();
	
	// Chideo se Accodamento o Prima svuoto (Se previsto da Funzione)
	$azioniDetail = new wi400Detail($azione."_FILTRI_LIST_DET", true);
	$azioniDetail->setTitle("Importazione");
	
	$myField = new wi400InputSwitch("OVERWRITE");
	$myField->setLabel("Sovrascrivi Dati");
	$myField->setOnLabel(_t('LABEL_YES'));
	$myField->setOffLabel(_t('LABEL_NO'));
	$myField->setChecked($check_overwrite);
	$myField->setValue(1);
	$azioniDetail->addField($myField);
	
	$myField = new wi400InputFile("IMPORT_FILE");
	$myField->setLabel("Importa dati da file");
	$myField->addValidation('required');
	$azioniDetail->addField($myField);
	
	$myField = new wi400InputText("FOGLIO");
	$myField->setLabel("Foglio");
	$myField->setSize(10);
	$myField->setMaxLength(100);
	$azioniDetail->addField($myField);
	
	$myField = new wi400InputText("RIGA");
	$myField->setLabel("Riga Titoli");
	$myField->setSize(10);
	$myField->setMaxLength(10);
	$myField->setValue($row_title);
	$azioniDetail->addField($myField);
	
	$myButton = new wi400InputButton('IMPORT_BUTTON');
	$myButton->setLabel("Importa");
	$myButton->setAction($azione);
	$myButton->setForm("IMPORT");
	$myButton->setConfirmMessage("Importare i dati?");
	$myButton->setValidation(true);
	$azioniDetail->addButton($myButton);
	
	$azioniDetail->dispose();
	if(isset($_SESSION[$azione."_XLS_FILE"]) && $_SESSION[$azione."_XLS_FILE"]!="") {
		$filename = urlencode($_SESSION[$azione.'_XLS_FILE']);
			
		unset($_SESSION[$azione.'_XLS_FILE']);
?>
			<script type="text/javascript">
				openWindow(_APP_BASE + APP_SCRIPT + "?DECORATION=lookUp&t=<?= $azione?>&f=EXPORT&FILE_NAME=<?= $filename?>");
			</script>
<?php			
	}
		
	// Import del fogliio
	// Controllo prima eventuali errori
	// Se tutto OK Ciclo per scrittura con metodo Subfile
	// Lancio il reload della lista alla chiusura della WINDOW
}
if($actionContext->getForm()=="SUCCESS") {
	close_block_window();
}