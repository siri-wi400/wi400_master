--TEST--
PHP.INI modulo imagemgick
--FILE--
<?php
if (!extension_loaded('imagick'))
    die('imagick not installed');
echo "OK";
?>
--EXPECT--
OK
