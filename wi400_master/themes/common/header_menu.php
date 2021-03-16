<tr>
	<td valign="top" class="top-area" style="position: relative; height: <?= (isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA']) ? "45px" : "33px") ?>;">
		 <?php 
		    if (isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA'])) {
		    	if ($actionContext->getAction()!="MENU_TABLET") {
			    	$myButton = new wi400InputButton($_SESSION['NAVIGAZIONE_TABLET_NEXT']);
			    	//$menu = rtvMenu ($_SESSION['NAVIGAZIONE_TABLET_NEXT']);
			    	$previsou="";
			    	if (isset($_GET['NAVIGAZIONE_TABLET_NEXT']) && isset($_SESSION[$_GET['NAVIGAZIONE_TABLET_NEXT']])) {
			    		$previous = $_SESSION[$_GET['NAVIGAZIONE_TABLET_NEXT']];
			    	}
			    	if(!$history->getFromModel()) {
						$doTabletAction = 'MENU_TABLET&LEFT_MENU_STATUS=close&PREVIOUS='.$_SESSION['NAVIGAZIONE_TABLET_PREVIOUS'].'&NEXT='.$_SESSION['NAVIGAZIONE_TABLET_NEXT'].'&HISTORYCLEAN=S';
				    	$history->addTabletAction($doTabletAction);
			    	}
		    	} else {
		    		$history->delete();
		    	}
			}
		    ?>
		<div id="pageTitle" style="margin-left:5px"><? $history->dispose(); ?></div>
		<div class="wi400-top-menu" style="top: 0px; right: 15px; width: 220px; position: absolute; text-align: right; height: 34px;">
	<?php 	if(isset($settings['scale_enable']) && $settings['scale_enable'] && isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA'])) {
				echo '<div style="width: 24px; height: 24px; display: inline;">
						<img id="icon_scale_zoom" onClick="openWindow(\'index.php?t=MODIFICA_ZOOM&f=\')" style="position: relative; top: 3px; cursor: pointer;" src="themes/common/images/zoom.png">
					</div>';
			}
			if(isset($settings['widget']) && $settings['widget']) {
				$opacity = (isset($_SESSION['WIDGET_ENABLE']) && $_SESSION['WIDGET_ENABLE']) ? "" : "opacity: 0.3;";
				echo '<div style="position: relative; width: 24px; height: 24px; display: inline;" title="Widget">
						<img id="icon_menu_widget" onClick="widgetMenu('.((isset($_SESSION['WIDGET_ENABLE']) && $_SESSION['WIDGET_ENABLE']) ? 0 : 1).')" style="position: relative; top: 4px; cursor: pointer; width: 22px; height: 22px; '.$opacity.'" src="themes/common/images/gear.png">
			    		<div class="cont-circle-widget"><i class="fa fa-exclamation" aria-hidden="true" style="color: #ededed;"></i></div>
					</div>';
			}
	 		if(isset($settings['messages_enable']) && $settings['messages_enable']) {
				echo '<div style="width: 24px; height: 24px; display: inline;">
						<img id="icon_announce_mess" style="position: relative; top: 5px; cursor: pointer;" src="themes/common/images/message.png">';
			}else {
				echo '<div style="width: 24px; height: 24px; display: inline-block;">';
			}
	?>
			</div>
	<?php if(isset($settings['messages_enable']) && $settings['messages_enable']) { ?>
			<script>
				jQuery('#icon_announce_mess').load(function() {
					var that = jQuery(this);
					that.unbind( "load");				

					that.bind("click", function() {
						//console.log(typeof(nuovo_mess));
						if(typeof(nuovo_mess) != "undefined") {
							clearInterval(nuovo_mess);
						}
						that.attr("src", "themes/common/images/message.png");
						openWindow(_APP_BASE + APP_SCRIPT + '?t=ANNOUNCE&f=ACTION&DECORATION=lookUp&ALL_MESS=1' + jQuery('#'+APP_FORM).serialize(), 'buttonAction', '800', '600', true, true, false);
					});
		<?php 
					$sql = "SELECT notusr FROM zmsgnot WHERE notusr='{$_SESSION['user']}'";
					$rs = $db->singleQuery($sql);
					if($rowlog = $db->fetch_array($rs)) { ?>
						nuovo_mess = setInterval(function() {
							if(that.attr("src") == "themes/common/images/new_message.png") {
								that.attr("src", "themes/common/images/message.png");
							}else {
								that.attr("src", "themes/common/images/new_message.png");
							}
						}, 1000);
		<?php		}	?>
					
				});
			</script>
		<?}
				if ($actionContext->getTimer() > 0){
			?>
					<div class="wi400-top-menu-cell" style="display: inline"><script>page_timer = true;page_timer_state = false;</script><input id="page_TIMER_IMG" class="wi400-pointer" type="image" title="(<?= $actionContext->getTimer()?> sec.)" onClick="timerPause('page', 'RESUBMIT')" src="themes/common/images/grid/grid_timer.gif"></div>
			<?
				}
				if (isset($_SESSION['XMLSERVICE_DEBUG'])) {
					$img_debug = "images/debug_not_active.png";
					if($_SESSION["XMLSERVICE_DEBUG_ACTIVE"]) {
						$img_debug = "images/debug_active.png";
					}
					
			?>
					<div style="display: inline" class="wi400-top-menu-cell">
						<img id="icon_debug" border="0" width="18" height="18" style="position: relative; top: 2px; cursor: pointer;" onClick="set_enable_debug('<?= $_SESSION['XMLSERVICE_DEBUG_ACTIVE']?>');" src="<?=  $temaDir.$img_debug ?>">
					</div>
				
				<!-- <td class="wi400-top-menu-cell"><a title="Home" href="javascript:doSubmit('','&LCK_DLT=true', false, true)"><img border="0" src="<?=  $temaDir ?>images/home.png"></a></td> -->
			<? 	}
				
				if ($actionContext->getHelp("url") && $actionContext->getHelp("url") != ""){
					//$tipoApertura = 'popup:top=10, left=10, width=450, height=600, status=no, menubar=no, toolbar=no scrollbars=no';
					//$tipoApertura = "_blank";
					//$tipoApertura = "_parent";
					$tipoApertura = "wi400";
					if (isset($settings['help_tipo_apertura'])) {
						$tipoApertura = $settings['help_tipo_apertura'];
					}
					?>
					<div style="display: inline" class="wi400-top-menu-cell">
						<a title="Help Online" style="cursor:help" href="javascript:openUrlHelp('<?= $actionContext->getHelp("url") ?>',<?= $actionContext->getHelp("width") ?>,<?= $actionContext->getHelp("height") ?>, '<?= $tipoApertura ?>')">
							<img border="0" src="<?=  $temaDir ?>images/help.png">
						</a>
					</div>
			<?	}?>
				
				<? if ($isMenuAction){ ?>
					<div class="wi400-top-menu-cell" style="display: inline"><span title="<?=_t('BOOKMARKS_ADD')?>" onClick="doPreferiti('ADD','preferitiDiv')" style="cursor:pointer"><img border="0" src="themes/common/images/preferiti.png" /></span></div>
				<? } 
				$display_print = 'none';
				if(isset($settings['header_menu_print']) && $settings['header_menu_print']) {
					$display_print = 'inline';
				}?>
				<div class="wi400-top-menu-cell" style="display: <?=$display_print?>"><a title="<?=_t('PAGE_PRINT')?>" href="javascript:window.print()"><img border="0" src="<?=  $temaDir ?>images/grid/printer.gif"></a></div>
				<div class="wi400-top-menu-cell" style="display: inline"><a title="Logout" onClick="logout()" href="javascript:doSubmit('<?= $_SESSION["LOGOUT_ACTION"] ?>','DELETE', false, true)"><img border="0" src="<?=  $temaDir ?>images/exit.png"></a></div>
		</div>
	</td>
</tr>