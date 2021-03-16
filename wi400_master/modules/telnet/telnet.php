<APPLET CODE="tn5250.class" ARCHIVE="modules/telnet/tn5250.jar"  WIDTH="100%" HEIGHT="100%">
<param name=type value="applet">
<param name=keyboard_bar value="false">
<param name=page_color value="#F3F3F3">
<param name=color_bg value="#000000">
<param name=keyfile value="modules/telnet/keys52">
<param name=ebcdic_file value="modules/telnet/ebcdic52.ita">
<?php 
$server = $settings['server_zend_ip'];
if ($server=='localhost') {
	if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
		$server = $_SERVER['HTTP_X_FORWARDED_HOST'];
		$server = substr($server,0,strlen($server)-3);
	} else {
		$server = $_SERVER['SERVER_NAME'];
	}
}
echo '<param name="host" value="'.$server.'">';
?>
</APPLET>