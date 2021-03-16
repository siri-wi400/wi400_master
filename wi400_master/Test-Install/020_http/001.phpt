--TEST--
HTTP Test Connessione a WEBSERVER
--SKIPIF--
<?php include('./skip_http.inc'); ?>
--FILE--
<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
$port = $settings['http_port'];
$ip = $settings['http_server'];
$url="http://$ip:$port/$appBase/Test-Install/020_http/http_001.php";
write_test_log("Http richiamo url:".$url);
$homepage = file_get_contents($url);
write_test_log("Http risposta:".$homepage);
if (!isset($homepage)) die("NO");
if (trim($homepage)!="TESTHTTP01") die("NO");
echo "OK";
?>
--EXPECT--
OK