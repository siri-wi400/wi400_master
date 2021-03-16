<?php 

	global $moduli_path,$appBase;
	
//	echo "AZIONE: ".$actionContext->getAction()." - FORM: ".$actionContext->getForm()."<br><br>";
//	echo "URL: ".$_SERVER['REQUEST_URI']."<br><br>";
	
	$azione = $actionContext->getAction();

	// Deposito
	//$deposito = wi400Detail::getDetailValue("SEARCH_ROLL","deposito");
	// @todo verificare perchè non funziona
	if (isset($_REQUEST['DEPOSITO'])) {
		$deposito = $_REQUEST['DEPOSITO'];
	//	$textDeposito =  new wi400InputText("deposito");
	//	$textDeposito->setValue = $deposito;
	//	wi400Detail::setDetailValue("SEARCH_ROLL",$textDeposito);
	//} else {
	//	$deposito = wi400Detail::getDetailValue("SEARCH_ROLL","deposito");	
	}
	
	$position = $_REQUEST['POSITION'];
	$coord = explode("-",$position);
		
	if (isset($_GET["NAVIGATION"])){
		if ($_GET["NAVIGATION"] == "NEXT"){
			$position = $_REQUEST['BAYNEXT'];
		}else if ($_GET["NAVIGATION"] == "PREV"){
			$position = $_REQUEST['BAYPREV'];
		}
	}
	$coord = explode("-",$position);	
	//$data_spedizione = wi400Detail::getDetailValue("SEARCH_ROLL","data_spedizione");
	$data_spedizione = date("Ymd");
	// Elementi comuni
	if(in_array($actionContext->getForm(),array("DEFAULT", "ART_DETAIL"))) {
		$rtlart = new wi400Routine('RTLART', $connzend);
    	$rtlart->load_description();
    	$rtlart->prepare();
	    $rtlart->set('NUMRIC',1);
	    $rtlart->set('DATINV', date("Ymd"));

	    $sql_pal_1 = "select * from FCAPALET where CAPCDE='$deposito' and CAPSTA in ('1', '2')
	    		and CAPZO1=? and CAPCO1=? and CAPBA1=? and CAPCP1=?";
	    		
//	    echo "SQL PAL 1: $sql_pal_1<br><br>";
	    
	    $stmt_pal_1 = $db->singlePrepare($sql_pal_1);
	    		
	    $sql_pal_2 = "select * from FCAPALET where CAPCDE='$deposito' and CAPSTA in ('1', '2')
	    		and CAPZO2=? and CAPCO2=? and CAPBA2=? and CAPCP2=?
	    	";
	    		
//	    echo "SQL PAL 2: $sql_pal_2<br><br>";
	    
	    $stmt_pal_2 = $db->singlePrepare($sql_pal_2);
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		$sql = "select * from FMADSTOC where MADCDE='$deposito' and MADZOD='".$coord[0]."'
			and MADCOR='".$coord[1]."' and MADBAY='".$coord[2]."' and MADSTA='1'
			order by MADTPP DESC, SUBSTR(MADPPA, 2, 1) DESC,  MADCDP";
		
//		echo "SQL: $sql<br><br>";
		
		$result = $db->query($sql);
	} // END DEFAULT
	else if($actionContext->getForm()=="ART_DETAIL") {
		// Azione corrente
		$actionContext->setLabel("Dettaglio articolo");
		
		$sql = "select * from FMADSTOC where MADCDE='$deposito' and MADSTA='1' and MADZOD='".$coord[0]."'
			and MADCOR='".$coord[1]."' and MADBAY='".$coord[2]."' and MADCDP='".$coord[3]."'";
		
//		echo "SQL: $sql<br><br>";
		
		$result = $db->query($sql);
		$row = $db->fetch_array($result);
		
		$keys = array($coord[0],$coord[1],$coord[2],$coord[3]);
		
		// Controllo indirizzo 1
		$result_pal = $db->execute($stmt_pal_1, $keys);
		$pallet = $db->fetch_array($stmt_pal_1);
		
		// Se non si trova in indirizzo 1 provo in indirizzo 2
		if(empty($pallet)) {
			$result_pal = $db->execute($stmt_pal_2, $keys);
			$pallet = $db->fetch_array($stmt_pal_2);
		}
  		
  		$articolo = $pallet['CAPCDA'];
		
//		echo "ARTICOLO: $articolo<br>";

  		// Routine RTLAA1
	    $rtlaa1 = new wi400Routine('RTLAA1', $connzend);
	    $rtlaa1->load_description();
	    
	    // Data ultima consegna
	    $rtlaa1->prepare();
	    $rtlaa1->set('CODICE',$deposito);
	    $rtlaa1->set('CODART',$articolo);
	    $rtlaa1->set('DATARF',date("Ymd"));
	    $rtlaa1->call();
	    
	    $aadp = $rtlaa1->get('AADP');

  		if(!empty($articolo)) {
			$rtlart->set('ARTICOLO',$articolo);
		    $rtlart->call();
		    $art = array();
		    $art = $rtlart->get('ARTI');
		    $anaagg = $rtlart->get('ANAAGG');
		    
		    // Livelli merceologici
			$tab0127 = new wi400Tabelle("0127", Null, $db);
			$tab0127->prepareStmt();
			// Sottofamiglia
			$suf = $art['MDASET'].$art['MDAFAM'].$art['MDASUF'];
			$tab0127->decodifica($suf);
			$sottofamiglia = array();
			$sottofamiglia = $tab0127->getRecord();
			// Famiglia
			$tab0127->decodifica($sottofamiglia['T127RM']);
			$famiglia = array();
			$famiglia = $tab0127->getRecord();
			// Settore
			$tab0127->decodifica($famiglia['T127RM']);
			$settore = array();
			$settore = $tab0127->getRecord();
			
			$sql_lotto = "select * from FKTLOTTI where KTLDEP='$deposito' and KTLPAL='".str_pad($pallet['CAPCDP'], 10, "0", STR_PAD_LEFT)."'";
		
//			echo "SQL LOTTO: $sql_lotto<br>";
		
			$result_lotto = $db->query($sql_lotto);
			$lotto = $db->fetch_array($result_lotto);
  		}
	} // END ART_DETAIL
	else if($actionContext->getForm()=="EXPORT") {
		require_once $routine_path."/classi/wi400ExportList.cls.php";
		
		$articolo = $_REQUEST['ARTICOLO'];
		
		$export = new wi400ExportList();
		
		$filename =   "Bay_Articolo_".$articolo."_".date("YmdHis").".pdf";
		$temp = "export";
		$TypeImage = "pdf.png";
		
		$export->setDatiExport($filename, $temp, $TypeImage);
		
//		echo "DETAILS: "; print_r($_REQUEST['IDDETAIL']); echo "<br>";
		
		$start = 10;
		$colonna = 22;
		$identificativo = "Stampato il ".date("d/m/Y"). " alle ore ".date("H:i:s") ." da utente ". $_SESSION['user'];
		
		$export->setDatiPDF($start, $colonna, $identificativo);
		
		// Impostazione grandezza carattere
		$char = 10;		// dimensione dei caratteri
		$pagina = 180;
		
		$style = array(
			'position' => 'S',
			'border' => false,
			'padding' => 4,
			'fgcolor' => array(0,0,0),
			'bgcolor' => false, //array(255,255,255),
			'text' => false,
			'font' => 'helvetica',
			'fontsize' => 8,
			'stretchtext' => 4
		);
		
		$pdf = $export->createPDF($char, $pagina, 'P');
		
		$subject = "Stampa dettaglio articolo bay";

		$export->dettagliPDF($pdf, $subject);
		
		$pdf->AddPage();
		$oldPage = $pdf->PageNo();
		
		if(isset($_REQUEST['IDDETAIL'])){
			$idDetails = $_REQUEST['IDDETAIL'];
			
			$y = $start;
			
			foreach($idDetails as $idDetail) {
				$detailFields = wi400Detail::getDetailFields($idDetail);

				$pdf->SetFillColorArray(array(176,196,222));
				
				$pdf->SetFont('Courier', '' , $char);
				
				$pdf->setXY($colonna, $y);
				$pdf->SetFont('Courier', 'B' , $char);
				
				$titolo = wi400Detail::getDetailTitle($idDetail);
				
				$titolo = html_entity_decode($titolo);
				if(mb_check_encoding($titolo,'UTF-8')===false)
					$value = utf8_encode($titolo);
				
//				echo "TITOLO: $titolo<br>"; die();
				$pdf->Cell(170, 6, $titolo, 1, 0, 'L', 1);
				
				$y += 6;
				
				$bg = 0;

				
				$pdf->SetFillColorArray(array(230,230,250));
		
				foreach($detailFields as $idField => $fieldObj){
//					echo "IDFIELD: "; print_r($idField); echo "<br>";
//					echo "FILEDOBJ: "; print_r($fieldObj); echo "<br>";
					
					if(is_object($fieldObj)) {
//						echo "<b>OBJECT</b><br>";
						if(get_class($fieldObj)=="wi400Text") {
//							echo "<b>TEXT</b><br>";
							$label = $fieldObj->getLabel(); // Etichetta
							$value = $fieldObj->getValue(); // Valore
							
//							echo $label." - ".$value."<br><br>";
						}
						else if(get_class($fieldObj)=="wi400Table") {
//							echo "<b>TABLE</b><br>";
							$label = $fieldObj->getLabel(); // Etichetta
							$rows = $fieldObj->getRows();
							
//							echo $label." - "; print_r($rows); echo "<br>";
							
							$cols = $fieldObj->getCols();

//							echo "COLS: "; print_r($cols); echo "<br>";
							$values = array();
							foreach($cols as $key => $val) {
								$col_title = $val->getKey();
//								echo "TITLE: $col_title<br>";
								$values[] = $col_title.": ".$rows[0][$key];
							}
							
//							echo "VALUES: "; print_r($values); echo "<br><br>";
							
							$value = implode(" - ", $values);
						}
					}
					
					
					$label = html_entity_decode($label);
					if(mb_check_encoding($label,'UTF-8')===false)
						$label = utf8_encode($label);
					$label = str_replace(array('<br>','</br>'), "\n", $label);
						
					$value = html_entity_decode($value);
					if(mb_check_encoding($value,'UTF-8')===false)
						$value = utf8_encode($value);
					
					$pdf->setXY($colonna, $y);
					$pdf->SetFont('Courier', 'B' , $char);
//					$pdf->Cell(50, 7, $label, 1, 0, 'L', $bg);
					$pdf->Cell(50, 6, $label, 1, 0, 'L', 1);
					$pdf->SetFont('Courier', '' , $char);
//					$pdf->Cell(100, 7, $value, 1, 0, 'L', $bg);
					$pdf->Cell(120, 6, $value, 1, 0, 'L', 0);
										
					$y += 6;
					
					if($bg==0)
						$bg = 1;
					else if($bg==1)
						$bg = 0;
				}
				
				$y += 6;
			}
		}
		
		// operazioni finali per stampa barcode articolo
/*	  	$code1 = rtvArtEan($articolo, dateViewToModel($data_spedizione));
	  	$code = substr($code1, 0 , 12);
	  	$html = "";
	  	$bars = barcode_encode($code,'ANY');
	  	if ($bars != "ERROR"){
	  		$html = barcode_outhtml($bars['text'],$bars['bars'], 2, 'jpg');
	  	}*/
		
		$barcode_8 = $articolo;
		
//		$pdf->write1DBarcode($barcode_8, 'EAN8', $colonna, $y, 22, 14, 0.4, $style, 'T');

		// Crea immagine barcode
		createBarcode($barcode_8);
		
		$myImage = new wi400Image('barcodeImage');
		$myImage->setUrl($barcode_8.".png");
		$myImage->setObjType("BARCODE");
		$myImage->getHtml();

		$url = $myImage->getUrl();
		$file = $doc_root.$url;
		
		$pdf->Image($file,$colonna, $y, 30, 15,$type);
		
		// Visualizza immagine dell'articolo
		$url = "";
//		$articolo = '0000270';
		
		// Immagine	
		$myImage = new wi400Image('printImage');
		$myImage->setObjCode($articolo);
		$myImage->setObjType("ART");
		$myImage->getHtml();

		$url = $myImage->getUrl();
		$file = $doc_root.$url;
		
		$img_parts = pathinfo($file);
		$type = "";
		if(isset($img_parts['extension']))
			$type = strtolower($img_parts['extension']);
		
		if(file_exists($file) && !empty($type)) {	
			if($printFormat=="DOUBLE" && ($oldPage%2)!=0) {
				$pdf->AddPage();
				$oldPage = $pdf->PageNo();
			}
			
			if($type=="jpg")
				$mtd = "_parsejpeg";
			else
				$mtd = "_parse".$type;
			$info = $pdf->$mtd($file);

			$w_dist = 0;
			$h_dist = 0;
			
//			$area_w = 278;
//			$area_h = 150;
			$area_w = 100;
			$area_h = 45;
			
			if($info['w']>$area_w || $info['h']>$area_h) {
				if($info['w']>$area_w) {
					$width = $area_w;
					$height=$area_w*$info['h']/$info['w'];
					if($height>$area_h) {
						$height = $area_h;
						$width=$area_h*$info['w']/$info['h'];
					}
				}
				else if($info['h']>$area_h) {
					$height = $area_h;
					$width=$area_h*$info['w']/$info['h'];
				}				
			}
			else {
				$width = 0;
				$height = 0;
			}
			$w_dist = ($area_w-$width)/2;
			$h_dist = ($area_h-$height)/2;

//			$pdf->Image($file, $start+$w_dist, $colonna+$h_dist, $width, $height,$type);	

			$pdf->Image($file, $colonna+50, $y, $width, $height,$type);
		}
		// Produco il pdf
		$pdf->Output($export->get_filepath(), 'F');		
	}

?>