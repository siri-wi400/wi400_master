<?php
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	// Carico la struttura del dettaglio
	$idDetail = $_REQUEST['RELOAD_ID_DETAIL'];
	$detail = wi400Session::load(wi400Session::$_TYPE_DETAIL, $idDetail."_STRUCT");
	$action = $detail->getReloadAction();
	$form = $detail->getReloadForm();
	// Eseguo l'Azione per il ricaricamento dei dati
	require_once $routine_path."/generali/encryption.php";
	$do = wi400_runAction($action, $form, "", $_REQUEST);
	// Catturo l'HTML del DISPOSE per ritornalo al chiamante
	$detail->reloadSession();
	ob_get_clean();
	ob_start();
	// Faccio il dispose del detail con parametro per AJAX (no extra DIV)
	$detail->dispose(True);
	$htmlOut = ob_get_clean();
	// Restarto l'ob e butto fuori l'output che avevo salvato prima di partire con l'azione forzata
	ob_start();
} else {
	die("not Ajax Request");
}