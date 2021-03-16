<?php
// recupero Template
require_once($routine_path.'/h2p/html2pdf.class.php');
ini_set("memory_limit","1000M");
set_time_limit(0);
require_once $moduli_path."/wrksplf/pdf_genera_template_commons.php";
$argomento = $batchContext->ARGOMENTO;
$scheda = $batchContext->SCHEDA;
$salto_pagina = 64;
//$multiRow = $batchContext->ROW;
$parameters = $batchContext->getAll();
if ($argomento =="") {
	die("PARAMETRO ARGOMENTO NON TROVATO O VUOTO");
}
if ($scheda =="") {
	die("PARAMETRO SCHEDA NON TROVATO O VUOTO");
}
$multi = False;
if ($scheda =="*ALL") {
	$multi = True;
}
if (isset($parameters['SALTO_PAGINA']) && $parameters['SALTO_PAGINA']!="") {
	$salto_pagina=$batchContext->SALTO_PAGINA;
}
// Recupero la scheda HTML con il template
$documentHtml ="";
//$documentHtml .= getInizioPagina();
$pageInit= False;
if ($multi==False) {
	// Pagina [[page_cu]]/[[page_nb]]
	//$documentHtml .= "<table cellpadding='0' cellspacing='0' border='0' width='730'><tr><td>";
	// Verifico se agganciare una testata standard passata come parametro
	// Sostituisco i parametri
	$documentHtml .= getInizioPagina();
	$documentHtml .= substituteFolderArray(get_template_html($argomento, $scheda),$parameters);
} else {
	// Devo ciclare su tutte le schede dell'argomento
	$sql = "SELECT * FROM ZFLDARGD WHERE FLD_TYPE<>'****' AND FLD_ARGO='$argomento' AND FLD_USO<>'A' ORDER BY FLD_ORDER";
	$result = $db->query($sql);
	while ($row=$db->fetch_array($result)) {
		// Verifico il tipo di scheda
		if ($row['FLD_SINGLE']=="S") {
			if ($pageInit==True) {
				$documentHtml.=chiudiPagina();
			}
			$documentHtml.=getInizioPagina();
			$pageInit=True;
		}
		// Se non mi hanno messo pagina nuova sulla prima lo metto di default
		if ($pageInit==False) {
			$documentHtml.=getInizioPagina();
			$pageInit=True;
		}
		if ($row['FLD_USO'] !="M") {
			$documentHtml .= substituteFolderArray(get_template_html($argomento, $row['FLD_TYPE']),$parameters);
		}
		/*
		 * 		
		 */
		// Stampa multipla
		if ($row['FLD_USO'] =="M") {
			$file = $batchContext->__get($row['FLD_TYPE']."_FILE");
			$order = $batchContext->__get($row['FLD_TYPE']."_ORDER");
			$where = $batchContext->__get($row['FLD_TYPE']."_WHERE");
			$membro = $batchContext->__get($row['FLD_TYPE']."_MEMB");
			$libreria = $batchContext->__get($row['FLD_TYPE']."_LIB");
			$file2 = $file;
			if ($libreria!="") {
				$file2 = trim($libreria).$settings['db_separator'].trim($file);
			}
			if ($where != "") {
				$where = " WHERE ".$where;
			}
			// Devo eseguire l'ovveride
			if ($membro!="") {
				// Elimino override
				$cmd = "DLTOVR FILE(".trim($file).") LVL(*JOB)";
				$len = strlen(trim($cmd));
				$ovrsql = "CALL QCMDEXC('".trim($cmd)."', ".$len.")"; 
				$db->query($ovrsql);
				// Aggiungo override
				$cmd = "OVRDBF FILE(".trim($file).") TOFILE(".trim($file2).") MBR(".trim($membro).") OVRSCOPE(*JOB)";
				$len = strlen(trim($cmd));
				$ovrsql = "CALL QCMDEXC('".trim($cmd)."', ".$len.")";
				$db->query($ovrsql);
			}
			// Calcolo il totale Vecchi versione
			if (!isset($parameters['TOTALE_PAGINE'])) {
			$sql4 = "SELECT COUNT(*) AS COUNT FROM $file2 $where AND CAUTIPO<>'4'";
			$result3 = $db->query($sql4);
			$rowtot = $db->fetch_array($result3);
			$sql5 = "SELECT COUNT(*) AS COUNT FROM $file2 $where AND CAUTIPO='4'";
			$result4 = $db->query($sql5);
			$rowtot2 = $db->fetch_array($result4);
			$tg = $rowtot['COUNT']+($rowtot2['COUNT']*2);
			$totale = ceil($tg/$salto_pagina);
			} else {
				$sql5 = "SELECT COUNT(*) AS COUNT FROM $file2 $where";
				$result4 = $db->query($sql5);
				$rowtot2 = $db->fetch_array($result4);
				$tg = $rowtot2['COUNT'];
				$totale = ceil($tg/$salto_pagina);
			}
			//
			$sql2 = "SELECT * FROM $file2 $where ORDER BY $order";
			$result2 = $db->query($sql2);
			$template = get_template_html($argomento, $row['FLD_TYPE']);
			$array_riga = explode('<!--ENDROW-->', $template);
			$tot = count($array_riga);
			$ID = False;
			$ident_field = $batchContext->__get($row['FLD_TYPE']."_IDENT");
			//
			if ($ident_field!="" && strpos($array_riga[2], '<!--TYPE_')!==False) {
				for ($i=2;$i<$tot;$i++) {
					$m = substr($array_riga[$i], strpos($array_riga[$i], '<!--TYPE_')+9);
					$m = substr($m, 0, strpos($m, '-->'));
					$array_type[$m]=$array_riga[$i];
				}
				$ID = True;
			}
			$riga = 0;
			$i=0;
			$pagina = 1;
			$pagcur = 1;
			$parameters['PAGINA']="$pagcur/$totale";
			$inizioPagina = substituteFolderArray(get_template_html($argomento, "HEAD"), $parameters).$array_riga[0].$array_riga[1];
			//$documentHtml .= get_template_html($argomento, "HEAD").$array_riga[0].$array_riga[1];
			$documentHtml .=$inizioPagina;
			$closed = False;
			$array_pdf = array();
			while ($row2=$db->fetch_array($result2)) {
				$i++;
				if ($i>$salto_pagina) {
					if ($pagina == 50) {
						$pagina = 1;
						$documentHtml .=$array_riga[$tot-1].chiudiPagina();
						$margini = array(1, 1, 1, 1);
						$html2pdf = new HTML2PDF('P','A4', 'it', true, 'UTF-8', $margini);
						$html2pdf->setTestTdInOnePage(false);
						$itemCode = "";
						$isOpenTable = false;
						$isOpenSubTable = false;
						$documentHtml=applicaFunzioni($documentHtml);
						$html2pdf->WriteHTML($documentHtml, isset($_GET['vuehtml']));
						$isOpenTable = false;
						$batch_file = wi400File::getUserFile('export', uniqid().".pdf");
						$html2pdf->Output($batch_file, 'F');
						unset($html2pdf);
						$array_pdf[]=$batch_file;
						unset($documentHtml);
						$documentHtml = "";
						$pagcur++;
						$parameters['PAGINA']="$pagcur/$totale";
						$inizioPagina = substituteFolderArray(get_template_html($argomento, "HEAD"), $parameters).$array_riga[0].$array_riga[1];
						$documentHtml .=getInizioPagina().$inizioPagina;
						$i=1;
					} else {
						$i=1;
						$pagina++;
						$pagcur++;
						$parameters['PAGINA']="$pagcur/$totale";
						$inizioPagina = substituteFolderArray(get_template_html($argomento, "HEAD"), $parameters).$array_riga[0].$array_riga[1];
						$documentHtml .=$array_riga[$tot-1].chiudiPagina().getInizioPagina().$inizioPagina;
					}
				}
				if ($ID==True) {
					$idx = trim($row2["$ident_field"]);
					if ($idx=="4") $i++;
					$documentHtml .= substituteFolderArray($array_type[$idx],array_merge($row2, $parameters));
				} else {
					$documentHtml .= substituteFolderArray($array_riga[2],array_merge($row2, $parameters));
				}
			}
			// Tolgo l'override
			if ($membro!="") {
				// Elimino override
				$cmd = "DLTOVR FILE(".trim($file).") LVL(*JOB)";
				$len = strlen(trim($cmd));
				$ovrsql = "CALL QCMDEXC('".trim($cmd)."', ".$len.")";
				$db->query($ovrsql);
			}
			// L'ultima riga è sempre di chiusura
			$documentHtml .= $array_riga[$tot-1];
			// Il salto pagina è automatio ... BOHHHHHH!!!
			/*if ($row['FLD_SINGLE']=="S" && $pageInit==False) {
				$documentHTml.=chiudiPagina();
				$documentHtml.=getInizioPagina();
			}*/
		}
	}
}
// Operazioni Finali
$documentHtml .=chiudiPagina();
$documentHtml=applicaFunzioni($documentHtml);
// Margini (left, top, right, bottom)
$margini = array(1, 2, 2, 1);
$html2pdf = new HTML2PDF('P','A4', 'it', true, 'UTF-8', $margini);
$html2pdf->setTestTdInOnePage(false);
$itemCode = "";
$isOpenTable = false;
$isOpenSubTable = false;
$html2pdf->WriteHTML($documentHtml, isset($_GET['vuehtml']));
$isOpenTable = false;
$batch_file = wi400File::getUserFile('tmp', uniqid().".pdf");
//echo "##EMAIL_AZIONE_BATCH_FILE:$batch_file##";
//$pdfFileName = wi400File::getUserFile("tmp",$fileName);
$html2pdf->Output($batch_file, 'F');
$array_pdf[]=$batch_file;
unset($html2pdf);
unset($documentHtml);
if (count($array_pdf)==1) {
	$the_file = $array_pdf[0];
} else {
	require('fpdf_merge.php');
	$merge = new FPDF_Merge();
	error_log("COUNT:".count($array_pdf));
	foreach ($array_pdf as $key => $value) {
		error_log("FILE:".$value);
		$merge->add($value);
	}
	$the_file = wi400File::getUserFile('tmp', uniqid().".pdf");
	$merge->output($the_file);
	unset($merge);
}
echo "##EMAIL_AZIONE_BATCH_FILE:$the_file##";
