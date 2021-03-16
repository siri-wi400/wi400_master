<?php
 
global $appBase;

echo "<script type=\"text/JavaScript\" src=\"".$appBase."modules/utilities/js/zoom_pan.js\"></script>";

echo "<div style=\"position:relative;width:".$_REQUEST['IMGW'].";height:".$_REQUEST['IMGH']."\">";
echo "<div style=\"position:absolute\">";

if (strnpos($_REQUEST['FILE_NAME'], "http:") !== false){
	echo "<img name='myimage' id='myimage' src='".$_REQUEST['FILE_NAME']."' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";
}else{
//	echo "<img name='myimage' id='myimage' src='".$appBase."index.php?DECORATION=clean&t=FILEDWN&CONTEST=tmp&FILE_NAME=".$_REQUEST['FILE_NAME']."' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";
	$link = create_file_download_link($_REQUEST['FILE_NAME'], "tmp");
	echo "<img name='myimage' id='myimage' src='".$link."' style='background=#FFFFFF;border: 1px solid #CCCCCC'>";
}
echo "</div></div>";

?>