<?php

 	$azioniDetail = new wi400Detail("DETTAGLIO_ARTICOLO");

 	$azioniDetail->setTitle($row['ZLLDSA']);
	$azioniDetail->isEditable(false);
	if (isset($row)){
		$azioniDetail->setSource($row);
	}
	
	$azioniDetail->addField(new wi400Text('ZLLDSA',"Descrizione",$row['ZLLDSA']));
	$azioniDetail->addField(new wi400Text('ZLLDSE',"Settore",$row['ZLLSET']."&nbsp;".$row['ZLLDSE']));
	$azioniDetail->addField(new wi400Text('ZLLDFA',"Famiglia",$row['ZLLFAM']."&nbsp;".$row['ZLLDFA']));
	$azioniDetail->addField(new wi400Text('ZLLDSU',"Sottofamiglia",$row['ZLLSUF']."&nbsp;".$row['ZLLDSU']));
	$azioniDetail->addField(new wi400Text('ZLLMAR',"Marchio",$row['ZLLMAR']));
	
	$price = new wi400Text('ZLLVEN',"Prezzo di vendita");
	$price->setValue(doubleModelToView($row['ZLLVEN'],2)."&nbsp;&euro;");
	
	$azioniDetail->addField($price);
	
	$azioniDetail->addField(new wi400Text('ZLLOFD',"Offerta",$row['ZLLOFD']));
	


  	$sql="SELECT ID FROM IMMAGINI WHERE TIPO='ART' AND CHIAVE='".$row["ZLLCDA"]."'";
	$result = $db->query($sql);
	$imageObj = $db->fetch_array($result);
  	
  	$myImage = new wi400Image('detailImage');
	$myImage->setWidth(150);
	$myImage->setObjCode($row["ZLLCDA"]);
	$myImage->setObjType("ART");
	$azioniDetail->addImage($myImage);
	
		  		
	// operazioni finali per stampa barcode articolo
  	$ean13 = rtvArtEan($row["ZLLCDA"], date("Ymd"));
  	if ($ean13){
	  	createBarcode($ean13, "EAN13");
		$myImage = new wi400Image('ean13Image');
		$myImage->setUrl($ean13.".png");
		$myImage->setObjType("BARCODE");
		$azioniDetail->addImage($myImage);
  	}

	$azioniDetail->dispose();

?>