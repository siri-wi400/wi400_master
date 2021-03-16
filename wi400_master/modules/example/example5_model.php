<?php
// Barra di navigazione superiore .. attacco automaticamente ogni azione/form 	
$history->addCurrent(); 
// Se vado sul dettaglio cambio il titolo sull'hisotry e recupero il menu selezionato
if ($actionContext->getForm()=='DETAIL') {
	$menu = wi400Detail::getDetailValue("PARAMETRI_UTENTE","menu");
	$actionContext->setLabel("Utenti che utilizzano il menu ".$menu);	
}	
?>