--TEST--
SETTINGS - sess_path Test Scrittura
--FILE--
<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
$dir = $settings['sess_path']."/TESTINST01";
rmdir($dir);
$rs = mkdir($dir, 777, true);
if (!$rs) die("KO");
// Scrittura di un file
$file = $dir."/TESTINST01.TXT";
$h = fopen($file, "w+");
$do = fwrite($h, "PROVATESTINST");
if (!$do) die("KO");
fclose($h);
unlink($file);
rmdir($dir);
echo "OK";
?>
--EXPECT--
OK