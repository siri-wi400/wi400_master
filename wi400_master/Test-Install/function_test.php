<?php
//$settings = getWi400_settings();
global $appBase,$root_path,$wi400Debug,$settings,$time_start,$time_step,$temaDir, $architettura;
global $viewContext,$gatewayContext,$menuContext,$messageContext,$actionContext,$breadCrumbs,$listContext,$lookUpContext;
global $pageDefaultDecoration,$showLoginForm,$show_footer,$show_header;
global $data_path,$actionLabel,$buttonsBar,$tab_index;
global $dbUser, $dbPath, $base_path, $CONTROLKEY, $INTERNALKEY;
global $history, $wi400Batch, $wi400Cli, $settings;

$dir = dirname(dirname(__FILE__));
require_once $dir."/base/includes/config.php";
require_once $dir."/base/includes/loader.php";
// Merge con parametri CUSTOM
require_once "config.inc";
$settings = array_merge( $settings, $test_settings);

function getWi400_settings() {
	/*static $settings;
	$dir = dirname(dirname(__FILE__));
	if(!@include_once($dir."/conf/wi400.conf.php")) {
	}
	return $settings;*/
	global $settings;
	return $settings;
}
function loader() {
	//$settings = getWi400_settings();
	//$dir = dirname(dirname(__FILE__));
	//require_once $dir."/base/includes/config.php";
	//require_once $dir."/base/includes/loader.php";
}
function write_test_log($dati) {
	static $handle;
	if (!isset($handle)) {
		$handle = fopen("/www/log_test_install.txt", "a+");
	}
	fwrite($handle, date("Y-m-d h:i:s ").$dati."\r\n");
}
function getFilePermission($file) {
	$length = strlen(decoct(fileperms($file)))-3;
	return substr(decoct(fileperms($file)),$length);
}