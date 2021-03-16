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
	if (!isset($SQL_add_clienti)) $SQL_add_clienti=""; 
	$miaLista = new wi400List("ADD_CLI_LIST", true);

	$miaLista->setFrom("FMEBINTL A,
	                LATERAL ( SELECT                                     
					rrn(o) AS NREL                                             
	                FROM   LMEBINTL o                          
	                WHERE  A.MEBCDF = o.MEBCDF and             
					digits(o.MEBAVA)!!digits(o.MEBMVA)!!digits(o.MEBGVA) <=".$_SESSION['data_validita']." 
	                FETCH FIRST ROW ONLY ) AS x,
					FMECRAPP C,     LATERAL ( SELECT                               
					    rrn(d) AS NREL                                             
					                FROM   LMECRAPP D                              
					WHERE  C.MECCDR = D.MECCDR and C.MECCDF = D.MECCDF and         
					    digits(D.MECAVA)!!digits(D.MECMVA)!!digits(D.MEcGVA)      
					<=".$_SESSION['data_validita']." FETCH FIRST ROW ONLY ) AS y");	
	$sql = " rrn(A) = x.NREL AND rrn(C) = y.NREL AND A.MEbCDF = c.MECCDF AND MECCDR='22' AND MECSTA = '1' AND MEBCDF NOT IN(SELECT DSTDST FROM ZMSGDST WHERE DSTID='{$dati_mess['TESID']}' AND DSTTYP='*INT')";

	$miaLista->setShowMenu(true);
	$miaLista->setSelection("MULTIPLE");
	$miaLista->setWhere($sql);
	$miaLista->setOrder("MEBCDF");
	
	
	$miaLista->setCalculateTotalRows("LAST");
	
	$descCliente = new wi400Column("MEBRAG","Descrizione");
	$descCliente->setSortable(false);
	
	$miaLista->setCols(array(
							new wi400Column("MEBCDF","Negozio"),
							$descCliente,					
							new wi400Column("MEBIVA","Partita IVA"),
							new wi400Column("MECCDR","Tipo Rapporto")
							)
						);
						
	$mioFiltro = new wi400Filter("MEBRAG","Descrizione","STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	$mioFiltro = new wi400Filter("MEBCDF","Codice","STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);
	
	// aggiunta chiavi di riga
	$miaLista->addKey("MEBCDF");
	
	listDispose($miaLista);
?>