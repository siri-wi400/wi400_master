--TEST--
PHP.INI Verifica max_input_vars > 5000
--FILE--
<?php
$value = ini_get("max_input_vars");
if ($value > 5000) die("OK");
echo "KO";	
?>
--EXPECT--
OK
