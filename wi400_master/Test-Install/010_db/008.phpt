--TEST--
DB Inserimento 100 Record in TESTINST
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
$fieldArt = $db->columns("TESTINST", "", False, "","PHPTEMP");
$stmtArt = $db->prepare("INSERT", "PHPTEMP".$settings['db_separator']."TESTINST", null, array_keys($fieldArt));
if (!$stmtArt) die("KO");
	for($i=1; $i<=100; $i++) {
		$fieldTes['UNO']=$i;
		$fieldTes['DUE']=$i;
		$result = $db->execute($stmtArt, $fieldTes);
		//if (!$result) die("KO");
	}
echo "OK";
?>
--EXPECT--
OK
