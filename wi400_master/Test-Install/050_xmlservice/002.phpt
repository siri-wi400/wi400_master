--TEST--
XMLSERVICE controllo xmlservice_jobd
--SKIPIF--
<?php include('./skip_xmlservice.inc'); ?>
--FILE--
<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
	if (!isset($settings['xmlservice_jobd_lib']) || $settings['xmlservice_jobd_lib']=="") {
		$settings['xmlservice_jobd_lib']="ZENDSVR";
		if (isset($settings['zend_server_version'])) {
			switch ($settings['zend_server_version']) {
				case '6':
					$settings['xmlservice_jobd_lib']="ZENDSVR6";
					break;
				case '9':
					$settings['xmlservice_jobd_lib']="ZENDPHP7";
					break;
				default:
					$settings['xmlservice_jobd_lib']="ZENDSVR";
					break;		
			}
		}
	}
	if (!isset($settings['xmlservice_jobd']) || $settings['xmlservice_jobd']=="") {
		$settings['xmlservice_jobd']="ZSVR_JOBD";
	}

$file = "/QSYS.LIB/".$settings['xmlservice_jobd_lib'].".LIB/".$settings['xmlservice_jobd'].".JOBD";
if (file_exists($file)) {
	echo "OK";
} else {
    echo "JOBD NON TROVATA";
}
?>
--EXPECT--
OK