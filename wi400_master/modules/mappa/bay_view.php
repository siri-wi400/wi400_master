<?php 

	$spacer = new wi400Spacer();
	
	/*if($actionContext->getForm()=="DEFAULT") {
		if($db->num_rows($result)<1) {
?>			
			<script>
				alert("Dati non trovati.");
				closeLookUp();
			</script>
<?php			
		}
	}*/
	
	if(in_array($actionContext->getForm(),array("DEFAULT", "ART_DETAIL"))) {
		$azioniDetail = new wi400Detail($azione."_".$actionContext->getForm()."_DET",true);
		$azioniDetail->setColsNum(6);
		
		$azioniDetail->addParameter("POSITION", $position);
		$azioniDetail->addParameter("DEPOSITO", $deposito);
		$azioniDetail->setTitle("POSIZIONE");

		$labelDetail = new wi400Text("POSIZIONE");
		$labelDetail->setLabel("Posizione");
		if($actionContext->getForm()=="DEFAULT")
			$labelDetail->setValue(substr($position,0,9));
		else if($actionContext->getForm()=="ART_DETAIL")
			$labelDetail->setValue($position);
		$azioniDetail->addField($labelDetail);
		
		if($actionContext->getForm()=="ART_DETAIL") {

			$tableDetail = new wi400Table("DIMENSIONE");
			$tableDetail->setLabel("Dimensione");
			$tableDetail->addCol(new wi400Column("Altezza"));
			$tableDetail->addCol(new wi400Column("Larghezza"));
			$tableDetail->addCol(new wi400Column("Profondità"));
			
			$rowArray = array(
				$row['MADALT'],
				$row['MADLAR'],
				$row['MADPRF']
			);

			$tableDetail->addRow($rowArray);
		
			$azioniDetail->addField($tableDetail);
			// Lato
			$labelDetail = new wi400Text("LATO");
			$labelDetail->setLabel("Lato");
			$labelDetail->setValue($row['MADLAT']);
			$azioniDetail->addField($labelDetail);
			// Livello
			$labelDetail = new wi400Text("PIANO");
			$labelDetail->setLabel("Piano");
			$labelDetail->setValue($row['MADPPA']);
			$azioniDetail->addField($labelDetail);
			// Tipo Posto
			$labelDetail = new wi400Text("TIPO");
			$labelDetail->setLabel("Tipo<br>posto");
			$labelDetail->setValue($row['MADTPP']);
			$azioniDetail->addField($labelDetail);
			// Tipo Slot
			$slot = substr($row['MADCDA'],0,2);
			if ($slot!='') {
				$tab_slot = $persTable->decodifica('0192', $slot);
				$descr = $tab_slot['DESCRIZIONE'];			
				$labelDetail = new wi400Text("TIPO");
				$labelDetail->setLabel("Tipo<br>slot");
				$labelDetail->setValue($slot."<br>".$descr);
				$azioniDetail->addField($labelDetail);
			}							
		}
		
		$azioniDetail->dispose();
			
		$spacer->dispose();
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$bay = new wi400Bay();

		$piano = "";
		$zoccolo = 14;
		$first= True;
		$pianoTerra = False;
		$oldAlt = 0;
		$lato ="";
		echo "<table border='0' cellpadding='0' cellspacing='0' align='center'><tr><td align='center'>";
        // Verifico se la bay non ha il piano UNO .. in questo caso desumo che sia un posto di passaggio
		
		while($row = $db->fetch_array($result)) {
			if (($row['MADPPA']=='P1' || $row['MADPPA']=='01' || $row['MADPPA']=='')) {
                $pianoTerra=True;
			} 
			$first = False;
			if(empty($piano) || $piano!=$row['MADPPA']) {
				$piano = $row['MADPPA'];
				
				if(isset($rowPallet))
					$bay->addPalletsRow($rowPallet, $oldAlt);

				$rowPallet = array();
				$oldAlt = 0;
				$i = 0;
			}
			$lato = $row['MADLAT'];
			if ($oldAlt < $row['MADALT']) $oldAlt = $row['MADALT'];
			$keys = array($row['MADZOD'],$row['MADCOR'],$row['MADBAY'],$row['MADCDP']);
			
			// Controllo indirizzo 1
			$result_pal = $db->execute($stmt_pal_1, $keys);
   			$pallet = $db->fetch_array($stmt_pal_1);
   			
   			// Se non si trova in indirizzo 1 provo in indirizzo 2
   			if(empty($pallet)) {
   				$result_pal = $db->execute($stmt_pal_2, $keys);
   				$pallet = $db->fetch_array($stmt_pal_2);
   			}
   			
   			$pos = substr($position,0,9)."-".$row['MADCDP'];
   			
   			$onClick = 'openWindow(_APP_BASE + APP_SCRIPT + "?t=BAY&f=ART_DETAIL&POSITION='.$pos.'&DEPOSITO='.$deposito.'", "Dettaglio", 800, 700)';
   			$title = "POSIZIONE:".$pos;
   			
   			if(empty($pallet)) {
   				// Pallet vuoto
   				$type = "EMPTY";
   				$move = "";
   				
   				$title .= "\r\nPALLET VUOTO";
   			}
   			else {
   				$rtlart->set('ARTICOLO',$pallet['CAPCDA']);
			    $rtlart->call();
			    $art = array();
			    $art = $rtlart->get('ARTI');
			    
			    $title .= "\r\nARTICOLO:".$pallet['CAPCDA']."\r\n".$art['MDADSA'];
			    
   				if(!$pallet['CAPZO1'] && $pallet['CAPZO2']) {
   					// pallet in entrata
   					$type = "EMPTY";
			    	$move = "IN";
			    }
			    else {
				    if($pallet['CAPZO1'] && $pallet['CAPZO2']) {
				    	// pallet in uscita
				    	$move = "OUT";
				    }
				    else if($pallet['CAPZO1'] && !$pallet['CAPZO2']) {
				    	$move = "";
					}

					// Pallet integro o non pieno
				    if($pallet['CAPQTC']==$pallet['CAPQDC']) {
						$type = "FULL";
					}
					else {
						$type = "HALF";
					}
					
					// Controllo dimensioni pallet
					if(($pallet['CAPALP']+$zoccolo)>$row['MADALT'] || $pallet['CAPLAP']>$row['MADLAR'] || $pallet['CAPPRP']>$row['MADPRF']) {
						$type = "ERROR";
					}
			    }
   			}
   			// Altre caratteristiche dei pallet presenti
   			if ($move!='IN') {
	   		    if ($pallet['CAPSTA']=='8') $move ='L';
	   		    if ($pallet['CAPSTA']=='7') $move ='OUT_M';
	   		    if ($pallet['CAPSTA']=='5') $move ='OUT_A';
	   		    if ($pallet['CAPSTA']=='6') $move ='OUT_S';
   			}
   		    
   		    $rowPallet[$i] = new wi400Pallet($type,$move, $pallet['CAPALP'], $zoccolo);
			$rowPallet[$i]->setOnClick($onClick);
			$rowPallet[$i]->setTitle($title);
   			
   			// posizione selezionata
   			if (isset($coord[3])) {
	   			if($coord[3]==$row['MADCDP']) {
	   				$rowPallet[$i]->setSelected(true);
	   			}
   			}
			$i++;
		}

		if(isset($rowPallet))
			$bay->addPalletsRow($rowPallet, $oldAlt);

		if (!$pianoTerra) {	
		    $i=0;
			$newPallet[$i] = new wi400Pallet("MULO","");
			$newPallet[$i]->setTitle("Sottopasso");
			$bay->addPalletsRow($newPallet, 200);
		}	
		// Cerco Next e Previous
		// Verifico se c'è NEXT
		$sql = "select * from FMADSTOC where MADCDE='$deposito' and MADZOD='".$coord[0]."'
		AND MADLAT='".$lato. "' and MADCOR='".$coord[1]."' and MADBAY>'".$coord[2]."' and MADSTA='1'
		order by MADBAY ASC ";
		$result = $db->query($sql, 1);
		$row = $db->fetch_array($result);
		if ($row) {
			$coord2[0]= $row['MADZOD'];
			$coord2[1]= $row['MADCOR'];
			$coord2[2]= $row['MADBAY'];
			$bay->setNext(true);
			$nextBay = new wi400InputHidden("BAYNEXT");
			$nextBay->setValue(implode("-", $coord2));
			$nextBay->dispose();						
		}
		$sql = "select * from FMADSTOC where MADCDE='$deposito' and MADZOD='".$coord[0]."'
		AND MADLAT='".$lato. "' and MADCOR='".$coord[1]."' and MADBAY<'".$coord[2]."' and MADSTA='1'
		order by MADBAY DESC ";
		$result = $db->query($sql, 1);
		$row = $db->fetch_array($result);
		if ($row) {
			$coord2[0]= $row['MADZOD'];
			$coord2[1]= $row['MADCOR'];
			$coord2[2]= $row['MADBAY'];
			$bay->setPrev(true);
			$prevBay = new wi400InputHidden("BAYPREV");
			$prevBay->setValue(implode("-", $coord2));
			$prevBay->dispose();						
		}
		
		
		$bay->dispose();
		echo "</tr></table>";
		
		$lato = new wi400InputHidden("LATO");
		$lato->setValue($lato);
		$lato->dispose();
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	} // END DEFAULT
	else if($actionContext->getForm()=="ART_DETAIL" && !empty($articolo)) {
		$azioniDetail = new wi400Detail($azione."_".$actionContext->getForm()."_PALLET",true);
		$azioniDetail->setColsNum(3);
		
		$azioniDetail->setTitle("PALLET");
		
		$labelDetail = new wi400Text("PALLET");
		$labelDetail->setLabel("Pallet");
		$labelDetail->setValue($pallet['CAPCDP']);
		$azioniDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("LOTTO");
		$labelDetail->setLabel("Lotto");
		$labelDetail->setValue($lotto['KTLNLF']);
		$azioniDetail->addField($labelDetail);
		
		$data_scadenza = dateFormat($pallet['CAPGSC'],$pallet['CAPMSC'],$pallet['CAPASC']);
		
		if(isset($pallet['CAPSCF']) && !empty($pallet['CAPSCF']))
			$data_scadenza .= " - Forzata"; 
		
		$labelDetail = new wi400Text("DATA_SCADENZA");
		$labelDetail->setLabel("Data scadenza");
		$labelDetail->setValue($data_scadenza);
		$azioniDetail->addField($labelDetail);
		
		if(!empty($pallet['CAPZO0']))
			$ind1 = $pallet['CAPZO0']."-".$pallet['CAPCO0']."-".$pallet['CAPBA0']."-".$pallet['CAPCP0'];
		else
			$ind1 = "";
		
		$labelDetail = new wi400Text("IND1");
		$labelDetail->setLabel("Posizione di Partenza");
		$labelDetail->setValue($ind1);
		$azioniDetail->addField($labelDetail);
		
		if(!empty($pallet['CAPZO1']))
			$ind2 = $pallet['CAPZO1']."-".$pallet['CAPCO1']."-".$pallet['CAPBA1']."-".$pallet['CAPCP1'];
		else
			$ind2 = "";
		
		$labelDetail = new wi400Text("IND2");
		$labelDetail->setLabel("Posizione Attuale");
		$labelDetail->setValue($ind2);
		$azioniDetail->addField($labelDetail);
		
		if(!empty($pallet['CAPZO2']))
			$ind3 = $pallet['CAPZO2']."-".$pallet['CAPCO2']."-".$pallet['CAPBA2']."-".$pallet['CAPCP2'];
		else
			$ind3 = "";
		
		$labelDetail = new wi400Text("IND3");
		$labelDetail->setLabel("Posizione Futura");
		$labelDetail->setValue($ind3);
		$azioniDetail->addField($labelDetail);
		
		$modulo = ($aadp['MHLBLK']*$aadp['MHLTIR'])+$aadp['MHLCLM'];
		
		$labelDetail = new wi400Text("MODULO");
		$labelDetail->setLabel("Modulo");
		$labelDetail->setValue($modulo);
		$azioniDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("COLLI_ORIG");
		$labelDetail->setLabel("Colli originali");
		$labelDetail->setValue($pallet['CAPQTC']);
		$azioniDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("COLLI_PRES");
		$labelDetail->setLabel("Colli presenti");
		$labelDetail->setValue($pallet['CAPQRC']);
		$azioniDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("COLLI_DISP");
		$labelDetail->setLabel("Colli disponibili");
		$labelDetail->setValue($pallet['CAPQDC']);
		$azioniDetail->addField($labelDetail);
/*
		$labelDetail = new wi400Text("DIMENSIONE");
		$labelDetail->setLabel("Dimensione:");
		$labelDetail->setValue($pallet['CAPALP']." x ".$pallet['CAPLAP']." x ".$pallet['CAPPRP']." <b>(hxlxp)</b>");
		$azioniDetail->addField($labelDetail);
*/
		$tableDetail = new wi400Table("DIMENSIONE");
		$tableDetail->setLabel("Dimensione");
		$tableDetail->addCol(new wi400Column("Altezza"));
		$tableDetail->addCol(new wi400Column("Larghezza"));
		$tableDetail->addCol(new wi400Column("Profondità"));
		
		$rowArray = array(
			$pallet['CAPALP'],
			$pallet['CAPLAP'],
			$pallet['CAPPRP']
		);
		
		$tableDetail->addRow($rowArray);
	
		$azioniDetail->addField($tableDetail);
		
		$labelDetail = new wi400Text("PEZZATURA");
		$labelDetail->setLabel("Pezzatura");
		$labelDetail->setValue($pallet['CAPPEZ']);
		$azioniDetail->addField($labelDetail);
		
		$labelDetail = new wi400Text("COSTO");
		$labelDetail->setLabel("Costo");
		$labelDetail->setValue(wi400_format_DOUBLE_2($pallet['CAPCON']));
		$azioniDetail->addField($labelDetail);
		
		$azioniDetail->dispose();
			
		$spacer->dispose();
		
		$azioniDetail = new wi400Detail($azione."_ARTICOLO_DETAIL",true);
		$azioniDetail->setColsNum(2);
		
		$azioniDetail->setTitle($articolo." - ".$art['MDADSA']);
		$azioniDetail->isEditable(false);	
		
		// Tipo articolo
		$tipoArticolo = $persTable->decodifica('0060', $art["MDATPA"]);
		$descrizioneTipo = $tipoArticolo['DESCRIZIONE'];
		
		$field = new wi400Text("tipoarticolo");
		$field->setLabel("Tipo articolo");
		$field->setValue($art['MDATPA']." - ".$descrizioneTipo);
		$azioniDetail->addField($field);
		
		// Confezione
		$tabelle = new wi400Tabelle("0005", $art['MDACON'], $db);
		
		$field = new wi400Text("confezione");
		$field->setLabel("Confezione");
		$field->setValue($art['MDACON']." - ".$tabelle->getDescrizione());
		$azioniDetail->addField($field);
		
		// Tipo anagrafica
		$tabelle = new wi400Tabelle("0014", $art['MDATRA'], $db);
		$anagrafica = $tabelle->getDescrizione();
		
		$field = new wi400Text("tipoanagrafica");
		$field->setLabel("Tipo Anagrafica");
		$field->setValue($art['MDATRA']." - ".$anagrafica);
		$azioniDetail->addField($field);
		
		// Tipo Grammatura
		$tabelle = new wi400Tabelle("0006", $art['MDATPG'], $db);
		
		$field = new wi400Text("tipo_gram");
		$field->setLabel("Tipo Grammatura:");
		$field->setValue($art['MDATPG']. " - ".$tabelle->getDescrizione());
		$azioniDetail->addField($field);
		
		// Tipo Trattamento
		$tabelle = new wi400Tabelle("0011", $art['MDAFMA'], $db);
		$trattamento = $tabelle->getDescrizione();
		
		$field = new wi400Text("trat");
		$field->setLabel("Tipo Trattamento");
		$field->setValue($art['MDAFMA']." - ".$trattamento);
		$azioniDetail->addField($field);
		
		// Grammatura/Idratati
		$field = new wi400Text("grammatura");
		$field->setLabel("Grammatura");
		$field->setValue($art['MDAGRA']);
		$azioniDetail->addField($field);
		
		// Settore
		$field = new wi400Text("settore");
		$field->setLabel("Settore");
		$field->setValue($famiglia['T127RM']." - ".$settore['T127DE']);
		$azioniDetail->addField($field);
			
		// Famiglia
		$field = new wi400Text("famiglia");
		$field->setLabel("Famiglia");
		$field->setValue($sottofamiglia['T127RM']." - ".$famiglia['T127DE']);
		$azioniDetail->addField($field);
		
		// Sottofamiglia
		$field = new wi400Text("sottofamiglia");
		$field->setLabel("Sottofamiglia");
		$field->setValue($suf." - ".$sottofamiglia['T127DE']);
		$azioniDetail->addField($field);
		
		// Multiplo
		$field = new wi400Text("multiplo");
		$field->setLabel("Multiplo");
		$field->setValue($art['MDAMUL']);
		$azioniDetail->addField($field);
		
		// Scontrino
		$field = new wi400Text("scontrino");
		$field->setLabel("Scontrino");
		$field->setValue($anaagg['MDWSCN']);
		$azioniDetail->addField($field);
	
		// Pezzatura (CP)
		$field = new wi400Text("pezzatura");
		$field->setLabel("Pezzatura");
		$field->setValue($art['MDAPEZ']);
		$azioniDetail->addField($field);
		
		// Marchio
		$labelDetail = new wi400Text("MARCHIO");
		$labelDetail->setLabel("Marchio");
		$labelDetail->setValue($art['MDAMAR']);
		$azioniDetail->addField($labelDetail);
		
		// operazioni finali per stampa barcode articolo
	  	$code1 = rtvArtEan($articolo, dateViewToModel($data_spedizione));
	  	$code = substr($code1, 0 , 12);

		$barcode_8 = $articolo;
		// Crea immagine barcode
		createBarcode($barcode_8);
		 	
		// Visualizza immagine dell'articolo
		$myImage = new wi400Image('detailImage');
		$myImage->setWidth(150);
		$myImage->setObjCode($articolo);
		$myImage->setObjType("ART");
		$azioniDetail->addImage($myImage);
		
		// Visualizza barcode dell'articolo 
		$myImage = new wi400Image('barcodeImage');
		$myImage->setUrl($barcode_8.".png");
		$myImage->setObjType("BARCODE");
		$azioniDetail->addImage($myImage);
		$azioniDetail->dispose();
		
		$hiddenField = new wi400InputHidden("ARTICOLO");
		$hiddenField->setValue($articolo);
		$hiddenField->dispose();
		
		$myButton = new wi400InputButton("EXPORT_BUTTON");
		$myButton->setAction($actionContext->getAction());
		$myButton->setForm("EXPORT");
		$myButton->setLabel("Esporta");
		$buttonsBar[] = $myButton;
		
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
	} // END ART_DETAIL
	else if($actionContext->getForm()=="EXPORT") {
		$myButton = new wi400InputButton("CANCEL_BUTTON");
		$myButton->setScript('history.back()');
		$myButton->setLabel("Indietro");
		$buttonsBar[] = $myButton;
	
		$myButton = new wi400InputButton("CLOSE_BUTTON");
		$myButton->setScript('closeLookUp()');
		$myButton->setLabel("Chiudi");
		$buttonsBar[] = $myButton;
		
		downloadDetail($TypeImage, $filename, $temp, "Esportazione del dettaglio dell'articolo");
	}
	
?>
<!--
<script>
	function openItemDetail(position) {
		openWindow(_APP_BASE + APP_SCRIPT + "?t=BAY&f=ART_DETAIL&POSITION=" + position);
	}
</script>
-->