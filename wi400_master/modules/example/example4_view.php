<?php
if ($actionContext->getForm()=='DEFAULT') {
	// Lista utenti di WI400
   	$miaLista = new wi400List("EXAMPLE4_LIST", true);
	$miaLista->setFrom("SIR_USERS");
	
	// Solo utenti con menu generale
	$miaLista->setWhere("MENU='UNO'");

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
	
	// Aggiungo una azione di lista
	$listaAction = new wi400ListAction();
	$listaAction->setLabel("Foto Utente");
	$listaAction->setAction("EXAMPLE4");
	$listaAction->setForm("DETAIL");
	$listaAction->setTarget("WINDOW");		
	$listaAction->setSelection("SINGLE");
	$miaLista->addAction($listaAction);	

	$miaLista->dispose();
}
if ($actionContext->getForm()=='DETAIL') {

	// Creo un nuovo detail 
	$userDetail = new wi400Detail("DETTAGLIO_UTENTE");

	$key = getListKeyArray("EXAMPLE4_LIST");
	$user = $key['USER_NAME'];			
    // Titolo del dettaglio
 	$userDetail->setTitle($user);
	// Creo l'oggetto immagine passando l'utente selezionato come chiave di ricerca
	$myImage = new wi400Image('detailImage');
	$myImage->setWidth(150);
	$myImage->setObjCode($user);
	$myImage->setObjType("USR");
	$userDetail->addImage($myImage);
	
	$userDetail->dispose();

}		

   $phpCode = new wi400PhpCode();
   $phpCode->addFile($moduli_path."/example/example4_view.php");
   $phpCode->dispose();
?>