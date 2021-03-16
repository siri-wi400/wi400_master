<?php
// Inizializzazione
$WI4_CHECK_VERIFY="OK";
$WI4_CHECK_MESSAGE=array();
$WI4_CHECK_VERSION="2.0";
$WI4_ACTION_NAME="TELNET_5250";
$WI400_CHECK_MESSAGE;
// Parametrizzazioni
// AZIONI PREREQUISITE
$WI4_ACTION_NEED = array(
	'AZIONI_ESTENSIONE_5250',
);
// File PHP 
/*$WI4_CHECK_FILES = array(
	'clean_session_model.php',
	'clean_session_view.php',
	'abcd.php' => array("path"=>"/www/zendsvr/htdocs/wi400/test/")	
);*/
// DIRECTORY -> Option
//  *OPZIONALE  CREATE=> True Se non esiste viene create
$WI4_CHECK_DIR = array(
		$routine_path."/conversion/" => True);
//);
// SETTINGS
// *OPZIONALE TYPE=>OPTIONAL (Parametro opzionale, altrimenti deve esistere nel settings errore
$WI4_CHECK_SETTINGS = array(
		'exit_point'=> array("TYPE"=>'TRUE'),
		
);

// FUNCTION
/*$WI4_CHECK_FUNCTIONS = array(
		'msg_queue_get',
		'msg_queue_exists'
);*/

// IBM i OBJECT
// OPZIONALE LIB= Libreria dove cercare l'oggetto
$WI4_CHECK_IBMI_OBJECT = array(
		'ACTIVIN' => array("LIB"=>"*LIBL", "OBJTYPE"=>"*PGM"),
		'ZGEBIDC' => array("LIB"=>"*LIBL", "OBJTYPE"=>"*PGM"),
		'ZGEBIDR' => array("LIB"=>"*LIBL", "OBJTYPE"=>"*PGM"),
		'TSTEXT' => array("LIB"=>"*LIBL", "OBJTYPE"=>"*PGM"),
		'ZCHKSBSA' => array("LIB"=>"*LIBL", "OBJTYPE"=>"*PGM")
);
// Tabelle
// OPZIONE STRUCT= Array con struttura campi, se esiste deve contenere tutti i campi dell'array
$WI4_CHECK_TABLE = array(
	'FAZI5250' => True,
	'ZOPNSESS' => True,	
	'ZEXIPOIN' => True,
	'ZOPNLOGS' => True	
);
// Moduli PHP Abilitati
$WI4_CHECK_PHP_MODULES = array(
);
// Particolari Configurazioni o Release, da funzione particolare		
function wi400_custom_function_check($execute=False) {

	$message = array();
	$browsecap = ini_get('browscap');
	if ($browsecap=="") {
		$message[]=wi400_check_message_format(False, "Browsecap non configurato");
	} else {
		if (!file_exists($browsecap)) {
			$message[]=wi400_check_message_format(False, "File configurazione Browsecap $browsecap non trovato");
		} else {
			$message[]=wi400_check_message_format(False, "Browsecap Abilitato");
		}
	}
	return $message;
}


