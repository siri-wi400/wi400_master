--TEST--
SETTINGS - doc_root
--FILE--
<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
if (file_exists($settings['doc_root'])) {
	echo "OK";
} else {
    echo "DATA PATH NOT FOUND";
}
?>
--EXPECT--
OK