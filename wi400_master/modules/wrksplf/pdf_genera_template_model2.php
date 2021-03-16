<?php
// recupero Template
require_once $moduli_path."/wrksplf/pdf_genera_template_commons.php";
$argomento = $batchContext->ARGOMENTO;
$scheda = $batchContext->SCHEDA;

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
	$sql = "SELECT * FROM ZFLDARGD WHERE FLD_TYPE<>'****' AND FLD_ARGO='$argomento' ORDER BY FLD_ORDER";
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
		// Stampa multipla
		if ($row['FLD_USO'] =="M") {
			$file = $batchContext->__get($row['FLD_TYPE']."_FILE");
			$order = $batchContext->__get($row['FLD_TYPE']."_ORDER");
			$sql2 = "SELECT * FROM $file ORDER BY $order";
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
			$documentHtml .= $array_riga[0].$array_riga[1];
			while ($row2=$db->fetch_array($result2)) {
				if ($ID==True) {
					$idx = trim($row2["$ident_field"]);
					$documentHtml .= substituteFolderArray($array_type[$idx],array_merge($row2, $parameters));
				} else {
					$documentHtml .= substituteFolderArray($array_riga[2],array_merge($row2, $parameters));
				}
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
require_once($routine_path.'/h2p/html2pdf.class.php');
// Margini (left, top, right, bottom)
$margini = array(2, 2, 2, 6);
$html2pdf = new HTML2PDF('P','A4', 'it', true, 'UTF-8', $margini);
$html2pdf->setTestTdInOnePage(false);
$itemCode = "";
$isOpenTable = false;
$isOpenSubTable = false;
$html2pdf->WriteHTML($documentHtml, isset($_GET['vuehtml']));
$isOpenTable = false;

//$fileName = "template_001.pdf";
$batch_file = wi400File::getUserFile('export', uniqid().".pdf");
echo "##EMAIL_AZIONE_BATCH_FILE:$batch_file##";
//$pdfFileName = wi400File::getUserFile("tmp",$fileName);
$html2pdf->Output($batch_file, 'F');
