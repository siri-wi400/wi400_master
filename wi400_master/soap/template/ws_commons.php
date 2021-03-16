<?php
global $appBase,$root_path,$wi400Debug,$time_start,$time_step,$temaDir;
global $data_path,$actionLabel,$buttonsBar,$tab_index, $architettura, $db_separator;
global $dbUser, $dbPath, $db, $settings, $routine_path, $moduli_path, $base_path, $wi400_isWS, $messageContext;
global $doc_root;
global $users_table, $AS400_tabella_utenti;
// @todo parse_ini file per recuperare i sistemi informativi abilitati
$authSysInf = parse_ini_file("abilitazioni.php");
//$authSysInf = array("INDTESTNEW"=>1001, "IND"=>1002, "FORMAZIONE"=>1003, "INDFRPR"=>1004, "YINDTESTNW"=>1005);  
//require_once "../../conf/wi400.conf.php";
require_once "../../base/includes/getconfiguration.php";
$settings = wi400GetSettings("");
// Carico configurazione custom installat dal cliente sulla directory settings
/*if (is_file("../../../settings/wi400CustomerBase.conf.php")) {
	require_once "../../../settings/wi400CustomerBase.conf.php";
	$settings = array_merge($settings, $customerBaseSettings);
}
if (is_file("../../../settings/wi400Customer.conf.php")) {
		require_once "../../../settings/wi400Customer.conf.php";
		$settings = array_merge($settings, $customerSettings);
}*/
$wi400_isWs= True;
//$appBase = '/WI400_LZOVI/';
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
// DEFAULT
$save_XML = False;
$save_LOG = False;
$log_file = False;
$save_path = $settings['data_path']."wslog/";
$server_down = False;
$users_table = $settings['table_prefix']."USERS"; // users table
$AS400_tabella_utenti = "FTAB017";
$ws_timeout = 600;
if (isset($settings['ws_log_xml'])) $save_XML = $settings['ws_log_xml'];
if (isset($settings['ws_log_enable'])) $save_LOG = $settings['ws_log_enable'];
if (isset($settings['ws_log_file'])) $log_file = $settings['ws_log_file'];
if (isset($settings['ws_server_down'])) $server_down = $settings['ws_server_down'];
if (isset($settings['ws_timeout'])) $ws_timeout = $settings['ws_timeout'];
// Configurazione
require_once $routine_path."/database/".strtolower($settings['database']).".cls.php";
//unset($settings['i5_toolkit']);
//$settings['xmlservice']=True;
//require_once $routine_path."/classi/wi400Routine.cls.php";
//require_once $routine_path."/classi/wi400Routine.cls.php";
if (isset($settings['xmlservice'])) {
	require_once $routine_path.'/classi/wi400RoutineXML.cls.php';
	require_once $routine_path."/generali/xmlsupport.php";		
} else {
    require_once $routine_path.'/classi/wi400Routine.cls.php';
}
require_once $routine_path."/generali/common.php";
require_once $routine_path."/generali/dateTime.php";
require_once $routine_path."/generali/os400command.php";
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
// file di log
$log_path = $root_path;
if(isset($settings['log_root']) && $settings['log_root']!="") {
	$log_path = $settings['log_root'];

	if(strpos($log_path, "##APP_BASE##")!==false) {
		$log_path = str_replace("##APP_BASE##", $appBase, $log_path);
	}

	if(strpos($log_path, "##USER##")!==false) {
		$log_path = str_replace("##USER##", $_SESSION['user'], $log_path);
	}
}
$file_error_path = $log_path."logs/error/";
if(!file_exists($file_error_path)) {
	mkdir($file_error_path, 777, true);
}
$file_error_name = "php_error_".date("Ymd").".log";
ini_set("error_log", $file_error_path.$file_error_name);
//$mycon = new i5_connect($settings['server_zend_ip'], $settings['db_user'], $settings['db_pwd'], "P");
$mycon="*NONE";
$db = new $settings['database'] ();
//$db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name']);

function retriveHeaderMessage($state)
{
	    $message = '0';
	    switch ($state) {
		case '0':
			   $message="";
			   break;
		case '1':
			   $message="UNCORRECT PARAMETERS";
			   break;
		case '2':
			   $message="NO INFORMATION RETRIEVED";
			   break;
		case '3':
			   $message="SERVER DOWN";
			   break;				   				   
		case '4':
			   $message="MEMORY ALLOCATION ERROR";
			   break;
		case '5':
			   $message="CODE NOT FOUND";
			   break;
		case '6':
			   $message="ERROR IN INVOKING RPG SERVICE FUNCTION";
			   break;
		case '7':
			   $message="ENTITY AND/OR SEGMENT NOT FOUND";
			   break;
	    case '8':
			   $message="INPUT XML NOT VALID";
			   break;
	    case '9':
			   $message="GENERIC ERROR";
			   break;
	    case '10':
			   $message="XML DOES NOT CONTAIN VALID PARAMETERS";
			   break;
	    case '11':
			   $message="SERVER BUSY. TRY OPERATION LATER";
			   break;
	    case '12':
			   $message="SERVICE DISABLED. TRY OPERATION LATER";
			   break;					   
	    case '13':
			   $message="INFORMATIVE SYSTEM LOADING NOT POSSIBLE: CHECK PARAMETERS";
			   break;
	    case '14':
			   $message="INPUT DATA INCOMPLETE. CHECK NUMBER OF RECORDS";
			   break;
	    case '15':
			   $message="CHECK ERRORS AND DETAIL LEVEL";
			   break;
	    case '16':
			   $message="INFORMATIVE SYSTEM NOT ENABLED TO WS";
			   break;			   
	    case 'B':
			   $message="LOGIN ERRATO";
			   break;			   
	    default:
			   $message="UNKNOWN ERROR";
			   break;
		}
		return $message;
}		
function retriveSetMessage($state)
{
	    $message = '0';
	    switch ($state) {
		case '0':
			   $message="OK";
			   break;
		case '1':
			   $message="DATE NOT VALID";
			   break;
		case '2':
			   $message="STORE NOT VALID";
			   break;
		case '3':
			   $message="ITEM NOT VALID";
			   break;
	    case '4':
			   $message="WRITE ERROR";
			   break;
	    case '5':
			   $message=" EXCEEDS ADMITTED LENGTH";
			   break;					   				   
	    case '6':
			   $message=" NOT NUMERICAL FORMAT";
			   break;	
	    case '7':
			   $message=" NUMBER OF DECIMAL OR WHOLE FIGURES NOT VALID";
			   break;
	    case '8':
			   $message=" INCOMPLETE";
			   break;
	    case '9':
			   $message=" ELEMENT LENGTH NOT VALID";
			   break;
	   case '10':
	   			$message="SUPPLIER NOT VALID";
	   			break;			   
		case '11':
			   $message="EMPTY FLOW";
			   break;
		case '12':
			   $message="FLOW DOES NOT BEGIN WITH RECORD TYPE FA";
			   break;
	    case '13':
			   $message="DIFFERENT UNIVOCAL ID FOR A SAME DOCUMENT";
			   break;
	    case '14':
			   $message="RECORD 90 NOT FOUND RIGHT AFTER THE LAST INVOICE OF THE DOCUMENT";
			   break;					   				   
	    case '15':
			   $message="TOTAL OF DOCUMENTS DIFFERS FROM THE DOCUMENTS CONTAINED IN THE FLOW";
			   break;	
	    case '16':
			   $message="DOCUMENTO DOES NOT BEGIN WITH RECORD TYPE 10";
			   break;
	    case '17':
			   $message="RECORD TYPE 90 DOES NOT CONTAIN THE NUMBER OF DOCUMENTS";
			   break;
	    case '18':
			   $message="UNIVOCAL ID IS NOT NUMERICAL";
			   break;			   			   			   	
	    case '19':
			   $message="FILE NOT FOUND";
			   break;
	    case '20':
			   $message="RECORD TYPE 15 MISSING IN THE DOCUMENT";
			   break;			   			   			   	
	    case '21':
			   $message="RECORD TYPE 15 MISSING IN THE DOCUMENT";
			   break;			   
	    case '22':
			   $message="FLOW ALREADY PROCESSED";
			   break;	
		case '30':
			   $message="STORE NOT DEFINED IN MASTERDATA";
			   break;
	    case '31':
			   $message="STORE NOT ENABLED";
			   break;	
	    case '32':
			   $message="ITEM NOT DEFINED IN MASTERDATA";
			   break;
	    case '33':
			   $message="FUTURE DATE NOT ADMITTED";
			   break;			   	
	    case '34':
			   $message="DATE BELONGING TO A MONTH ALREADY CERTIFIED";
			   break;
	    case '35':
			   $message="FIELD QUANTITY IS EMPTY";
			   break;
	    case '36':
			   $message="FIELD QUANTITY IS ZERO";
			   break;
	    case '37':
			   $message="FIELD QUANTITY IS ZERO";
			   break;
	    case '38':
			   $message="'RICETTONE' (BIG RECIPE) NOT ADMITTED";
			   break;			   
	    case '39':
			   $message="RECORD ID ALREADY PROCESSED";
			   break;	
	    case '43':
	   		   $message="STOCK NOT UPDATED";
	   		   break;
	    case '44':
	   		   $message="RECIPE HEADER NOT FOUND";
	   		   break;
	   	case '45':
	   		   $message="RECIPE NOT FOUND IN ITEM MASTERDATA";
	   		   break;
	   	case '46':
	   		   $message="ELEMENT NOT PRESENT IN TABLE 0006";
	   		   break;
	   	case '47':
	   		   $message="DIVISOR IS ZERO ** RICPOR **";
	   		   break;
	    case '48':
	   		   $message="DIVISOR IS ZERO ** T06DIV **";
	   		   break;
   		case '49':
	   		   	$message="PRE INVOICE NOT FOUND";
   			   	break;
   		case '50':
   			   	$message="DELIVERY NOT VALID";
   			   	break;
   		case '51':
   			   	$message="NON TROVATO DOCUMENTO LEGATO AL BARCODE";
   		   		break;
   		case '52':
   			   	$message="BARCODE LEGATO A DOCUMENTO GIA' PROTOCOLLATO";
   			   	break;	   		   
	    default:
			   $message="UNKNOWN ERROR";
			   break;
		}
		return $message;
}		
?>
