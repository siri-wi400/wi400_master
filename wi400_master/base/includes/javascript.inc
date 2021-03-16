<?php
    $mylang = strtolower($language);
	$yav_config='yav-config-'.$mylang.'.js';
	
	$javascript = new wi400Bundle("javascript");
	$javascript->add("/base/js/wi400-constants.js");
	$javascript->add("/base/js/wi400-core.js");
	//$javascript->add("/base/js/html5.js");
	$javascript->add("/base/js/wi400-windows.js");
	$javascript->add("/base/js/yav/yav.js");
	$javascript->add("/base/js/jscalendar/jscal2.js");
	$javascript->add("/base/js/jscalendar/lang/it.js");
	$javascript->add("/base/js/wi400-contextmenu.js");
	$javascript->add("/modules/tree/js/treeMenu.js");
	$javascript->add("/modules/list/js/wi400-grid.js");
	$javascript->add("/modules/tree/js/wi400-tree.js");
	$javascript->add("/modules/file/js/wi400-file.js");
	$javascript->add("/base/js/wi400-functions.js");
	$javascript->add("/base/js/wi400-switch.js");
	$javascript->add("/routine/jquery/jquery-1.9.1.js");
	$javascript->add("/routine/jquery/jquery.blockUI.js");
	$javascript->add("/routine/jquery/jquery-ui.min.js");
	$javascript->add("/routine/jquery/jquery.sorttable.js");
	$javascript->add("/routine/jquery/jquery-ui-i18n.js");
	$javascript->add("/base/js/wi400-shortcut.js");
	$javascript->add("/routine/jquery/jquery.scrollTo.js");
	$javascript->add("/routine/jquery/jquery.checknet-1.6.min.js");
	$javascript->add("/routine/jquery/jquery.jBreadCrumb.1.1.js");
	$javascript->add("/routine/jquery/jquery.ui.touch-punch.min.js");
	$javascript->add("/routine/jquery/keyboard/js/jquery.keyboard.js");	
	//$javascript->add("/routine/jquery/jquery.ferro.ferroMenu-1.2.3.min.js");
	$javascript->add("/routine/jquery/jquery.floatThead.js");
	//$javascript->add("/routine/jquery/colResizable-1.3.source.js");
	//$javascript->add("/routine/jquery/customResizable.js");
	$javascript->add("/base/js/checkswitch.js");
	$javascript->add("/base/js/searchAction.js");
	$shortcut = p13n("modules/shortcut/shortcut.js");
	if ($shortcut!==False) {
		$shortcut = "/".str_replace($root_path, "", $shortcut);
		$javascript->add($shortcut);
	}
	$javascript->dispose();
	if (!isset($currentSESSION)) {
		$currentSESSION="";
	}
?>

<script type="text/javascript">
	var _APP_BASE = "<?= $appBase ?>";
	var _THEME_DIR = "<?= $temaDir ?>";
	var _BLOCK_BROWSER = false;
	var _BLOCK_BROWSER_MAP = new wi400Map();
	var rules = new Array();
	var _DECIMAL_SEPARATOR = "<?= $settings['decimal_separator']?>";
	var _THOUSAND_SEPARATOR = "<?= $settings['thousand_separator'] ?>";
	var _DATE_FORMAT = "<?= $settings['date_format'] ?>";	
	var _USER_LANGUAGE = "<?= $language ?>";
	var _USER_LANG_2 = "<?= $language_2 ?>";
	var _ADD = "<?= _t("ADD") ?>";
	var _REMOVE = "<?= _t("REMOVE") ?>";
	
	var LAST_FOCUSED_FIELD = "";
	var FOCUSED_FIELD = "";
	var FOCUSED_TAB ="";
	var REFRESH_FOCUS = false;
	var __WI400_HMAC = "<?= $currentHMAC; ?>";
	var __WI400_SESSION = "<?= $currentSESSION; ?>";	
	var CURRENT_ACTION = "<?= $actionContext->getAction(); ?>";
	var CURRENT_FORM = "<?= $actionContext->getForm(); ?>";
	var LEFT_MENU_OPEN = "<?= $leftMenuOpen ?>";
	var LEFT_MENU_ROWS = new Array(<?= $settings['leftMenuRows'] ?>);
	var WIDTH_BREAD_CRUMB = <?= $settings['width_breadCrumb']?>;
	var IS_MOBILE = "<?= isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) ? $_SESSION['NAVIGAZIONE_TABLET_ATTIVA'] : "" ?>";
    // JQUERY No Conflict con Prototype
	$.noConflict();
	jQuery.ajaxSetup({
		beforeSend: function (request)
		{
		    request.setRequestHeader("WI400_HMAC", __WI400_HMAC);
		    request.setRequestHeader("WI400_SESSION", __WI400_SESSION);
			try {
		    	request.setRequestHeader("WI400_CHECKID", decodeURIComponent(this.url).length);
			}catch(e) {
				//
			}
		}
	});
	var wi400top = getWI400Top();
	//var wi400top = self;
<?
	if ($actionContext->getTimer() > 0){
?>
	window.onload = function() {
		timerStart("page", <?= $actionContext->getTimer() ?>, "RESUBMIT");
	}
<?
	}
?>
</script>
<?php 
if (isset($_SESSION['user'])) {
	if (isDeveloper()) {
		// Pulizia Array Developer Runtime Field
		$skip_action = array("DEVELOPER_DOC", "ROUTINE_VIEWER");
		$skip_list = array("ROUTINE_VIEWER_LIST");
		if((isset($_GET['t']) && !in_array($_GET['t'], $skip_action)) || (isset($_GET['IDLIST']) && !in_array($_GET['IDLIST'], $skip_list))) {
			$_SESSION['DEVELOPER_DOC_INCLUDED'] = get_included_files();
			// VERIFICARE
			//$_SESSION['DEVELOPER_RUNTIME_FIELD']=array();
		}
		?>
			<script type="text/javascript">
			shortcut.add('CTRL+ALT+D',function() { 
				openDeveloperWindow();	
			});
			function openDeveloperWindow() {
				w = 1200;
				h = 600;
				openWindow(_APP_BASE + "index.php?t=DEVELOPER_DOC" + "&ACTION=<?php echo $actionContext->getAction();?>" + "&DECORATION=lookup&g=ESCAPE&f=DETAIL", "detailSave", w, h);
			}
			</script>
			<?php 
		}
	}
?>
<script src="<?= $appBase  ?>base/js/yav/<?=$yav_config?>" type="text/javascript"></script>