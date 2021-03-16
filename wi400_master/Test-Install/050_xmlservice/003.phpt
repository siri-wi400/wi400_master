--TEST--
XMLSERVICE Test Lancio comando CHGJOB e RTVJOBA
--SKIPIF--
<?php include('./skip_xmlservice.inc'); ?>
--FILE--
<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
loader();
$db = new $settings['database'] ();
$db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name'], $settings['db_conn_type'], false, $settings['db_log']);
$db->connect(True);
if ($db->make_connection()) {
 //
} else {
 die("NO");
}
// Verifica Richiamo COMANDI
$stringa="TSTINSTALL01";
write_test_log("Cambio PRTTXT:".$stringa);
$result = executeCommand("chgjob",array('prttxt'=>$stringa),array());
if (!$result) die("NO");
$result = executeCommand("rtvjoba",array(),array("prttxt" => "prttxt"));
if (!$result) die("NO");
if (!isset($prttxt)) die("NO");
write_test_log("PRTTXT Ritornato:".$prttxt);
if (trim($prttxt)!=$stringa) die("NO");
echo "OK";
?>
--EXPECT--
OK
