<?php

	$azione = $actionContext->getAction();
	
	if($actionContext->getForm()=="RESET_PRINTER") {
		unset($_SESSION['DEFAULT_PRINTER']);
		unset($_SESSION['DEFAULT_PRINTER_COPY']);
		wi400Detail::cleanSession($azione.'_SCELTA_PRINTER');
	
		$actionContext->gotoAction($azione, "PRINTER_SEL", "", true);
	}
	
	if($actionContext->getForm()=="DEFAULT") {
		wi400Detail::cleanSession($azione.'_SCELTA_PRINTER');		
		$actionContext->gotoAction($azione, "PRINTER_SEL", "", true);
	}
	else if($actionContext->getForm()=="PRINTER_SEL") {
//	if(in_array($actionContext->getForm(), array("PRINTER_SEL", "RESET_PRINTER"))) {
/*		
		$outq = "";
		if(isset($_SESSION['DEFAULT_PRINTER']) && !empty($_SESSION['DEFAULT_PRINTER']))
			$outq = $_SESSION['DEFAULT_PRINTER'];
*/		
//		$outq = "";
		$default = false;
		
		$coda_def = "";
		if(isset($_REQUEST['DEF_PRINT']) && !empty($_REQUEST['DEF_PRINT']))
			$coda_def = $_REQUEST['DEF_PRINT'];
		$copy_def =1;
//		echo "CODA DEF: $coda_def<br>";
			
		$coda_array = get_coda_stampa($coda_def);
//		echo "CODA ARRAY:<pre>"; print_r($coda_array); echo "</pre>";
//		echo "CODA DEFAULT: ".$coda_array['CODA']."<br>";
		
//		if(wi400Detail::getDetailValue($azione.'_SCELTA_PRINTER', 'OUTQ')!="") {
			$outq = wi400Detail::getDetailValue($azione.'_SCELTA_PRINTER', 'OUTQ');
			$copie = wi400Detail::getDetailValue($azione.'_SCELTA_PRINTER', 'COPIE');
			
			$default = check_coda_stampa_def($outq, $coda_array["CODA_DEF"], $coda_array["CODA_USER"]);
//		}
//		echo "OUTQ:"; var_dump($outq); echo "<br>";
		if(!isset($copie)) {
			if (isset($coda_array['COPIE'])) {
				$copie = $coda_array['COPIE'];
			} else {
				$copie = $copy_def;
			}
		}
		if(!isset($outq)) {
			$outq = $coda_array['CODA'];
			$default = $coda_array['DEFAULT'];
		}		
		
//		echo "OUTQ: $outq - DEFAULT: $default<br>";
/*
		if($actionContext->getForm()=="RESET_PRINTER") {
			$messageContext->addMessage("SUCCESS", "Impostata la stampante ".$outq);
			
			$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
		}
*/		
	}
	else if($actionContext->getForm()=="SAVE_PRINTER") {
		$outq = "";
		if(wi400Detail::getDetailValue($azione.'_SCELTA_PRINTER', 'OUTQ')!="")
		$outq = wi400Detail::getDetailValue($azione.'_SCELTA_PRINTER', 'OUTQ');
		$copie = wi400Detail::getDetailValue($azione.'_SCELTA_PRINTER', 'COPIE');
//		echo "OUTQ:$outq<br>";

		if($outq=="") {
			unset($_SESSION['DEFAULT_PRINTER']);
			$_SESSION['DEFAULT_PRINTER_COPY']=1;
				
			$messageContext->addMessage("ALERT", "Non è stata impostata una stampante");
		}
		else {
			$_SESSION['DEFAULT_PRINTER'] = $outq;
			$_SESSION['DEFAULT_PRINTER_COPY'] = $copie;
		
			$messageContext->addMessage("SUCCESS", "Impostata la stampante ".$outq);
		}
		
		if(isset($_REQUEST['WI400_IS_WINDOW']) && $_REQUEST['WI400_IS_WINDOW']) {
			$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
		}else {
			$actionContext->gotoAction($azione, "DEFAULT", "", true);
		}
	}
	else if($actionContext->getForm()=="DEFAULT_PRINTER") {
		$outq = "";
		$copie=1;
		if(wi400Detail::getDetailValue($azione.'_SCELTA_PRINTER', 'OUTQ')!="")
		$outq = wi400Detail::getDetailValue($azione.'_SCELTA_PRINTER', 'OUTQ');
		$copie = wi400Detail::getDetailValue($azione.'_SCELTA_PRINTER', 'COPIE');
//		echo "OUTQ:$outq<br>";
		// Salvataggio stampante di default dell'utente
		$file = get_file_default_printer_user();

		if($outq=="") {
			unlink($file);

			unset($_SESSION['DEFAULT_PRINTER']);
			unset($_SESSION['DEFAULT_PRINTER_COPY']);
			
			$messageContext->addMessage("SUCCESS", "Sono state resettate le impostazioni di default");
		}
		else {
			$handle = fopen($file, "w");
			fwrite($handle, serialize($outq."|".$copie));
			fclose($handle);
			
			$_SESSION['DEFAULT_PRINTER'] = $outq;
			$_SESSION['DEFAULT_PRINTER_COPY'] = $copie;

			$messageContext->addMessage("SUCCESS", "Impostata la stampante di default ".$outq);
		}
		
		if(isset($_REQUEST['WI400_IS_WINDOW']) && $_REQUEST['WI400_IS_WINDOW']) {
			$actionContext->gotoAction("CLOSE", "CLOSE_WINDOW_MSG", "", true);
		}else {
			$actionContext->gotoAction($azione, "DEFAULT", "", true);
		}
	}