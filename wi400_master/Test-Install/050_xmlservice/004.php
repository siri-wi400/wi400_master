<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
$settings = getWi400_settings();
loader();
// Richiede anche init
$dir = dirname(dirname(dirname(__FILE__)));
require_once $dir."/base/includes/init.php";
$db = new $settings['database'] ();
$db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name'], $settings['db_conn_type'], false, $settings['db_log']);
$db->connect(True);
if ($db->make_connection()) {
 //
} else {
 die("NO");
}
// Verifica Richiamo routine
write_test_log("Richiamo Routine ZDIAGMSG");
$result = executeCommand("rtvjoba",array(),array("job" => "job","user" => "user","nbr" => "nbr"));
// Aggiunta Librerie
unset($settings['delay_library_list']);
$db->add_to_librarylist($librerie, True);
$message ="PROVA01";
$pgm = new wi400Routine("ZDIAGMSG", $connzend);
$pgm->load_description();
$pgm->prepare();
$pgm->set('MESSAGE',$message);
$pgm->set('IP', "127.0.0.1");
$pgm->set('USER', "TESTINS");
$do = $pgm->call();
write_test_log("Esito chiamata:".$do);
if (!$do) die("NO");
// Verifico se effettivamente è stato scritto il log
$sql = "select * from table(qsys2.joblog_info('$nbr/$user/$job')) a order by ORDIN00001 desc";  
write_test_log("Verifico messaggi sul log:".$sql);
$result = $db->query($sql);
if (!$result) {
	die("NO");
}
$row = $db->fetch_array($result);
if (!$row) die("NO");
write_test_log("Messaggio del LOG:".$row['MESSAGE_TEXT']);
if ($row['MESSAGE_TEXT']!=$message) die("NO");
echo "OK";
?>
