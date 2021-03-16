<?php
$fullPath = "";
$fileId   = $_GET['FILE_ID'];
$contest = $_GET['CONTEST'];
if (isset($_SESSION[$contest."_".$fileId])){
	$fullPath = $_SESSION[$contest."_".$fileId];
}

if ($fullPath != "" && file_exists($fullPath)){
	if ($fd = fopen ($fullPath, "r")) {
	    $fsize = filesize($fullPath);
	    $path_parts = pathinfo($fullPath);
	    $ext = strtolower($path_parts["extension"]); 
	    switch ($ext) {
	        case "pdf":
	        header("Content-type: application/pdf"); // add here more headers for diff. extensions
	        header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachement' to force a download
	        break;
	        default:
	        header("Content-type: application/octet-stream");
	        header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\"");
	    }
	    header("Content-length: $fsize");
	    header("Cache-control: private"); //use this to open files directly
	    while(!feof($fd)) {
	        $buffer = fread($fd, 2048);
	        echo $buffer;
	    }
	}
	fclose ($fd);
}else{
	echo "FILE NOT FOUND!";
	exit;
}
exit;
?>