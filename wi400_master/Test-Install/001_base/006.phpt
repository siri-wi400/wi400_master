--TEST--
SETTINGS - template_path
--FILE--
<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
if (file_exists($settings['template_path'])) {
	echo "OK";
} else {
    echo "DATA PATH NOT FOUND";
}
?>
--EXPECT--
OK