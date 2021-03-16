<object
id="MATN5250"
classid="CLSID:A6A216EB-4F7C-11D5-8438-0000B456BA3D"
codebase="modules/telnetActiveX/matn5250.cab#version=2,0,0,0" align="center" border="0" width="100%" height="100%">

<!-- See the README.TXT file included with the product
for a description of parameters to the program.
-->

<param name="localsave" value="0">
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
echo '<param name="host_name" value="'.$server.'">';
?>
<param name="licname" value="">
<param name="lickey" value="">
<param name="port" value="23">
<param name="ssh" value="0">
<param name="devicename" value="">
<param name="autoconnect" value="1">
<param name="security_connect" value="1">
<param name="auto_login" value="0">
<param name="auto_user" value="">
<param name="auto_password" value="">
<param name="termtype" value="0">
<param name="keys" value="modules/telnetActiveX/KEYS.TXT">
<param name="ignore_fer" value="1">
<param name="ebcdic" value="modules/telnetActiveX/EBCDIC_US.TXT">
<param name="color_bg" value="0">
<param name="color_cursor" value="8421504">
<param name="color_select" value="32896">
<param name="color_red" value="255">
<param name="color_blue" value="16711680">
<param name="color_pink" value="16711935">
<param name="color_green" value="65280">
<param name="color_turquoise" value="16776960">
<param name="color_yellow" value="65535">
<param name="color_white" value="16777215">
<param name="font_weight" value="0">
<param name="font_italic" value="0">
<param name="term_size" value="80">
<param name="font_name" value="Courier New">
<param name="pfontsize" value="-17">
<param name="pfont_weight" value="0">
<param name="pfont_italic" value="0">
<param name="pfont_name" value="Courier New">
<param name="use_cv" value="0">
<param name="proxy_host" value="">
<param name="proxy_enable" value="0">
<param name="proxy_port" value="1080">
<param name="proxy_syntax" value="$1 $2\n\r">
<param name="proxy_socks_enable" value="1">
<param name="proxy_return" value="\015\012">
<param name="blink_cursor" value="0">
<param name="cursortype" value="0">
<param name="typeahead" value="1">
<param name="negotiate_display" value="0">
<param name="tablepaste" value="0">
<param name="bell" value="1">
<param name="hotspots" value="1">
<param name="swap_del" value="0">
<param name="message_bell" value="1">
<param name="charset" value="0">
<param name="pcharset" value="0">
<param name="keep_alive" value="1">
</object>