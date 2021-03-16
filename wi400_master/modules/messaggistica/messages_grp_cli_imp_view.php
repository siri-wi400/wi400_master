<?

	if ($actionContext->getForm() == "ADD"){ 
?>		
	<script>
		if (IFRAME_LOOKUP){
			top.doSubmit("MANAGER_MESSAGES", "DESTINATARI");
			top.f_dialogClose();
		}else{
			window.opener.doSubmit("MANAGER_MESSAGES", "DESTINATARI");
			self.close();
		}
	</script>
<?		
		exit();
	}
	
	// Bottoni fondo lookup
	$myButton = new wi400InputButton("ADD_BUTTON");
	$myButton->setAction($azione);
	$myButton->setForm("ADD");
	$myButton->setLabel("Aggiungi");
	$buttonsBar[] = $myButton;
		
	$myButton = new wi400InputButton("CANCEL_BUTTON");
	$myButton->setScript('closeLookUp()');
	$myButton->setLabel("Annulla");
	$buttonsBar[] = $myButton;
	
	$tabella = "V014";
	$sql = "SELECT * FROM FTABTABE WHERE TABSIG='".$tabella."'";
	$row = $db->fetch_array($db->singleQuery($sql));
	
	$tabelle = new wi400Tabelle (Null, Null,  $db);
	$tabelle->preparaTabella($tabella);
	
	// Inizializzo lista 
	$subfile = new wi400Subfile($db, "TAB", $settings['db_temp'], 10);
	$subfile->setModulo("to");
	$array = array();
	$array['CODICE']=$db->singleColumns("1", 20 );
	$array['DESCRIZIONE']=$db->singleColumns("1", 40 );
	$subfile->inz($array);
	
	while($tabelle->caricaTabella()){	
		if ($tabelle->getStato() != '0')
	    {
	    	// Controllo se sono presenti CLIENTI
	    	$sql = "SELECT MCDCLI FROM FMCLIDFT WHERE MCDOFF='" . sanitize_sql_string ($tabelle->getElemento()) . "' AND MCDSTA='1'";
	    	$result = $db->singleQuery( $sql );
	    	if ( $campi = $db->fetch_array ( $result ) ) {	    	
		        $dati = array($tabelle->getElemento() , $tabelle->getDescrizione());				
		 		$subfile->write($dati);
	    	}
	     }
	}
	$subfile->finalize();
	
	$miaLista = new wi400List("TABELLA_$tabella");
	$miaLista->setSelection("MULTI");
	
	$miaLista->setFrom($subfile->getTable());
	$miaLista->setOrder("CODICE ASC");
	
	$miaLista->setCols(array(
							new wi400Column("CODICE","Codice"),
							new wi400Column("DESCRIZIONE","Descrizione elemento"),
							)
						);
	
	// Aggiunta chiavi di riga
	$mioFiltro = new wi400Filter("DESCRIZIONE","Descrizione","STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	$mioFiltro = new wi400Filter("CODICE","Codice","STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	$miaLista->addKey("CODICE");
	$miaLista->dispose();
?>
