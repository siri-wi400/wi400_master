function doNothing(which){
}

function getFocusedField(){
	return FOCUSED_FIELD;
}
//var BACKUP_VALUE = "";
function setFocusedField(which){
	//var last_focused_field = which.id;
	if (typeof(which) != "undefined"){
		FOCUSED_FIELD = which;
		LAST_FOCUSED_FIELD = which;
//		FOCUSED_FIELD = which.id;
//		BACKUP_VALUE = which.value;
	}else{
		FOCUSED_FIELD = "";
	}
	if (FOCUSED_FIELD!="" && REFRESH_FOCUS==true) { 
		jQuery.ajax({  
			type: "GET",
			url: _APP_BASE + APP_SCRIPT + "?t=AJAX_FOCUSED_FIELD&DECORATION=clean&FOCUSED_FIELD="+FOCUSED_FIELD.id+"&FOCUSED_TAB="+FOCUSED_TAB
			}).done(function ( response ) {  
				//alert(response);
				//document.getElementById(div).innerHTML = response;
			}).fail(function ( data ) {  
				
			}); 
	}
}

wi400WindowSize = getCookieWindowSize();
wi400WindowSizeSave = {};
//wi400SetCookie

function getCookieWindowSize(name) {
	if(typeof(name) == 'undefined') name = 'wi400WindowSize';
	
	var size = wi400GetCookie(name);
	if(size) {
		size = JSON.parse(size);
	}else {
		size = {};
	}
	
	return size;
}

function addCookieWindowSize(w, h, azione, form, plus, save) { //azione, form, plus, w, h
	
	if(typeof(azione) == 'undefined') azione = CURRENT_ACTION;
	if(typeof(form) == 'undefined') form = CURRENT_FORM;
	if(typeof(plus) == 'undefined') plus = '';
	if(typeof(save) == 'undefined') save = true;
	
	//console.log(w, h, azione, form, plus);
	var chiave = azione+'|'+form;
	if(plus) chiave += '|'+plus;
	
	var size = w+'|'+h+'|'+wi400top.window.innerWidth+'|'+wi400top.window.innerHeight;
	
	wi400WindowSize[chiave] = size;
	
	//console.log(wi400WindowSize);
	if(save) {
		wi400SetCookie('wi400WindowSize', JSON.stringify(wi400WindowSize));
		
		wi400WindowSizeSave = getCookieWindowSize('wi400WindowSizeSave');
		wi400WindowSizeSave[chiave] = size;
		wi400SetCookie('wi400WindowSizeSave', JSON.stringify(wi400WindowSizeSave));
	}
}

function wi400GetWindowSizeKeyByUrl(url) {
	var chiave = '';
	if(wi400UrlParam(url, 'FROM_LIST')) {
		var field_id = wi400UrlParam(url, 'FIELD_ID').split('-');
		chiave = field_id[0]+"_"+field_id[2];
	}else {
		chiave = [];
		var detail_id = wi400UrlParam(url, 'DETAIL_ID');
		var field_id = wi400UrlParam(url, 'FIELD_ID');
		
		if(detail_id) chiave.push(detail_id);
		if(field_id) chiave.push(field_id);
		chiave = chiave.join('_');
	}
	
	return chiave;
}

function wi400WindowAdapterResolution(size) {
	var size = size.split("|");
	var w1 = size[0], h1 = size[1], w2 = size[2], h2 = size[3];
	var current_w = wi400top.window.innerWidth;
	var current_h = wi400top.window.innerHeight;
	
	var size0 = w1, size1 = h1;
	
	//console.log(w2+" "+current_w+" => "+h2+" "+current_h);
	
	if(w2 != current_w || h2 != current_h) {
		var size0 = ((w1*current_w)/w2).toFixed(2);
		var size1 = ((h1*current_h)/h2).toFixed(2);
		
		//console.log('Nuova size');
		//console.log(size0, size1);
	}else {
		//console.log('Mantengo lo stesso size');
	}
	
	return [size0, size1];
}

document.onmousedown = function(e) {
/*	
	if (!checkBlockBrowser()){
//	if (!_BLOCK_BROWSER){
		return false;
	}
*/
	if (typeof(_BLOCK_BROWSER)=="undefined") {
		_BLOCK_BROWSER=false;
	}
	var checkBlock = _BLOCK_BROWSER;

	if (checkBlock){
		alert(yav_config.WAITING);
		return false;
	}else{
		return true;
	}
};
/*
function doOnChange(which, functionName){
	if (BACKUP_VALUE != which.value){
		eval(functionName);
	}
	return null;
}
*/
function confirmModale(message) {
    jQuery.blockUI({ message:'<h1>'+ message + '</h1><input type="button" id="si" value="Si" /><input type="button" id="no" value="No" />', css: { width: '275px' } });  
	jQuery('#si').click(function() { 
		return true;
    }); 
    jQuery('#no').click(function() { 
        jQuery.unblockUI(); 
        return false; 
    }); 
}

// Gestione pressione tasti
document.onkeydown = function(e) {
      evt = (e) ? e : window.event;
      var type = evt.type;
      var pK = e ? e.which : window.event.keyCode;
      
		/*e.cancelBubble = true;
		e.returnValue = false;
		if(e.preventDefault) e.preventDefault();
		//e.stopPropagation works in Firefox.
		if (e.stopPropagation) {
			e.stopPropagation();
		}*/

      
      // TASTO INVIO
      if (pK == 13){
    	  fieldfocused = getFocusedField();
    	  if (fieldfocused == ""){
    		  return false;
    	  }else if (fieldfocused.type == "textarea"){
    		  // textarea
    	  }else{
    		  return false;
    	  }
    	 
      }else{
		  if (typeof(paginationListKey) != "undefined"){
			  // Navigazione in lista
	    	  if (currentPaginationListKey=="") {
	    	     	currentPaginationListKey=arrayPaginationListKey[1];
	    	  }
	    	  if (window[currentPaginationListKey + "_CR"]!=0) {
	    		  	window[currentPaginationListKey+'_SELECT']=window[currentPaginationListKey + "_CR"];
	    	  }
	    	  // Pagina in avanti
		      if (pK == 39 || pK==34){
		      	if (!document.getElementById("MOVINGWITHKEYS")) {
			      	if (!document.getElementById(currentPaginationListKey+"_NEXT_BUTTON").disabled){
			      		if (getFocusedField() == "") doPagination(currentPaginationListKey,_PAGE_NEXT);
			      	}
		      	}
		      // Pagina indietro	
		      }else if (pK == 37 || pK==33){
		    	if (!document.getElementById("MOVINGWITHKEYS")) {
			    	if (!document.getElementById(currentPaginationListKey+"_PREV_BUTTON").disabled){
			      		if (getFocusedField() == "") doPagination(currentPaginationListKey,_PAGE_PREV);
			      	}
		      	}
		      // Freccia giù
		      }else if (pK == 40){
					window[currentPaginationListKey+'_SELECT']++;
					// Supero lunghezza della pagina
					if (window[currentPaginationListKey+'_SELECT']>=window[currentPaginationListKey+'_ROWS'] || (jQuery('#'+currentPaginationListKey + "-" + window[currentPaginationListKey+'_SELECT']).length == 0)) {
						// Controllo se sono su pagina con più liste
						if (arrayPaginationListKey.length> 2) {
							// Reperisco l'indice attuale della lista utilizzate
							var a = arrayPaginationListKey.indexOf(currentPaginationListKey); 
							if (a==arrayPaginationListKey.length-1) {
								a=1;
							} else {
								a++;
							}
							// Rimuovo select sulla precedente lista
							checkGridRow(currentPaginationListKey, window[currentPaginationListKey+'_SELECT']-1, false);
							currentPaginationListKey = arrayPaginationListKey[a];
							window[currentPaginationListKey+'_SELECT']=0;
						} else {
							window[currentPaginationListKey+'_SELECT']=0;
						}
					}
			        checkGridRow(currentPaginationListKey, window[currentPaginationListKey+'_SELECT'], true);
			        //jQuery('body').scrollTo('#'+currentPaginationListKey + "-" + window[currentPaginationListKey+'_SELECT']+"-checkbox");
			  // Freccia su      
		      }else if (pK == 38){
			        window[currentPaginationListKey+'_SELECT']--;
			        // Riga Attualmente selezionata alert(window[paginationListKey + "_CR"] );
					if (window[currentPaginationListKey+'_SELECT']<0)  {
						// Controllo se sono su pagina con più liste
						if (arrayPaginationListKey.length> 2) {
							// Reperisco l'indice attuale della lista utilizzate
							var a = arrayPaginationListKey.indexOf(currentPaginationListKey); 
							if (a==1) {
								a= arrayPaginationListKey.length-1;
							} else {
								a--;
								if (a==0) a= arrayPaginationListKey.length-1;
							}
							// Rimuovo select sulla precedente lista
							if (window[currentPaginationListKey+'_SELECT']>-2) {
								checkGridRow(currentPaginationListKey, window[currentPaginationListKey+'_SELECT']+1, false);
							}
							currentPaginationListKey = arrayPaginationListKey[a];
							window[currentPaginationListKey+'_SELECT']=window[currentPaginationListKey+'_ROWS']-1;
						} else {
							window[currentPaginationListKey+'_SELECT']=window[currentPaginationListKey+'_ROWS']-1;
						}
						
					}
					var count = 0;
					while (jQuery('#'+currentPaginationListKey + "-" + window[currentPaginationListKey+'_SELECT']).length == 0) {
						count++;
						window[currentPaginationListKey+'_SELECT']--;
						if (count>100) {
							break;
						}
					}	
			        checkGridRow(currentPaginationListKey,window[currentPaginationListKey+'_SELECT'], true);
			        //jQuery('body').scrollTo('#'+currentPaginationListKey + "-" + window[currentPaginationListKey+'_SELECT']+"-checkbox");
		      }
	      }else if (typeof(functionKey) != "undefined" && functionKey == "PRESENTATION"){
			  if (pK == 33){
					if (!document.getElementById("PREV_BUTTON").disabled){
						doSubmit("TO_ITEM_DETAIL","PREV");
						return false;
					}else{
						alert("E' stato raggiunto l'inizio della presentazione!");
						return false;
					}
		      }else if (pK == 34){
					if (!document.getElementById("NEXT_BUTTON").disabled){
						doSubmit("TO_ITEM_DETAIL","NEXT");
						return false;
					}else{
						alert("E' stata raggiunta la fine della presentazione!");
						return false;
					}
		      }
		  }
      }
	  
};

function logout(){
	if (window.opener && window.opener != null && window.opener.open && !window.opener.closed){
		if (window.opener["_CHECK_LOGOUT"]){
			window.opener["_CHECK_LOGOUT"] = false;
		}
	}
}
function doPreferiti(action, div, id){
	var messaggio = yav_config.BOOKMARKS_ADD;
	if (action=="REMOVE") {
		messaggio = yav_config.REMOVE;
	}
	if (confirm(messaggio)){
		
		var currentAction = document.getElementById("CURRENT_ACTION").value;
		var currentForm   = document.getElementById("CURRENT_FORM").value;
		
		// Cancellazione
		var removeString = "";
		if (action == "REMOVE"){
			removeString = "&REMOVE=" + id;
		}
		
		jQuery.ajax({  
			type: "GET",
			url: _APP_BASE + APP_SCRIPT + "?t=PREFERITI&DECORATION=clean&ACTION=" + currentAction + "&FORM=" + currentForm + "&f=" + action + removeString
			}).done(function ( response ) {  
				//alert(response);
				document.getElementById(div).innerHTML = response;
			}).fail(function ( data ) {  
				
			}); 
	}
}

/* 
 * myObj => oggetto da cui parte la ricerca della wi400_keys
 * num => quale chiave voglio? la prima che trovo => 1, la seconda => 2 ecc...
 * maxParent => massimo di salti indietro che devi fare la funzione
 */
function get_column_key(myObj, num, maxParent) {
	if(num == undefined) {
		num = 1;
	}
	if(maxParent == undefined) {
		maxParent = 15;
	}
	
	var key = "";
	
	try {
		var nodoParent = myObj.parentNode;
		key = nodoParent.getAttribute("wi400_keys");
	}catch(e) {
		return null;
	}
	
	//console.log(parent);
	//console.log(maxParent);
	if(maxParent) {
		if(key && num == 1) {
			return key;
		}else {
			if(key) {
				return get_column_key(nodoParent, num-1, maxParent-1);
			}else {
				return get_column_key(nodoParent, num, maxParent-1);
			}
		}
	}else {
		return null;
	}
}

function openCloseInputSearch(id) {
	//console.log("\n"+jQuery("#startBox").is(':checked'));
	//console.log(jQuery("#caseSensitiveBox").is(':checked'));
	
	var src = jQuery('#image_effect_input_'+id).attr('src');
	
	if(src == "themes/common/images/text.png") {
		jQuery("#DES_"+id+"_DIV").show("drop");
		jQuery("#image_effect_input_"+id).attr("src", "themes/common/images/map/leftArrow.gif");
	}else{
		jQuery("#DES_"+id+"_DIV").hide("drop");
		jQuery("#image_effect_input_"+id).attr("src", "themes/common/images/text.png");
	}
}

function openCloseInputSearchStyle5250(id) {
	var display = jQuery('#DES_'+id+'_DIV').css('display');
	
	if(display == 'none') {
		jQuery("#DES_"+id+"_DIV").show("drop");
		//jQuery("#image_effect_input_"+id).addClass('fa-arrow-circle-o-left');
	}else{
		jQuery("#DES_"+id+"_DIV").hide("drop");
		//jQuery("#image_effect_input_"+id).removeClass('fa-arrow-circle-o-left');
	}
}

function openHelp(topic){
	openWindow(_APP_BASE + WIKI_URL + topic, "wiki400", 840, 500);
}

function openUrlHelp(url, width, height, tipoApertura){
	if (typeof(tipoApertura) == "undefined"){
		tipoApertura = "wi400";
	}
	if (tipoApertura=="wi400") {
		openWindow(url, "WI400_GUIDE", width, height);
	}
	if (tipoApertura=="_blank" || tipoApertura=="_parent") {
		window.open(url, tipoApertura);
	}
	if (tipoApertura.substring(0,5)=="popup") {
		// La parte finale della stringa contiene gli stili
		var stile = tipoApertura.substring(6);
		//var stile = "top=10, left=10, width=250, height=200, status=no, menubar=no, toolbar=no scrollbars=no";
		window.open(url, "", stile);
	}
}

function openFullHelp(topic){
	openWindow(_APP_BASE + WIKI_URL + topic, "wiki400", 840, 500, false);
}

function manageDetail(idDetail){
	w = 600;
	h = 150;
	
	openWindow(_APP_BASE + APP_SCRIPT + "?IDDETAIL=" + idDetail + "&DECORATION=lookUp&t=MANAGE_DETAIL", "manageDetail", w, h);
}

function doSaveDetail(idDetail){
	w = 400;
	h = 200;
	openWindow(_APP_BASE + "index.php?t=SAVE_NAME_DETAIL&IDDETAIL=" + idDetail + "&DECORATION=lookup", "detailSave", w, h);
}
function doReloadDetail(idDetail) {
	// @todo Funzione da implementare
	// Richiamo AJAX che lancia l'elaborazione.
		jQuery.ajax({
			url: _APP_BASE + "index.php?t=AJAX_RELOAD_FORM&DECORATION=clean",
			type: "POST",
			data: jQuery("#" + APP_FORM).serialize()+"&RELOAD_ID_DETAIL="+idDetail,
			success: function(response) {
				var myjson=response.substring(response.lastIndexOf("REPLY:")+6,response.lastIndexOf(":END-REPLY"));
				var decodeJSON = jQuery.parseJSON(myjson);
				if (decodeJSON.outputHtmlRow!="") {
					jQuery('#'+idDetail+'_slider').replaceWith(decodeJSON.outputHtmlRow);
				};
			},
		});
	// sostituzione del codice HTML ritornato dall'AJAX
}
function doResetDetail(idDetail){
	if (window[idDetail + "_FORM_ARRAY"] != null){
		
		for (var i=0; i < window[idDetail + "_FORM_ARRAY"].length; i++) {
			var input = window[idDetail + "_FORM_ARRAY"][i];
			
			// INFO -> !window[idDetail + "_FORM_ARRAY"][0].disabled ???? forse era i al posto dello 0
			if( ( input.type == 'text' || input.type == 'textarea' || input.type == 'checkbox' )
				&& !window[idDetail + "_FORM_ARRAY"][0].disabled && !input.readOnly && !input.disabled){
				
				if (input.type == "checkbox") {
					input.checked = false;
					if(input.className == "checkSwitch") {
						jQuery(input).parent().attr('class', "checkSwitch off");
					}
					if (typeof input.change == "function"){
						input.change();
					}
				}else{
					if((""+input.onchange).indexOf("multiFieldAddRemove") != -1) {
						jQuery("#"+input.id+"_PARENT").html("");
					}else {
						window[idDetail + "_FORM_ARRAY"][i].value = "";
						
						//Sbianco anche la sua decodifia
						if (document.getElementById(window[idDetail + "_FORM_ARRAY"][i].id + "_DESCRIPTION")){
							document.getElementById(window[idDetail + "_FORM_ARRAY"][i].id + "_DESCRIPTION").innerHTML = "&nbsp;";
						}
					}
				}
			}
		}
		
		//Gestione select multiple
		var selects = jQuery('#'+idDetail+'_slider select');
		for(var i=0; i<selects.length; i++) {
			if(selects[i].multiple) {
				jQuery(selects[i]).find("option:selected").removeAttr("selected");
			}else {
				if(selects[i].id.indexOf("_CUSTOM_FILTER") == -1) {
					selects[i].selectedIndex = 0;
				}
			}
		}
	}
}

function wi400Init(){
	if (rules.length > 0){
		yav.init('wi400Form', rules);
		yav.addMask('wi400Date', MASK_DATE, '1234567890');
		yav.addMask('wi400Time', MASK_TIME, '1234567890');
	}
	
	//Aggiungo la funzione nativa forEach per IE8
	if (!Array.prototype.forEach) {
	    Array.prototype.forEach = function(fn, scope) {
	        for(var i = 0, len = this.length; i < len; ++i) {
	            fn.call(scope, this[i], i, this);
	        }
	    };
	}

  	// Abilito context menu
  	if (document.getElementById("CM1")){
	  	SimpleContextMenu.setup({'preventDefault':true, 'preventForms':false});
	    SimpleContextMenu.attach('body-area', 'CM1');
	}
  	
  	// Abilitazione colore sfondo campi di testo
  	/*jQuery("#"+APP_FORM).each(function(index ,element) {
		var type = jQuery.type(element);
  		if (jQuery(element).is("[disabled]") == true || jQuery(element).is("[readonly]") == true 
  				|| type == "select-one" || type == "hidden" || type == "image" || type == "button" || type == "radio" || type == "checkbox"){
  			// nothing to do
  		}else{
			//this.observe('focus', function(event){
  			jQuery(element).focus(function(){
    	  	    jQuery(element).setStyle({backgroundColor: '#ffffa5'});
			});
		
	  		//this.observe('blur', function(event){
  			jQuery(element).focus( function () {
	  	  	  jQuery(element).setStyle({backgroundColor: ''});
	  	  	});
  		}
  	});*/
  	if(typeof(disabledInputFocusStyle) == "undefined") {
	    jQuery('#'+ APP_FORM).find('input[type=text],textarea,select').filter(':not(:disabled):not([readonly])').each(function (index){
	    	var name = this.id;
	    		//jQuery(this).focus();
	    		//return false;
				jQuery(this).focus(function(){
	    	  	    jQuery(this).css({backgroundColor: '#ffffa5'});
				});
	  			jQuery(this).blur( function () {
		  	  	  jQuery(this).css({backgroundColor: ''});
		  	  	});
	    });
  	}

	// Auto Focus First Field
	if (typeof(AUTO_FOCUS_FIELD_ID) != "undefined" && document.getElementById(AUTO_FOCUS_FIELD_ID)){
		try {
			//document.getElementById(AUTO_FOCUS_FIELD_ID).focus();
			jQuery("#"+AUTO_FOCUS_FIELD_ID).focus();
		} catch(e) {
			
		}
  	}
	 // iphone checkbox
	//checkSwitch();
}


function update_read_message(id_mess, test_risp, type_risp) {
	//console.log('ho letto il messaggio '+id_mess);

	if(type_risp == "txt" && !test_risp) {
		return;
	}
	
	var dati = '';
	
	if(test_risp) {
		dati += '&TESTO_RISP='+test_risp;
	}

	jQuery.ajax({
		type: 'GET',
		url: _APP_BASE + APP_SCRIPT + '?t=ANNOUNCE_MESSAGE&DECORATION=clean&f=CONFIRM_READ&CODICE='+id_mess+dati
	}).done(function ( response ) {
		if(!response) {
			var bottone = jQuery('#button_conf_read_'+id_mess);
			//bottone.parent().find('br').slice(-2).remove();
			var div = bottone.parent();
			div.append("<span class='confirm_mess_button' style='background-image: url(\"themes/common/images/yav/valid.gif\");'>Ho letto</span>");
			bottone.remove();
		}else {
			jQuery('#cont_risp_'+id_mess).html("Risposta: "+response);
		}
	}).fail(function ( data ) {
		console.log('Errore conferma lettura messaggio '+id_mess+'!');
	});
}

function showMessages(sourceMessagesDiv, severity){
	if (typeof(sourceMessagesDiv) == "undefined"){
		sourceMessagesDiv = "messagesList";
	}
	
	if (document.getElementById("messageArea") != null
		&& document.getElementById(sourceMessagesDiv) != null 
		&& document.getElementById(sourceMessagesDiv).innerHTML != ""){
		
		document.getElementById("messageArea").innerHTML = document.getElementById(sourceMessagesDiv).innerHTML;
		document.getElementById(sourceMessagesDiv).innerHTML = "";
		if (typeof(severity) != "undefined"){
			document.getElementById("messageArea").className = "messageArea_" + severity;
		}
		jQuery('#messageArea').fadeIn("");
	}
}

function showParentMessages(sourceMessagesDiv, severity){

	if (typeof(sourceMessagesDiv) == "undefined"){
		sourceMessagesDiv = "messagesList";
	}
	if (parent.document.getElementById("messageArea") != null
		&& document.getElementById(sourceMessagesDiv) != null 
		&& document.getElementById(sourceMessagesDiv).innerHTML != ""){
		parent.document.getElementById("messageArea").innerHTML = document.getElementById(sourceMessagesDiv).innerHTML;
		document.getElementById(sourceMessagesDiv).innerHTML = "";
		if (typeof(severity) != "undefined"){
			parent.document.getElementById("messageArea").className = "messageArea_" + severity;
		}
		
		//parent.document.getElementById("messageArea").appear();
		parent.jQuery('#messageArea').fadeIn("");
	}
}

function slideMenu(what){
	if (typeof(what) == "undefined") what = "slide";
	var menuStatus = "";
	// Se il menu è già nello stato che ho chiesto non faccio nulla
	//alert("Menu: "+LEFT_MENU_OPEN);
	if (LEFT_MENU_OPEN == what) {
		return;
	}
	var charAngle = jQuery('.trapezoid').find('.fa');
	if (LEFT_MENU_OPEN == "open"){
		closeLeftMenu();
		menuStatus = "close";
		charAngle.switchClass("fa-angle-left", "fa-angle-right");
	}else{
		openLeftMenu();
		menuStatus = "open";
		charAngle.switchClass("fa-angle-right", "fa-angle-left");
	}
	LEFT_MENU_OPEN = menuStatus;
	
	jQuery.ajax({
	    url: _APP_BASE + "index.php?t=CHANGE_LEFT_MENU&MENU_STATUS="+LEFT_MENU_OPEN+"&DECORATION=clean",
	    type: "GET"
	    //async:false
	 });
	
	// Salvataggio visualizzazione
	document.getElementById("LEFT_MENU_STATUS").disabled = false;
	document.getElementById("LEFT_MENU_STATUS").value = menuStatus;
	
	resizeDescriptionDetail();
}

function isWindowOpen(){
	if (!window.LOOK_UP || window.LOOK_UP.closed) {
		return false;
	}else{
		return true;
	}

}

function resizeMessageArea(){

	var messageArea = document.getElementById("messageArea");
	if (MESSAGE_AREA_OPEN){
		//messageArea.style.overflowY = "visible";
		//messageArea.style.height = "";
		//
		
	}else{
		//messageArea.style.height = "7px";
		//messageArea.style.overflowY = "hidden";
		jQuery("#messageArea").slideUp("slow");
	}
	
	MESSAGE_AREA_OPEN = !MESSAGE_AREA_OPEN;
}

function getParentObj(idList){
	var parentObj = "";
	var secondObj = "";
	var first = true;
	if (IFRAME_LOOKUP){
		var lookUpParent = document.getElementById("LOOKUP_PARENT");
		if (lookUpParent && lookUpParent.value != ""){
			IFrameObj = parent.window.frames;
			for (var x = 0; x < IFrameObj.length; x++){
				if(IFrameObj[x].name == lookUpParent.value+"_content"){
					parentObj = IFrameObj[x];
					break;
				}
				if (first==true && x == 1) {
					if(IFrameObj[x].frameElement.className != "cke_wysiwyg_frame cke_reset") {
						secondObj = IFrameObj[x];
					}
					first = false;
				}
			}
			//Controllo se ci sono più liste dentro un iframe nella window principare
			if(idList) {
				for (x = 0; x < IFrameObj.length; x++){
					try {
//						console.log(x+": "+IFrameObj[x].location.href);
//						console.log("Posizione: "+(IFrameObj[x].location.href).indexOf("t="+idList));
						if((IFrameObj[x].location.href).indexOf("t="+idList) != -1) {
							parentObj = IFrameObj[x];
							break;
						}
					}catch(e) {
						console.log("Errore nella funzione parentObj");
					}
				}
			}
			if (parentObj=="") {
				parentObj = secondObj;
			}
		}else{
			try {
				parent.location.href
				parentObj = parent;
			} catch (e) {
				parentObj = window;
			}
		}
	}else{
		//parentObj = window.opener;
		 try {
			 parentObj=window.opener;
		 } catch (e) {
			 parentObj=window;
		 }
	}
	if (parentObj=="") {
		 //parentObj = parent;
		 try {
			 parentObj=parent;
		 } catch (e) {
			 parentObj=window;
		 }
		 //parentObj = window;
	}
	return parentObj;
}
function wi400_check_login_digit(){
	if (jQuery('#userField').val() == "" || jQuery('#userPass').val() == ""){
		//jQuery("#nextImage").attr("src", _THEME_DIR + "images/next_disabled.gif");
		jQuery("#nextImage").attr("disabled", true);
	}else{
		//jQuery("#nextImage").attr("src", _THEME_DIR + "images/next.gif");
		jQuery("#nextImage").attr("disabled", false);
	}
}
function check_is_safari() {
	if(navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
		return  true;
	}
	return false;
}
function slideRowMenu(which, imageOn, imageOff){

	var slideOnImage = _THEME_DIR + "images/tag_yellow.gif";
	var slideOffImage = _THEME_DIR + "images/tag.gif";
	
	if (typeof(imageOn) != "undefined" && typeof(imageOff) != "undefined" ){
		slideOnImage  = imageOn;
		slideOffImage = imageOff;
	}
	
	if (typeof(LEFT_MENU_ROWS) != "undefined"){
		LEFT_MENU_ROWS[which] = !LEFT_MENU_ROWS[which];
		var callback = function () {
			if (check_is_safari()) {
				jQuery(".left-menu-slider").find('div').hide().show(0);
			}
			//Altezza browser
			var altezzaBrowser = document.documentElement.clientHeight;
			
			//Altezza del menu di sinistra
			var tableMenu = document.getElementById('tableMenu').offsetHeight;
			
			//document.getElementById('altezzaTabella').setAttribute("height", altezzaBrowser+"px");
			if(tableMenu < altezzaBrowser) {
				document.getElementById('altezzaTabella2').setAttribute("height", altezzaBrowser+"px");
			}
			else {
				document.getElementById('altezzaTabella2').setAttribute("height", tableMenu+"px");
			}
			//var altezzaTabella = document.getElementById('leftMenu').offsetHeight;
			//alert(altezzaTabella);
			
		};
		// Salvataggio visualizzazione
		document.getElementById("LEFT_MENU_ROWS_STATUS").value = LEFT_MENU_ROWS;
		if (!LEFT_MENU_ROWS[which]){
			//document.getElementById("left_menu_content_" + which).className = "left-menu-row-content-close";
			document.getElementById("left_menu_row_" + which).className = "left-menu-label-active";
			document.getElementById("left_menu_tag_" + which).src = slideOffImage;
			//Effect.SlideUp("left_menu_content_" + which, { duration: 0.2 });
			jQuery("#left_menu_content_" + which).slideUp(callback);
		}else{
			//document.getElementById("left_menu_content_" + which).className = "left-menu-row-content";
			document.getElementById("left_menu_row_" + which).className = "left-menu-label-selected";
			document.getElementById("left_menu_tag_" + which).src = slideOnImage;
			//Effect.SlideDown("left_menu_content_" + which, { duration: 0.2 });
			jQuery("#left_menu_content_" + which).slideDown(callback); 
			if(check_is_safari()) {
				jQuery(".left-menu-slider").find('div').hide().show(0);
			}/*{
				var altezzaBrowser = document.documentElement.clientHeight;
				//document.getElementById('altezzaTabella').setAttribute("height", altezzaBrowser+"px");
				
				var altezzaContenutoDestro = document.getElementById('leftMenu').offsetHeight;
				//alert(altezzaTabella);
				document.getElementById('altezzaTabella2').setAttribute("height", altezzaContenutoDestro+"px");
			});*/
		}


	}
	
	/*var altezzaBrowser = document.documentElement.clientHeight;
	document.getElementById('altezzaTabella').setAttribute("height", altezzaBrowser+"px");*/
	
	
	
}

function openLeftMenu(){
	document.getElementById("leftMenuContainer").style.overflow = "visible";
	document.getElementById("leftMenuContainer").style.visibility = "visible";
	document.getElementById("leftMenuContainer").style.width = "100%";
	document.getElementById("leftMenu").style.width = "200px";
	document.getElementById("leftMenu").style.display = "table-cell";
	document.getElementById("leftMenu").style.position = "relative";
	//jQuery("#leftMenu").slideDown();

}

function closeLeftMenu(){
	//document.getElementById("leftMenuContainer").style.overflow = "hidden";
	document.getElementById("leftMenu").style.display = "none";
	//document.getElementById("leftMenuContainer").style.width = 1;
	//document.getElementById("leftMenuContainer").style.visibility = "hidden";
	//jQuery("#leftMenu").slideUp();
}

function openDetail(id){
	//Effect.SlideDown(id + "_slider", { duration: 0.2 });
	jQuery("#" +id + "_slider").slideDown(100);
	jQuery("#" +id + "_opener").hide();
	jQuery("#" + id + "_STATUS").val("OPEN");
}

function closeDetail(id){
	//Effect.SlideUp(id + "_slider", { duration: 0.2 });
	jQuery("#" + id + "_slider").slideUp();
	jQuery("#" + id + "_opener").show();
	jQuery("#" + id + "_STATUS").val("CLOSE");
}

function openClose(divId){
	if (window[divId].style.display != "none"){
		//Effect.SlideUp(divId, { duration: 0.2 });
		jQuery("#"+divId).slideUp();
		//window[divId] = false;
	}else{
		//Effect.SlideDown(divId, { duration: 0.2 });
		jQuery("#"+divId).slideDown();
		//window[divId] = true;
	}
	
}
function AdjustIframeHeightOnLoad(id) {
    if (document.getElementById(id).contentWindow.document.body.scrollHeight != null) { 
		document.getElementById(id).style.height = document.getElementById(id).contentWindow.document.body.scrollHeight + "px";
	} else {
		document.getElementById(id).style.height = "auto";		
	}					
    setInterval(function () {
		try {
			if (document.getElementById(id).contentWindow.document.body.scrollHeight != null) {
				document.getElementById(id).style.height = "auto";
				document.getElementById(id).style.height = document.getElementById(id).contentWindow.document.body.scrollHeight + "px";
			}
		}catch(e) {}
	}, 500);
}
function leftMenuResize(i){
	document.getElementById("leftMenu").style.width = i;
	document.getElementById("leftMenuContainer").style.width = i;
	document.getElementById("leftMenuContainer").style.overflow = "hidden";
	if (i == 2){
		document.getElementById("leftMenuContainer").style.visibility = "hidden";
	}else{
		document.getElementById("leftMenuContainer").style.visibility = "visible";
	}
}
var wi400_window_counter = 0;
var troppo_tempo;
function openWindow(url, name, w, h, modale, canClose, checkSubmit, closeFunction, bigData){
	w_iniz = w;
	h_iniz = h;
	if (typeof(w) == "undefined" || w == "" || w == 0) w = LOOKUP_WIDTH;
	if (typeof(h) == "undefined" || h == "" || h == 0) h = LOOKUP_HEIGHT;
	if (typeof(name) == "undefined") name = "lookUp";
	if (typeof(modale) == "undefined") modale = true;
	if (typeof(canClose) == "undefined") canClose = true;
	if (typeof(closeFunction) == "undefined") closeFunction = 'closeLookUp()';
	if (typeof(checkSubmit) == "undefined") checkSubmit = false;
	if (typeof(bigData) == "undefined") bigData = "";
    w = parseInt(w);
    h = parseInt(h);
    urlarray = url.split("/");
    // se è minore toldo i px partendo dalla grandezza dello schermo
	var parentObj = getParentObj();
	if(w < 0) w = top.innerWidth+w; 
	if(h < 0) h = (top.innerHeight+h)-20;
	//var parent_name = "lookup" + wi400top.wi400_window_counter;
	//wi400top.wi400_window_counter++;
	//var window_name = "lookup" + wi400top.wi400_window_counter;
    if (checkSubmit == true){
		if (rules.length > 0 && !yav.performCheck('wi400Form', rules, "inline")){
			alert(yav_config.MODULE_ERROR);
			return;
		}else{
			if (document.getElementById("ACTION_FORM_VALIDATION")){
				document.getElementById("ACTION_FORM_VALIDATION").disabled = false;
			}
		}
	}
	if (checkSubmit == "GLOBAL"){
		if (jQuery(".errorField")[0]){
			alert(yav_config.MODULE_ERROR);
			return;
		} else {
		    // Tutto OK
		}
		if (jQuery(".row-has-error")[0]){
			alert(yav_config.MODULE_ERROR);
			return;
		} else {
		    // Tutto OK
		}
	}
	// Validazione dell'intero FORM AJAX
	if (wi400top.wi400_window_counter>0){
		mydoc2 = getFrameWindow("lookup" + wi400top.wi400_window_counter+"_content");
		mydoc = mydoc2.contentWindow.document;
		//mydoc = document.getElementById("lookup" + wi400top.wi400_window_counter);
	} else {
		mydoc = document;
	}
	
	var scrollTop = jQuery(document).scrollTop();
	var isWindow = "&WI400_IS_WINDOW=1";
	var _THEAPPBASE = _APP_BASE;
	if (urlarray[1]!=_APP_BASE) {
		_THEAPPBASE = "/"+urlarray[1]+"/";
	}
    //myframe = getFrameWindowById();
    //alert(mydoc.getElementById("CURRENT_FORM").value);
	if (checkSubmit == true){
		var iserror = false;
		jQuery.ajax({
			url: _APP_BASE + "index.php?t=AJAX_VALIDATION&DECORATION=clean",
			type: "POST",
			data: jQuery("#" + APP_FORM, mydoc).serialize()+"&DECORATION=clean&DETAIL_VALIDATION=SI"+isWindow+"&f="+wi400UrlParam(url, 'f')+"&NAME_ACTION="+name,
			success: function(html) {
				var decodeJSON = jQuery.parseJSON(html);
				if (decodeJSON.decode==false) {
					if(name == "actionList") {
						alert(decodeJSON.fieldMessage);
					}else {
						if (wi400top.wi400_window_counter==0){
							//document.location.reload(true);
							window.location.href = window.location.href
						} else {
							mydoc2.contentWindow.location.reload(true);
						}
					}
					iserror = true;
					return false;
				};
			},
			async:false
		});
		if (iserror == true) return;
	}
	// Fine validazione dell'intero form
	
	if (checkSubmit == "SERVER"){
		if (document.getElementById("ACTION_FORM_VALIDATION")){
			document.getElementById("ACTION_FORM_VALIDATION").disabled = false;
		}
	}
	var parent_name = "lookup" + wi400top.wi400_window_counter;
	wi400top.wi400_window_counter++;
	var window_name = "lookup" + wi400top.wi400_window_counter;

	//var myhtml="";
	var myid = window_name + '_content';
	var myhtml = '<script>jQuery("#' + window_name + '").ready( function() {jQuery("#'+window_name+'").height(jQuery("#'+window_name+'").contents().find("body").height());});</script>';
	var caricamento = '<img id="'+window_name+'_loading" src="themes/common/images/loading.gif" style="position: absolute; width: 40px; height: 40px; top: 50%; left: 50%; margin-left: -20px; margin-top: -20px;">';
	//wi400top.jQuery("body").append(wi400top.jQuery("<div id='" + window_name + "' style='position: relative;'></div>").html("<iframe scrolling='auto' style='overflow-x: hidden; overflow-y: scroll;min-width: 99%;height:99%;' width='100%' marginwidth='0' marginheight='0' height='100%' frameborder='0' hspace='0' vspace='0' name='" + window_name + "_content' id='" + window_name + "_content' onload=\"AdjustIframeHeightOnLoad('"+myid+"')\"></iframe>"+myhtml+caricamento));
	wi400top.jQuery("body").append(wi400top.jQuery("<div id='" + window_name + "' style='position: relative;'></div>").html("<iframe scrolling='auto' style='overflow-x: hidden; overflow-y: scroll;min-width: 99%;height:99%;' width='100%' marginwidth='0' marginheight='0' height='100%' frameborder='0' hspace='0' vspace='0' name='" + window_name + "_content' id='" + window_name + "_content'></iframe>"+myhtml+caricamento));

	if (url.indexOf("?")==-1) url+="?";
	
	// Lookup decoration
	if (url.indexOf("DECORATION") == -1){
		url += "&DECORATION=lookup";
	}
	
	//cookie window size
	var to_azione = wi400UrlParam(url, 't');
	var to_form = wi400UrlParam(url, 'f');
	var window_plus_key = wi400UrlParam(url, 'WINDOW_SIZE_KEY');
	if(!window_plus_key) {
		window_plus_key = wi400GetWindowSizeKeyByUrl(url);
	}
	if(!to_form) to_form = 'DEFAULT';
	
	var windowSizeKey = to_azione+"|"+to_form;
	if(window_plus_key) {
		windowSizeKey += "|"+window_plus_key;
	}
		
	//console.log(to_azione, to_form);
	//console.log(wi400WindowSize);
	//console.log("Chiave: "+windowSizeKey);
	//console.log(wi400WindowSize);
	//console.log(document.cookie);
	if(!wi400WindowSize[windowSizeKey]) {
		wi400WindowSize = getCookieWindowSize();
	}
	
	if(wi400WindowSize[windowSizeKey]) {
		//console.log("sono dentro");
		//var lookup_size = wi400WindowSize[windowSizeKey].split("|");
		var lookup_size = wi400WindowAdapterResolution(wi400WindowSize[windowSizeKey]);
		//console.log(lookup_size);
		w = lookup_size[0];
		h = parseFloat(lookup_size[1])+10;
		
		//console.log('trovata in wi400WindowSize dimensione '+w+' h: '+h);
		
		w_ini = w;
		h_iniz = h;
	}
	// fine cookie window size
	
	//var x = (screen.height-h)/3;
	var x = (parentObj.innerHeight-h)/3;
	if(IS_MOBILE) x = 10;
	cx = wi400_window_counter * 10;
	cy = wi400_window_counter * 50;
	if (bigData.length < 2000) {
		url = url + "&" + bigData;
		bigData="";
	} else {
	// Post dei dati della finestra
		var ID_FILE = "";
		jQuery.ajax({
		    url: _THEAPPBASE + "index.php?t=AJAX_POST&DECORATION=clean"+isWindow,
		    type: "POST",
		    data: "BASE64=" + Base64.encode(bigData),
		    success: function(html) {
		      var decodeJSON = jQuery.parseJSON(html);
		      ID_FILE = decodeJSON.ID;
		    },
		    async:false
		 });
		url = url + "&ID_FILE=" + ID_FILE;
	}
	//jQuery('#' + window_name).data("bigData", bigData);
	//Funzione che ritorna la versione del ie che si sta usando 
	var ie = (function(){
	    var undef, v = 3, div = document.createElement('div'), all = div.getElementsByTagName('i');
	    while (
	        div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',
	        all[0]
	    );
	    return v > 4 ? v : 10;
	}());
	
	var bottoniDialog = {};
	if(ie <= 6) {
		bottoniDialog = {
				'Chiudi': function() {
					eval(closeFunction);
					wi400top.jQuery(window_name).remove();
		        }
		};
	}
	
	wi400top.jQuery('#' + window_name).dialog({
			resizable: true,
			modal: modale,
			width:w,
			height:h+20,
			position: ["middle",x],
			//position: [10+ cx,50+cy],
			buttons: bottoniDialog,
			resize: function (event, ui) {
				iframeId = event.target.id;
				
				objWindow = document.getElementById(iframeId+'_content').contentWindow;
				resizeDescriptionDetail(objWindow);
			},
			resizeStop: function(event, ui) {
				//var w = jQuery(this).outerWidth();
				//var h = jQuery(this).outerHeight();
				
				//console.log("size ui "+ui.size.width+" "+ui.size.height);
				
				w = ui.size.width;
				h = ui.size.height;
				
		        //console.log("STOP Width: " + w + ", height: " + h+ " => "+to_azione+" "+to_form);
		        
		        addCookieWindowSize(w, h, to_azione, to_form, window_plus_key);
		    },
			show: { 
				effect: "slideDown", 
				duration: 200 , 
				complete: function() {
					blockBrowser(false);
		        }
			},
			open: function(event, ui) {
				// Parent window, segnalare che c'è un caricamento in corso
				// @TODO Vedere Vega blockBrowser(true);
				if(typeof(IS_MOBILE) != "undefined" && IS_MOBILE) {
					jQuery('.ui-icon-gripsmall-diagonal-se').css({"width": "40px", "height": "40px", "background-position": "-76px -224px"});
					wi400top.jQuery('.ui-dialog-titlebar-close').bind('mousedown', function () { jQuery(this).trigger('click'); });
				}
				
				url = url + "&LOOKUP_PARENT=" + parent_name+'&WAIT_LOADING=true&SCROLL_TOP='+scrollTop+isWindow;
				url = url + "&CHECKID=" + decodeURIComponent(url).length;
				//alert(decodeURIComponent(url));
				//troppo_tempo = setTimeout(function() {jQuery.blockUI({ message: "<h1><img src=\"themes/common/images/busy.gif\" /> Prego attendere ...</h1>" });}, 3000);
				wi400top.jQuery('#' + window_name + '_content').attr('src', url);
				
				//Per settare il focus dentro alla finestra
				// LZ non funziona più, verificare 
				/*setTimeout(function() {
					iframes[iframes.length-1].contentWindow.focus();
				}, 300);*/
				
				//Elimino questo tag html per il focus di chrome che mette l'ombra blu sul bottone di chiusura finestra
				wi400top.jQuery('.ui-button-text').remove();
				
				wi400top.jQuery('#' + window_name + '_content').load(function () {
					wi400top.jQuery('#' + window_name + '_loading').css('visibility', 'hidden');
					setTimeout(function() {
						wi400ResizeWindow(w_iniz, h_iniz);
					}, 300);
				});
				//jQuery('#' + window_name + '_content').post(url, "marameo");
				if (canClose==false) {
					wi400top.jQuery(this).closest('.ui-dialog').find('.ui-dialog-titlebar-close').hide();
				}
				//var my_data = jQuery("#lookup1").data('bigData');
				//alert(my_data);'
				// Se ci mette troppo tempo
			},
			close: function(event, ui) {
					eval(closeFunction);
					wi400top.jQuery('#'+window_name).remove();
			}
	});
	
	if (canClose==true && closeFunction == 'closeLookUp()'){
		wi400top.jQuery('#' + window_name).dialog( "option", "closeOnEscape", true );
	}
}
function updateField(fieldId){
	setTimeout(function(){
		var obj = jQuery('#' + fieldId);
		obj.focus();
		obj.blur();
	},100);
	
}

function lookUp(idLookUp, fromList, fromRow) {
	w = LOOKUP_WIDTH;
	h = LOOKUP_HEIGHT;
	
	var lookup 		 = window[idLookUp + "_LOOKUP"];
	
	var fieldId = lookup.get("FIELD_ID");
	var detailId = "";
	if(!fromList) {
		detailId = "&DETAIL_ID="+jQuery('#'+fieldId).attr("detailid");
	}
	
	var action 		 = lookup.get("ACTION");
	var jsParameters = lookup.get("JS_PARAMETERS");

	// Lookup contenuto in una lista
	var fromUrl = "";
	if (fromList) {
		fromUrl = "&FROM_LIST=" + fromList;
	}
	if (typeof (fromRow) != "undefined"){
		fromUrl += "&FROM_ROW=" + fromRow;
		var colonna = fieldId.split("-");
		colonna = colonna[colonna.length-1];
		fromUrl += "&COLONNA=" + colonna;
	}
	// Se è una lista mi prendo anche eventuali keys
	var otherValue="";
	
	if (fromList) {
		var myObj = jQuery("#"+fieldId);
		otherValue = "&WI400_OTHERKEY=" +get_column_key(myObj[0]);
		otherValue += "&WI400_LIST_KEY=" + document.getElementById(fromList + "-" + fromRow).value;
		otherValue += "&FROMLIST=1";
		otherValue += getJsParametersList(jsParameters, fromList, fromRow);
	}
	// Aggiunta valori della pagina
	jsParameters = getJsParameters(jsParameters);
	//alert(parameters);
		
	openWindow(_APP_BASE + "index.php?LOOKUP=" + idLookUp + "&t=" + action + detailId + "&FIELD_ID="+ fieldId + fromUrl + jsParameters + otherValue);
}
function getJsParametersList(jsParameters, fromList, fromRow) {
	var jsParamArray = jsParameters.split("|");
	jsParameters = "";
	for (var jsCount = 0; jsCount < jsParamArray.length; jsCount++){
		var idF = fromList + "-" + fromRow + "-" + jsParamArray[jsCount];
		var id = jsParamArray[jsCount];
		if (document.getElementById(idF)){
			if(!jQuery('#'+idF+"_MULTIPLE").length) {
				jsParameters += "&" + id + "=" + encodeURIComponent(document.getElementById(idF).value);
			}else {
				jQuery('input[name="'+idF+'[]"]').each(function(index, ele) {
					jsParameters += "&"+id+"[]="+encodeURIComponent(ele.value);
				});
			}
		}
	}
	
	return jsParameters;
}
function getJsParameters(jsParameters) {
	var jsParamArray = jsParameters.split("|");
	jsParameters = "";
	for (var jsCount = 0; jsCount < jsParamArray.length; jsCount++){
		var id = jsParamArray[jsCount];
		if (document.getElementById(id)){
			if(!jQuery('#'+id+"_MULTIPLE").length) {
				jsParameters += "&" + id + "=" + encodeURIComponent(document.getElementById(id).value);
			}else {
				jQuery('input[name="'+id+'[]"]').each(function(index, ele) {
					jsParameters += "&"+id+"[]="+encodeURIComponent(ele.value);
				});
			}
		}
	}
	
	return jsParameters;
}

function customTool(action, form, campo, jsParameters, width, height, validation, gateway, bigData) {
	if (typeof(gateway) == "undefined") gateway = "";
	jsParameters = getJsParameters(jsParameters);
	if(bigData) bigData = jQuery("#" + APP_FORM).serialize();
	
	openWindow(_APP_BASE + APP_SCRIPT + '?t='+action+'&f='+form+"&g="+gateway+campo+jsParameters, 'customTool', ''+width, ''+height, true, true, validation, 'closeLookUp()', bigData);
}


function showTree(idTree){
	w = 350;
	h = 500;
	openWindow(_APP_BASE + "index.php?t=TREELIST&IDTREE=" + idTree + "&DECORATION=lookup", "filterTree", w, h);
}

function showItem(){
	w = 600;
	h = 370;
	var itemCode = document.getElementById("SELECT_ITEM_CODE").value;
	if (itemCode != ""){
		openWindow(_APP_BASE + APP_SCRIPT + "?DECORATION=lookUp&t=TLISDETA&DETAIL_KEY="+itemCode, "detailProduct", w, h);
	}else{
		alert("Inserire il codice articolo");
	}
}

function showImageZoom(objType,imgType,imgName,directUrl){
	if (typeof(directUrl) == "undefined") directUrl = false;
	
	openWindow(_APP_BASE + APP_SCRIPT + "?t=IMAGEZOOM&DECORATION=lookUp&OBJ_TYPE=" + objType + "&IMAGE_TYPE=" + imgType + "&IMAGE_NAME=" + imgName + "&DIRECT_URL=" + directUrl, "imageZoom", screen.width-200, screen.height - 200);
}

function closeLookUp(remove){
	remove = typeof(remove) == "undefined" ? true : remove;
	
	if(this.CURRENT_ACTION != wi400top.CURRENT_ACTION) {
		wi400top.closeLookUp(remove);
		return;
	}

	var window_name = "#lookup" + wi400top.wi400_window_counter;
	
	//wi400top.jQuery(window_name+"_content").attr("src", 'ciao');
	//jQuery(window_name+"_content").remove();
	
	//alert(wi400top.location);
	if(wi400top.wi400_window_counter > 0) {
		wi400top.wi400_window_counter--;
	}
	if (wi400top.wi400_window_counter>0) {
		try {
			var iframe = jQuery('iframe', parent.window.document);
		} catch (e) {
			var iframe = jQuery('iframe', window.document);
		}
		iframe[wi400top.wi400_window_counter-1].focus();
	} else {
		window.focus();
	}
	
	if(remove) {
		wi400top.jQuery(window_name+"_content").remove();
		wi400top.jQuery(window_name).remove();
	}
}
function getWI400Top() {
	try 
	{
	    if (top.document || top.document.domain){
	          //Sono stato aperto direttamente da browse
	    		return top;
	    }
	}		
		catch(e) {
			var jk = 0;
			previous = window;
			var dominio = window.document.domain;
			//alert(dominio);
			for (jk; jk < 10; jk++) {
				try {
					//var vuoto = previuos.parent.location;
					var vuoto = previous.parent.domain;
					if (previous.parent.document.domain != dominio) {
						break;
					}
					previous = previous.parent;
					//alert(previous.document.domain);
				} catch(e) {
					// Sono arrivato alla fine della ricerca
					break;
				}
			}
			//var iframes = previous.document.getElementById('wi400Form').parentNode;
			//wi400top = iframes.parentNode.parentNode.defaultView;
			return previous;
	}
}
function closeLookUpAndReloadList(idList){
	if (IFRAME_LOOKUP){
		doPagination(idList, "RELOAD");
		closeLookUp();
		//top.f_dialogClose();
	}else{
		closeLookUp();
		window.opener.doPagination(idList, "RELOAD");
		//self.close();
	}
}

/*function closeWindow() {
	if (IFRAME_LOOKUP){
		wi400top.location.href=wi400top.location.href;
		closeLookUp();
		//top.f_dialogClose();
	}
	else{
		window.opener.location.href=window.opener.location.href;
		//self.close();
		closeLookUp();
	}
}*/
// Utilizzabile anche per ricaricare la pagina intera (compreso il contenuto fuori dall'i-frame), ad esempio in caso in $actionContext->onSuccess();
function closeWindow(history, nameIframe) {
	if (typeof(history) == "undefined") history=false;
	var hst_nav ="";
	if (history==true) {
		hst_nav = "&HST_NAV";
	}

	if (IFRAME_LOOKUP){
		if(nameIframe) {
			var obj = getFrameWindow(nameIframe);
			obj.contentWindow.location.reload();
			closeLookUp();
		}else {
			var addHmac = "";
			if(wi400top.location.href.indexOf("?") != "-1") {
				addHmac = hst_nav+"&WI400_HMAC="+__WI400_HMAC;
			}
			wi400top.location.href=wi400top.location.href+addHmac;
			wi400top.f_dialogClose();
		}
	}
	else{
		window.opener.location.href=window.opener.location.href+hst_nav+"&WI400_HMAC="+__WI400_HMAC;
		self.close();
	}
}
function reloadPreviousWindow(windowname) {
	if (typeof(windowname) == "undefined") windowname = "lookup1_content";
	//blockBrowser(true, "Reload Finestra ...");
	//var iframes = window.parent.document.getElementsByTagName('iframe');
	iframes = wi400_getIFrames();
	 for (var i=0; i<iframes.length; ++i)
     {
	     try
          {
		      var d=iframes[i].contentDocument || iframes[i].contentWindow.document || iframes[i].document; //ie | firefox etc | ie5
	          rtn_iframe=iframes[i];
			  if (rtn_iframe.name==windowname) {
				rtn_iframe.contentWindow.location.reload(true);
			  }
          }
      catch(e) {}
     }
	 blockBrowser(false);
}
function getFrameWindow(windowname) {
	if (typeof(windowname) == "undefined") return false;
	//IFrameObj = parent.window.frames;
	//var iframes = window.parent.document.getElementsByTagName('iframe');
	iframes = wi400_getIFrames();
	 for (var i=0; i<iframes.length; ++i)
     {
	     try
          {
		      var d=iframes[i].contentDocument || iframes[i].contentWindow.document || iframes[i].document; //ie | firefox etc | ie5
	          rtn_iframe=iframes[i];
			  if (rtn_iframe.name==windowname) {
				return rtn_iframe;
			  }
          }
      catch(e) {}
     }
	 return null;
}
function wi400_getIFrames() {
	wi400top = getWI400Top();
	iframes = wi400top.document.getElementsByTagName('iframe');
	return iframes;
}
function wi400Empty() {
}
function wi400_topblock(mode) {
	try
	{
		wi400top.blockBrowser(mode);
	}
	catch(e)
	{
		self.parent.blockBrowser(mode);
	}	
}
/*
 Funzione definitiva per capire il contesto della variabile
 - ID della variabile
 - less_one True se saltare il frame corrente nella ricerca
 - Single True, ritorna solamente l'oggetto ID e non tutto il frame
*/
/*function getFrameWindowById2(id, less_one, single) {
	// Migliorare per cercare il proprio IFRAME
	if (typeof(id) == "undefined") return false;
	if (typeof(single) == "undefined") single=true;
	if (typeof(less_one) == "undefined") less_one=false;
	
	var parentObj="";
	var parentObj2=""; 
	
	var idList = id;
	if(id.indexOf('Container') != -1) idList = id.slice(0, -9);
	if(id.indexOf('fixedContainer') != -1) idList = id.slice(0, -15);
	//var inIframe = wi400GetCookie('iframe_'+idList);
	var inIframe = wi400GetCookie('WI400_LAST_IFRAME');
	// Cerco se per caso devo rimanere all'interno del mio contesto iframe
	var lookUpParent="";
	if (!inIframe && inIframe!="") {
		lookUpParent = document.getElementById("WI400_IS_IFRAME");
	} else {
		lookUpParanet = inIframe;
	}
	//console.log(lookUpParanet);
	jQuery("iframe").each(function(){
		 jQuery(this).contents().find("iframe").each(function(){
		 	// Se meno uno salto me stesso
		 	if (less_one && window.frameElement.id) {
		 		console.log("less one!");
		 		if(jQuery(this).attr('id')==window.frameElement.id) {
		 			return true;
		 		}
		 	}	
			if(jQuery(this).contents().find('#'+id).length>0) {
				//console.log("trovato:"+id+" single "+single); 
				if (single==true) {
					parentObj=jQuery(this).contents().find('#'+id);
				} else {
				    parentObj=jQuery(this)[0];
				}	
				if (lookUpParent && lookUpParent.value != "" ){
					if(jQuery(this).attr('id') == lookUpParent.value){
						parentObj2=parentObj;
					}
				}
			}
			return parentObj;
		 });
		 
		 return parentObj;	
    });
    var datoBack="";
    if (parentObj!="") {
    	if (parentObj2!="") {
    		datoBack = parentObj2;
    	}
    	datoBack = parentObj;
    } else {
     	if (single==true) {
		  datoBack = jQuery('#'+id);
		} else {
		  	try {
				var vuoto = parent.document.domain;
				datoBack = parent;
			} catch (e) {
				datoBack=window;
			}
		}
	}
	return datoBack;
}*/
/**
 * Cerco il primo frame andando a ritroso che contiene l'ID del campo
 * Ma devo partire da quello che mi contiene
 * @param windowname
 * @returns
 */
function getFrameWindowById(id, less_one, single) {
	if (typeof(id) == "undefined") return false;
	if (typeof(less_one) == "undefined") less_one = false;
	if (typeof(single) == "undefined") single = false;
	return wi400SearchElement(id, less_one, single);
	//return getFrameWindowById2(id, less_one, single);
	/*iframes = wi400_getIFrames();
	//console.log(window.frameElement.id);
	var i = iframes.length-1;
	if (less_one) {
		i = i -1;
	} else {
		// Cerco Prima su Frame presente
		if (IFRAME_LOOKUP){
			var lookUpParent = document.getElementById("WI400_IS_IFRAME");
			if (lookUpParent && lookUpParent.value != ""){
				//alert(lookUpParent.value);
				// Sono in un lookup aperto da un altro lookup
				for (x = 0; x < iframes.length; x++){
					//alert(iframes[x].name);
					if(iframes[x].name == lookUpParent.value){
						//alert("TROVATO");
						//parentObj = iframes[x];
						if (typeof(iframes[x].contentWindow[0]) == "undefined") {
							parentObj = iframes[x].contentWindow;
						} else {  
							parentObj = iframes[x].contentWindow[0];
						}
						//console.log(parentObj);
						//console.log(id);
						//alert(id);
						if (parentObj.document.getElementById(id)){
							return parentObj;
						}
					}
				}
			}
		}
	}	
	for (i; i>=0; i--) {
		try {
			//Verifico se esiste il campo, allora è l'iframe che cerco
			//TODO provare a togliere il ritorno del contentWindow[0]
			if (typeof(iframes[i].contentWindow[0]) == "undefined") {
				parentObj = iframes[i].contentWindow;
			} else {  
				parentObj = iframes[i].contentWindow[0];
			}
			
			if (parentObj.document.getElementById(id)){
				return parentObj;
			}

			//Funzione alternativa con jQuery
			if(jQuery(iframes[i]).contents().find('#'+id).length) {
				return iframes[i].contentWindow;
			}
		}catch(e) {}
	}
	try {
		var vuoto = parent.document.domain;
		parentObj = parent;
	} catch (e) {
		parentObj=window;
	}
	//parentObj = window;
	//console.log(getFrameWindowById2(id, less_one).contents());
	//console.log(parentObj);
	return parentObj;*/
}
function reloadSelAction(azione, form, idList) {
//	alert("reloadSelAction");
	
	if (typeof(idList) == "undefined") idList = "";
	
	if(idList!="") {
		azione = azione + "&IDLIST=" + idList;
	}
//	alert("AZIONE: " + azione + " - FORM: " + form);
	
	if (IFRAME_LOOKUP){
		wi400top.blockBrowser(false);
//		alert("IFRAME_LOOKUP");
		wi400top.doSubmit(azione, form);
//		top.f_dialogClose();
	}
	else{
//		alert("WINDOW");
		blockBrowser(false);
		window.opener.doSubmit(azione, form);
//		self.close();
	}
	
	closeLookUp();
}

function resubmitPage(validation){
	if (typeof(validation) == "undefined") validation="";
	doSubmit(CURRENT_ACTION, CURRENT_FORM+validation);
}

function risottomettiForm(form, action) {
	if (typeof(action) == "undefined") action = CURRENT_ACTION;
	
	doSubmit(action, form);
}

function checkLookUp(which, idLookUp, fromList, fromRow){
	if (which.value == "?"){
		which.value = "";
		lookUp(idLookUp, fromList, fromRow);
	}
}

function checkTree(which, idTree){
	if (which.value == "?"){
		which.value = "";
		showTree(idTree);
	}
}

function checkBlockBrowser(context){

	var checkBlock = _BLOCK_BROWSER;
	if (typeof(context) != "undefined"){
		if(typeof(_BLOCK_BROWSER_MAP.get(context)) == "undefined"){
			checkBlock = false;
		}else{
			checkBlock = _BLOCK_BROWSER_MAP.get(context);
		}
	}

	if (checkBlock){
		alert(yav_config.WAITING);
		return false;
	}else{
		return true;
	}
}

function updateBrowser(){
	if (document.getElementById("UPDATE_STATUS") && document.getElementById("UPDATE_STATUS").value == "ON"){
		//document.getElementById("pageLoader").className="pageUpdate";
		//document.getElementById("pageLoader").innerHTML = "In modifica";
		jQuery('#wi400_modify_box').slideDown(400);
		jQuery('#wi400_modify_box').attr('class', "pageUpdate");
		jQuery('#wi400_modify_box').html("In modifica");
	}
}
function blockBrowser(status, message, context){
	if (typeof(status) == "undefined" || status){
		var num = 400;
		if (/iPad|iPhone|iPod/.test(navigator.userAgent) 
				&& !window.MSStream && arguments.callee.caller.name == "doSubmit") {
			num = 0;
		}
		jQuery('#wi400_info_box').slideDown(num);
		
		if (typeof(context) != "undefined"){
			_BLOCK_BROWSER_MAP.put(context, true);
		}else{
			_BLOCK_BROWSER = true;
		}
		
		
		document.body.style.cursor= "wait";
	}else{
		
		jQuery('#wi400_info_box').slideUp(300);
		if (typeof(context) != "undefined"){
			_BLOCK_BROWSER_MAP.put(context, false);
		}else{
			_BLOCK_BROWSER = false;
		}
		if (document.body) {
			document.body.style.cursor= "default";
		}
	}
}
/*function blockBrowser(status, message, context){
	/*if ( window.self === window.top ) { 
		mydoc = window.document; 
		alert("non sono in un iframe");
	} else {
		alert("sono in un iframe!!");
		mydoc = window.top.document
	}
	mydoc = window.document; 
	if (mydoc.getElementById("pageLoader")==null) {
		mydoc = window.top.document;
	}
	if (typeof(status) == "undefined" || status){
		if (mydoc.getElementById("pageLoader")){
			mydoc.getElementById("pageLoader").className="pageLoader";
			loadingMessage = yav_config.LOADING;
			if (typeof(message) != "undefined" && message != ""){
				loadingMessage = message; 
			}
			mydoc.getElementById("pageLoader").innerHTML = loadingMessage;
		}
		
		if (typeof(context) != "undefined"){
			_BLOCK_BROWSER_MAP.put(context, true);
		}else{
			_BLOCK_BROWSER = true;
		}
		
		
		mydoc.body.style.cursor= "wait";
	}else{
		mydoc.getElementById("pageLoader").innerHTML = "";
		mydoc.getElementById("pageLoader").className="";
		
		if (typeof(context) != "undefined"){
			_BLOCK_BROWSER_MAP.put(context, false);
		}else{
			_BLOCK_BROWSER = false;
		}

		mydoc.body.style.cursor= "default";
	}
}*/


function checkSuccessToken(responseText, token){
	if (typeof(token) == "undefined") token = "SUCCESS";
	return (responseText.indexOf(token) >= 0);
}

function doAjax(ajaxId, checkSubmit, sync){
	
	if (typeof(checkSubmit) == "undefined") checkSubmit = false;
	
	// Controllo su validazione
	if (checkSubmit == true){
		if (rules.length > 0 && !yav.performCheck('wi400Form', rules, "inline")){
			alert(yav_config.MODULE_ERROR);
			return;
		}else{
			if (document.getElementById("ACTION_FORM_VALIDATION")){
				document.getElementById("ACTION_FORM_VALIDATION").disabled = false;
			}
		}
	}
	
	if (sync == true){
		if (checkBlockBrowser()){
			blockBrowser(true);
		}
	}
	
	eval("doAjax_" + ajaxId + "()");
}

function setUpdateStatus(uStatus){
	if (document.getElementById("UPDATE_STATUS")){
		document.getElementById("UPDATE_STATUS").value = uStatus;
		document.getElementById("UPDATE_STATUS").disabled = false;
		updateBrowser();
	}
	
}

function getUpdateStatus(){
	var uStatus = "";
	if (document.getElementById("UPDATE_STATUS")){
		uStatus = document.getElementById("UPDATE_STATUS").value;
	}
	return uStatus;
}
function set_enable_log_xmlservice(type, value) {
	jQuery.ajax({
		type: "GET",
		url: _APP_BASE + APP_SCRIPT + "?t=AJAX_DEBUG_PROGRAM&DECORATION=clean&value="+value+"&"+type
	}).done(function ( response ) {
		//
		resubmitPage();
	}).fail(function ( data ) {
		//
	});
}
function set_enable_debug(value) {
	jQuery.ajax({
		type: "GET",
		url: _APP_BASE + APP_SCRIPT + "?t=DEBUG&DECORATION=clean&f=SET_XMLSERVICE_DEBUG_ACTIVE&value="+value
	}).done(function ( response ) {
		var src = jQuery("#icon_debug").attr('src');
		var array_src = src.split("/");
		if(response) {
			array_src[(array_src.length)-1] = "debug_active.png";
			jQuery('#icon_debug').attr('onClick', "set_enable_debug('"+response+"');");
		}else {
			array_src[(array_src.length)-1] = "debug_not_active.png";
			jQuery('#icon_debug').attr('onClick', "set_enable_debug('');");
		}
		src = array_src.join("/");
		jQuery("#icon_debug").attr('src', src);
	}).fail(function ( data ) {
		alert("Error! Non è stato possibile disattivare la modalità debug!");
		console.log("La chiamata ajax è andata in errore!");
	});
}

function doSubmit(actionName, formName, checkSubmit, checkUpdate, confirmMessage, showLoading, cleanForm){
//	alert("doSubmit - AZIONE: " + actionName + " - FORM: " + formName);
	// Default value
	if (typeof(confirmMessage) == "undefined") confirmMessage = "";
	if (typeof(checkUpdate) == "undefined") checkUpdate = false;
	if (typeof(checkSubmit) == "undefined") checkSubmit = false;
	if (typeof(showLoading) == "undefined") showLoading = false;
	if (typeof(cleanForm) == "undefined") cleanForm = "";
	var clean ="";
	// Controllo stato browser
	if (checkBlockBrowser()){
		
		if (checkUpdate != "preserve"){
			if ((getUpdateStatus() == "ON") && (checkUpdate == true)){
				if (!confirm("Le modifiche della pagina verranno perse. Continuare?")){
					return;
				}
			}
			setUpdateStatus("OFF");
		}
		
		// Stato iniziale di validazione
		if (document.getElementById("ACTION_FORM_VALIDATION")){
			document.getElementById("ACTION_FORM_VALIDATION").disabled = true;
		}
		
		// Controllo messaggio conferma
		if (confirmMessage != ""){
			if (!confirm(confirmMessage)){
				return;
			}
		}
		
		// Controllo su validazione Se abilitata sul form oppure a livello globale
		if (checkSubmit == true || checkSubmit == "GLOBAL"){
			if (rules.length > 0 && !yav.performCheck('wi400Form', rules, "inline")){
				alert(yav_config.MODULE_ERROR);
				return;
			}else{
				if (document.getElementById("ACTION_FORM_VALIDATION")){
					document.getElementById("ACTION_FORM_VALIDATION").disabled = false;
				}
			}
		}
		
		if (checkSubmit == "SERVER"){
			if (document.getElementById("ACTION_FORM_VALIDATION")){
				document.getElementById("ACTION_FORM_VALIDATION").disabled = false;
			}
		}
		if (checkSubmit == "GLOBAL"){
			if (jQuery(".errorField")[0]){
				alert(yav_config.MODULE_ERROR);
				return;
			} else {
			    // Tutto OK
			}
			if (jQuery(".row-has-error")[0]){
				alert(yav_config.MODULE_ERROR);
				return;
			} else {
			    // Tutto OK
			}
		}
	
		if (typeof(formName) == "undefined"){
			formName = "";
		}
		if (typeof(actionName) == "undefined"){
			actionName = CURRENT_ACTION;
		}
		
		var scrollTop = jQuery(document).scrollTop();
		
		// Bloccaggio browser
		blockBrowser(true);
		if (showLoading)  document.getElementById(APP_FORM).style.display = "block";
		var formObj = document.getElementById(APP_FORM);
		if (cleanForm!="") {
			clean ="&cleanID=" +cleanForm;
		}
		// Aggiorno l'eventuale presenza di scheda editor
		//if (typeof(CKEDITOR) != "undefined") {
		//	for(var instanceName in CKEDITOR.instances)
		//	    CKEDITOR.instances[instanceName].updateElement();
		//}

		formObj.action = _APP_BASE + APP_SCRIPT + "?t=" + actionName + "&f=" + formName + clean;
		//formObj.action = _APP_BASE + APP_SCRIPT;
		// 02/05/2015 LZ Tentativo Patch per leggere sempre e comunque in post i campi disabled
		//jQuery("input").removeAttr("disabled");
		jQuery("input:checkbox").prop('disabled', false);
		//jQuery("#target :input").prop("disabled", false);
		
		// Aggiunta chiavi sul post
		var string = _APP_BASE + APP_SCRIPT + "?t=" + actionName + "&f=" + formName + clean;
		jQuery('#' + APP_FORM).append('<input type="hidden" name="SCROLL_TOP" value="'+scrollTop+'"/>');
		jQuery('#' + APP_FORM).append('<input type="hidden" name="wi400_validate_url" value="'+string.length+'" />');
		jQuery('#' + APP_FORM).append('<input type="hidden" name="WI400_HMAC" value="'+__WI400_HMAC+'" />');
		//		jQuery('#' + APP_FORM).append('<input type="hidden" name="wi400_post_data" value="t='+actionName + "&f=" + formName + clean+'" />');
		formObj.submit();
	}
}



function checkDateFormat(el){
	
	var error = false;
	
	if (typeof(el.value) != "undefined" && el.value != "" && el.value != "00/00/0000"){
		var dateFormat = yav_config.DATE_FORMAT;
	    ddReg = new RegExp("dd");
	    MMReg = new RegExp("MM");
	    yyyyReg = new RegExp("yyyy");
	    if ( !ddReg.test(dateFormat) || !MMReg.test(dateFormat) || !yyyyReg.test(dateFormat)  ) {
	        yav.debug('DEBUG: locale format ' + dateFormat + ' not supported');
	    } else {
	        ddStart = dateFormat.indexOf('dd');
	        MMStart = dateFormat.indexOf('MM');
	        yyyyStart = dateFormat.indexOf('yyyy');
	    }
	    strReg = dateFormat.replace('dd','[0-9]{2}').replace('MM','[0-9]{2}').replace('yyyy','[0-9]{4}');
	    reg = new RegExp("^" + strReg + "$");
	    if ( !reg.test(el.value) ) {
	        yav.highlight(el, yav_config.inputclasserror);
	        error = true;
	    } else {
	        dd   = el.value.substring(ddStart, ddStart+2);
	        MM   = el.value.substring(MMStart, MMStart+2);
	        yyyy = el.value.substring(yyyyStart, yyyyStart+4);
	        if ( !yav.checkddMMyyyy(dd, MM, yyyy) ) {
	            yav.highlight(el, yav_config.inputclasserror);
	            error = true;
	        }
	    }
		
	    if (error == true){
	    	alert("Formato data non corretto!");
	    	el.value = "";
	    }
	}
	
}

//Controlla che la data passata esiste 
function checkDateExists(year, month, day) {
    var d = new Date(year, month - 1, day);
    return d.getFullYear() === year && (d.getMonth() + 1) === month && d.getDate() === day;
};

function currencyFormatter(amount, decimals)
{
	// Pulizia numero
	currency = amount.replace(_THOUSAND_SEPARATOR,"");
	if (_THOUSAND_SEPARATOR != ""){
		while(currency.indexOf(_THOUSAND_SEPARATOR)>=0){
			currency = currency.replace(_THOUSAND_SEPARATOR,"");
		}
	}

	if (_DECIMAL_SEPARATOR != ""){
		currency = currency.replace(_DECIMAL_SEPARATOR,"#");
		// Elimina altri eventuali .
		while(currency.indexOf(_DECIMAL_SEPARATOR)>=0){
			currency = currency.replace(_DECIMAL_SEPARATOR,"");
		}
		currency = currency.replace("#",".");
	}

	var i = parseFloat(currency);
	if(isNaN(i)) { i = 0; }
	
	var minus = '';
	if(i < 0) { minus = '-'; }
	
	var truncator = 1;
	for (t = 0; t < decimals; t++) truncator = truncator * 10;
	
	i = Math.abs(i);
	i = parseInt(Math.round(i*truncator), 10);
	i = i / truncator;
	s = new String(i);
	s = minus + s.replace(".",_DECIMAL_SEPARATOR);
	
	// Aggiunta eventuale _DECIMAL_SEPARATOR
	if(decimals > 0 && s.indexOf(_DECIMAL_SEPARATOR) < 0) { s += _DECIMAL_SEPARATOR; }

	// Aggiunta eventuali 0
	while(decimals > 0 && s.indexOf(_DECIMAL_SEPARATOR) >= (s.length - decimals)){
		s = s += '0';
	}
	
	// divido decimali da interi
	var amountSplit = s.split(_DECIMAL_SEPARATOR,2);
	var intPart = amountSplit[0];
	var decimalPart = amountSplit[1];
    
	intPart = intPart.replace("-","");
	intPart = intPart.replace("+","");
	var n = new String(intPart);

	var a = [];
	while(intPart.length > 3)
	{
		var nn = intPart.substr(intPart.length-3);
		a.unshift(nn);
		intPart = intPart.substr(0,intPart.length-3);
	}
	if(intPart.length > 0) { a.unshift(intPart); }
	intPart = a.join(_THOUSAND_SEPARATOR);
	
	s = minus + intPart;
	if (decimals > 0) s += _DECIMAL_SEPARATOR + decimalPart;
	return s;
}

/*var reorderStatus = false;
function startReorderList(idField){
	var fieldObj = document.getElementById(idField);
	var listId = idField + "_PARENT";
	if (!reorderStatus){
		Sortable.create(listId);
		document.getElementById(listId).className='activeOrder';
		fieldObj.disabled = true;
		fieldObj.value = "";
	}else{
		Sortable.destroy(listId);
		document.getElementById(listId).className='deactiveOrder';
		fieldObj.disabled = false;
	}
	reorderStatus = !reorderStatus;
	
}*/
function startReorderList(idField){
	var fieldObj = document.getElementById(idField);
	var listId = idField + "_PARENT";
	jQuery( "#" + listId ).sortable({});
	jQuery( "#" + listId ).disableSelection();
}

function wi400ResizeWindow(w, h, num, setW, setH) {
	var setSize = false;
	
	if(!setW && !setH) {
		if(w || h) {
			//console.log("Hai settato le dimensioni finestra quindi esco!");
			return;
		}
		//console.log("Resize normale");
	}else {
		setSize = true;
		//console.log("set manuale Resize");
	}

	var dialogObj = "";
	if(num) {
		dialogObj = jQuery('[aria-describedby$="lookup'+num+'"]');
	}else {
		dialogObj = jQuery('.ui-dialog').last();
	}
	
	if(!dialogObj.length) {
		return;
	}
	
	var iframeObj = dialogObj.find('iframe');
	var num_lookup = "";
	if(iframeObj) {
		if(num) {
			num_lookup = num;
		}else {
			num_lookup = (((iframeObj.attr('id')).split("_"))[0]).slice(-1); //Recupero il numero di lookup aperto
		}
	}else {
		return;
	}
	
	if(!setSize) {
		var base = iframeObj.contents().find('#tableWidth').innerWidth();
		
		// Alternativa a BASE
		if (!base) {
			base = iframeObj.contents().find('#wi400_msg_box').innerWidth();
			base = base + 50;
		}
		var altezza = parseInt(iframeObj.contents().find('#lookup_content').innerHeight());
	}else {
		var base = setW;
		var altezza = setH;
		//console.log('setSize w:'+base+' h: '+altezza);
		
	}
	
	var altezzaBrowser, larghezzaBrowser;
	var plusBase = 0;

	if (navigator.appVersion.indexOf("MSIE 7.") != -1) {
	    plusBase = 150;
	}
	
			
	if(num_lookup > 1) {
		altezza += 51;
		plusBase += 121;
	}else{
		altezza +=  38;
		plusBase += 80;
	}
	
	altezzaBrowser = document.documentElement.clientHeight;
	larghezzaBrowser = document.documentElement.clientWidth;
	var app = altezza +80;
	if(app >altezzaBrowser) {
		altezza = altezzaBrowser - 100;
	}

	var tree = iframeObj.contents().find("div[id^='TREE_']").length;

	if(!tree) {
		dialogObj.css({'height': "auto"});
		jQuery('#lookup'+num_lookup).css({'height': altezza+"px"});
		if(altezza > 490) {
			var scrollTop = jQuery(document).scrollTop();
			dialogObj.css({top: (scrollTop+10)+"px"});
		}
	}
	if(base) {
		var width_dialog = base+plusBase;
		
		jQuery('#lookup'+num_lookup).css({'width': "auto"});
		if(width_dialog < larghezzaBrowser) {
			dialogObj.css({'width': width_dialog+"px"});
		
			//Centramento finestra
			dialogObj.css({left: ((larghezzaBrowser/2)-(width_dialog/2))+"px"});
		}else {
			var marginBrowser = 40;
			dialogObj.css({'width': (larghezzaBrowser-marginBrowser)+"px"});
			dialogObj.css({left: (marginBrowser/2)+"px"});
		}
	}
	
	if(!setSize) {
		var url = iframeObj.attr('src');
		var to_azione = wi400UrlParam(url, 't');
		var to_form = wi400UrlParam(url, 'f');
		if(!to_form) to_form = 'DEFAULT';
		
		var window_plus_key = wi400UrlParam(url, 'WINDOW_SIZE_KEY');
		if(!window_plus_key) {
			window_plus_key = wi400GetWindowSizeKeyByUrl(url);
		}
		
		addCookieWindowSize(parseFloat(dialogObj.css('width')), altezza, to_azione, to_form, window_plus_key, false);
	}
}

function cleanField(which) {
	document.getElementById(which).value ="";
	jQuery("#"+which).trigger("onchange");
	var descriptionObj = document.getElementById(which+"_DESCRIPTION");
	if(descriptionObj) {
		descriptionObj.innerHTML ="&nbsp;";
	}	
}

function cleanNumericField(which) {
	document.getElementById(which).value = 0;
	jQuery("#"+which).trigger("onchange");
	var descriptionObj = document.getElementById(which+"_DESCRIPTION");
	if(descriptionObj) {
		descriptionObj.innerHTML ="0";
	}	
}

function multiFieldAddRemove(action, id, which, ajaxDecode, sortField, checkDuplicate){
	var lessone = false;
	
	if (typeof(ajaxDecode) == "undefined"){
		ajaxDecode = false;
	}
	var sortFieldText = 'true';
	if (typeof(sortField) == "undefined"){
		sortField = false;
		sortFieldText = 'false';
	}
	
	if(typeof(checkDuplicate)=="undefined") {
		checkDuplicate = true;
	}
//	alert("DUP:"+checkDuplicate);
	
	var win_count = 1;
	if (typeof(a_windows) == "undefined"){
		win_count = 0;
		// Provo a contarle ...
		//IFrameObj = parent.window.frames;
		IFrameObj = wi400top.document.getElementsByTagName('iframe');
		// Sono in un lookup aperto da un altro lookup conto il numero di finestre
		for (var y = 0; y < IFrameObj.length; y++){
				//
		}
		win_count = y;
	} else {
		win_count = a_windows.length;
	}

	//var fieldParent = document.getElementById(id + "_PARENT");
	if (IFRAME_LOOKUP && win_count>1){
		parentObj = getFrameWindowById(id, lessone);
		/*if (typeof(frame.contentWindow[0])=="undefined") {
			parentObj = frame.contentWindow;
		} else {
			parentObj = frame.contentWindow[0];
		}*/
		var fieldParent = parentObj.document.getElementById(id + "_PARENT");
		if (parentObj.document.getElementById(id + "_DESCRIPTION")){
			parentObj.document.getElementById(id + "_DESCRIPTION").innerHTML = "&nbsp;";
		}
	} else {
		var fieldParent = document.getElementById(id + "_PARENT");
		if (document.getElementById(id + "_DESCRIPTION")){
			document.getElementById(id + "_DESCRIPTION").innerHTML = "&nbsp;";
		}
	}
	/*if (document.getElementById(id + "_DESCRIPTION")){
		document.getElementById(id + "_DESCRIPTION").innerHTML = "&nbsp;";
	}*/
	if (action == "ADD"){
		if (IFRAME_LOOKUP && win_count>1){
			var fieldObj = parentObj.document.getElementById(id);
		} else {
			var fieldObj = document.getElementById(id);
		}
		
		if (fieldObj.value == "" && fieldObj.getAttribute('blankvalue') == null){
			alert(yav_config.REQUIRED_VAL);
		}else{
			// Separatore Spazi .. in generale se copia da excel o altro mi perdo i crlf o i tab
			var fields = fieldObj.value.split(" ");  // in your case s.split("\t", 11) might also do
			// Cerco eventualmente con separatore ;
			if (fields.length<=1) {
				fields = fieldObj.value.split(";")
			}
			fields.forEach(function(item, index){
			// Controllo valore unico per valori già inseriti
			var existValue = false;
			if (IFRAME_LOOKUP && win_count>1){
				var fieldsCheckList = parentObj.document.getElementsByName(id+'[]');
			} else {
				var fieldsCheckList = document.getElementsByName(id+'[]');
			}
			if (fieldsCheckList){
				for (var mc = 0; mc < fieldsCheckList.length; mc++){
					var fieldCheckObj = fieldsCheckList[mc];
					if (fieldsCheckList[mc] && fieldsCheckList[mc].value == item){
						existValue = true;
						break;
					}
				}
			}
			parentObj = getFrameWindowById(id + "_COUNTER");
			if (parentObj.document.getElementById(id + "_COUNTER")) {
				counter = parentObj.document.getElementById(id + "_COUNTER").value;
			} else {
				counter = 0;
			}
			// Controllo valore unico per valori nuovi
			if (counter > 0){
				for (var mc = 1; mc < counter + 1; mc++){
						if (IFRAME_LOOKUP && win_count>1){
							var fieldCheckObj = parentObj.document.getElementById("new_" + id + "_" + mc);
						} else {
							var fieldCheckObj = document.getElementById("new_" + id + "_" + mc);
						}
						if (fieldCheckObj && fieldCheckObj.value == item){
							existValue = true;
							break;
						}
				}
			}
			
			if (!existValue || checkDuplicate==false){
				counter++;
				parentObj.document.getElementById(id + "_COUNTER").value=counter;
				/*if (window[id + "_COUNTER"]){
					window[id + "_COUNTER"]++;
				}else{
					window[id + "_COUNTER"] = 1;
				}*/
				
				//var numBase = parentObj.jQuery('#tableWidth');
				//console.log(numBase);
			 	
				var fieldCopy = document.createElement("li");
				fieldCopy.id = id +  counter;
				fieldCopy.className = "deactiveOrder sizeDeactiveOrder";
				/*fieldHTMLStart = '<table border="0" cellpadding="0" cellspacing="0"><tr>';
				fieldHTMLObj = '<input id="new_' + id + "_" + window[id + "_COUNTER"] + '" type="TEXT" value="' + fieldObj.value + '" size="' + fieldObj.size + '" readonly class="inputtextDisabled">';
				fieldHTMLEnd   = '<td><img onClick="multiFieldAddRemove(\'REMOVE\',\'' + id + '\', ' + window[id + "_COUNTER"] + ')" hspace="5" class="wi400-pointer" src="' + _THEME_DIR + 'images/remove.png" title="' + yav_config.REMOVE + '"></td>';

				if (ajaxDecode){
					fieldHTMLEnd += '<td class="detail-message-cell"><span id="errorsDiv_new_' + id + '_' + window[id + "_COUNTER"] + '"></span></td>';
					fieldHTMLEnd += '<td class="detail-message-cell" id="new_' + id + '_' + window[id + "_COUNTER"] + '_DESCRIPTION">&nbsp;</td>';
				}
				
				fieldHTMLEnd += '</tr></table>';
		
				fieldCopy.innerHTML = fieldHTMLStart + "<td>" + fieldHTMLObj + "</td>" + fieldHTMLEnd;*/
				
				fieldHTMLObj = "";
				if(sortField) {
					fieldHTMLObj = '<img class="wi400-updown" src="themes/common/images/triangle_up_down.png"></img>';
				}else {
					fieldHTMLObj = '<div class="wi400-updown-none"></div>';
				}
				
				var showKeys = jQuery( fieldParent ).find( "span[id*='_key_']" );
				//console.log(showKeys);
				var flagKey = 0;
				var val = "error";
				if(showKeys.length) {
					var val = prompt("Vuoi dare un nome al parametro?", "");
				    if (val) {
				    	fieldHTMLObj += '<input id="new_' + id + "_" + counter + '" type="TEXT" name="'+id+'['+val+']" value="' + item + '" size="' + fieldObj.size + '" readonly class="inputtextDisabled multiInputtextDisabled">';
				    	flagKey = 1;
				    }
				}
				
				if(!flagKey) {
					fieldHTMLObj += '<input id="new_' + id + "_" + counter + '" type="TEXT" name="'+id+'[]" value="' + item + '" size="' + fieldObj.size + '" readonly class="inputtextDisabled multiInputtextDisabled">';
				}
				
				//'<img onClick="multiFieldAddRemove(\'REMOVE\',\''.$field->getName().'\', '.$fieldArrayCounter.')" hspace="5" class="wi400-pointer" src="'.$temaDir.'images/remove.png" title="'._t('REMOVE').'">';
				fieldHTMLObj += "<img title='Remove' onclick='multiFieldAddRemove(\"REMOVE\",\"" + id + "\", "+ counter+", false, " + sortFieldText + ")' hspace='5' class='multi-wi400-pointer' src='"+_THEME_DIR+"images/remove.png'>&nbsp;</span>";
				//fieldHTMLObj += "<span id='errorsDiv_new_" + id + "_" + window[id + '_COUNTER'] + "' class='innerError' ></span>";
				if (ajaxDecode){
					fieldHTMLObj += '<span class="multi-detail-message-cell"><span id="errorsDiv_new_' + id + '_' + counter + '"></span>';
				}
				fieldHTMLObj += "<span class='sub-detail-message-cell' id='new_" + id + "_" + counter + "_DESCRIPTION'>&nbsp;</span>";
				
				if(flagKey) {
					fieldHTMLObj += "<span id='"+id+"_key_"+val+"' style='position: relative; top: -11px; left: -6px;'>"+val+"</span>";
				}

				//fieldCopy.innerHTML = fieldHTMLObj;
				var textHtml = '<li id='+id +  counter+' class="deactiveOrder sizeDeactiveOrder">'+fieldHTMLObj+'</li>';
				
				//fieldParent.appendChild(fieldCopy);
				fieldParent.innerHTML += textHtml;
				if (IFRAME_LOOKUP && win_count>1){
					var newFieldObj = parentObj.document.getElementById("new_" + id + "_" + counter);
				} else {
					var newFieldObj = document.getElementById("new_" + id + "_" + counter);
				}
				//var newFieldObj = document.getElementById("new_" + id + "_" + window[id + "_COUNTER"]);
				//newFieldObj.name = id + "[]";
				item = "";
				// Decodifica ajax
				if (ajaxDecode){
					rules[rules.length] = newFieldObj.id + ":LABEL|custom|decodeValidation(\"" + newFieldObj.id  + "\",\"Valore non valido!\")";
					wi400_decode(newFieldObj, undefined, false);
				}

				setTimeout(function() {
					// Se è un campo multi-text e sto selezionando più valori non devo resizare la finestra
					//if(!jQuery("iframe").contents().find('#BUTTON_MULTI_SELECT_LOOKUP')) {
						if(wi400top.wi400_window_counter > 0) {
							if(wi400top.wi400_window_counter > 1) {
								wi400ResizeWindow(null, null, wi400top.wi400_window_counter-1); //azamalama
							}else {
								wi400top.wi400ResizeWindow();
							}
						}
					//}
				}, 100);
			}else{
				alert(yav_config.DUPLICATE_VALUE);
				item = "";
			}
			});
			fieldObj.value="";
			//Risetto il focus sull'input text (per IE devo fare anche il blur...)
			setTimeout(function() { fieldObj.blur(); fieldObj.focus(); }, 0);
		}
		////// FINE CICLO
	}else if (action == "REMOVE"){
		var fieldObj = document.getElementById(id + which);
		//parentObj = getFrameWindowById(id);
		//var fieldParent = parentObj.document.getElementById(id + "_PARENT");
		fieldParent.removeChild(fieldObj);
		
		if(wi400top.wi400_window_counter > 0) 
			wi400top.wi400ResizeWindow();
	}else if (action == "REMOVEALL"){
		var fc = fieldParent.firstChild;
		while( fc ) {
			fieldParent.removeChild( fc );
		    fc = fieldParent.firstChild;
		}
		
		if(wi400top.wi400_window_counter > 0)
			wi400top.wi400ResizeWindow();
	}
	if(action == "REMOVEALL" || action == "REMOVE" || action == "ADD") {
		if(typeof parentObj == 'undefined') 
			var parentObj = window;
		
		var obj = parentObj.document.getElementById(id);
		if(obj.min || obj.max) {
			yav.performCheck('wi400Form', rules, "inline", id);
		}
	}
}
function inputAddRemove(operator, id, acceptNegative){

	if (typeof(acceptNegative) == "undefined") acceptNegative = false;
	
	var fieldObj = document.getElementById(id);
	var removeObj = document.getElementById(id + "_REMOVE_TOOL");
	var addObj 	  = document.getElementById(id + "_ADD_TOOL");
	
	if (fieldObj){
		fieldValue = trim(fieldObj.value);
		if(fieldValue.indexOf(".")) {
			fieldValue = fieldValue.replace(/\./g, "");
		}
		if (_DECIMAL_SEPARATOR != ""){
			fieldValue = fieldValue.replace(_DECIMAL_SEPARATOR,".");
		}
		
		if (fieldValue == "" || isNaN(fieldValue)){
			fieldValue = 0;
		}
		
		var newValue = fieldValue;
		if (operator == "-" && (acceptNegative || fieldValue != 0)){
			newValue = eval(fieldValue) - 1;
		}else if (operator == "+"){
			newValue = eval(fieldValue) + 1;
		}
		
		if (newValue > 0){
			removeObj.src = _THEME_DIR + "images/grid/remove.png";
		}else if (!acceptNegative){
			newValue = 0;
			removeObj.src = _THEME_DIR + "images/grid/remove_disabled.png";
		}
		
		if (operator == "-" || operator == "+"){
			
			if (_DECIMAL_SEPARATOR != ""){
				fieldObj.value = (newValue + "").replace(".",_DECIMAL_SEPARATOR);
			}
			
			fieldObj.onchange();
		}
		
		
	}
}


function trim(str, chars) {
	return ltrim(rtrim(str, chars), chars);
}

function ltrim(str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}

function rtrim(str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}


// Simulate Java Hashtable
function wi400Map(){
    var len = 0;
    var keys = new Array();
    var values = new Array();

    this.get = function(key){
        var val = null;
        for(var i=0; i<len; i++){
            if(keys[i] == key){
                val = values[i];
                break;
            }//end if
        }//end for

        return val;
    };//end get()

    this.put = function(key, value){
    	var con = false;
    	for(var i=0; i<len; i++){
            if(keys[i] == key){
                con = true;
                values[i] = value;
                break;
            }
        }
        if (!con){
	        keys[len] = key;
    	    values[len++] = value;
    	 }
    };//end put()

    this.length = function(){
        return len;
    };//end length()

	this.getKeys = function(){
		return keys;
	};
	
	this.getValues = function(){
		return values;
	};

    this.contains = function(key){
    	var con = false;
        for(var i=0; i<len; i++){
            if(keys[i] == key){
                con = true;
                break;
            }//end if
        }//end for

        return con;
    };//end contains()

    this.remove = function(key){
        var keyArr = new Array();
        var valArr = new Array();
        var l = 0;
        for(var i=0; i<len; i++){
            if(keys[i] != key){
                keyArr[l] = keys[i];
                valArr[l++] = values[i];
            }//end if
        }//end for

        keys = keyArr;
        values = valArr;
        len = l;
    };//end remove()        

}//end Map



function timerPause(idTimer, reloadJsAction){
	if (window[idTimer + "_timer"]){
		// Metto in pausa
		document.getElementById(idTimer + "_TIMER_IMG").src = "themes/common/images/grid/grid_pause.png";
	}else{
		// Metto in play
		window[idTimer + "_timer_state"] = false;
		if (reloadJsAction == _PAGE_RELOAD || reloadJsAction == _PAGE_REGENERATE) {
			doPagination(idTimer, reloadJsAction);
		}else if (reloadJsAction == "RESUBMIT"){
			doSubmit(CURRENT_ACTION, CURRENT_FORM);
		}
		document.getElementById(idTimer + "_TIMER_IMG").src = "themes/common/images/grid/grid_timer.gif";
	}
	
	// Cambio stato
	window[idTimer + "_timer"] = !window[idTimer + "_timer"];
}

function timerStart(idTimer, time, reloadJsAction){
	if (window[idTimer + "_timer"]){
		window[idTimer + "_timer_state"] = true;
		setTimeout(function(){
			if (window[idTimer + "_timer"]){
				window[idTimer + "_timer_state"] = false;
				if (reloadJsAction == _PAGE_RELOAD || reloadJsAction == _PAGE_REGENERATE) {
					doPagination(idTimer, reloadJsAction);
				}else if (reloadJsAction == "RESUBMIT"){
					doSubmit(CURRENT_ACTION, CURRENT_FORM);
				}
			}
		}, (time * 1000));
	}
}

function setMultipartFormEncoding(){
	var formObj = document.getElementById(APP_FORM);
	formObj.encoding = "multipart/form-data";
}

function focusBlur(which){
	document.getElementById(which).focus();
	document.getElementById(which).blur();
}

function startLoading(divId){
	//document.getElementById(divId).innerHTML = "<div style='text-align:center'><img vspace='30' src='" + _THEME_DIR + "images/wi400-loader.gif'/></div>"
}


var TRange=null;

function findString(str, where) {

	//var whereHtml = document.getElementById(where).innerHTML;

	if (document.selection) {
		var div = document.body.createTextRange();

		div.moveToElementText(document.getElementById("todo_0000075"));
		div.select();
	}else {
		var div = document.createRange();
		
		div.setStartBefore(document.getElementById("todo_0000075"));
		div.setEndAfter(document.getElementById("todo_0000075")) ;
		
		window.getSelection().addRange(div);
	}
	
	return;
	
	// E QUESTO CODICE? NON SERVER A NIENTE CON IL RETURN SOPRA.... (ALBERTO 09/09/2015) HO COMMENTATO
	/*if (parseInt(navigator.appVersion)<4) return;
	var strFound;
	if (window.find) {
		// CODE FOR BROWSERS THAT SUPPORT window.find

		strFound=self.find(str);
  		if (strFound && self.getSelection && !self.getSelection().anchorNode) {
  			strFound=self.find(str);
  		}
  		if (!strFound) {
  			strFound=self.find(str,0,1);
  			while (self.find(str,0,1)) continue;
  		}
	}else if (navigator.appName.indexOf("Microsoft")!=-1) {

		// EXPLORER-SPECIFIC CODE
	
		if (TRange!=null) {
			TRange.collapse(false);
			strFound=TRange.findText(str);
			if (strFound) TRange.select();
		}
		if (TRange==null || strFound==0) {
			TRange=self.document.body.createTextRange();
			TRange.moveToElementText(document.getElementById(where)); 
		
			strFound=TRange.findText(str);
			if (strFound) TRange.select();
		}
	}else if (navigator.appName=="Opera") {
		alert("Opera browsers not supported, sorry...");
		return;
	}
	if (!strFound) alert("String '"+str+"' not found!");

	return;*/
}


// WI400TABS

	function cleanErrorTab(idTabs, tabId){
		if (window[ idTabs + "_wi400TabsArray"]){
			var tabsArray  = window[ idTabs + "_wi400TabsArray"];
			var tabsErrors =  window[ idTabs + "_wi400TabsErrors"].getValues();
			for (var c = 0; c < tabsArray.length; c++){
				if (typeof(tabId)=="undefined" || tabId == null || tabId == tabsArray[c]){
					tabsErrors[c] = false;
				}
			}
		}
	}

	function hasErrorTab(idTabs, fieldTab, tabId){
		if (window[ idTabs + "_wi400TabsArray"]){
			var tabsArray  = window[ idTabs + "_wi400TabsArray"];
			var tabsErrors =  window[ idTabs + "_wi400TabsErrors"].getValues();
			for (var c = 0; c < tabsArray.length; c++){
				if (typeof(tabId)=="undefined" || tabId == null || tabId == tabsArray[c]){
					if (fieldTab == tabsArray[c]) tabsErrors[c] = true;
				}
			}
		}
		//wi400Tab_refresh(idTabs);
	}

	function wi400Detail_select_row(idDetail, idField, which){

		var radioObj = document.forms['wi400Form'].elements[idDetail];
		
		if(radioObj){
			var radioLength = radioObj.length;

			if(radioLength == undefined){
				if(radioObj.checked){
						// Selection
						document.getElementById(idDetail + "_" + radioObj.value + "_LABEL").className = 
							document.getElementById(idDetail + "_" + radioObj.value + "_LABEL").className + " wi400-grid-row_selected";
			
						document.getElementById(idDetail + "_" + radioObj.value + "_SELECT").className = 
							document.getElementById(idDetail + "_" + radioObj.value + "_SELECT").className + " wi400-grid-row_selected";
					}else{
						// No selection
						document.getElementById(idDetail + "_" + radioObj.value + "_LABEL").className = "detail-label-cell";
			
						document.getElementById(idDetail + "_" + radioObj.value + "_SELECT").className = "detail-select-cell";
					}
				
				}else{
					for(var i = 0; i < radioLength; i++) {
						if(radioObj[i].checked) {
							// Selection
							document.getElementById(idDetail + "_" + radioObj[i].value + "_LABEL").className = 
								document.getElementById(idDetail + "_" + radioObj[i].value + "_LABEL").className + " wi400-grid-row_selected";
				
							document.getElementById(idDetail + "_" + radioObj[i].value + "_SELECT").className = 
								document.getElementById(idDetail + "_" + radioObj[i].value + "_SELECT").className + " wi400-grid-row_selected";
						}else{
							// No selection
							document.getElementById(idDetail + "_" + radioObj[i].value + "_LABEL").className = "detail-label-cell";
				
							document.getElementById(idDetail + "_" + radioObj[i].value + "_SELECT").className = "detail-select-cell";
						}
					}
				}
			}
	}

	function wi400Tab_select(idTabs, idTab){
		//window[ idTabs + "_wi400TabsActive"] = idTab;
		FOCUSED_TAB = idTab;
		document.getElementById(idTabs + "_ACTIVE_TAB").value = idTab;
		wi400Tab_refresh(idTabs);
	}
	
	function wi400Tab_refresh(idTabs){
		if (window[ idTabs + "_wi400TabsArray"]){
			var tabsArray  = window[ idTabs + "_wi400TabsArray"];
			var tabsErrors = window[ idTabs + "_wi400TabsErrors"].getValues();
			var tabActive = document.getElementById(idTabs + "_ACTIVE_TAB").value;
			var isFirstError = true;		
			for (var c = 0; c < tabsArray.length; c++){
				var tabContainerStyle = "wi400TabContainer";
				var tabStyle 		  = "wi400Tab";
				var tabContentStyle   = "wi400TabContent_Hide";
	
				if (tabActive == tabsArray[c]){
					tabStyle 		= "wi400TabActive";
					tabContentStyle = "wi400TabContent_Show";
					
					// Container Style
					if (tabsErrors[c]) tabContainerStyle += "Error";
					document.getElementById(idTabs + "_wi400Tab_Container").className = tabContainerStyle;
				}
				
				// Tab Style
				if (tabsErrors[c]) {
					tabStyle += "Error";
				}
				try {	
					document.getElementById(idTabs + "_wi400Tab_" + tabsArray[c]).className = tabStyle;
					document.getElementById(idTabs + "_" + tabsArray[c]).className 			= tabContentStyle;
				} catch (err) {
					// nothing
				}	
			}
		}
	}
function wi400_complete(fieldId, descrizione, min, maxResult, settings, sendrequest) {
	if (typeof(min) == "undefined") min = 2;
	if (typeof(maxResult) == "undefined") maxResult = 12;
	if (typeof(settings) == "undefined") settings ="";
	if (typeof(sendrequest) == "undefined") sendrequest = false;
	
	var fieldObj = fieldId;
	if(descrizione) {
		fieldObj = "DES_"+fieldId;
	}	
	
	var detailId = jQuery('#'+fieldId).attr("detailid");
	var detail_list = "";
	if(detailId) {
		detail_list = "&DETAIL_ID="+detailId;
	}else {
		var dati = fieldId.split("-");
		var riga = dati[1];
		listId = dati[0];
		detail_list = "&LIST_ID="+listId;
		colonna = dati[dati.length-1];
		detail_list += "&COLONNA=" + colonna;
		var myObj = jQuery("#"+fieldObj.id);
		detail_list += "&WI400_OTHERKEY=" +get_column_key(myObj[0]);
		detail_list += "&WI400_LIST_KEY=" + document.getElementById(listId + "-" + dati[1]).value;
		detail_list += "&FROMLIST=1";
		detail_list += "&ROW_NUMBER="+riga;
	}
	
	var cache = {};
			 jQuery( "#"+ fieldObj).autocomplete({
				 minLength: min,
				 autoFocus: true,
				 source: function( request, response ) {
					 var term = request.term;
					 /*if ( term in cache ) {
						 response( cache[ term ] );
						 return;
					 }*/
					 // Reperisco Key Sensitive e Inizio
					 start = jQuery("#DES_START_"+fieldId).prop('checked');
					 sensitive = jQuery("#DES_CASE_"+fieldId).prop('checked'); 
					 url = _APP_BASE + APP_SCRIPT + "?t=AJAX_COMPLETE&DECORATION=clean" + detail_list + "&DESC=" + descrizione + "&FIELD_ID=" + fieldId+"&FIELD_VALUE=" + term+"&FIELD_MAX_RESULT="+maxResult + "&CASE="+sensitive +"&START="+start;
					// url = url  +"&" + jQuery("#" + APP_FORM).serialize()+ "&CHECKID=" + urldecode(url).length;
					 url = url  + "&CHECKID=" + urldecode(url).length;
					 if (sendrequest) {
						 if(detailId) {
							 request = jQuery("#" + APP_FORM).serialize();
						 } else {		 
							 request = jQuery("#" + listId + "-" + riga+ "-tr :input").serialize();
						 }
				 	 }
					 //alert(start + " --- " + sensitive);
					 jQuery.getJSON(url, 
							 request, function( data, status, xhr ) {
								 cache[ term ] = data;
								 response( data );
					 });
				 },
				 select: function( event, ui ) {
					 //this.value =  ui.item.label.substring(0, ui.item.label.indexOf("-")-1);
					 if (descrizione=="" || settings=="*CUSTOM") { //descrizione == ""
						this.value =  ui.item.label;
						that = this;

						setTimeout(function() {
							var tabFieldId = jQuery(document.activeElement).attr('id');
							/*console.log("comparazione input focus");
							console.log(fieldId);
							console.log(tabFieldId);*/
							if(fieldId == tabFieldId) {
								var onchangeString = jQuery('#'+fieldId).attr('onchange');
								if(onchangeString.indexOf('updateListRow') == -1) {
									that.onchange();
								}
							}
						}, 0);
					 } else {
						 jQuery( "#"+ fieldId).val(ui.item.label);
						 jQuery( "#"+ fieldId).trigger('change');
						 jQuery( "#DES_"+ fieldId+"_DIV").hide();
						 jQuery("#image_effect_input_"+fieldId).attr("src", "themes/common/images/text.png");
					 }
					 return false;
				 }	 
		 }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
			 var cv = jQuery( "#"+ fieldObj).val();
			 var cm = cv.length;
			 if (settings=="*CUSTOM") {
				 var stringa = item.label;
				 var desc = item.desc;
			 } else {
				 if (descrizione == "") {
					 //var stringa = "<strong>"+cv+"</strong>"+item.label.substring(cm);
					 //var desc = item.desc;
					 var stringa = item.label.replace(cv, "<strong>"+cv+"</strong>");
					 var desc = item.desc;
				 } else {
					 var stringa = item.label;
					 var desc = item.desc.replace(cv, "<strong>"+cv+"</strong>");
				 }
			 }
			 return jQuery( "<li>" ).append( "<a class='detail-label-cell' style='font-size: 11px;'>" + stringa +" - " + desc + "</a>" ).appendTo( ul );
		};
		jQuery( "#"+ fieldObj).keydown( function( event ) {
            var isOpen = jQuery( this ).autocomplete( "widget" ).is( ":visible" );
            var keyCode = jQuery.ui.keyCode;
            //alert(isOpen+' '+keyCode);
            //if ( isOpen && ( event.keyCode == keyCode.UP || event.keyCode == keyCode.DOWN ) ) {
            if (!isOpen && event.keyCode == 17) {
            	jQuery(this).autocomplete("search", "");  
            	event.stopImmediatePropagation();
            }
            if (isOpen) {
            	event.stopImmediatePropagation();
            }
           
      });
}
function wi400_decode(fieldObj, isList, asyncr, sendrequest){
	if (typeof(isList) == "undefined") isList = false;
	if (typeof(asyncr) == "undefined") asyncr = true;
	if (typeof(sendrequest) == "undefined") sendrequest = false;

	var listId = "";
	var detail_list = "";
	var id_detail = "default";
	if(!isList) {
		var id = "";
		if(fieldObj.id.indexOf("new_") !== -1) {
			var arr = fieldObj.id.split("_");
			arr.pop();
			arr.shift();
			id = arr.join("_"); //[0] => new, [1] => idParent, [2] => num elemento
		}else {
			id = fieldObj.id;
		}
		var parentObj = getFrameWindowById(fieldObj.id);
		
		id_detail = parentObj.jQuery('input[id="'+id+'"]').attr("detailid");
		detail_list = "&DETAIL_ID="+id_detail;
	}else {
		var dati = fieldObj.id.split("-");
		var riga = dati[1]
		listId = dati[0];
		detail_list = "&LIST_ID="+listId;
		colonna = dati[dati.length-1];
		detail_list += "&COLONNA=" + colonna;
		var myObj = jQuery("#"+fieldObj.id);
		detail_list += "&WI400_OTHERKEY=" +get_column_key(myObj[0]);
		detail_list += "&WI400_LIST_KEY=" + document.getElementById(listId + "-" + dati[1]).value;
		detail_list += "&FROMLIST=1";
		detail_list += "&ROW_NUMBER="+riga;
	}
	
	if(!id_detail) {
		console.log("ritorno");
		return;
	}
	
	if (fieldObj.value != ""){
		//alert(fieldObj.value);
		if (!isList) jQuery("#" + fieldObj.id + "_DESCRIPTION").html("<img src='themes/common/images/decode_loading.gif'/>");
		var url = _APP_BASE + APP_SCRIPT + "?t=AJAX_DECODING&DECORATION=clean&FIELD_ID=" + fieldObj.id;
		url = url + "&CHECKID=" + urldecode(url).length;
		jQuery.ajax({  
			type: "POST",
			cache: false,
			async: asyncr,
			url: url,
			data: "&FIELD_VALUE=" + encodeURIComponent(fieldObj.value) + detail_list +"&" + jQuery("#" + APP_FORM).serialize()
		}).done(function ( response ) {
			var decodeJSON = jQuery.parseJSON(response);
			mydoc = getFrameWindowById(fieldObj.id + "_DESCRIPTION");
			//console.log(decodeJSON);
			if (decodeJSON.decode !== false){
				if (!isList) {
					var field_desc = mydoc.document.getElementById(fieldObj.id + "_DESCRIPTION");
					if(field_desc) {
						field_desc.innerHTML = "&nbsp;" + decodeJSON.decode;
					}
				}
				if (decodeJSON.decodeFields != null){
 					jQuery.map(decodeJSON.decodeFields,function(value,key) { 
 						jQuery("#" + key).val(value);
 					});
				}
				if (isList){ 
 					//document.getElementById(decodeJSON.fieldId).className = "inputtext";
 					jQuery("#"+decodeJSON.fieldId).removeClass("errorField");
 					jQuery("#"+decodeJSON.fieldId).prop('title', decodeJSON.decode);
 					//document.getElementById(decodeJSON.fieldId).title = decodeJSON.decode;
				} else {
					// @INFO Prima questa parte non c'era, il detail in qualche modo li reimpostava ma se chiamata diretta al metodo non funzionava
 					//alert(document.getElementById(decodeJSON.fieldId).className);
					jQuery("#"+decodeJSON.fieldId).removeClass("errorField");
					//document.getElementById(decodeJSON.fieldId).className = "inputtext";
 					jQuery("#errorsDiv_" + fieldObj.id).hide();
				}
			}else{
				// Errore solo se permetto nel campo codici nuovi
				//if (decodeJSON.allow_new==false) {
 					if (!isList)  {
 						jQuery("#" + fieldObj.id + "_DESCRIPTION").html("&nbsp;");
 						yav.serverErrors.put(fieldObj.id,decodeJSON.fieldValue);
 						yav.performCheck('wi400Form', rules, "inline", fieldObj.id);
 						jQuery("#errorsDiv_" + fieldObj.id).html(decodeJSON.fieldMessage);
 						jQuery("#errorsDiv_" + fieldObj.id).find('#span_and_or').remove();
 					}else{
 						//mydoc = getFrameWindowById(fieldObj.id);
 						jQuery("#"+decodeJSON.fieldId).addClass("errorField");
 						jQuery("#"+decodeJSON.fieldId).prop('title', decodeJSON.fieldMessage);
 						//mydoc.getElementById(decodeJSON.fieldId).className = "inputError";
	 					//mydoc.getElementById(decodeJSON.fieldId).title = "";
 					}
				//}	
			}
		}).fail(function ( data ) {
			if (!isList)  jQuery("#" + fieldObj.id + "_DESCRIPTION").html("&nbsp;");
		});
		
	}else{
		if (!isList)  {
			jQuery("#" + fieldObj.id + "_DESCRIPTION").html("&nbsp;");
		}else{
			fieldObj.title = "";
		}
	}
}

	/*function wi400_list_decode(fieldObj, decodeParameters){
		
		if (fieldObj.value != ""){
				new Ajax.Request(_APP_BASE + APP_SCRIPT + "?t=AJAX_DECODING&DECORATION=clean&DECODE_PARAMETERS=" + decodeParameters + "&FIELD_ID=" + fieldObj.id + "&FIELD_VALUE=" + fieldObj.value, { 
					method:'post',
					parameters: jQuery(APP_FORM).serialize(),
					onSuccess: function(response){
		 				var decodeJSON = response.responseText.evalJSON();
		 				if (decodeJSON.decode != ""){
		 					var h = $H(decodeJSON.decodeFields);
		 					var hkeys = h.keys();
		 					for (var j = 0; j < hkeys.length; j++){
		 						if (document.getElementById(hkeys[j])){
		 							document.getElementById(hkeys[j]).value = h.get(hkeys[j]);
		 						}
		 					}
		 					
		 					document.getElementById(decodeJSON.fieldId).className = "inputtext";
		 					document.getElementById(decodeJSON.fieldId).title = decodeJSON.decode;
		 				}else{
		 					document.getElementById(decodeJSON.fieldId).className = "inputError";
		 					label = "";
		 					msg = yav_config.NOT_VALID_VAL.replace('{1}', label);
		 					document.getElementById(decodeJSON.fieldId).title = msg;
		 				}
		    		}
				
		  		});
		}else{
			fieldObj.title = "";
		}
	}*/
function wi400_list_decode(fieldObj){
	wi400_decode(fieldObj, true, undefined);
}
	
function maxLength(field,maxChars) {
	if(field.value.length >= maxChars) {
		event.returnValue=false;
		return false;
	}
}

function maxLengthPaste(field,maxChars) {
	event.returnValue=false;
	if((field.value.length + window.clipboardData.getData("Text").length) > maxChars) {
		return false;
	}
	event.returnValue=true;
}

function textLimit(field, maxlen) {
	if (field.value.length > maxlen + 1)
		alert('Superata la lunghezza MAX consentita ('+maxlen+'). Il testo in eccesso è stato troncato.');
	if (field.value.length > maxlen)
		field.value = field.value.substring(0, maxlen);
}
function updateCheckboxValue(which){
	 document.getElementById(which.id + "_HIDDEN").disabled = which.checked;
}

function updateIpValue(which, counter){
	var ipValue = "";
	for (var ipCounter = 0; ipCounter < 4; ipCounter++){
		var ipField = document.getElementById(which + "_" + ipCounter);
		if (ipCounter > 0) ipValue = ipValue + ".";
		ipValue = ipValue + ipField.value;
	}
	var ipField = document.getElementById(which);
	if (ipValue != "..."){
		ipField.value = ipValue;
	}else{
		ipField.value = "";
	}
}

//INIZIO FUNZIONI DATEPICKER --------------------------
var input_glob = null;
var altezza_init = 0;
function changeYear(input, opzione) {
	var data = new Date();
	var day = input.currentDay;
	var mese = input.drawMonth+1;
	var anno = input.currentYear;
	
	//Verifico se è presente il defaultData altrimenti cerco io il giorno
	if(input.currentDay == 0) {
		day = data.getDate();
	}
	if(input.currentYear == 0) {
		anno = data.getFullYear();
	}
	
	data = new Date(anno, mese-1, day);
	if(opzione == "+") {
		data.setFullYear(data.getFullYear()+1);
	}else {
		data.setFullYear(data.getFullYear()-1);
	}

	var changeDate = setDate(data.getDate(), data.getMonth()+1);
	day = changeDate[0];
	mese = changeDate[1];
	
	data = new Date(data.getFullYear(), mese-1, day);
	
	return jQuery.datepicker.formatDate(input.settings.dateFormat, data);
}

//Modifico la data in modo che sia dello stesso formato del datepicker
function setDate(day, mese) {
	if((""+day).length == 1) {
		day = "0"+day;
	}
	if((""+mese).length == 1) {
		mese = "0"+mese;
	}
	return [day, mese];
}

function oggi() {
	setTimeout(function() {
		var data = new Date();
		input_glob.value = jQuery.datepicker.formatDate(jQuery(input_glob).datepicker('option', 'dateFormat'), data);
			
		//Chiudo il calendario
		jQuery(input_glob).datepicker("hide");

		input_glob.onchange();
	}, 0);
}

/* Funzione per ridimensionare momentaneamente la grandezza della finestra
 * per rendere visibile il calendario. Quando il calendario sparisce la finestra
 * ritorna alla grandezza precedente
*/
function setDialogHeight(alt) {
	var w = window.frameElement;
	var num_id = w.id.split("_")[0].slice(-1);
	
	var dialogObj = parent.jQuery("#lookup"+num_id);
	
	if(!alt) {				
		altezza_init = dialogObj.css("height");
		//var altez = document.body.scrollHeight;
		var altez = jQuery(document).innerHeight();
		if(altez < document.body.clientHeight)    
			return;
		else
			altez += 10;
	}else {
		var altez = alt;
	}
	
	dialogObj.height(altez);
}

function closeDatePicker(text, obj) {
	setTimeout(function() {
		if(window.frameElement) {
			if(altezza_init)
				setDialogHeight(altezza_init);
		}
	}, 100);								
};

//Creazione dei bottoni per cambiare anno
function createButtonYears(input, oggetto, isChange) {
	input_glob = input;
	
	if(!isChange) {
		setTimeout(function() {
			if(window.frameElement) {
				setDialogHeight();
			}
		}, 100);
	}

	setTimeout(function() {
		var headerPane = jQuery( input ).datepicker( "widget" ).find( ".ui-datepicker-header" );
		var contenuti  = jQuery( input ).datepicker( "widget" );
		contenuti.find(".ui-datepicker-month").css({ position: "absolute", left: "50%", marginLeft: "-90px", width: "90px" });
		contenuti.find(".ui-datepicker-year").css({ position: "absolute", left: "50%", marginRight: "-90px", width: "90px" });
		contenuti.find(".ui-datepicker-prev").css({ position: "absolute", left: "40px"});
		contenuti.find(".ui-datepicker-next").css({ position: "absolute", right: "40px"});
		contenuti.find(".ui-datepicker-current").attr("onClick", "oggi()");
		
		jQuery( "<a>", {
			"class": "ui-datepicker-next ui-corner-all",
			"click": function() {
				jQuery(input).datepicker( "setDate", ""+changeYear(oggetto, "+"));
			}
		}).append(jQuery("<span>", {
			"class": "ui-icon ui-icon-circle-arrow-e"
		})).appendTo(headerPane);

		jQuery( "<a>", {
			"class": "ui-datepicker-prev ui-corner-all",
			"click": function() {
				jQuery(input).datepicker( "setDate", ""+changeYear(oggetto, "-"));
			}
		}).append(jQuery("<span>", {
			"class": "ui-icon ui-icon-circle-arrow-w"
		})).appendTo(headerPane);
	}, 1 );
};

//Ho cambiato il mese o l'anno del datepicker
function changeMonthYear(anno, mese, input) {
	var testo = document.getElementById(input.id);
	var day = input.selectedDay;
	
	if(!input.currentDay) {
		var data = new Date();
		day = data.getDate();
		input.currentDay = day;
		input.selectedDay = day;
	}

	var changeDate = setDate(day, mese);
	if(!checkDateExists(anno, mese, day)) {
		input.selectedDay -= 1;
		changeMonthYear(anno, mese, input);
		return;
	}

	var dataValue = new Date(anno, changeDate[1]-1, changeDate[0]);
	testo.value = jQuery.datepicker.formatDate(input.settings.dateFormat, dataValue);

	setTimeout(function() {
		var giorno = day;
		var cella = jQuery( input ).datepicker( "widget" ).find( "td:contains(\'"+giorno+"\')");
		var tagA = jQuery( input ).datepicker( "widget" ).find( "a:contains(\'"+giorno+"\')");
		cella[0].className = " ui-datepicker-current-day";
		tagA[0].className += " ui-state-active";
	}, 100);
	createButtonYears(document.getElementById(input.id), input, true);

	testo.onchange();
}
// FINE FUNZIONI DATEPICKER --------------------------

function activeIframe(id) {
	var img = jQuery("#"+id).contents().find("#img_tab_active");
	if(img) {
		img.trigger("click");
	}
}

function checkDisabledButtonSelect(id) {
	var select = jQuery('#'+id+' option:selected').attr('value');
	var first = jQuery('#'+id+' option:first');
	var last = jQuery('#'+id+' option:last');
	
	jQuery('#prev_'+id).prop('disabled', false);
	jQuery('#next_'+id).prop('disabled', false);
	
	if(select == first.attr('value')) {
		jQuery('#prev_'+id).prop('disabled', true);
	}else if(select == last.attr('value')) {
		jQuery('#next_'+id).prop('disabled', true);
	}
}

function changeOptionSelect(id, obj) {
	var a = jQuery( "#"+id+" option:selected");
	
	var option = +obj.getAttribute("option");
	if(!option) {
		if(a.index()) a = a.prev();
	}else {
		a = a.next();
	}
	
	jQuery(a).prop('selected', true);

	checkDisabledButtonSelect(id);

	var select = jQuery('#'+id);
	if(select.change) {
		select.change();
	}
}

function setKeyAction(key, actionStyle, context) {
	if (typeof(context) == "undefined"){
		context = "main";
	}
	// Conto il numero di finestre aperte
	IFrameObj = wi400_getIFrames();
	//IFrameObj = parent.window.frames;
	y=0;
	for (var i = 0; i < IFrameObj.length; i++){
		rtn_iframe=IFrameObj[i];
		  if (rtn_iframe.name.indexOf("window_")!=-1) {
			  y++;
		 }
	}
	number_w = 0;
	if (y>0) {
		context = "window";
		number_w= y;
	}
	// Verifico se sono in una finestra e sto attivando un tasto di funzione
	shortcut.add(key,function() {
		if (wi400top.a_windows && wi400top.a_windows.length > 0 && !IFRAME_LOOKUP){
			// Se finestra non aperta e non sono una finestra
		}else{
			//Conto finestre
			IFrameObj = wi400_getIFrames();
			//IFrameObj = parent.window.frames;
			y=0;
			for (var i = 0; i < IFrameObj.length; i++){
				rtn_iframe=IFrameObj[i];
				  if (rtn_iframe.name.indexOf("window_")!=-1) {
					  y++;
				 }
			}
			
			var callOnClick = function(actionStyle) {
				var obj = jQuery('.'+actionStyle);
				if(obj.length) {
					eval(obj.attr("onclick"));
				}else {
					eval(jQuery('#'+actionStyle).attr("onclick"));
				}
			};
			
			//Tasto di funzione abilitato solamente su finestra main di windows
			if (context=="main" && y==0) {
				callOnClick(actionStyle);
			}
			//Tasto di funzione abilitato solamente su finestra di lookup
			if (context=="window" && IFRAME_LOOKUP && y == number_w) {
				callOnClick(actionStyle);
			}
		}
	});
}
function setKeyScript(key, idList, idAction) {
	shortcut.add(key,function() {
		     if (wi400top.a_windows && wi400top.a_windows.length > 0 && !IFRAME_LOOKUP){
					// Se finestra non aperta e non sono una finestra
			   	 }else{
			    	 doListAction(idList, idAction);
				 }
	});
}

function widgetMenu(type) {
	var url = window.location.href;
	if(url.indexOf("ACTIVE_WIDGET") == -1) {
		if(url.indexOf("?") == -1)
			url += "?";
		else 
			url += "&";
		
		url += "ACTIVE_WIDGET="+type;
	}else {
		var search = 1;
		if(type) search = 0; 
		url = url.replace("ACTIVE_WIDGET="+search, "ACTIVE_WIDGET="+type);
	}
//	console.log(url);
	window.location.href = url;
}

function lockRightMenu(obj) {
	obj = jQuery(obj);
	var lock = (obj.attr("lock") === "true");
	var icon = lock ? "fa-unlock" : "fa-lock";
	var openMenu = jQuery(".openWidget");
	openMenu.attr("onmouseout", "openCloseRightMenu("+(lock ? 0 : 1)+")");
	obj.attr("class", "fa "+icon);
	obj.attr("lock", !lock);
	
	jQuery.ajax({
		type: "GET",
		url: _APP_BASE + APP_SCRIPT + "?t=CHANGE_LEFT_MENU&DECORATION=clean&MENU_RIGHT="+(lock ? 0 : 1)
	}).done(function (response) {
		//console.log("ok");
	}).fail(function (data) {
		console.log("Errore ajax apertura/chiusura menù widget");
	});
}

function openCloseRightMenu(type) {
	var obj = jQuery(".contBodyWidget");
	if(type)
		obj.addClass("openRightMenu");
	else {
		obj.removeClass("openRightMenu");
	}
}
function wi400_on_paste(which) {
    setTimeout( function() {
    	wi400_on_paste2(which)
    }, 100);
}
function wi400_on_paste2(questo) {
	var righe = 10;
	var colonne = 3;
	var posizione = questo.id.split("-");
	// Separatore Spazi .. in generale se copia da excel o altro mi perdo i crlf o i tab
	var valore = questo.value;
	alert(valore);
	var fields = valore.split(" ");  // in your case s.split("\t", 11) might also do
	// Cerco eventualmente con separatore ;
	if (fields.length<=1) {
		fields = valore.split(";")
	}
	var row = posizione[1];
	var col = posizione[2];
	fields.forEach(function(item, index){ 
		// Verifico se MultiTab
							alert("giro")
		items = item.split("\t")
		if (items.length<=1) {
			document.getElementById(questo.id).value = items[0];
		}
		row = row + 1;
	});
}
function urldecode(str) {
	  return decodeURIComponent((str + '')
	    .replace(/%(?![\da-f]{2})/gi, function() {
	      // PHP tolerates poorly formed escape sequences
	      return '%25';
	    })
	    .replace(/\+/g, '%20'));
}

function disable_button($idButton, $val) {
	//jQuery("#PUBBLICA_BUTTON").css('display', $val ? "block" : "none");
	jQuery("#"+$idButton).prop('disabled', $val ? true : false);
}

function wi400_size_window() {
	//console.log(w, h);
	if(w < 0) w = window.innerWidth+w; 
	if(h < 0) h = window.innerHeight+h;
}
function openAllDrop() {
	jQuery.ajaxSetup({
		async: false
		});
	jQuery("img[src$='expand.png']").each(function() {
		  //alert(jQuery( this ).attr('id'));
		  jQuery(this).trigger('click');
	});
	jQuery.ajaxSetup({
		async: true
		});
}
function closeAllDrop() {
	jQuery("img[src$='collapse.png']").each(function() {
		  //alert(jQuery( this ).attr('id'));
		  jQuery(this).trigger('click');
	});
}
function wi400_conferma_azione_5250(link) {
	if(confirm("Azione batch! Vuoi continuare?")) {
		link = atob(link);
		link = link.replace(/\\'/g, "'");
		//console.log(link);
		eval(link);
	}else {
		return;
	}
}

function wi400_console_debug_history() {
	var callerLine = new Error().stack;
	console.log(callerLine);
}

function wi400CheckValidation(showAlert) {
	if (typeof(tipoApertura) == "undefined"){
		showAlert = true;
	}
	
	if (rules.length > 0 && !yav.performCheck('wi400Form', rules, "inline")){
		if(showAlert) alert(yav_config.MODULE_ERROR);
		return false;
	}else{
		if (document.getElementById("ACTION_FORM_VALIDATION")){
			document.getElementById("ACTION_FORM_VALIDATION").disabled = false;
		}
	}
	
	return true;
}

//Reperisce il valore del parametro da una stringa URL
function wi400UrlParam(url, name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
    if (results == null){
       return null;
    }else {
       return decodeURI(results[1]) || '';
    }
}

function resizeDescriptionDetail(objWindow) {
	/*jQuery(".detail-value-cell").each(function (e) { 
		console.log(this.offsetWidth);
	});*/
	if(typeof(objWindow) == 'undefined') objWindow = window;
	
	objWindow.jQuery("td[id$='_DESCRIPTION']").each(function() {
		var objDesc = jQuery(this);
		if(objDesc.offsetParent().next().attr('colspan')) {
			objDesc.css('overflow', 'unset');
			return; 
		}
		var leftDesc = objDesc.position().left;
		var paddingDesc = parseInt(objDesc.css('padding-left')) + parseInt(objDesc.css('padding-right'));
		var contenitoreWidth = objDesc.offsetParent().innerWidth();
		var max_width = contenitoreWidth - leftDesc - paddingDesc;
		//console.log(max_width);
		
		objDesc.css('max-width', max_width);
	});
}

window.addEventListener('resize', onResizeWindow);

function onResizeWindow() {
	resizeDescriptionDetail();
}

function wi400GetCookie(cname) {
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

function wi400SetCookie(cname, cvalue, exdays) {
	// Prima devo rimuoverlo
	wi400RemoveCookie(cname);
	var date = new Date();
	var days= 1;
    date.setTime(date.getTime() + (days * 24 * 60 * 60 *1000));
    var expires = "; expires=" + date.toGMTString();
	document.cookie = cname + "=" + cvalue+ expires + "; path=/";;
}

function wi400RemoveCookie(cname) {
	var date = new Date();
	var days= -1;
    date.setTime(date.getTime() + (days * 24 * 60 * 60 *1000));
    var expires = "; expires=" + date.toGMTString();
	document.cookie = cname + "=''"+expires+ "; path=/";;
}
function showMyPassword(id) {
  var x = document.getElementById(id);
  if (x.type === "password") {
    x.type = "text";
  } else {
    x.type = "password";
  }
}
/*
  Ricerca il documento che contiene un ID
*/
var iFramesArray =[];
function wi400SearchElement(id, less_one, single) {
	
	if (typeof(id) == "undefined") return false;
	if (typeof(single) == "undefined") single=false;
	if (typeof(less_one) == "undefined") less_one=false;
	trovato = false;
	//console.log("CERCA:"+id);
	iFramesArray=[];
	// Provo a cercare l'elemento dentro gli iframe
	getIframeElements(wi400top.document, id, less_one, single);
	// Se non ho trovato nulla cerco nel modo classico
	if (iFramesArray.length==0) {
		try {
			var vuoto = parent.document.domain;
			parentObj = parent;
		} catch (e) {
			parentObj=window;
		}
		if (single==true) {
			parentObj=parentObj.document.getElementById(id);
		}
		iFramesArray[0]=parentObj;	
	}
	//console.log(iFramesArray[0]);
	return iFramesArray[0];	
}
function getIframeElements(htmlDocument, id, less_one, single) {
var frames = htmlDocument.getElementsByTagName("iframe");
// Ciclo su tutti gli iframe
if (frames.length > 0) {
    for (var i = 0; i < frames.length; i++) {
        try {
            // Se meno uno devo saltare l'origine
		 	if (less_one && window.frameElement.id) {
		 		if(frames[i].id==window.frameElement.id) {
		 			continue;
		 		}
			}
			//console.log("Cerco :"+id);
			// Cerco se c'è l'oggetto che ricerco 
			if (frames[i].contentWindow.document.getElementById(id)){
				//console.log("trovato!"+id);
				if (single==true) {
					iFramesArray.push(frames[i].contentWindow.document.getElementById(id));
				} else {	
	            	iFramesArray.push(frames[i].contentWindow);
				}
				break;
			}	
            //formName = frames[i].id;
            //console.log(frames[i].id);
            // Cerco ulteriori Frame all'interno
            //iFramesArray.push(frames[i].contentWindow.document);
            getIframeElements(frames[i].contentWindow.document, id, less_one, single);
        } catch (err) {
            console.log(err);
        }
    }
}
}
var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}