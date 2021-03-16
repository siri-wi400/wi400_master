<?php
// Dettaglio parametro
if ($actionContext->getForm () == "DEFAULT") {
	// Estrazione Dati
	$searchAction = new wi400Detail ( $azione . "_DET", true );
	$searchAction->setSaveDetail ( true );
	$searchAction->setColsNum ( 1 );
	
	// Libreria //
	$myField = new wi400InputText ( 'LIBRERIA' );
	$myField->setLabel ( "Libreria" );
	$myField->setMaxLength ( 10 );
	$myField->setSize ( 10 );
	$myField->setCase ( 'UPPER' );
	$myField->addValidation ( 'required' );
	$myField->setInfo ( "Seleziona la libreria" );
	$searchAction->addField ( $myField );
	
	// Seleziona
	$myButton = new wi400InputButton ( 'ELABORA' );
	$myButton->setLabel ( "Elabora" );
	$myButton->setValidation ( True );
	$myButton->setAction ( $azione );
	$myButton->setForm ( "LIST" );
	$searchAction->addButton ( $myButton );
	
	$searchAction->dispose ();
}

// Lista Sorgenti
elseif ($actionContext->getForm () == "LIST") {
	// Stampo il dettaglio parametri
	// Intestazione di dettaglio paramentri
	$dettaglio = new wi400Detail ( $azione . "_INTE", true );
	$dettaglio->setColsNum ( 1 );
	// Libreria
	$myField = new wi400Text ( 'LIBRERIA' );
	$myField->setLabel ( "Libreria :" );
	$myField->setValue ( $libreria );
	$dettaglio->addField ( $myField );
	$dettaglio->dispose ();
	echo "<br/>";
	
	$miaLista = new wi400List ( $azione . "_LIST", true );
	$miaLista->setFrom ( "SYSTABLES" );
	$where = implode ( " and ", $where_array );
	// echo $where."<br/>";
	$miaLista->setWhere ( $where );
	$miaLista->setOrder ( "TABLE_NAME" );
	$miaLista->setField ( "TABLE_NAME,TABLE_TEXT" );
	$nome=new wi400Column ( "TABLE_NAME", "Sorgente", "STRING", "left" );
	$nome->setActionListId('DETTAGLIO');
	
	$miaLista->setCols ( array (
			
			$nome,
			new wi400Column ( "TABLE_TEXT", "Descrizione", "STRING", "left" ) 
	)
	 );
	
	// aggiunta chiavi di riga
	$miaLista->addKey ( "TABLE_NAME" );
	// Aggiunta filtri
	$listFlt = new wi400Filter ( "TABLE_NAME", "Libreria", "STRING" );
	// $listFlt->setFast(True);
	$miaLista->addFilter ( $listFlt );
	// Aggiunta azioni
	$action = new wi400ListAction ();
	$action->setAction ( $azione );
	$action->setSelection ( "SINGLE" );
	$action->setForm ( "LMBR" );
	$action->setLabel ( "Visualizza contenuto" );
	$action->setId('DETTAGLIO');
	$miaLista->addAction ( $action );
	
	$miaLista->dispose ();
}

// Lista Membri
elseif ($actionContext->getForm () == "LMBR") {
	// Stampo il dettaglio parametri
	// Intestazione di dettaglio paramentri
	$dettaglio = new wi400Detail ( $azione . "_INTE", true );
	$dettaglio->setColsNum ( 1 );
	// Libreria
	$myField = new wi400Text ( 'LIBRERIA' );
	$myField->setLabel ( "Libreria :" );
	$myField->setValue ( $libreria );
	$dettaglio->addField ( $myField );
	// File
	$myField = new wi400Text ( 'FILE' );
	$myField->setLabel ( "File :" );
	$myField->setValue ( $file );
	$dettaglio->addField ( $myField );
	$dettaglio->dispose ();
	echo "<br/>";
	
	$miaLista = new wi400List ( $azione . "_LMBR", true );
	$miaLista->setFrom ( "PGMDOC" );
	$where = implode ( " and ", $where_array );
	// echo $where."<br/>";
	$miaLista->setWhere ( $where );
	$miaLista->setOrder ( "SRCNAM" );
	$miaLista->setField ( "SRCNAM,SRCTIP,SRCDES,SRCDAC,SRCDAM" );
	$nome=new wi400Column ( "SRCNAM", "File", "STRING", "left" );
	$nome->setActionListId("DETTAGLIO");
	
	$miaLista->setCols ( array (
			
			$nome,
			new wi400Column ( "SRCTIP", "Tipo", "STRING", "left" ),
			new wi400Column ( "SRCDES", "Descrizione", "STRING", "left" ),
			new wi400Column ( "SRCDAC", "Data di creazione", "STRING", "left" ),
			new wi400Column ( "SRCDAM", "Data di modifica", "STRING", "left" ) 
	)
	 );
	
	$miaLista->addKey ( "SRCNAM" );
	// Aggiunta filtri
	$listFlt = new wi400Filter ( "SRCNAM", "File", "STRING" );
	// $listFlt->setFast(True);
	$miaLista->addFilter ( $listFlt );
	// Aggiunta azioni
	$action = new wi400ListAction ();
	$action->setAction ( $azione );
	$action->setSelection ( "SINGLE" );
	$action->setForm ( "LCON" );
	$action->setLabel ( "Visualizza contenuto" );
	$action->setId("DETTAGLIO");
	$miaLista->addAction ( $action );
	
	$miaLista->dispose ();
}

// Contenuto File
elseif ($actionContext->getForm () == "LCON") {
	// Stampo il dettaglio parametri
	// Intestazione di dettaglio paramentri
	$dettaglio = new wi400Detail ( $azione . "_INTE", true );
	$dettaglio->setColsNum ( 1 );
	// Libreria
	$myField = new wi400Text ( 'LIBRERIA' );
	$myField->setLabel ( "Libreria :" );
	$myField->setValue ( $libreria );
	$dettaglio->addField ( $myField );
	// File
	$myField = new wi400Text ( 'FILE' );
	$myField->setLabel ( "File :" );
	$myField->setValue ( $file );
	$dettaglio->addField ( $myField );
	// Membro
	$myField = new wi400Text ( 'MEMBRO' );
	$myField->setLabel ( "Membro :" );
	$myField->setValue ( $membro );
	$dettaglio->addField ( $myField );
	$dettaglio->dispose ();
	//echo "<br/>";

	$miaLista = new wi400List ( $azione . "_LCON", true );

	$sql = "CREATE ALIAS PHPTEMP/SRCDST1 FOR $libreria/$file($membro) ";
	$result = $db->query($sql);
	$sql= "select * from phptemp/srcdst1";
	$result = $db->query($sql);
	$dati = "";
	while ($row=$db->fetch_array($result)) {
		$dati .= $row['SRCDTA']."\r\n";
	}
	//$dati=str_replace(' ','&nbsp;',$dati);
	$pos = strpos($dati, "PGM(");
	while ($pos!==False) {
		$fine = strpos($dati, ")", $pos);
		$pgm=substr($dati, $pos+4, $fine-($pos+4));
		//$dati = substr($dati, 0, $pos+4). "<a id='font_pgm' href=\"javascript:open('index.php?t=$azione&f=LCOM&OGGETTO=$pgm','Contenuto')\">".substr($dati, $pos+4, $fine-($pos+4))."</a>".substr($dati, $fine);
		$pos = strpos($dati, "PGM(", $fine);
	}
	echo "<FONT SIZE=2><FONT FACE= 'Courier'>";
	include_once $routine_path.'/geshi/geshi.php';
	//
	// Create a GeSHi object//
	$geshi = new GeSHi($dati, 'CLP');
	//
	// And echo the result!//
	echo $geshi->parse_code();
//	echo $dati;
	//die("FINITO!!");
	$sql = "DROP ALIAS PHPTEMP/SRCDST1";
	$result = $db->query($sql);

}

// Contenuto PGM
elseif ($actionContext->getForm () == "LCOM") {
	// Stampo il dettaglio parametri
	// Intestazione di dettaglio paramentri
	$dettaglio = new wi400Detail ( $azione . "_INTE", true );
	$dettaglio->setColsNum ( 1 );
	// Libreria
	$myField = new wi400Text ( 'LIBRERIA' );
	$myField->setLabel ( "Libreria :" );
	$myField->setValue ( $libreria );
	$dettaglio->addField ( $myField );
	// File
	$myField = new wi400Text ( 'FILE' );
	$myField->setLabel ( "File :" );
	$myField->setValue ( $file );
	$dettaglio->addField ( $myField );
	// Membro
	$myField = new wi400Text ( 'MEMBRO' );
	$myField->setLabel ( "Membro :" );
	$myField->setValue ( $oggetto );
	$dettaglio->addField ( $myField );
	$dettaglio->dispose ();
	echo "<br/>";

	$miaLista = new wi400List ( $azione . "_LCOM", true );

	$sql = "CREATE ALIAS PHPTEMP/SRCDST1 FOR $libreria/$file($oggetto) ";
	$result = $db->query($sql);
	$sql= "select * from phptemp/srcdst1";
	$result = $db->query($sql);
	$dati = "";
	while ($row=$db->fetch_array($result,null,false)) {
		$dati .= $row['SRCDTA']."<br>";
	}
	$dati=str_replace(' ','&nbsp;',$dati);
	$pos = strpos($dati, "PGM(");
	while ($pos!==False) {
		$fine = strpos($dati, ")", $pos);
		$pgm=substr($dati, $pos+4, $fine-($pos+4));
		$dati = substr($dati, 0, $pos+4). "<a id='font_pgm' href=\"javascript:open('index.php?t=$azione&f=LCOM&OGGETTO$pgm=','Contenuto')\">".substr($dati, $pos+4, $fine-($pos+4))."</a>".substr($dati, $fine);
		$pos = strpos($dati, "PGM(", $fine);
	}
	echo "<FONT SIZE=2><FONT FACE= 'Courier'>";
	echo $dati;
	//die("FINITO!!");
	$sql = "DROP ALIAS PHPTEMP/SRCDST1";
	$result = $db->query($sql);

}