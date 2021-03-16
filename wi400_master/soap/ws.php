<?php 
$name = explode('/',$_SERVER['REQUEST_URI']);
if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
	$server = $_SERVER['HTTP_X_FORWARDED_HOST'];
	//$server = substr($server,0,strlen($server)-3);
	$path = $server;
} else {
	$server = $_SERVER['SERVER_NAME'];
	$path = $server.":".$_SERVER['SERVER_PORT'];
}
$tot = count($name)-1;
for($i=0; $i<$tot;$i++) {
	$path .= $name[$i]."/";
}
?>
<html>
<head>
</head>
<frameset rows="300,*">
	<frame name="search" src="http://<?php echo $path ?>wsclient.php">
	<frame name="xmlresult" src="">
</frameset>
</html>