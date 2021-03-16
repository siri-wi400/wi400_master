--TEST--
DB Drop TABELLA PHPTEMP/TESTINST
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
	// Faccio il DROP se per caso esiste
	$sql = "DROP TABLE PHPTEMP".$settings['db_separator']."TESTINST";
	$result = $db->query($sql);
	write_test_log("Query Drop:".$sql);
	if (!$result) {
		die("NO");
	}
echo "OK";
?>
--EXPECT--
OK
