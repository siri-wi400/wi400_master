<?php
if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	/*$unique_id = uniqid("AJP_", true);
	$filename = wi400File::getUserFile("ajax_post", $unique_id.".post");
	$handle = fopen($filename, "w");
	fwrite($handle, serialize($_POST));
	fclose($handle);*/
	$dati = base64_decode($_POST['BASE64'], True);
	if ($dati===False) {
		die("ERRORE BASE64 DECODE");
	}
	$do = parse_str($dati, $datiPost);
	$unique_id = serializeAndGetUinqueID($datiPost);
} else {
	die("not Ajax Request");
}