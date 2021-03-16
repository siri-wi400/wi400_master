<?php
// Inizializzazione
$WI4_CHECK_VERIFY="OK";
$WI4_CHECK_MESSAGE=array();
$WI4_CHECK_VERSION="2.0";
$WI4_ACTION_NAME="CLEAN_SESSION";
// Parametrizzazioni
// AZIONI PREREQUISITE
/*$WI4_ACTION_NEED = array(
	'LOG_ERROR',
	'CHECK_PALLET',
	'MYTEST'
);*/
// File PHP 
/*$WI4_CHECK_FILES = array(
	'clean_session_model.php',
	'clean_session_view.php',
	'abcd.php' => array("path"=>"/www/zendsvr/htdocs/wi400/test/")	
);*/
// DIRECTORY -> Option
//  *OPZIONALE  CREATE=> True Se non esiste viene create
$WI4_CHECK_DIR = array(
		$settings['data_path']."/batch/ID" => array(
			'CREATE'=> True
		)
);
// SETTINGS
// *OPZIONALE TYPE=>OPTIONAL (Parametro opzionale, altrimenti deve esistere nel settings errore
$WI4_CHECK_SETTINGS = array(
		'export_list_batch'=> array("TYPE"=>'OPTIONAL'),
);
// FUNCTION
/*$WI4_CHECK_FUNCTIONS = array(
		'msg_queue_get',
		'msg_queue_exists'
);*/

// IBM i OBJECT
// OPZIONALE LIB= Libreria dove cercare l'oggetto
$WI4_CHECK_IBMI_OBJECT = array(
		'ZBATCHELA' => array("LIB"=>"*LIBL", "OBJTYPE"=>"*PGM"),
		'ZBATCHCC' => array("LIB"=>"*LIBL", "OBJTYPE"=>"*PGM")
);
// Tabelle
// OPZIONE STRUCT= Array con struttura campi, se esiste deve contenere tutti i campi dell'array
/*$WI4_CHECK_TABLE = array(
	'FAZISIRI' => array(
			'LIBL' => "PHPLIB",
			'CREATE'=> False,
			'ALTER' => True,
			'SRUCT'=> array(
				"CAMPO1" => array("TYPE"=>"CHAR","LENGHT"=>"30"),
				"CAMPO2" => array("TYPE"=>"CHAR","LENGHT"=>"20"),
				"URL" => array("TYPE"=>"CHAR","LENGHT"=>"30")
			)
	),	
		'FAZIPROVA' => array(
				'LIBL' => "PHPLIB",
				'CREATE'=> False,
				'ALTER' => True,
				'SRUCT'=> array(
						"CAMPO1" => array("TYPE"=>"CHAR","LENGHT"=>"30"),
						"CAMPO2" => array("TYPE"=>"CHAR","LENGHT"=>"20"),
						"URL" => array("TYPE"=>"CHAR","LENGHT"=>"30")
				)
		),
		'CAUSRILE' => array()
);*/
// Moduli PHP Abilitati
$WI4_CHECK_PHP_MODULES = array(
);
// Particolari Configurazioni o Release, da funzione particolare		
function wi400_custom_function_check($execute=False) {
	//
}


