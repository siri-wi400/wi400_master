--TEST--
HTTP Test richiamo Azione WI400 utente?
--SKIPIF--
<?php include('./skip_http.inc'); 
require_once dirname(dirname(__FILE__))."/function_test.php"; 
if (!isset($settings['http_user']) || $settings['http_user']=="") die("skip HTTP configurare 'http_user'");
?>
--FILE--
<?php
require_once dirname(dirname(__FILE__))."/function_test.php"; 
loader();
$db = new $settings['database'] ();
$db->set($settings['db_host'], $settings['db_user'], $settings['db_pwd'], $settings['db_name'], $settings['db_conn_type'], false, $settings['db_log']);
$db->connect(True);
if ($db->make_connection()) {
 //
} else {
 die("NO");
}
$settings = getWi400_settings();
$port = $settings['http_port'];
$ip = $settings['http_server'];
$utente = $settings['http_user'];
$otm = new wi400Otm();
unset($settings['delay_library_list']);
$db->add_to_librarylist($librerie, True);
$key = $otm->getOtmPassword($utente, "TEXT", "", "");
$url="http://$ip:$port/$appBase/index.php?OTM=$key&t=AJAX_POST";
write_test_log("Http richiamo url:".$url);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, TRUE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$homepage = curl_exec($ch);
if (strpos($homepage, "/index.php?t=AJAX_POST")!==False) die("OK");
echo "NO";
?>
--EXPECT--
OK
