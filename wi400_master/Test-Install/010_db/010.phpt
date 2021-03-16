--TEST--
DB Aggiornamento Record in TESTINST
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
// Verifica QUERY  	
//$struct = $db->columns("TESTINST", "", True, "","PHPTEMP");
$keyUpdt = array("UNO" => "1");
$field = array("UNO");
$fieldsValue = array('UNO' => "UNO");
$stmt_updt = $db->prepare("UPDATE", "PHPTEMP".$settings['db_separator']."TESTINST", $keyUpdt, $field);
if (!$stmt_updt) die("KO");
$res_updt = $db->execute($stmt_updt, $fieldsValue);
if (!$res_updt) die("KO");
echo "OK";
?>
--EXPECT--
OK
