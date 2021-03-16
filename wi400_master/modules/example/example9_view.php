<?php
	// Stati del TO
	$statoUtenteArray = array();
	$statoUtenteArray["A"] = "Annullato";
	$statoUtenteArray["T"] = "Temporaneo";
	$statoUtenteArray["V"] = "Valido";
	$statoUtenteArray["S"] = "Sospeso";
	
	$statoUtenteColor = array();
	$statoUtenteColor["A"] = "wi400_grid_red";
	$statoUtenteColor["T"] = "wi400_grid_orange";
	$statoUtenteColor["V"] = "wi400_grid_green";
	$statoUtenteColor["S"] = "wi400_grid_gray";
	// Lista utenti di WI400
   	$miaLista = new wi400List("EXAMPLE9_LIST", true);
	$miaLista->setFrom("SIR_USERS");
    // Creo un array a cui attaccare le colonne
    $cols = array();
	$cols[] = new wi400Column("USER_NAME","Prenotazione", null, "right");
	$cols[] = new wi400Column("EMAIL","Causale");
	// Creo al volo un'altra colonna
	$stato =  new wi400Column("STATO_UTENTE","Stato","","","");
	$stato->setShow(true);
	$stato->setSortable(false);
	$statoCond 	 = array();
	// Passo le condizioni per codificare lo stato
	foreach ($statoUtenteArray as $key => $desc){
		$statoCond[] = array('EVAL:$row["STATO"]=="'.$key.'"', $desc);
	}
	$stato->setDefaultValue($statoCond);
	// Passo le condizioni per colorare la colonna
	$statoCond = array();
	foreach ($statoUtenteColor as $key => $color){
		$statoCond[] = array('EVAL:$row["STATO"]=="'.$key.'"', $color);
	}
	$stato->setStyle($statoCond);	
	$cols[]=$stato;
	// Aggiungiamo una colonna con una immagine
	$playCol = new wi400Column("PLAYCOL","Presentazione");
	$playImg = new wi400Image("PLAY");
	$playImg->setUrl("play_th.gif");

	$playCol->setDefaultValue($playImg->getHtml());
	$playCol->setDetailAction("EXAMPLE4","DETAIL");
	$playCol->addDetailKey('USER_NAME');
	$playCol->setDetailSize(730,600);
	$playCol->setSortable(False);
	$playCol->setExportable(False);
	$miaLista->addCol($playCol);
	$cols[]=$playCol;
	
	$miaLista->setCols($cols);
	// Aggiungo le chiavi di lista
	$miaLista->addKey('USER_NAME');
    // Genero la lista 
	$miaLista->dispose();
	
	$phpCode = new wi400PhpCode();
	$phpCode->addFile($moduli_path."/example/example9_view.php");
	$phpCode->dispose();
?>