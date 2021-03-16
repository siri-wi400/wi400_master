<!DOCTYPE HTML>
<html lang="it">
<head>
	<meta charset="utf-8">
	<title><?= $settings['window_title']  ?></title>
	
	<!-- Apple iOS and Android stuff -->
	<meta name="apple-mobile-web-app-capable" content="no">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<link rel="apple-touch-icon-precomposed" href="apple-touch-icon-precomposed.png">
	
	<!-- Apple iOS and Android stuff - don't remove! -->
	<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no,maximum-scale=1">
	<link rel="stylesheet" type="text/css" href="routine/jquery/css/jquery-ui.min.css">

<?php 
	require_once $base_path."/includes/theme.inc";
	require_once $base_path."/includes/javascript.inc";
	/*if (isset($_GET['ID_FILE']) && $_GET['ID_FILE']!="") {
			/*echo "<br>prima";			
			echo "<pre>";
			print_r($_REQUEST);
			echo "</pre>";*/
			//$filename = wi400File::getUserFile("ajax_post", $_GET['ID_FILE'].".post");
			//$custom_data = unserialize(file_get_contents($filename));
			//$_REQUEST = array_merge($_REQUEST, $custom_data);
			/*echo "<br>dopo";
			echo "<pre>";
			print_r($_REQUEST);
			echo "</pre>";
	}*/
	if(isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) && $_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) {
		echo '<link rel="stylesheet" type="text/css" href="themes/common/css/menu_tablet.css">';
	}
?>
<!--[if lt IE 9]>
	<!--<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>-->
<!-- [endif]-->
<?
	if ($actionContext->getLabel() !== false){
?>
	<script>
<?
	if ($actionContext->getLabel() != ""){	
?>	
	jQuery(document).ready(function(){
		//top.blockBrowser(false);
		wi400_topblock(false);
		jQuery(".ui-dialog-title:last", window.parent.document).html("<?= $actionContext->getLabel(); ?>");
	});
<?
	}
	if ($actionContext->getTimer() > 0){
		?>
											<div style="display:inline;float:right;margin-top:6px;margin-right:4px;"><script>page_timer = true;page_timer_state = false;</script><input id="page_TIMER_IMG" class="wi400-pointer" type="image" title="(<?= $actionContext->getTimer()?> sec.)" onClick="timerPause('page', 'RESUBMIT')" src="themes/common/images/grid/grid_timer.gif"></div>
		<?
	}
?>
	if(self.parent.location.href != window.location.href) {
		function openWindow(url, name, w, h, modale, canClose, checkSubmit, closeFunction, bigData) {
			self.parent.openWindow(url, name, w, h, modale, canClose, checkSubmit, closeFunction, bigData);
		}
	}
	</script>
	<?
		}
	?>
<script>
	wi400_topblock(false);
	//top.blockBrowser(false);
</script>	
</head>
<body onload="wi400Init();showMessages();">
<form name="wi400Form" id="wi400Form" method="POST" onSubmit="return false">
<!-- Non togliere questo input! Altrimenti si rallentano tutti i lookup (solo su chrome) -->
<input type="text" style='position: absolute; width: 0px; height: 0px; background: transparent; border: 0px;'/> 
<div id="lookup_content" class="body-area">
<div id="wi400_info_box"><?echo _t("ATTENDERE_CARICAMENTO")?></div>
<div id="wi400_modify_box"></div>
<div id="wi400_msg_box"></div>
<input id="DECORATION" name="DECORATION" type="hidden" value="lookUp">
<input type="hidden" id="CURRENT_ACTION" name="CURRENT_ACTION" value="<?= $actionContext->getAction() ?>">
<input type="hidden" id="CURRENT_FORM" 	 name="CURRENT_FORM"   value="<?= $actionContext->getForm() ?>">
<input id="LOOKUP_PARENT" name="LOOKUP_PARENT" type="hidden" value="<? if (isset($_REQUEST["LOOKUP_PARENT"])) echo $_REQUEST["LOOKUP_PARENT"]; ?>">
<input id="WI400_IS_IFRAME" name="WI400_IS_IFRAME" type="hidden" value="<? if (isset($_REQUEST["WI400_IS_IFRAME"])) echo $_REQUEST["WI400_IS_IFRAME"]; ?>">
<input id="WI400_IS_WINDOW" name="WI400_IS_WINDOW" type="hidden" value="<? if(isset($_REQUEST["WI400_IS_WINDOW"])) echo $_REQUEST["WI400_IS_WINDOW"]; ?>">
<?
	include_once $base_path."/includes/messagesContainer.php";
	
	if (isset($_REQUEST['WI400_IS_IFRAME'])) {
		if ($history->getEntry() > 0) {
		?>
	<div id="page2Title" ><? $history->dispose(True); ?></div>
 <?php 	
		}
	}
?>