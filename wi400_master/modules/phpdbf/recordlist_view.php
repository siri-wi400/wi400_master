<?php

$miaLista = new wi400List("RECORDLIST", True);
$miaLista->setConfigFileName("RECORDLIST_".$libreria."_".$tabella);
$miaLista->setFrom("$libreria".$settings['db_separator']."$tabella A");
$miaLista->setField("RRN(A) AS NREL, A.*");
$detail_create = False;

// Verifico se abilitare il filtro in data
if (isset($gestione_date[$tabella]['DATA'])) {
	
	    $detail = new wi400Detail("GESTIONE_DATA_SI", True);
		$detail->setTitle('Filtro per gestione in data');
		$detail->isEditable(true);
		// Data di rifornimento
		$myField = new wi400InputText('DATA_RIF');
		$myField->addValidation('required');
		if(!isset($data_rif) || empty($data_rif))
			$myField->setValue(dateModelToView($_SESSION['data_validita']));
		else
			$myField->setValue($data_rif);
		$myField->addValidation('date');
		$myField->setLabel("Data di validità:");
		$detail->addField($myField);
	
		$myField = new wi400InputCheckbox("SI_GESTIONE_DATE");
		$myField->setLabel("Abilita Filtro per data");
		if (isset($_REQUEST["SI_GESTIONE_DATE"])){
			$myField->setChecked(True);
		}
		$detail->addField($myField);	
		$detail_create = True;	
		$myButton = new wi400InputButton('APPLICA');
		$myButton->setLabel("Applica scelte");
		$myButton->setAction("RECORDLIST");
		$myButton->setValidation(true);
		$detail->addButton($myButton);		
	
}
// Verifico se abilitare la decodifica dei campi calcolati
if (isset($gestione_date[$tabella]['LINK_EVAL'])) {
	
	    if (!$detail_create) {
	    $detail = new wi400Detail("GESTIONE_LINK", False);
		$detail->setTitle('Decodifica Campi calcolati');
		$detail->isEditable(true);
	    }
	    
		$myField = new wi400InputCheckbox("SI_DECODIFICA");
		$myField->setLabel("Decodifica campi calcolati");
		if (isset($_REQUEST["SI_DECODIFICA"])){
			$myField->setChecked(True);
		}		
		$detail->addField($myField);	
	    if (!$detail_create){		
			$myButton = new wi400InputButton('APPLICA');
			$myButton->setLabel("Applica scelte");
			$myButton->setAction("RECORDLIST");
			$myButton->setValidation(true);
			$detail->addButton($myButton);
	    }		
		$detail_create=True;
}

if ($detail_create) {
			$detail->dispose();
}
if (isset($_REQUEST["SI_GESTIONE_DATE"])){
	  $key = $gestione_date[$tabella]['DATA']['KEY'];
	  $anno = $gestione_date[$tabella]['DATA']['ANNO'];
	  $mese = $gestione_date[$tabella]['DATA']['MESE'];
	  $giorno = $gestione_date[$tabella]['DATA']['GIORNO'];
	  $index = 	 $gestione_date[$tabella]['DATA']['INDEX'];
	  //$where = $gestione_date[$tabella]['FUNC'].dateViewToModel($_REQUEST["DATA_RIF"]).")=".$gestione_date[$tabella]['EXPR'];	
      $where = " rrn(A) = x.NREL ";
      $miaLista->setFrom("$libreria/$tabella A, 
      LATERAL ( SELECT                                     
				rrn(o) AS NREL                                             
                FROM   $libreria/$index o                          
                WHERE  A.$key = o.$key and             
				digits($anno)!!digits($mese)!!digits($giorno) <=".dateViewToModel($_REQUEST["DATA_RIF"])." 
                FETCH FIRST ROW ONLY ) AS x");
	  $miaLista->setWhere($where);
	  $miaLista->setOrder($key);
	  $miaLista->setCalculateTotalRows("FALSE");
	}
$cols = getColumnListFromTable($tabella, $libreria);
if (isset($gestione_date[$tabella]['LINK_EVAL']) && isset($_REQUEST["SI_DECODIFICA"])) {
    $ncols = array(); 
	foreach($cols as $key=>$value) {
	    $ncols[$key]=$value;
	    if (isset($gestione_date[$tabella]['LINK_EVAL'][$key])) {
	                $descrizione = "Descrizione";
	    			if (isset($gestione_date[$tabella]['LINK_EVAL'][$key]['DESCRIZIONE'])) {
	    			    $descrizione = $gestione_date[$tabella]['LINK_EVAL'][$key]['DESCRIZIONE'];
	    			} 
               		$mycol = new wi400Column ( $key."_DECODED", $descrizione );
               		$mycol->setShow(true);
					$mycol->setSortable(false);
					$function=$gestione_date[$tabella]['LINK_EVAL'][$key]['FUNCTION'];
					if ($function!='table') {
						//$mycol->setDefaultValue('EVAL:wi400_decode_'.$function.'("", "", $row["'.$key.'"],"",True)');
						$mycol->setDecode(array('TYPE'=>"$function"), $key);					
					} else {
						$table = $gestione_date[$tabella]['LINK_EVAL'][$key]['TABLE'];
						//$mycol->setDefaultValue('EVAL:wi400_decode_'.$function.'("", "", $row["'.$key.'"], array("TABLE"=>"'.$table.'"), True)');
					    $mycol->setDecode(array('TYPE'=>'table', "TABLE"=>"$table"), $key);						
					}
               		$ncols[$key."_DECODED"]=$mycol;
		}
	}
    $cols = $ncols;
}
// Aggiunta colonna con numero relativo di record
$cols[] = new wi400Column ("NREL", "NUMERO RELATIVO RECORD" );
$miaLista->setCols($cols);
// Aggiunta dinamica filtri su tutti i campi
foreach($cols as $key=>$value) {
	$listFlt = new wi400Filter($key);
	$listFlt->setDescription($value->getDescription());
	if ($value->getAlign()=='right') $listFlt->setType("NUMERIC");
	else $listFlt->setCaseSensitive(wi400Filter::$CASE_SENSITIVE_NONE);
	if (isset($gestione_date[$tabella]['LINK_EVAL'][$key]['LOOKUP_TEMPLATE'])) {
		//$articolo = getTemplateField($gestione_date[$tabella]['LINK_EVAL'][$key]['LOOKUP_TEMPLATE'], $key."_Lookup");
		//$listFlt->setFieldObj($articolo);
	}
	$miaLista->addFilter($listFlt);
}
// Aggiunta Azioni
$action = new wi400ListAction();
$action->setAction("RECORD_DETAIL");
$action->setLabel("Modifica Record");
$action->setTarget("WINDOW");
$miaLista->addAction($action);
// Aggiunta chiavi di riga
$miaLista->addKey("NREL");
$miaLista->addParameter("LIBRERIA", $libreria);
$miaLista->addParameter("TABELLA", $tabella);
listDispose($miaLista);
?>
