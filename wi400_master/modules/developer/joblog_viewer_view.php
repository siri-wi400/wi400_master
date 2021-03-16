<?php
require_once $moduli_path."/analisi/job_log_commons.php";
if($actionContext->getForm()=="DEFAULT") {
	//Richiesta Parametri Codice Sessione
	$searchAction = new wi400Detail("JOBLOG_VIEWER_SRC", False);
	$searchAction->setTitle('Parametri');
	$searchAction->isEditable(true);
	$myField = new wi400InputText('JOB');
	$myField->setLabel("File Oggetto");
	$myField->addValidation('required');
	$myField->setMaxLength(30);
	$myField->setSize(30);
	$myField->setInfo("Inserire il nome del lavoro nel fomato nbr/user/job");
	$searchAction->addField($myField);

	$myField = new wi400InputText('FORMATO');
	$myField->setLabel("Formato");
	$myField->addValidation('required');
	$myField->setMaxLength(1);
	$myField->setSize(1);
	$myField->setInfo("Inserire il formato, R=Ridotto, E=Esteso");
	$searchAction->addField($myField);
	
	$myButton = new wi400InputButton('SEARCH_BUTTON');
	$myButton->setLabel("Seleziona");
	$myButton->setAction($azione);
	$myButton->setForm("DETAIL");
	$myButton->setValidation(true);
	$searchAction->addButton($myButton);

	$searchAction->dispose();
} else if ($actionContext->getForm()=="DETAIL") {
	$gateway = wi400Detail::getDetailValue("JOBLOG_VIEWER_SRC", "FROM_GATEWAY");
	if ($formato=="E") {
		$label="Formato Ridotto"; 
	} else {
		$label="Formato Esteso";
	}
	if (isset($_REQUEST['GATEWAY'])) {
		$gatewayb = $_REQUEST['GATEWAY'];
	} else {
		$gatewayb = $gateway;
	}
	$myButton = new wi400InputButton('CHANGE_FORMATO');
	$myButton->setLabel($label);
	$myButton->setAction($azione);
	$myButton->addParameter("GATEWAY", $gatewayb);
	$myButton->setForm("CHANGE");
	$myButton->setValidation(true);
	$myButton->dispose();
	
	if ($formato=="E") {
		$jobInfo = getJobInfo(True);
		require_once $routine_path."/os400/wi400Os400Spool.cls.php";
		// Stampo lo SPOOL con il job log
		executeCommand("DSPJOBLOG OUTPUT(*PRINT) JOB($jobComplete)");
		$splfname=  "QPJOBLOG";    //splf
		$splfnbr=   "*LAST";    //splfnbr
		$ifsfile='/home/test.txt';  // *not used here but optional last parm
		
		echo "JOBNAME:".$jobnumber."/".$jobuser."/".$jobname;
		// string i5_spool_get_data(string spool_name, string jobname, string username,
		//                           integer job_number, integer spool_id [,string filename])
		
		//$str = i5_spool_get_data($splfname,$jobname,$jobuser,$jobnumber,$splfnbr);
		$jobname=   $jobInfo['JOB'];    //job
		$jobuser=   $jobInfo['USR'];    //user
		$jobnumber= $jobInfo['NBR'];    //nbr*/
		$jobqual = str_pad($jobname, 10).str_pad($jobuser, 10).str_pad($jobnumber, 6);
		$dati = wi400Os400Spool::getData($jobqual, $splfname, $splfnbr, "*HTML");
		//echo "DATI:<pre>"; print_r($dati); echo "</pre>";
		$str = implode("<br>", $dati);
		if ($str === false) {
			print("<br>Command failed");
		}
		executeCommand("DLTSPLF FILE($splfname) JOB($jobnumber/$jobuser/$jobname) SPLNBR(*LAST)");
		// Tolgo l'ultimo carattere
		//$str= substr($str, 0, strlen($str)-1);
		//$ultimo = substr($str, strlen($str)-1, 1);
		//$replace = "<br><FONT COLOR='#de0021'><-------------------------------------- "._t('SPOOL_PAGE_SKIP')." -----------------------------------------></FONT><br>";
		//$str = str_replace  ( chr(12)  , $replace  , $str  , $count );
		//$str = str_replace
		
		$out_spool= '<pre><code>';
		$out_spool.="<FONT COLOR='#FF00FF'><-------------------------------------- "._t('SPOOL_PAGE_BEGIN')." ----------------------------------------></font><br>";
		$out_spool.=utf8_encode($str);
		$out_spool.="<br><FONT COLOR='#FF00FF'><--------------------------------------- "._t('SPOOL_PAGE_END')." -----------------------------------------></font>";
		echo $out_spool;
	} else {
		$dati = get_job_log_data($jobname,$jobuser,$jobnumber,$actionContext->getForm());
		$lines = $dati['LINES'];
		showArray($lines);
	}	
}