<?php

 	$azioniDetail = new wi400Detail("DETTAGLIO_EMAIL");

 	$azioniDetail->setTitle("Dettaglio email ID ".$row['MAIREC']);
	$azioniDetail->isEditable(false);
	if (isset($row)){
		$azioniDetail->setSource($row);
	}

	// Protezione scaricamento non autorizzato
	$contest = "ATTACHMENT";
	$_SESSION[$contest."_".$row['MAIREC']] = $row['MAIATC'];
	
	$azioniDetail->addField(new wi400Text('MAIFRM',"Mittente",$row['MAIFRM']));
	$azioniDetail->addField(new wi400Text('MAITOR',"Destinatario",$row['MAITOR']));
	$azioniDetail->addField(new wi400Text('MAISBJ',"Oggetto",$row['MAISBJ']));
	$azioniDetail->addField(new wi400Text('MAIINS',"Data Inserimento",$row['MAIINS'], "TIMESTAMP"));
	$azioniDetail->addField(new wi400Text('MAIELA',"Data Elaborazione",$row['MAIELA'], "TIMESTAMP"));
	$azioniDetail->addField(new wi400Text('MAIERR',"Codice elaborazione",$row['MAIERR']));
	$azioniDetail->addField(new wi400Text('MAIDER',"Descrizione ela",$row['MAIDER']));
	$azioniDetail->addField(new wi400Text('MAIATC',"Attachment",$row['MAIATC'],"",$appBase."index.php?t=FILEIDDWN&DECORATION=clean&CONTEST=".$contest."&FILE_ID=".$row['MAIREC']));
	$azioniDetail->addField(new wi400Text('MAIUSR',"Utente Creazione",$row['MAIUSR']));
	$azioniDetail->addField(new wi400Text('MAIJOB',"Job Creazione",$row['MAIJOB']));
	$azioniDetail->addField(new wi400Text('MAIARG',"Argomento/Contesto",$row['MAIARG']));
	$azioniDetail->addField(new wi400Text('MAIFRM',"Spedizioni effettuate",$row['MAIRIS']));
	

	
    $sql="SELECT ID FROM IMMAGINI WHERE TIPO='VARIE' AND CHIAVE='EMAIL'";
	$result = $db->query($sql);
	$imageObj = $db->fetch_array($result);
  	
	$myImage = new wi400Image('detailImage');
	if (isset($imageObj['ID'])){
		$myImage->setUrl($imageObj['ID'].".jpg");
	}else{
		$myImage->setUrl("email.png");
	}
	
	$azioniDetail->addImage($myImage);
	$azioniDetail->dispose();
?>