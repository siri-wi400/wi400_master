--TEST--
DB Recupero Struttura TESTINST
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
$struct = $db->columns("TESTINST", "", True, "","PHPTEMP");
print_r($struct);
?>
--EXPECT--
Array
(
    [0] => UNO
    [1] => DUE
)
