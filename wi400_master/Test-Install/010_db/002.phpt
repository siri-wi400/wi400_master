--TEST--
DB Controllo QUERY con file AZIONI
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
$sql = "SELECT * FROM ".$settings['db_name'].$settings['db_separator']."FAZISIRI";
$result = $db->query($sql);
if (!$result) {
	die("NO");
}
$row = $db->fetch_array($result);
if (!$row) die("NO");
write_test_log($row['AZIONE']);
echo "OK";
?>
--EXPECT--
OK