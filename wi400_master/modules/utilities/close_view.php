<?php
	$form = $actionContext->getForm();
	
	if(in_array($form, array("CLOSE_WINDOW", "CLOSE_WINDOW_MSG"))) {
		if(isset($_REQUEST['WI400_IS_IFRAME'])) {
?>			<script>
				closeWindow(false, '<?= $_REQUEST['WI400_IS_IFRAME']?>');
			</script>
<?php 
		}else {
//			close_window();
			close_window($history);
		}
		
		if($form == "CLOSE_WINDOW_MSG") {
			die();
		}
	}else if($form=="CLOSE_LOOKUP") {
//		echo "CLEAN_DETAIL: ".$_REQUEST['CLEAN_DETAIL']."<br>";
		
//		$messageContext->removeMessages();
		
		close_lookup();
	} else if ($form=="RELOAD_PREVIOUS_WINDOW") {
			?>
			<script>
			iframes = wi400_getIFrames();
			//Filtro solo gli iframe con id 'lookup*_content'
			iframes = jQuery(iframes).filter(function (index) { 
			    if(this.id.indexOf('lookup') != -1 && this.id.indexOf('_content') != -1) return true;
			});
			if (iframes.length>1) {
				var i = iframes.length-1;
				reloadPreviousWindow("lookup"+i+"_content");
				closeLookUp();
			} else {
				closeWindow();
			}	
			</script>
			<?php
	}
	
/*	
	else if($actionContext->getForm()=="CLOSE_LOOKUP_MSG") {
		close_lookup();
		die();		// @todo NON FUNZIONA perchè serve il ricaricamento della pagina per far comparire il messaggio
	}	
	else if($actionContext->getForm()=="CLOSE_LOOKUP_RELOAD_LIST") {
		close_lookup_reload_list($idList, $reloadAction);
	}
	else if($actionContext->getForm()=="SEL_ACTION") {
		reload_sel_action($reloadAction, $reloadForm, $idList);
	}
*/