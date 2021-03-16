--TEST--
SETTINGS - download_dir
--FILE--
<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
$download_dirs = array();
if (isset($settings['download_dir']))  {
	$download_dirs = $settings['download_dir'];
}
foreach ($download_dirs as $key => $value) {
	if (file_exists($value)) {
	} else {
	    die("DATA PATH NOT FOUND");
	}
}
echo "OK";
?>
--EXPECT--
OK