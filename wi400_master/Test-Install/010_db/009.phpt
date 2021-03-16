--TEST--
DB Conteggio Record in TESTINST
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
$sql = "SELECT COUNT(*) AS COUNTER FROM PHPTEMP".$settings['db_separator']."TESTINST";
$result = $db->query($sql);
if (!$result) {
		die("NO");
}
$row = $db->fetch_array($result);
if (!$row) die("NO");
if ($row['COUNTER']!="100") die("KO");
echo "OK";
?>
--EXPECT--
OK
