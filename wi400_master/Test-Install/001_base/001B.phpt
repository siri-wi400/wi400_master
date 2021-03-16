--TEST--
Verifica caricamento prerequisiti
--FILE--
<?php
	$basefunction = dirname(dirname(__FILE__))."/function_test.php"; 
	if(!@include_once($basefunction)) {
		echo "KO";
	}	
	echo "OK"
?>
--EXPECT--
OK
