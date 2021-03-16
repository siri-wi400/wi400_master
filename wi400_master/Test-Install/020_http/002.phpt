--TEST--
HTTP Controllo apertura login WI400
--SKIPIF--
<?php include('./skip_http.inc'); ?>
--FILE--
<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
$port = $settings['http_port'];
$ip = $settings['http_server'];
$url="http://$ip:$port/$appBase/index.php";
write_test_log("Http richiamo url:".$url);
$homepage = file_get_contents($url);
if (strpos($homepage, "doSubmit('CHECK_LOGIN')")!==False) die("OK");
echo "NO";
?>
--EXPECT--
OK
