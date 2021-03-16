<?php
/**
 * Questo script viene utilizzato dalla chiamate Ajax che hanno necessita di salvare o reperire i dati della sessione.
 * Per un esempio di chiamata vedere read_bizerba.php.
 */
require_once "conf/wi400.conf.php";
require_once "routine/generali/sessions.php";
$name = explode('/',$_SERVER['REQUEST_URI']);
$appBase = "/".$name[1]."/";
$name = explode('/',$_SERVER['REQUEST_URI']);
$path = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$name[1];
if(!file_exists($path)) {
	// may be this is an alias
	$path_root ="/";
} else {
	$path_root =$appBase;
}
$doc_root     = $settings['doc_root'];
$data_path    = $settings['data_path'];
$doc_root = substr($doc_root,0, strlen($doc_root)-1);
$conf_path    = $doc_root.$path_root."conf";
$root_path    = $doc_root.$path_root;
$moduli_path  = $doc_root.$path_root."modules";
$base_path    = $doc_root.$path_root."base";
$routine_path = $doc_root.$path_root."routine";
$main_page    = $doc_root.$path_root."index.php";
$themes_path  = $doc_root.$path_root."themes";