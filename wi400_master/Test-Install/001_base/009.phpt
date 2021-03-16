--TEST--
SETTINGS - Verifica File Architettura
--FILE--
<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
$package = 	$base_path."/arch/".strtolower($settings['architettura']).".php";
if (file_exists($package)) {
	echo "OK";
} else {
    echo "DATA PATH NOT FOUND";
}
?>
--EXPECT--
OK