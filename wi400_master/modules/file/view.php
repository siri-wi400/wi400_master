<?php
if (isset($_GET['ID'])) {
	$id = filter_input(INPUT_GET, 'ID', FILTER_SANITIZE_STRING);
	$file_name = get_file_from_id($id);
}
if (isset($_GET['FILE_NAME'])) {
	$file_name = filter_input(INPUT_GET, 'FILE_NAME', FILTER_SANITIZE_STRING);
}
if ($actionContext->getForm() == "COMMON"){
	$filename = wi400File::getCommonFile($_GET['CONTEST'], $file_name);
}
else{
	if(isset($_GET['CONTEST']) && $_GET['CONTEST']!="")
		$filename = wi400File::getUserFile($_GET['CONTEST'], $file_name);
	else 
		$filename = $file_name;
}
if (!isset($_GET['APPLICATION'])) {
	$file_parts = pathinfo($filename);
	$fileType = $file_parts['extension'];
	$_GET['APPLICATION']=$fileType;
}
//echo "FILENAME: $filename<br>";
//ob_end_clean();						// ????? non sempre funziona (dipende da versione di php?)

header("Content-Type: application/octet-stream");
header("Content-type: application/".$_GET['APPLICATION']);
readfile($filename);
?>