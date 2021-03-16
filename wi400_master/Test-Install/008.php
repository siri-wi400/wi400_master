<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
$package = 	$base_path."/package/".strtolower($settings['package'])."/".strtolower($settings['package']).".php";
if (file_exists($package)) {
	echo "OK";
} else {
    echo "DATA PATH NOT FOUND";
}
?>
