<?php
/**
 * Ricarico la pagine superiore se sono dentro un iframe
 */
function reload_top_page() {
	?>
	<script>
	window.top.location.reload();
	</script>
	<?php	
}
/**
 * @desc Chiusura finestra/lookup e ricaricamento azione sottostante
 * @param string $history
 */
function close_window($history=true) {
?>
	<script>
		closeWindow(<?= $history==false ? '' : "true"?>);
	</script>
<?php 
}
/**
 * @desc Chiusura finestra bloccante
 */
function close_block_window() {
?>
	<script>
		if (IFRAME_LOOKUP){
			wi400_topblock(true);
			top.location.href=top.location.href;
			//top.f_dialogClose();
			closeLookUp();
		}
		else{
			blockBrowser(true);
			window.opener.location.href=window.opener.location.href;
			//self.close();
			closeLookUp();
		}
	</script>
<?		
}	
/**
 * @desc Setta un tasto/combinazione associato ad una azione
 * @param string $key: tasto/combinazione da associare
 * @param string $styleAction: ID associato all'azione
 * @param string $prefix: Tasto associato alla pressione di CTRL o ALT
 */
function setKeyAction($key, $styleAction, $prefix=""){

	if ($prefix =="") {
		?>
	<script>
		setKeyAction("<?= $key ?>", "<?= $styleAction ?>");
	</script>
	<?php
	} 
	if ($prefix =="CTRL") {
	?>
	<script>
		setCtrlKeyAction("<?= $key ?>", "<?= $styleAction ?>");
	</script>
	<?php 
	}
	if ($prefix =="ALT") {
		?>
		<script>
			setAltKeyAction("<?= $key ?>", "<?= $styleAction ?>");
		</script>
	<?php 
	}
}
/**
 * @desc Rimoazione di un tasto/combinazione
 * @param unknown $key
 */
function removeKeyAction($key){
	?>
	<script>
		removeKeyAction("<?= $key ?>");
	</script>
	<?php 
}

/**
 * @desc Risottemette il form
 * @param string $form: Nome del form
 * @param string $action: Azione del form, se non passato viene prese quello di default
 */
function risottometti_form($form, $action=null) {
	?>
	<script>
		risottomettiForm("<?= $form ?>", "<?= $action ?>");
	</script>
<?
}
/**
 * @desc Ricarica una specifica azione chiudendo eventuali lookup presenti
 * @param string $action: Azione
 * @param string $form: Form dell'azione
 * @param string $idList: Codice Lista da ricaricare
 */
function reload_sel_action($action, $form, $idList=""){
	?>
	<script>
		reloadSelAction("<?= $action ?>", "<?= $form ?>", "<?= $idList ?>");
	</script>
<?
}

/**
 * @desc Chiusura del lookup
**/
function close_lookup() {
	?>
	<script>
		closeLookUp();
	</script>
<?php
}
/**
 * @desc Chiude il lookup e ricarica la finestra precedente
 * @param string $idList
 * @param string $reloadAction
 * @param string $search Per forzare il ricaricamento delle finestra precedente
 */
function close_lookup_reload_list($idList, $reloadAction, $search=False, $showProgress=false){
	if ($search == False) {
		$previous = "null";
	} else {
		$previous = "true";
	}
?>
	<script>
		if (IFRAME_LOOKUP){
			top.doPagination("<?= $idList ?>", "<?= $reloadAction ?>", <?= $previous ?>);
			<?if($showProgress) {?>
				top.openProgressBar("<?=$idList?>");
<?			}?>
		}
		else{
			window.opener.doPagination("<?= $idList ?>", "<?= $reloadAction ?>");
			<?if($showProgress) {?>
				window.opener.openProgressBar("<?=$idList?>");
<?			}?>
		}
		closeLookUp();
	</script>
<?php	
}
/**
 * @desc updateRecord() Funzione Javascript per chiamare via AJAX l'aggiornamento della riga
 * @param string $azione: Codice Azione
 * @param string $form: Nome del form da richiamare
 * @param string $lista: Nome della lista 
 **/
function updateRecordJs($azione, $form, $lista) {
?>
<script type="text/javascript">
function updateRecord(questo, parametro){
	var chiave = get_column_key(questo);//reperisco nrrelativco record
	var valoreCampo = jQuery( "#"+ questo.id).val();//contentuto del campo in input
	// Reperisco gli identificativi del campo
	var dati = questo.id.split("-");
	var lista = dati[0];
	var riga = dati[1];
	var campo = dati[2];
	//alert ("ID LISTA:"+dati[0] + "\r\nRiga:"+dati[1]+"\r\nCampo:"+dati[2]+"\r\nDati"+articolo);
	// Aggiornamento della riga appena selezionata
	jQuery.ajax({
		type: "POST",
		async: false,
		url: _APP_BASE + APP_SCRIPT + "?t=<?= $azione ?>&f=<?= $form ?>&DECORATION=clean&IDLIST=<?= $lista ?>&KEY_SUBFILE=" + chiave +"&PARAMETRO="+parametro+"&VALORE="+valoreCampo,
		data: jQuery("#" + APP_FORM).serialize()
	}).done(function ( response ) {
		var myjson=response.substring(response.lastIndexOf("REPLY:")+6,response.lastIndexOf(":END-REPLY"));
		var decodeJSON = jQuery.parseJSON(myjson);
		if (decodeJSON.result=='OK') {
			// Ricarica Pagina
			if (decodeJSON.action=='RELOAD') {
				window.top.location.reload();
			}
			return true;
		} else {
			window.top.location.reload();
		}
	}).fail(function ( data ) {
		alert("Errore aggiornamento Dati")
	});
}
</script>
<?php 
}
/**
 * @desc reloadCurrentRowJs() Ricarica la riga corrente selezionata dalla lista
 */ 
function reloadCurrentRowJs($idList, $row="") {
	?>
	<script type="text/javascript">
	 var currentrow = '<?= $row ?>';
	 if (row == "") currentrow = <?= $idList?>_RRN;
	 reloadCurrentRow('<?= $idList ?>', '<?=$row?>>')
	 function reloadCurrentRow(idList, row) {
		var nome =  idList + "-"+row+"-checkbox";
        var questo = jQuery("#"+nome);
        updatListRow(questo, "");
    }  
	 </script>
	<?
}
/**
 * @desc updateListRowJs() Funzione Javascript per chiamare via AJAX l'aggiornamento di una riga della lista
 **/
function updateListRowJs($async=True, $idList = null) {
	$textasync="true";
	if ($async == False) {
		$textasync = "false";
    }
?>
<script type="text/javascript">
    function updateListRowTimeout(questo, parametro,backGround, isrow) {
        var _that=questo;
        setTimeout(function () {
    	    updateListRow(questo, parametro,backGround, isrow);
    	  }, 150);
    }    
	function updateListRow(questo, parametro, backGround, isrow){
		//jQuery(document).ajaxStop(jQuery.unblockUI);
		//jQuery.blockUI();
		if (typeof(isrow)=="undefined") {
			isrow=false;
		}
		if (typeof(backGround)=="undefined") {
				backGround=false;
		}	
		//alert(backGround);
		if (isrow==false) {
			var chiave = get_column_key(questo);//reperisco nrrelativco record
			var valoreCampo = jQuery("#"+ questo.id).val();//contentuto del campo in input
			// Reperisco gli identificativi del campo
			var dati = questo.id.split("-");
			var lista = dati[0];
			var riga = dati[1];
			var campo = dati[2];
		} else {
			var chiave = "";
			var valoreCampo = "";
			// Reperisco gli identificativi del campo
			var dati = questo.id.split("-");
			var lista = dati[0];
			var riga = dati[1];
			var campo = "";
		}
		if (backGround!=true) {
			blockBrowser(true, "", lista);
		}
		// Recupero il tabindex più vicino
		var tabindex = 99999;
		var tabind = 0;
		var tabinda = "";
		// Colonne
		var myrow = jQuery('#'+lista+"-"+riga+"-tr");
		jQuery('input', myrow).each(function() {
			      tabinda = jQuery(this).attr('tabindex');
			      if (typeof(tabind)!="undefined") {
				      tabind = parseInt(tabinda);
				      if (tabind < tabindex) tabindex = tabind;
			      }     
	 	});
	 	// Colonne Fisse
		var myrow = jQuery('#'+lista+"-Fixed-"+riga+"-tr");
		jQuery('input', myrow).each(function() {
			      tabinda = jQuery(this).attr('tabindex');
			      if (typeof(tabind)!="undefined") {
				      tabind = parseInt(tabinda);
				      if (tabind < tabindex) tabindex = tabind;
			      }     
	 	});
	 	
		// Reperisco la chiave di riga
		var key = jQuery("#"+lista+"-"+riga).val();
		key = encodeURIComponent(key);

		//Elimino nel form serialize IDLIST che viene passato nell'url
		// TEST SOLO RIGA
		// 		var string_serialize = jQuery("#" + lista + "-" + riga+ "-tr :input").serialize();
		var string_serialize = jQuery("#" + APP_FORM).serialize();
		var array_serialize = string_serialize.split("&");
		var array_request = [];
		for(var ele in array_serialize) {
			var arr = array_serialize[ele].split("=");
			array_request[arr[0]] = arr[1];
		}
		delete(array_request['IDLIST']);
		var string_serialize = "";
		for(var ele in array_request) {
			string_serialize += ele+"="+array_request[ele]+"&";
		}

		valoreCampo = encodeURIComponent(valoreCampo);
		parametro = encodeURIComponent(parametro);

		//alert ("ID LISTA:"+dati[0] + "\r\nRiga:"+dati[1]+"\r\nCampo:"+dati[2]+"\r\nDati"+articolo);
		// Aggiornamento della riga appena selezionata
		jQuery.ajax({  
			type: "POST",
			async: <?= $textasync;?>,
			url: _APP_BASE + APP_SCRIPT + "?t=AJAX_LIST_UPDATE&f=F2_AJAX_AGGIORNA&DECORATION=clean&IDLIST="+lista+"&KEY_SUBFILE=" + chiave +"&COLONNA="+campo+"&PARAMETRO="+parametro+"&VALORE="+valoreCampo+"&ROW_COUNTER="+riga,
			data: "TABINDEX="+tabindex+"&LIST_KEY="+key +"&"+string_serialize
			}).done(function ( response ) {
				var myjson=response.substring(response.lastIndexOf("REPLY:")+6,response.lastIndexOf(":END-REPLY"));
				var decodeJSON = jQuery.parseJSON(myjson);
				// Get Focused Field
				var focused = jQuery(':focus');				
				if (decodeJSON.outputHtmlRow!="*NONE" && backGround!=true) {
					jQuery("#"+lista+"-"+riga+"-tr").replaceWith(decodeJSON.outputHtmlRow);
					jQuery("#"+lista+"-Fixed-"+riga+"-tr").replaceWith(decodeJSON.fixedHtmlRow);
					//Cancello le regole doppie
					for(var i=yav.startFrom; i<rules.length; i++) {
						var posit = rules.indexOf(rules[i]);
						rules.splice(posit, 1);
						if(yav.startFrom)
							yav.startFrom--;
					}
					if (rules.length > 0){
						yav.init('wi400Form', rules);
					}
					// Verifico se il dettaglio è già aperto e presente, in quel caso lo tolgo
					if (jQuery("tr[id='"+lista+"-"+riga+"-detail']").length>1) {
						// Rimuovo il primo elemento ritornato dall'AJAX
						jQuery("tr[id='"+lista+"-"+riga+"-detail']").first().remove();
						// Verifico se già aperto per modificare icona +/-
						if (jQuery("tr[id='"+lista+"-"+riga+"-detail']").is(':visible')) {
							jQuery("#"+lista+"-"+riga+"-detail-img").attr('src', "themes/common/images/grid/collapse.png");
						}	
					}
					// Se era aperto il calendario lo rimetto
					var objClass = jQuery(questo).attr("class");
					if(objClass && objClass.indexOf('hasDatepicker') != -1) {
						setTimeout(function() {
							if(jQuery("#ui-datepicker-div").css("display") != "none") { 
								jQuery("#"+questo.id).datepicker("show");
							}
						}, 170);
					}
					if (typeof(bindUpdateListRowChange) == 'function') {
						var rowObj = jQuery("#"+lista+"-"+riga+"-tr");
						rowObj.on('click', 'img', imgFocus);
						rowObj.on('blur', 'input, select', blurRow);
						rowObj.on('focus', 'input, select', focusRow);
					}
					if(typeof(floatTheadTable) != "undefined") {
						setTimeout(function() { floatTheadTable.floatThead('reflow'); }, 100);
					}
				}
				if (backGround==false) {
					if (decodeJSON.action!="") {
						blockBrowser(false, "", lista);
						if (decodeJSON.action=='RELOAD') {
							window.top.location.href = window.top.location.href;
							return true;
						}
						if (decodeJSON.action=='RELOAD_LIST') {
							doPagination(lista, 'RELOAD');
							return true;
						}
						if (decodeJSON.action=='SUBMIT') {
							doSubmit(CURRENT_ACTION, CURRENT_FORM);
							return true;
						}
					}
					if (decodeJSON.action!="") {
						if (decodeJSON.action=='DELETEROW') {
							jQuery("#"+lista+"-"+riga+"-tr").hide();
							jQuery("#"+lista+"-Fixed-"+riga+"-tr").hide();
				  			blockBrowser(false, "", lista);
							return true;
						}
					}
					if (decodeJSON.message!="") {
						//jQuery.unblockUI();
						alert(decodeJSON.message);
					}
					//jQuery.unblockUI();
					resizeListRow(lista);
					// Focus
					if (focused.attr('id')!==questo.id) {
						jQuery("#"+focused.attr('id')).focus();
					} else {
						// Dovrei cercare di trovare il prossimo elemento ..
						jQuery("#"+questo.id).focus();
					}
					// Reinizializzo per il focus 		
				    jQuery('#'+ APP_FORM).find('input[type=text],textarea,select').filter(':not(:disabled):not([readonly])').each(function (index){
				    	var name = this.id;
				    		//jQuery(this).focus();
				    		//return false;
							jQuery(this).focus(function(){
				    	  	    jQuery(this).css({backgroundColor: '#ffffa5'});
							});
				  			jQuery(this).blur( function (e) {
					  	  	  jQuery(this).css({backgroundColor: ''});
					  	  	  e.stopPropagation();
					  	  	});
				    });
				}
				// Disabilito sempre il check
				setTimeout(function() {
					checkGridRow(lista, riga, false);
					jQuery("#"+focused.attr('id')).focus();
					jQuery("#"+focused.attr('id')).select();
		  			blockBrowser(false, "", lista);
					//resizeListRow(lista);
				}, 200);
				return true;
		  		}).fail(function ( data ) {
		  			jQuery.unblockUI; 
		  			blockBrowser(false); 
		  			// Se campbio pagina o vado su hisotry succede perchè si ricarica la pagina.
					//alert("Errore aggiornamento Dati");
				});
		return true; 		
	}
	</script>
<?php 
} 
/**
 * @desc updateListRowJs() Funzione Javascript per chiamare via AJAX l'aggiornamento di una riga della lista
 **/
function updateListRowJsChange($tableId, $async=True) {
	$textasync="true";
	if ($async == False) {
		$textasync = "false";
	}
	updateListRowJs($async, null);
	?>
	<script type="text/javascript">
		var delayedFn, blurredFrom;

		function blurRow(event) {
			var that = this;
			blurredFrom = event.delegateTarget;
			delayedFn = setTimeout(function() {
		       var obj = jQuery(that)[0];
		       if(obj.id.split("-")[2] == "checkbox") return;
		       updateListRow(obj, "", false);
		    }, 200);
		}
		function focusRow(event) {
			if (blurredFrom === event.delegateTarget) {
		        clearTimeout(delayedFn);
		    }
		}
		function imgFocus(event) {
			clearTimeout(delayedFn);
			var id = jQuery(this).attr("id");
			id = id.slice(0, -6);
			jQuery("#"+id).focus();
		}
					
		function bindUpdateListRowChange() {
			var righeTabella = jQuery('tr[id^="<?= $tableId?>"]');
			righeTabella.on('click', 'img', imgFocus);
			righeTabella.on('blur', 'input, select', blurRow);
			righeTabella.on('focus', 'input, select', focusRow);
		}
	</script>
<?php 
} 
/**
 * @desc removeListRowJs() Funzione Javascript per rimuovere una intera riga dalla lista
 **/
function removeListRowJs() {
?>
<script type="text/javascript">
	function removeListRow(questo, parametro){
		var chiave = get_column_key(questo);//reperisco nrrelativco record
		var valoreCampo = jQuery( "#"+ questo.id).val();//contentuto del campo in input
		// Reperisco gli identificativi del campo
		var dati = questo.id.split("-");
		var lista = dati[0];
		var riga = dati[1];
		var campo = dati[2];
		// Reperisco la chiave di riga
		var key = jQuery("#"+lista+"-"+riga).val();
		jQuery("#"+lista+"-"+riga+"-tr").hide();
		jQuery("#"+lista+"-Fixed-"+riga+"-tr").hide();
		return true; 		
	}
	</script>
<?php 
} 	
/**
 * @desc callBackRowJs() Funzione Javascript per chiamare via AJAX una azione
 **/
function callBackRowJs($async) {
	$textasync="true";
	if ($async == False) {
		$textasync = "false";
	}
	?>
<script type="text/javascript">
	function callBackRow(questo, azione, parametro){
		var chiave = get_column_key(questo);//reperisco nrrelativco record
		var valoreCampo = jQuery( "#"+ questo.id).val();//contentuto del campo in input
		// Reperisco gli identificativi del campo
		var dati = questo.id.split("-");
		var lista = dati[0];
		var riga = dati[1];
		var campo = dati[2];
		// Reperisco la chiave di riga
		var key = jQuery("#"+lista+"-"+riga).val();
		//alert ("ID LISTA:"+dati[0] + "\r\nRiga:"+dati[1]+"\r\nCampo:"+dati[2]+"\r\nDati"+articolo);
		// Aggiornamento della riga appena selezionata
		jQuery.ajax({  
			type: "POST",
			async: <?= $textasync;?>,
			url: _APP_BASE + APP_SCRIPT + "?t=" + azione + "&DECORATION=clean&IDLIST="+lista+"&KEY_SUBFILE=" + chiave +"&COLONNA="+campo+"&PARAMETRO="+parametro+"&VALORE="+valoreCampo+"&ROW_COUNTER="+riga,
			data: "LIST_KEY="+key +"&"+jQuery("#" + APP_FORM).serialize()
			}).done(function ( response ) {
				return true;
  		}).fail(function ( data ) {
  			// Se campbio pagina o vado su hisotry succede perchè si ricarica la pagina.
			//alert("Errore aggiornamento Dati");
		});
		return true; 		
	}
	</script>
<?php 
} 