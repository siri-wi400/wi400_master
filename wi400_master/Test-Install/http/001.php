<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
$port = $settings['http_port'];
$url="http://127.0.0.1:$port/$appBase/Test-install/http/http_001.php";
write_test_log("Http richiamo url:".$url);
$homepage = file_get_contents($url);
write_test_log("Http risposta:".$homepage);
if (!isset($homepage)) die("NO");
if (trim($homepage)!="TESTHTTP01") die("NO");
echo "OK";
?>
