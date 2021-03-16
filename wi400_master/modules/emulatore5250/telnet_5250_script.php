<script type="text/javascript">
<?php 
	echo "var colorCursore = '".($_SESSION['TEMA_5250']['cursor'])."';";
?>
var campoSelezionato="";
var relX=0;
var relY=0;
var numeroCaratteri=0;
var interval = [];
var insEnable = true;
var blockButton = false;
var viewScale = "";
var enableExport = true;

yav.addMask("alphabeticOnly", null, null, "^[A-Za-z\-,. \n\r\t]*$");
yav.addMask("numericOnly", null, null, "^[0-9\+,. \n\r\t]*$");

function onClickMonitor() {
	jQuery("#CLIENT_5250_DIV").click(function(e) {
		// Non devo farlo se il campo è di input e scrivibile
		
		var idInput = e.target.id;
		//console.log(idInput);
		if (idInput && jQuery('input[id='+idInput+']').filter(':not(:disabled):not([readonly])').length) {
			return;
		}
		
		campoSelezionato="";
		numeroCaratteri=0;
		//var offset = jQuery(this).offset();
		var offset = jQuery(this).parent().offset();
		//relX = e.pageX - (offset.left * viewScale);
        //relY = e.pageY - (offset.top * viewScale);
		relX = e.pageX - offset.left;
        relY = e.pageY - offset.top;
		//console.log(relX, relY);
        relX = Math.round((relX/(moltiplic*viewScale))+1);
        relY = Math.round(relY/(addX*viewScale));
        var relBoxCoords = "(" + relY + "," + relX + ")";
        jQuery('#debug').html(relBoxCoords);
	});
	jQuery("#CLIENT_5250_DIV input").not('#input_system_line').keyup(function(e) {
        getRelPosition(e.target.id);
	});
}
function getRelPosition(id) { 
	var input = jQuery("#"+id);
	var divParent = input.parent();

	var row = +divParent.attr("row");
	var col = +divParent.attr("column");
	/*var x = input.offset();
	relX = x.left - jQuery("#CLIENT_5250_DIV").offset().left;
    relY = x.top - jQuery("#CLIENT_5250_DIV").offset().top;
    relX = Math.round((relX/moltiplic)+1);
    relY = Math.round(relY/addX);*/
	var caretIndex = +input[0].selectionStart;
	/*var tbc = input.val().substring(0, caretIndex);
	relX=relX+tbc.length;*/
    var relBoxCoords = "(" + row + "," + (col+caretIndex) + ")";
    jQuery('#debug').html(relBoxCoords);
}
function startReadInterval() {
	if (typeof(disableAjax) != "undefined") return;

	var id  = setInterval(function() {
		jQuery.ajax({
			'type': "POST",	
			'url': _APP_BASE + APP_SCRIPT + "?t=TELNET_5250_AJAX&f=READ&DECORATION=clean&SESSION_ID="+session_display+"&CAMPO_SELEZIONATO="+campoSelezionato+"&CARATTERI="+numeroCaratteri+"&ROW="+relY+"&COL="+relX,
			'cache': false,
			'data': jQuery('#CLIENT_5250_DIV :input').serialize()	  
		}).success(function( html ) {

			json = jQuery.parseJSON(html.substring(html.indexOf("JSON:") + 5) );
			if(json['5250Complete'] == "*CANCEL") {
				if(typeof(numWindow) == "undefined") {
					closeSession5250();
				} else {
					closeSession5250(function() {
						eval("closeLookUp()");
						wi400top.jQuery('#lookup'+wi400_window_counter).remove();
					});
				}		
				//cambiaSchermata(json);
			}
			//console.log(json);
			if(json['5250html'] != "") {
				cambiaSchermata(json);
			}
			if(json['5250Extraction'] != "") {
				if(enableExport)  
					openWindow(_APP_BASE + APP_SCRIPT + "?t=TELNET_5250&f=DOWNLOAD_EXTRACTION&DECORATION=iframe&ID="+json['5250Extraction'], '5250Extraction', 700, 470);
				chiudiEstrazione(true);
			}
		}).error(function(jqXHR, textStatus){
			//x_system(false);
			//x_error(true);
			//alert('Errore read server. Riprovare l\'operazione');
		});
	}, 1000);
	interval.push(id);
}

function stopReadInterval() {
	for(var i=0;i<interval.length;i++) {
		clearInterval(interval[i]);
	}
	
	interval = [];
}

startReadInterval();

function focusedField(questo) {
	setTimeout(function(){
		questo.setSelectionRange(0, 0);
	}, 0);
	campoSelezionato = questo.id;
	numeroCaratteri = jQuery(questo).val().length;
	getRelPosition(questo.id);
}
function autoEnterField(event, questo, session_id) {
	//Controllo che siano caratteri
	if(event.key.length > 1) return;

	var caretIndex = questo.selectionStart;

	if(caretIndex == questo.maxLength) {
		questo.onchange();
		doFunctionKey(questo, session_id, "F1");
	}
}
function keyDownField(questo) {
	campoSelezionato = questo.id;
	numeroCaratteri = jQuery(questo).val().length;
}

function checkTabPosition(obj) {
	var prova = jQuery('.display input[tabindex]').filter(':not(:disabled):not([readonly])').filter(function() {
		return jQuery(this).attr('tabindex') > 0;
	});

	//trovo l'index dell'oggetto passato
	var pos = prova.index(obj) + 1;

	//Ultimo elemento e quindi ricomincio
	if(prova.length == pos) {
		setTimeout(function() {
			//console.log(prova.first());
			prova.first().focus();
		}, 0);
	}
	//console.log(prova.index(obj));
	//console.log(obj);
}

//Premo il tasto Ctrl a destra della tastiera
function checkTastoCtrl(event) {
	if(event.keyCode == 17 && event.originalEvent.location == 2) { 
		doFunctionKey("", session_display, "F1");
	}
}

function enableOverwrite() {
	//Gestione cambio campo con frecce su e giù
	jQuery('.display').find('input').keydown(function(event) {
		var keyCode = event.keyCode;
		//console.log(keyCode);
		if(keyCode == 45) {
			insEnable = !insEnable;
		}else if(keyCode == 9) {
			checkTabPosition(event.currentTarget);
		}else if(keyCode == 107) { //stato + dal tastierino numerico
			event.currentTarget.value = event.currentTarget.value.slice(0, event.currentTarget.selectionStart);

			//Cerco il tabindex successivo valido
			var that = this;
			var nextElement = jQuery('.display input[tabindex]').filter(':not(:disabled):not([readonly])').filter(function() {
				return jQuery(this).attr('tabindex') > that.tabIndex;
			}).first();
			
			if (nextElement.length) {
				jQuery('input[tabindex="' + (nextElement.attr('tabIndex')) + '"]').focus();
				event.preventDefault();
			}else {
				checkTabPosition(this);
				event.preventDefault();
			}
		}

		var fieldId = event.currentTarget.id;
		if(fieldId.indexOf("DES_") == 0) {
			return;
		}

		var enableKey = [38, 40, 37, 39];
		if(enableKey.indexOf(keyCode) != -1) {
			x_error(false);

			var caretIndex = event.currentTarget.selectionStart;
			//console.log(caretIndex);
			if(keyCode == 37 && caretIndex != 0) {
				return;
			}
			if(keyCode == 39 && caretIndex < event.currentTarget.value.length) {
				return;
			}

			var field = search_field(event.currentTarget, keyCode);
			if(field && event.currentTarget.id != field.attr('id')) {
				field.focus();
			}else {
				if(keyCode == 40) { //Se sono arrivato alla fine ricomincio
					checkTabPosition(event.currentTarget);
				}
			}
		}
		//console.log(event);
		//if(notKey.indexOf(event.keyCode) != -1) return;
		
	});

	//Gestione overwrite caratteri 
	jQuery('.display').find('input').filter(':not(:disabled):not([readonly])').keypress(function(event) {
		var notKey = [37, 38, 39, 40, 9];
		if(!insEnable || event.charCode == 0 || notKey.indexOf(event.keyCode) != -1 || event.altKey || event.ctrlKey) return;
		
		var questo = this;
	  	var s = questo.selectionStart;
	    questo.value = questo.value.substr(0, s) + questo.value.substr(s + 1);
	    questo.selectionEnd = s;
	});

	//Gestione copia e incolla con overwrite
	jQuery(document).ready(function() {
		jQuery('input').on('paste', function (e) {
			if(insEnable) {
				var that = this;
				var dt = e.originalEvent.clipboardData;
				if(dt && dt.items && dt.items[0]) {
					dt.items[0].getAsString(function(text) {
						pasteText(that, text);
					});
				}else if(dt && 'getData' in dt) {
					var text = dt.getData('text'); //the pasted content
					pasteText(that, text);
				}
	
				e.preventDefault();
			}
		});

		//Inizializzo il viewScale
		var div5250 = jQuery('#CLIENT_5250_DIV');
		var viewScaleFireFox = div5250.css('-moz-transform');
		var viewScaleChromeIE = div5250.css('zoom');
		/*console.log("viewScale");
		console.log(viewScaleFireFox);
		console.log(viewScaleChromeIE);*/
		if(viewScaleFireFox) {
			viewScaleFireFox = viewScaleFireFox.split(",");
			viewScale = +viewScaleFireFox[0].slice(7);
			
			div5250.parent().height(div5250[0].getBoundingClientRect().height);
		}else if(viewScaleChromeIE) {
			if(viewScaleChromeIE.substr(-1) == '%') {
				viewScale = viewScaleChromeIE.substr(0, viewScaleChromeIE.length-1);
				viewScale = viewScale / 100;

				//console.log(viewScale);
			}else {
				viewScale = +viewScaleChromeIE;
			}
		}else {
			viewScale = 1;
		}
	});

	//Se non è settato nessun focus setto il primo bottone
	if(!window["AUTO_FOCUS_FIELD_ID"]) {
		setTimeout(function() {
			jQuery('#F1').focus();
		}, 0);
	}
}

function pasteText(input, text) {
	var caretIndex = input.selectionStart;
	var value = input.value;
	var maxLength = input.maxLength;

	var value1 = value.slice(0, caretIndex);
	//var value1 = value.slice(caretIndex, caretIndex+text.length);
	var value2 = value.slice(caretIndex+text.length);

	//console.log(value1, value2);

	value = value1+text+value2;

	value = value.slice(0, maxLength);
	input.value = value;
	input.setSelectionRange(caretIndex, caretIndex);
}

function search_field(focus, code, iniTop) {
	var focus = jQuery(focus);

	//console.log(focus);
	var ele = "";
	if(code == 40 || code == 39 || !code) {
		ele = focus.closest('.input').nextAll('.input').first();
	}else {
		ele = focus.closest('.input').prevAll('.input').first();
	}

	//console.log(ele);

	if(ele.length) {
		if(code) {
			var ele_top = ele.css('top');

			if(typeof(iniTop) != "undefined") {
				var focus_top = iniTop;
			}else {
				var focus_top = focus.parent().css('top');
			}
	
			if((code == 37 || code == 39) && focus_top != ele_top) {
				return focus;
			}else if(((code == 38 || code == 40) && focus_top == ele_top) || ele.find('input').is('[readonly]') || ele.find('input').is('[disabled]') || ele.hasClass('i27')) { //ele.hasClass('i27')
				return search_field(ele.find('input'), code, focus_top);
			}else {
				return ele.find('input');
			}
		}else {
			return ele.find('input');
		}
	}else {
		//Ritorno me stesso
		return null;
	}
}

function x_system(attivo) {
	blockButton = attivo;
	if(attivo) {
		jQuery('.cont_block, #x_system').css('display', 'block');
		
	}else {
		jQuery('.cont_block, #x_system').css('display', '');
	}
}
function x_error(attivo) {
	if(attivo) {
		jQuery('.cont_block2, #x_error').css('display', 'block');
	}else {
		jQuery('.cont_block2, #x_error').css('display', '');
	}
}

doFunctionKey = function(which, session_id, functionKey) {
	if (typeof(disableAjax) != "undefined") return;
	
	//console.log(which, session_id, functionKey);
    if (typeof(functionKey) == "undefined") functionKey = "F1";

    //Se il blocco tasti è attivo non faccio niente
    if(blockButton) return;
	
    x_system(true);

    stopReadInterval();

    //Controllo se è un'azione di sistema
    var cmd = "";
    if(jQuery('.cont_cmd_line').css('display') == 'block') {
        cmd = "&CMD_SYSTEM="+jQuery('#input_system_line').val();
    }
    //blockBrowser(true);
   //jQuery.blockUI({ message: '<h1><img src="themes/common/images/busy.gif" /> Prego attendere ...</h1>' });
	jQuery.ajax({
	  'type': "POST",	
	  'url': _APP_BASE + APP_SCRIPT + "?t=TELNET_5250_AJAX&f=WRITE&DECORATION=clean&SESSION_ID="+session_id+cmd+"&FUNCTION_KEY="+functionKey+"&CAMPO_SELEZIONATO="+campoSelezionato+"&CARATTERI="+numeroCaratteri+"&ROW="+relY+"&COL="+relX,
	  'cache': false,
	  'data': jQuery('#CLIENT_5250_DIV :input').serialize()	  
	}).success(function( html ) {
		x_system(false);
		
		risultato = true;
		json = jQuery.parseJSON(html.substring(html.indexOf("JSON:") + 5) );

		cambiaSchermata(json);

	  	startReadInterval();
		/*		
		if (pesoArray["risposta"]=="S") {
			doSubmit("DI003", "F2_DETTAGLIO_PARTITA");
			return;
		}		
		if (pesoArray["risposta"]=="W" && operazione =="CHK") {
			w = 350;
			h = 250;
			openWindow(_APP_BASE + "index.php?t=DI003&DECORATION=lookup&f=W10_CONFERMA_TAGLIO_EAN&"+$(APP_FORM).serialize(), "confirmTaglio", w, h, true, false);
			return;
		}
		if (pesoArray["risposta"]=="N" && operazione =="CHK") {
			doSendPeso2(which, "AGG"); 
			return;
		}
		resubmitPage();
		return;*/			
	}).error(function(jqXHR, textStatus){
		x_system(false);
		x_error(true);
		//alert('Errore server. Riprovare l\'operazione');

		startReadInterval();
	});
	//jQuery.unblockUI();
	//blockBrowser(false);
};

function closeSession5250(callback) {
	jQuery.ajax({
	  'type': "POST",
		'url': _APP_BASE + APP_SCRIPT + "?t=TELNET_5250_AJAX&f=CLOSE&DECORATION=clean&SESSION_ID="+session_display,
		'cache': false,
		//'data': jQuery('#CLIENT_5250_DIV :input').serialize()	  
	}).success(function( html ) {
		//json = jQuery.parseJSON(html.substring(html.indexOf("JSON:") + 5) );
		console.log("chiusura success");

		if(typeof(numWindow) == "undefined") {
			console.log(wi400_window_counter, "stopInterval");
			stopReadInterval();

			jQuery('#CLIENT_5250_DIV').html("<div id='scollegato'>Monitor scollegato</div>");
		}else {
			callback();
		} 		
	}).error(function(jqXHR, textStatus){
		console.error("Errore ajax closeSession");

		callback();
	});
}
function estraiSubfile() {

	if(blockButton) return;
	
	blockButton = true;
	jQuery('.cont_block_export').css('display', 'block');
	
	jQuery.ajax({
	  'type': "POST",
		'url': _APP_BASE + APP_SCRIPT + "?t=TELNET_5250_AJAX&f=ESTRAI_SUBFILE&DECORATION=clean&SESSION_ID="+session_display,
		'cache': false,
		//'data': jQuery('#CLIENT_5250_DIV :input').serialize()	  
	}).success(function( html ) {
		//json = jQuery.parseJSON(html.substring(html.indexOf("JSON:") + 5) );
		console.log("success ajax estraiSubfile");
		
	}).error(function(jqXHR, textStatus){
		console.error("Errore ajax estraiSubfile");
		blockButton = false;
	});
}

function chiudiEstrazione(annulla) {
	blockButton = false;
	enableExport = annulla;

	jQuery('.cont_block_export').css('display', 'none');
	
}

function cambiaSchermata(json) {
	jQuery('#CLIENT_5250_DIV').html(json["5250html"]);

	if (typeof(AUTO_FOCUS_FIELD_ID) != "undefined" && document.getElementById(AUTO_FOCUS_FIELD_ID)){
		try {
			//document.getElementById(AUTO_FOCUS_FIELD_ID).focus();
			jQuery("#"+AUTO_FOCUS_FIELD_ID).focus();
		} catch(e) {
			
		}
  	}
}

function rightAdjust(obj, char) {
	if(!obj.value) return;
	 
	var size = obj.maxLength;
	obj.value = obj.value.padStart(size, char);
	//console.log(size);
	//console.log(obj, char);
}

function activeCaretHorizontal($) { 
	$(".display input").on('change mouseup focus keydown keyup blur', function(evt) {
		var $el = $(evt.target);

		x_error(false);

		if(evt.type == 'blur') {
			setTimeout(function() {
				$el.css('background', '');
			}, 0);
			return;
		}
		if($el.attr('readonly')) {
			return;
		}
		
		//check if the carret can be hidden
		//AFAIK from the modern mainstream browsers
		//only Safari doesn't support caret-color
		if (!$el.css("caret-color")) return;
		var caretIndex = $el[0].selectionStart;
		var textBeforeCarret = $el.val().substring(0, caretIndex);

		var bgr = getBackgroundStyle($el, textBeforeCarret);
		$el.css("background", bgr);
		//clearInterval(window.blinkInterval);
		//just an examplethis should be in a module scope, not on window level
		//window.blinkInterval = setInterval(blink, 600);

		//Gestione riempimento campo e passo al campo successivo 
		if(evt.type == 'keyup') {
			//console.log(event.currentTarget.value.length, event.currentTarget.maxLength, caretIndex);
			if(evt.key.length == 1 && evt.currentTarget.value.length == evt.currentTarget.maxLength && caretIndex == evt.currentTarget.maxLength) {
				var field = search_field($el[0], "");
				if(field && $el.attr('id') != field.attr('id')) { 
					field.focus();
				}else if(!field) {
					//Ricomincio dall'inizio. Dal primo campo
					checkTabPosition($el[0]);
				}
			}
		}
	});
}

/*function blink() {
	jQuery(".display").find("input").filter(':not(:disabled):not([readonly])').each((index, el) => {
		var $el = jQuery(el);
		if ($el.css("background-blend-mode") != "normal") {
			$el.css("background-blend-mode", "normal");
		}else {
			$el.css("background-blend-mode", "color-burn");
		}
	});
}*/


function getBackgroundStyle($el, text) {
  var fontSize = $el.css("font-size");

  var innerWidth = $el[0].getBoundingClientRect().width;

  //browser arriva dalla funzione addHtmlContainer (display)
  if(browser == "Firefox") innerWidth /= viewScale;

  var innerHeight = +window.getComputedStyle($el[0], null).getPropertyValue('height').slice(0,-2);

  //var innerHeight = $el[0].getBoundingClientRect().height;
  var maxLength = $el[0].maxLength;
  //console.log(innerWidth+"  "+maxLength+"  "+text+"  "+(text.length));
  var fontFamily = $el.css("font-family");

  var font = fontSize + " " + fontFamily;
  var canvas = $el.data("carretCanvas");
  //cache the canvas for performance reasons
  //it is a good idea to invalidate if the input size changes because of the browser text resize/zoom)
  if (canvas == null) {
    canvas = document.createElement("canvas");
	canvas.width = 13;
    canvas.height = 13;
    $el.data("carretCanvas", canvas);
    var ctx = canvas.getContext("2d");
    ctx.font = font;
    ctx.strokeStyle = colorCursore;//$el.css("color");
	//ctx.lineWidth = Math.ceil(parseInt(fontSize) / 5);
	ctx.lineWidth = Math.ceil(parseInt(fontSize));
    ctx.beginPath();
    ctx.moveTo(0, 0);
    //aproximate width of the caret
    ctx.lineTo(parseInt(fontSize) / 2, 0);
    ctx.stroke();
  }
  //console.log(parseInt($el.css("padding-left")));
  //var offsetLeft2 = canvas.getContext("2d").measureText(text).width + parseInt($el.css("padding-left"));
  var offsetLeft = (text.length * (innerWidth / maxLength)) + parseInt($el.css("padding-left"));
  
  return "url(" + canvas.toDataURL() + ") no-repeat " +
    (offsetLeft - $el.scrollLeft()) + "px " +
    (innerHeight + parseInt($el.css("padding-top")) - (insEnable ? 3 : 5)) + "px";
}

jQuery.prototype.disableTab = function() {
    this.each(function() {
        jQuery(this).attr('tabindex', '-3');
        jQuery(this).attr('disabled', 'true');
    });
};

function disableTab() {
	jQuery('.display input').not('.window input').filter(':not(:disabled):not([readonly])').disableTab();
}

function startWindowDrag() {
	jQuery('.window').draggable({
		containment: ".display", 
		scroll: false,
		//cursorAt: { left: 0, top: 0 }
	});
}

function showSystemLine() {
	var display = jQuery('.cont_cmd_line').css('display');
	if(display == 'none') {
		jQuery('.cont_cmd_line').css('display', 'block');
		jQuery('#input_system_line').focus();
	}else {
		jQuery('.cont_cmd_line').css('display', 'none');
		jQuery('#'+campoSelezionato).focus();
	}
}

function hideSystemLine() {
	jQuery('.cont_cmd_line').css('display', 'none');
	jQuery('#'+campoSelezionato).focus();
}

function test_check_radio(obj) {
	//console.log(obj.checked);
}

function zoomSchermata(aumenta) {
	if(aumenta) viewScale += 0.1;
	else viewScale -= 0.1;

	viewScale = +viewScale.toPrecision(2);

	var div5250 = jQuery('#CLIENT_5250_DIV');

	if(div5250.css('-moz-transform')) {
		jQuery('#CLIENT_5250_DIV, .cont_block, .cont_block2, .cont_block_export').css('-moz-transform', 'matrix('+viewScale+', 0, 0, '+viewScale+', 0, 0)');
		div5250.parent().height(div5250[0].getBoundingClientRect().height);
	}else {
		jQuery('#CLIENT_5250_DIV, .cont_block, .cont_block2, .cont_block_export').css('zoom', viewScale);
	}

	saveZoom(viewScale);
}

function saveZoom(zoom) {
	jQuery.ajax({
	  'type': "POST",
		'url': _APP_BASE + APP_SCRIPT + "?t=TELNET_5250_AJAX&f=SAVE_ZOOM&DECORATION=clean&ZOOM="+zoom,
	}).success(function( html ) {
		//console.log("success"); 		
	}).error(function(jqXHR, textStatus){
		console.error("Errore ajax saveZoom");
	});
}

</script>