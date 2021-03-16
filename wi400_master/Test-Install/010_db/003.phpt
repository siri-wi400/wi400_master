--TEST--
DB Controllo Librerie db_lib_list
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
$librerie = explode(";", $settings['db_lib_list']);
foreach ($librerie as $key) {
    if ($key!="") {
	$sql = "SELECT * FROM SCHEMATA WHERE SCHEMA_NAME='$key'";
	$result = $db->query($sql);
	if (!$result) {
		die("NO $key");
	}
	$row = $db->fetch_array($result);
	if (!$row && $value!="QTEMP") die("NO $key");
	}
}
echo "OK";
?>
--EXPECT--
OK
