<?php     

/*
 * Esempio: 
http://10.0.50.1:89/WI400/batch/download5250.php?path=ZNC/1/&df=990.jpg

si potrebbe ricevere anche più foto.
per esempio separate da ;
poi con explode si possono mettere in array
con foreach si possono leggere
per ogni elemento visualizzare e andare a capo.
 */

$fullPath = "/www/zendsvr/htdocs/upload/";
$fullPath.= $_GET['path'];
//$fullPath.= $_GET['df'];
if (file_exists($fullPath)){
	header("Content-type: image/jpeg");
	readfile($fullPath);
}else{
	echo "File non trovato!";
}
exit();
                                                                     
?>                                                                                                                                                                                                                