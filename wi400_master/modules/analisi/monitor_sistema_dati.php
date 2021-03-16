<?php 
$dati = file_get_contents("/www/zendsvr/perfomance.data");
echo json_encode(unserialize($dati));
?>