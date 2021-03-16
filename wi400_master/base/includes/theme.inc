<?
	$css = new wi400Bundle("css");

	$css->add("/themes/common/css/common.css");
	
	if (isIE()){
		$css->add("/themes/common/css/common-ie.css");
	} 
	
	$css->add("/themes/common/css/windows.css");
	$css->add("/themes/common/css/switch.css");
	$css->add("/themes/common/css/font-awesome.min.css");
	$css->add("/themes/".$settings['temaDefault']."/css/wi400.css");
	$css->add("/themes/common/css/yav-style.css");
	$css->add("/themes/common/css/calendar/jscal2.css");
	$css->add("/themes/common/css/contextmenu.css");
	$css->add("/themes/common/css/grid.css");
	$css->add("/themes/common/css/checkswitch.css");
	$css->add("/routine/jquery/css/checknet.css");
	$css->add("/routine/jquery/css/BreadCrumb.css");
	$css->add("/routine/jquery/keyboard/css/keyboard.css");	
//	$css->add("/routine/jquery/contextMenu/jquery.contextMenu.css");	
	
	$css->dispose();
	
?>
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="icon" href="/favicon.ico" />
