--TEST--
SETTINGS - sess_path permission 7x7
--FILE--
<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
$perm = getFilePermission($settings['sess_path']);
if (substr($perm, 0,1)=="7" && substr($perm, 0,1)=="7") die("OK");
echo "KO";
?>
--EXPECT--
OK