<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title><?= $actionContext->getLabel() ?></title>
	<?php 
		require_once $base_path."/includes/theme.inc";
		//require_once $base_path."/includes/javascript.inc";
	?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<link rel="stylesheet" href="routine/jqueryMobile/themes/custom-theme-<?=$settings["temaDefault"]?>.css" />
  	<link rel="stylesheet" href="routine/jqueryMobile/themes/jquery.mobile.icons.min.css" />
  	
	<!-- <link rel="stylesheet" href="routine/jqueryMobile/jquery.mobile.external-png-1.4.5.min.css" />
	<link rel="stylesheet" href="routine/jqueryMobile/jquery.mobile.icons-1.4.5.min.css" />
	<link rel="stylesheet" href="routine/jqueryMobile/jquery.mobile.inline-png-1.4.5.min.css" />
	<link rel="stylesheet" href="routine/jqueryMobile/jquery.mobile.inline-svg-1.4.5.min.css" />
	-->
	<!-- <link rel="stylesheet" href="<?=$appBase?>themes/common/css/font-awesome.min.css" />-->
	<link rel="stylesheet" href="routine/jqueryMobile/jquery.mobile.structure-1.4.5.min.css" />
	<!--<link rel="stylesheet" href="routine/jqueryMobile/jquery.mobile-1.4.5.min.css" />-->
	<script src="routine/jquery/jquery-1.11.1.min.js"></script> 
	<script src="routine/jqueryMobile/jquery.mobile-1.4.5.min.js"></script>

</head>
<body>
	