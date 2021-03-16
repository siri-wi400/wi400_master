<?php
	$scannerDetail = new wi400Detail("SCANNER_DETAIL");

	$myField = new wi400InputText('BARCODE');
	$myField->setLabel("Barcode articolo:");
	$myField->setInfo("Esegui scansione");
	$myField->setSize(20);
	$myField->setMaxLength(20);
	
	if (!$findItem){
		$myField->setAutoFocus(true);
		$myField->setValue(" ");
	}else{
		$myField->setReadonly(true);
	}	
	
	$scannerDetail->addField($myField);

	$myField = new wi400InputText('ADDQTA');
	$myField->setLabel("Quantità:");
	$myField->setInfo("Specifica quantità");
	$myField->setSize(3);
	$myField->setMaxLength(3);
	$myField->addTool("ADD_TOOL");
	$myField->addTool("REMOVE_TOOL");
	
	if ($findItem){
		$myField->setAutoFocus(true);
		$myField->setValue($qtaRow["QTA"]);
		$scannerDetail->addParameter("ARTICOLO", "".$row["MDACDA"]);
		$scannerDetail->addParameter("DESCRIZIONE", "".$row["MDADSA"]);
	}else{
		$myField->setReadonly(true);
		$myField->setValue(" ");
	}	
	
	$scannerDetail->addField($myField);
	
	
	if (!$findItem){
		$myButton = new wi400InputButton("FILTER_SEARCH_BUTTON");
		$myButton->setAction($actionContext->getAction());
		$myButton->setForm("FIND");
		$myButton->setLabel("Cerca Articolo");
		$scannerDetail->addButton($myButton);
	}
	$scannerDetail->dispose();
	

	$myButton = new wi400InputButton("FILTER_ADD_BUTTON");
	$myButton->setAction($actionContext->getAction());
	$myButton->setForm("ADD");
	$myButton->setLabel("Aggiungi al carrello");
	$buttonsBar[] = $myButton;

	if ($findItem){
		$myButton = new wi400InputButton("FILTER_CANCEL_BUTTON");
		$myButton->setAction($actionContext->getAction());
		$myButton->setLabel("Annulla");
		$buttonsBar[] = $myButton;
	}
		
	$myButton = new wi400InputButton("FILTER_REMOVE_BUTTON");
	$myButton->setScript('closeAndRefresh()');
	$myButton->setLabel("Chiudi");
	$buttonsBar[] = $myButton;
?>

<? 
	if ($actionContext->getForm() == "DEFAULT" || !$findItem){ 
?>
<div class="work-area" style="background-color:#FFFFFF;margin-top:10px;" id="detail_container">
<center>
<?
	$scannerImage = new wi400Image("scanner");
	$scannerImage->setUrl("scanner_ani.gif");
	$scannerImage->dispose();

?>
</center>
</div>
<? 
	}else if ($actionContext->getForm() == "FIND"){
	
		echo "<br>";
	 	$azioniDetail = new wi400Detail("DETTAGLIO_ARTICOLO");
	
	 	$azioniDetail->setTitle($row['MDADSA']);
		$azioniDetail->isEditable(false);
		if (isset($row)){
			$azioniDetail->setSource($row);
		}
	
		$azioniDetail->addField(new wi400Text('MDADSA',"Descrizione",$row['MDADSA']));
		// Reperisco la descrizione del setore
	    $tabelle = new wi400Tabelle("0001", $row['MDASET'], $db);
		$azioniDetail->addField(new wi400Text('MDASET',"Settore",$row['MDASET']."&nbsp;".$tabelle->getDescrizione()));
	    $tabelle = new wi400Tabelle("0002", $row['MDASET'], $db);
		$azioniDetail->addField(new wi400Text('MDAFAM',"Famiglia",$row['MDAFAM']."&nbsp;".$tabelle->getDescrizione()));
	    $tabelle = new wi400Tabelle("0004", $row['MDASET'], $db);
		$azioniDetail->addField(new wi400Text('MDASUF',"Sottofamiglia",$row['MDASUF']."&nbsp;".$tabelle->getDescrizione()));
		$azioniDetail->addField(new wi400Text('MDAMAR',"Marchio",$row['MDAMAR']));
		$azioniDetail->addField(new wi400Text('ZLLCES',"Prezzo cessione",$listRow["ZLLCES"],"DOUBLE_3"));
		$azioniDetail->addField(new wi400Text('ZLLVEN',"Prezzo di vendita",$listRow["ZLLVEN"], "DOUBLE_3"));
		
	  	$myImage = new wi400Image('detailImage');
		$myImage->setWidth(150);
		$myImage->setObjCode($row["MDACDA"]);
		$myImage->setObjType("ART");
		$azioniDetail->addImage($myImage);
		$azioniDetail->dispose();
	
 	}
 ?>


