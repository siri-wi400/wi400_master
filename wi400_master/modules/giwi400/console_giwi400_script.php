
<script>
	var stopAjax = false;

	function string_pad(obj, stringPad, left) {
		obj = jQuery(obj);
		var valore = obj.val();
		var maxlength = obj.attr('maxlength');
		if(left) {
			obj.val(valore.padStart(maxlength, stringPad));
		}else {
			obj.val(valore.padEnd(maxlength, stringPad));
		}
	}

	jQuery(document).keydown(function(e) {
		//console.log(e);
		if(e.altKey && e.ctrlKey && e.keyCode == 75) {
			openWindow(_APP_BASE + APP_SCRIPT + "?t="+CURRENT_ACTION+"&f=SYSTEM_BUTTON&DECORATION=lookUp", "giwi400Button", undefined, undefined, true, true);
		}
	});

	function openLookupGiwi400(idField) {
		//console.log(idField);

		jQuery('#'+idField).attr('class', 'i22 inputtext').val('?');

		submitPressButton("ENTER", "", true);
	} 

	function getChiaveWindowCookie(rs) {

		return rs.libreria+"_"+rs.file+"_"+rs.form;
	}

	function submitPressButton(button, indicatore, checkValidation, checkRetry) {
		//if (typeof(batch) == "undefined") batch = false;
		if (typeof(indicatore) == "undefined") indicatore = '';
		if (typeof(checkRetry) == "undefined") checkRetry = false;

		if (!retry && rules.length > 0 && !yav.performCheck('wi400Form', rules, "inline")){
			alert(yav_config.MODULE_ERROR);
			return;
		}

		blockBrowser(true); 

		if (wi400top.wi400_window_counter>0){
			mydoc2 = getFrameWindow("lookup" + wi400top.wi400_window_counter+"_content");
			mydoc = mydoc2.contentWindow.document;
		} else {
			mydoc = document;
		}

		//console.log(mydoc);
		var validation = '';
		if(checkValidation) validation = '&DETAIL_VALIDATION=si';

		var retry = '';
		if(checkRetry) retry = '&RETRY=si';
		
		jQuery.ajax({
			type: "POST",
			url: _APP_BASE + APP_SCRIPT + "?t=CONSOLE_GIWI400&f=AJAX_PRESS_BUTTON&DECORATION=clean",
			data: "&GIWI_BUTTON="+button+"&INDICATORE_BOTTONE="+indicatore+validation+retry+"&"+jQuery("#" + APP_FORM, mydoc).serialize()
		}).done(function (response) {

			blockBrowser(false);

			//if(batch) return;
			if(stopAjax) return;

			var myjson = response.substring(response.lastIndexOf("REPLY:")+6,response.lastIndexOf(":END-REPLY"));
			var rs = JSON.parse(myjson);
			//console.log(rs);
			var window_plus_key = getChiaveWindowCookie(rs);

			if(rs.success) {
				if(rs.target == 'close_refresh') {
					doSubmit("CLOSE","RELOAD_PREVIOUS_WINDOW", false, false, "", true);
				}else {
					if(rs.target != 'close') {
						if(rs.target == 'window') {
							//var closeFunction = "submitPressButton('F12', true);closeLookUp();";
							openWindow(_APP_BASE + APP_SCRIPT + "?t="+CURRENT_ACTION+"&f=DEFAULT&DECORATION=lookUp&GIWI400_WINDOW=si&WINDOW_SIZE_KEY="+window_plus_key, "giwi400", undefined, undefined, true, false);
						}else {
							doSubmit(CURRENT_ACTION,"DEFAULT", false, false, "", true);
						}
					}else {
						wi400top.doSubmit(CURRENT_ACTION,"DEFAULT", false, false, "", true);
					}
				}
			}else {
				if(rs.error) {
					if(rs.target == 'timeout') {
						if(confirm('Il sistema ha impiegato troppo tempo. Vuoi attendere ancora?') ) {
							submitPressButton(button, indicatore, false, true);
						}
					}else {
						if(rs.target) alert(rs.target);
						
						doSubmit(CURRENT_ACTION,"DEFAULT&HST_NAV=true", false, false, "", true);
					}
				}else {
					console.log('Errore ajax');
				}
			}
			//alert(response);
			//document.getElementById(div).innerHTML = response;
		}).fail(function (data) {
			blockBrowser(false);
			console.log('ERROR: '+data);
		});
	}

	jQuery(document).ready(function() {
		jQuery('#CONSOLE_GIWI400_NOME_PROGRAM_default input.wi400-pointer').click(function() {
			console.log("closeeee");
			saveOpenCloseDetailAjax(0);
		});
	
		jQuery('#CONSOLE_GIWI400_NOME_PROGRAM_opener').click(function() {
			console.log("opennnn");
			saveOpenCloseDetailAjax(1);
		});
	});

	function saveOpenCloseDetailAjax(open_close) {
		
		jQuery.ajax({
			type: "POST",
			url: _APP_BASE + APP_SCRIPT + "?t=CONSOLE_GIWI400&f=AJAX_SAVE_OPEN_CLOSE_DETAIL&DECORATION=clean&OPEN_CLOSE="+open_close,
		}).done(function (response) {
			//console.log(response);
		}).fail(function (data) {  
			console.log('ERROR: '+data);
		});	
	}
</script>