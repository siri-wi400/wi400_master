<?php
function rtvEntDesc($entCode, $datval = NULL) {
	global $connzend;
	
	return "GENERICO";
}
// Ricerca utente su AS400 con società di riferimento
function rtvUserAS400($name, $datval = NULL) {
	global $db, $AS400_tabella_utenti, $connzend;
	
	$datiUtente ['NOME'] = $_SESSION['user'];
	$datiUtente ['DESCRIZIONE'] = "UTENTE WI400";
	$datiUtente ['CODICE'] = "*NONE";
	
	$datiUtente ['DES_COD'] = "GENERICO";
	
	return $datiUtente;
}
function rtvArtEan($articolo, $datval) {
	global $db, $connzend;
	
	return "METODO NON IMPLEMENTATO";
}
// Ricerca utente su AS400 con società di riferimento
function rtvUserDB($name, $datval = NULL) {
	global $db, $AS400_tabella_utenti, $connzend;

	$datiUtente ['NOME'] = $_SESSION['user'];
	$datiUtente ['DESCRIZIONE'] = "UTENTE WI400 DB";
	$datiUtente ['CODICE'] = "*NONE";

	$datiUtente ['DES_COD'] = "GENERICO DB";

	return $datiUtente;
}
// Ricerca utente su LDAP per decodifica
function rtvUserLDAP($name, $datval = NULL) {
	global $db, $AS400_tabella_utenti, $connzend;
	
	$datiUtente ['NOME'] = $_SESSION['user'];
	$datiUtente ['DESCRIZIONE'] = "UTENTE WI400";
	$datiUtente ['CODICE'] = "*NONE";
	
	$datiUtente ['DES_COD'] = "GENERICO";
		
	return $datiUtente;
}
// Ricerca la società legata all'utente collegato
function rtvUserSoc($name) {
	global $db, $AS400_tabella_utenti;

	return "GENERICO";
}
function rtvArticoloImmagine($articolo) {
	global $AS400_articoli_immagini, $db;
	$sql = "select * from " . $AS400_articoli_immagini . " where codice='" . strtoupper ( $articolo ) . "'";
	$result = $db->singleQuery ( $sql );
	$row = $db->fetch_array ( $result );
	// Se non trovo ricerco l'immagine di default
	if (! $row) {
		$sql = "select * from " . $AS400_articoli_immagini . " where codice='DEFAULT'";
		$result = $db->singleQuery ( $sql );
		$row = $db->fetch_array ( $result );
	}
	return $row;
}
?>