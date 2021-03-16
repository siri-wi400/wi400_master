<?php
$value = ini_get("max_execution_time");
if ($value> 300) die("OK");	
echo "KO";
?>
