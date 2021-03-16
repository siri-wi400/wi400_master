<?php
global $appBase,$root_path,$wi400Debug,$time_start,$time_step,$temaDir;
global $data_path,$actionLabel,$buttonsBar,$tab_index, $architettura, $db_separator;
global $dbUser, $dbPath, $db, $settings, $messageContext, $INTERNALKEY, $CONTROLKEY;

require_once "../../conf/wi400.conf.php";
$wsparm = parse_ini_file('settings.ini');
// Verifico esistenza directory
if (isset($wsparm['save_path'])) {
   if (!file_exists($wsparm['save_path'])) {
         wi400_mkdir($wsparm['save_path'], 0777, True);   
   }
}
if (isset($wsparm['save_file'])) {
   if (!file_exists($wsparm['save_file'])) {
         wi400_mkdir($wsparm['save_file'], 0777, True);   
   }
}
$name = explode('/',$_SERVER['REQUEST_URI']);
$appBase = "/".$name[1]."/";
// PERCORSI
$doc_root     = $settings['doc_root'];
$data_path    = $settings['data_path'];
$doc_root = substr($doc_root,0, strlen($doc_root)-1);
$conf_path    = $doc_root.$appBase."conf";
$root_path    = $doc_root.$appBase;
$moduli_path  = $doc_root.$appBase."modules";
$base_path    = $doc_root.$appBase."base";
$routine_path = $doc_root.$appBase."routine";
$main_page    = $doc_root.$appBase."index.php";
$themes_path  = $doc_root.$appBase."themes";
$p13n_path    = "p13n/".$settings['p13n']."/";
$settings['p13n_path']= $p13n_path;
if ($settings['architettura']=="") $settings['architettura'] = 'default';
if ($settings['package']=="") $settings['package'] = 'default';
if (!isset($settings['caching_type']) || $settings['caching_type']=="") $settings['caching_type'] = 'default';	
$settings['package']=strtolower($settings['package']);
// Configurazione
require_once $routine_path."/database/".$settings['database'].".cls.php";
//require_once $routine_path."/classi/wi400Routine.cls.php";
if (isset($settings['xmlservice'])) {
	require_once $routine_path.'/classi/wi400RoutineXML.cls.php';
	require_once $routine_path."/generali/xmlsupport.php";		
} else {
    require_once $routine_path.'/classi/wi400Routine.cls.php';
}
require_once $routine_path.'/classi/wi400Messages.cls.php';
$messageContext = new wi400Messages();
require_once $routine_path."/generali/common.php";
require_once $routine_path."/generali/wi400File.php";
require_once $base_path."/arch/default.php";
require_once $base_path."/arch/".strtolower($settings['architettura']).".php";
require_once $base_path."/package/".strtolower($settings['package'])."/".strtolower($settings['package']).".php";
require_once $base_path.'/caching/'.strtolower($settings['caching_type']).'.php';
date_default_timezone_set($settings['timezone']);
$string = 'architettura_'.strtolower($settings['architettura']);
$architettura = new $string();
// **************************************************
// Connessione al sottisistema ZEND per le chiamate native su AS400
// **************************************************
include_once $routine_path.'/classi/wi400Connect.cls.php';
$db_user='PHPWEBSRVS';
$db_pwd='PHPWEBSRVS';
//unset($settings['xmlservice']);
//$settings['i5_toolkit']='internal';
$CONTROLKEY = ' *wait(6000) *call(6000) *sbmjob(ZENDSVR/ZSVR_JOBD/PHPWSORD)';
$INTERNALKEY = '/tmp/'.uniqid("WS_");
$mycon = "";
if (isset($settings['i5_toolit'])) {
	$mycon = new i5_connect($settings['server_zend_ip'], $settings['db_user'], $settings['db_pwd'], "T");
}
$db = new $settings['database'] ();
$db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name']);

require_once 'ws_otm_functions.php';			
?>