<?php
ob_end_clean();
//$id = $_GET['ID'];
$id=$batchContext->fileId;
$file = get_file_from_id($id);
$fileType = get_file_type("", $file);
//echo $id;

//$fullPath = $_GET['df'];

$fullPath = $file;
$downloadfile = basename($fullPath);
//echo $file;
//die();
if (file_exists($fullPath)){
	header("content-type: text/$fileType"); 
	header("content-disposition: attachment; filename=$downloadfile"); 
	readfile($fullPath);
}else{
	echo "File non trovato!";
}
unset($_SESSION);
exit();
?>                                                                                                                                                                                                                  