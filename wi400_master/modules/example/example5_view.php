<?php
if ($actionContext->getForm()=='DETAIL') {

	// Lista utenti di WI400
   	$miaLista = new wi400List("EXAMPLE5_LIST", true);
	$miaLista->setFrom("SIR_USERS");
	
	// Solo utenti selezionati da filtro del form iniziale
	$where = "MENU='".$menu."'";
	//$where = "MENU='".$_POST['menu'];
	$miaLista->setWhere($where);

    // Scelta Colonne
	$miaLista->addCol(new wi400Column("USER_NAME","Prenotazione", null, "right"));
	$miaLista->addCol(new wi400Column("EMAIL","Causale"));

	$colonna = new wi400Column("AUTH_METOD", "Verifica Utente");
	$colonna->setShow(False);
	$miaLista->addCol($colonna);
	$miaLista->addKey("USER_NAME");
	// Aggiunta filtro veloce
	$mioFiltro = new wi400Filter("USER_NAME","Codice Utente","STRING");
	$mioFiltro->setFast(true);
	$miaLista->addFilter($mioFiltro);

	$miaLista->dispose();

}
if ($actionContext->getForm()=='DEFAULT') {

	// Creo il dettaglio, pulisco eventuali dati memorizzati per uno stesso dettaglio utilizzato
	// precedentemente
	$userDetail = new wi400Detail("PARAMETRI_UTENTE", True);

	$userDetail->setTitle('Ricerca Azione');
	$userDetail->isEditable(true);
	// Aggiungo i campi al detail
	$myField = new wi400InputText('menu');
	$myField->setLabel("Codice Menu");
    // Regole di validatzione formali 
	$myField->addValidation("required");
	// In automatico il campo verrà messo in uppercase
	$myField->setCase("UPPER");	
	// Massima lunghezza del campo
	$myField->setMaxLength(30);	
	$myField->setInfo('Inserire il codice menu utilizzato dagli utenti di WI400');
	// Precarico il default "MENU" se presente, in ogni caso un volta selezionato diverrà il nuovo
	// default per MENU.
	$myField->setUserApplicationValue("MENU");	
	$decodeParameters = array(
		'TYPE'			  => 'common',
		'COLUMN' => 'DESCRIZIONE',
		'TABLE_NAME' 	  => 'FMNUSIRI',
//	    'AJAX'            => True, 
		'KEY_FIELD_NAME'  => 'MENU'
	);
	$myField->setDecode($decodeParameters);
	// Aggiungo al campo un lookup
	$myLookUp = new wi400LookUp("LU_MENU");
	$myLookUp->addField("menu");
	// @todo
	//// Al lookup potrei passare altri parametri per filtrare la scelta
	///$myLookUp->addParameter("FILTER_SQL", "MENU IN (SELECT MENU FROM SIR_USERS A WHERE A.MENU=MENU)")
	$myField->setLookUp($myLookUp);
	
	$userDetail->addField($myField);
	
	// Bottone
	$myButton = new wi400InputButton('SEARCH_BUTTON');
	$myButton->setLabel("Seleziona");
	$myButton->setValidation(true);
	$myButton->setAction("EXAMPLE5");
	$myButton->setForm("DETAIL");
	$userDetail->addButton($myButton);
	
	$userDetail->dispose();
}		

   $phpCode = new wi400PhpCode();
   $phpCode->addFile($moduli_path."/example/example5_model.php");
   $phpCode->addFile($moduli_path."/example/example5_view.php");
   $phpCode->dispose();
?>