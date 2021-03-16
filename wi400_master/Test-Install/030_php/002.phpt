--TEST--
PHP.INI Verifica max_execution_time > 300
--FILE--
<?php
$value = ini_get("max_execution_time");
if ($value > 300 || $value==0) die("OK");	
echo "KO";
?>
--EXPECT--
OK
