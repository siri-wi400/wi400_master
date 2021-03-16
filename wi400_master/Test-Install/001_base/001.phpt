--TEST--
Verifica file di configurazione WI400
--FILE--
<?php
//$dir = dirname(dirname(dirname(__FILE__)));
//$dir = "/www/zendsvr/htdocs/WI400_LZOVI/";
require_once "/../config.dir";
if(!@include_once($dir."/conf/wi400.conf.php")) {
    echo "File di configurazione non trovato!";
} else {
	echo "FOUND";
}	
?>
--EXPECT--
FOUND
