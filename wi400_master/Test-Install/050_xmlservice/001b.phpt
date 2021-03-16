--TEST--
XMLSERVICE Controllo esistenza xmlservice_lib
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

	$key = $settings['xmlservice_lib'];
	$sql = "SELECT * FROM SCHEMATA WHERE SCHEMA_NAME='$key'";
	$result = $db->query($sql);
	if (!$result) {
		die("NO");
	}
	$row = $db->fetch_array($result);
	if (!$row) die("NO");


echo "OK";
?>
--EXPECT--
OK
