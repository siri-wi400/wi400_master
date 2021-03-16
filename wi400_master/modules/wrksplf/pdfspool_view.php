<?php

if(count($wi400List->getSelectionArray())==1) {
	// Creazione file per ricevimento dati dello spool
	//$do =executeCommand("CRTPF",array("FILE"=>"QTEMP/TEMPFILE", "RCDLEN"=>200, "SIZE"=>"*NOMAX"), array(), $connzend);
	// Copia spool su file
	//$do =executeCommand("CPYSPLF", array("FILE"=>$splfname, "TOFILE"=>"QTEMP/TEMPFILE",
	//	"JOB"=>$jobnumber."/".$jobuser."/".$jobname,"SPLNBR"=>$splfnbr, "CTLCHAR"=>"*PRTCTL"), array(),$connzend);
	// Copio su IFS
	// ===> CPYTOSTMF FROMMBR('QSYS.LIB/QTEMP.LIB/TEMPFILE.FILE/TEMPFILE.MBR') TOSTMF(
	//'/siritemp/aaaa.dat') DBFCCSID(280) STMFCCSID(*PCASCII)
	// Determino la modalità di generazione PDF
	$max_row = 500;
	if (isset($settings['pdf_rpg'])) {
		$max_row = $settings['pdf_rpg'];
	}
	if ($splpagenbr < $max_row) {
		//echo "PHP PDF!";
		$theFile= wi400File::getUserFile("tmp", "temp".$_SESSION['user'].date("Ymd").".dat");
		$tofile ="'".$theFile."'";
		$tofile2 =$theFile;	
		
		require_once $routine_path."/os400/wi400Os400Spool.cls.php";
		$jobqual = str_pad($jobname, 10).str_pad($jobuser, 10).str_pad($jobnumber, 6);
		$dati = wi400Os400Spool::getData($jobqual, $splfname, $splfnbr);
		$str = implode("\r\n", $dati);
		file_put_contents($tofile2,$str);	
	} else {
		//echo "RPG PDF!";
		// CONVERSIONE VELOCE CON CVTSPOOLC
		//  ===> CVTSPOOL SPOOLNAME(SPPOL) JOB(NBR/UTENTE/LAVORO) NUMSPOOL(NRS) FILEOUT(PHPTEMP/SOUTPUT)                                                                   
		$do =executeCommand("CVTSPOOL", array("SPOOLNAME"=>$splfname, "JOB"=>"$jobnumber/$jobuser/$jobname", "NUMSPOOL"=>$splfnbr, "FILEOUT"=>"PHPTEMP/SOUTPUT"), array(),$connzend);
		$theFile= wi400File::getUserFile("tmp", "temp".$_SESSION['user'].date("Ymd_his").".pdf");
		if ($settings['OS400']=="V5R4M0") {
			$do =executeCommand("CPYTOSTMF FROMMBR('/qsys.lib/phptemp.lib/soutput.file/soutput.mbr') TOSTMF('$theFile') STMFOPT(*REPLACE) STMFCODPAG(*PCASCII)", array(), array(), $connzend);
		} else {
			$do =executeCommand("CPYTOSTMF FROMMBR('/qsys.lib/phptemp.lib/soutput.file/soutput.mbr') TOSTMF('$theFile') STMFOPT(*REPLACE) STMFCCSID(*PCASCII)", array(), array(), $connzend);
		}	
		
		downloadDetail("pdf", basename($theFile), "tmp", _t('ESPORTAZIONE_COMPLETATA'), "", "");
	}
	// toolkit external
	/*if($settings['i5_toolkit'] == 'external') {
		$sql="select * from TEMPFILE";
		$result=$db->query($sql);
		$testo_spool='';
		while ($row=$db->fetch_array($result)) {
			if (!preg_match('/\d\d\d/',substr($row['TEMPFILE'],0,3))) {
				$row['TEMPFILE']='   '.$row['TEMPFILE'];
			}
			$testo_spool.=$row['TEMPFILE']."\r\n";
		}
		file_put_contents($tofile2,$testo_spool);
	} else {
	
		//$handle = fopen($tofile2, "w");
		//echo "File:".$tofile2;
		$str = implode("\r\n", $dati);
		file_put_contents($tofile2,$str);
		//fputs($handle, $str);
		//fclose($handle);
		/*$do = executeCommand("CPYTOSTMF", array("FROMMBR"=>"'/QSYS.LIB/QTEMP.LIB/TEMPFILE.FILE/TEMPFILE.MBR'",
			"STMFOPT"=>"*REPLACE", "TOSTMF"=>$tofile, "STMFCODPAG"=>"*PCASCII"), array(), $connzend);
	
		if (isset($settings['OS400']) && $settings['OS400']=='V5R3M0') {
	      $comando = "CHGAUT OBJ(".$tofile.") USER(*PUBLIC) DTAAUT(*RWX) OBJAUT(*ALL)";
		} else {
			$comando = "CHGAUT OBJ(".$tofile.") USER(*PUBLIC) DTAAUT(*RWX) OBJAUT(*ALL) SUBTREE(*ALL)";  
		}    		                                                
		$do = executeCommand($comando);                                                             
	}*/
	

	// Cerco il modulo associato                                                
	$codice_modulo = $spluserdata;			// @todo è giusto così?
	if($codice_modulo=="") 
		$codice_modulo = $splmodulo; 
//	echo "CODICE MODULO: $codice_modulo<br>";
		
	// Se non hanno valorizzato il codice modulo lo imposto con il codice argomento.
	$sql="select * from SIR_MODULI WHERE MODNAM='$codice_modulo'";
	$result = $db->singleQuery($sql);

	$modulo= $db->fetch_array($result);
	if(!$modulo) {
		$codice_modulo = "*DEFAULT";
		$sql="select * from SIR_MODULI WHERE MODNAM='$codice_modulo'";
		$result = $db->singleQuery($sql);
		$modulo=$db->fetch_array($result);	
	}
	
//	echo "MODULO ARRAY:<pre>"; print_r($modulo); echo "</pre><br>";
	
	// Istanzio la classe e trovo l'eventuale personalizzazione
	$classe = $routine_path."/classi/wi400SpoolCvt.cls.php";
	$nome_classe='wi400SpoolConvert';
	if(trim($modulo['MODCLS']!="" && $modulo['MODCLS']!="*DEFAULT")) {
		$classe_particolare = "$base_path/package/".$settings['package'].'/persconv/wi400SpoolCvt_'.trim($modulo['MODCLS']).".cls.php";
		if (file_exists($classe_particolare)) {
			$classe = $classe_particolare;
    		$nome_classe .='_'.$modulo['MODCLS'];
		}
	}

//	echo "CLASSE: $classe<br>";
		
	require_once $classe;
	if ($splpagenbr < $max_row) {
	if(!isset($modulo['MODABP']) || $modulo['MODABP']!="N") {
		// Istanzio la classe
		$convert = new $nome_classe("INTERACT", $connzend, $db, $tofile2, null);
		
		if($convert->getFile()) {
			// Carico i parametri dai moduli SIRI
			$convert->setDati('NOME', $codice_modulo);
			$convert->setDati('AUTORE', 'WI400 By SIRI-Informatica!');
			$convert->setDati('CREATORE', $settings['cliente_installazione']);
			$convert->setDati('ARGOMENTO', $spluserdata);
			$convert->setDatiConv($dati_conv);
			$dati = $convert->setDatiBySiriModuli($modulo);
			ini_set('max_execution_time', 1200);
			$convert->createPdf();
			//getMicroTimeStep("Start");
			$do = $convert->convert();
			//getMicroTimeStep("Finish");
			$filename = $convert->getPdfName();
			$fileType = $convert->getFileType();
			$email = $convert->getEmail();
	
			//downloadDetail($fileType, $filename, "tmp", _t('ESPORTAZIONE_COMPLETATA'), "", $email);
			$actionContext->gotoAction("FILEVIEW&DECORATION=clean&APPLICATION=pdf&CONTEST=tmp&FILE_NAME=".$filename, "", "", true);
		} 
		else {
			echo "Problemi nel reperimento del file contattare EDP";
		}
	
		unlink($tofile2);
	}
	else {
?>
		<script>
			alert("File non abilitato alla conversione PDF.");
			closeLookUp();
		</script>
<?		
	}
}
}
else {
?>
		<script>
			alert("Selezionare solo uno spool da convertire alla volta.");
			closeLookUp();
		</script>
<?	
}

?>