var _PAGE_FIRST = "FIRST";
var _PAGE_NEXT = "NEXT";
var _PAGE_PREV = "PREV";
var _PAGE_LAST = "LAST";
var _PAGE_RELOAD = "RELOAD";
var _PAGE_REGENERATE = "REGENERATE";
var _PAGE_EXPORT = "EXPORT";

// Consente di eseguire uno script javascript DOPO aver sottomesso la lista a server

// Row states
var _ROW_BASE_STYLE = "wi400-grid-row";
var _ROW_DEFAULT  = "";
var _ROW_SELECTED = "_selected";
var _ROW_PAIR = "_pair";
var _ROW_OVER     = "_over";
var _ROW_OVER_SELECTED     = "_over_selected";
var copiaDati = [];

function gridHeaderOver(which){
	which.className = 'wi400-grid-header-cell-over';
}

function gridHeaderOut(which){
	which.className = 'wi400-grid-header-cell';
}
function showToolTipQueued(which, url, persistence, mess_alert){
	if (typeof(mess_alert) == "undefined") mess_alert = false;
	
	if (!persistence || which.title == ""){
		which.title = "";
		which.style.cursor='wait';
		jQuery.ajax({  
			type: "POST",
			url: _APP_BASE + url + "&DECORATION=clean",
			data: jQuery("#" + APP_FORM).serialize(),
			async: false
		}).done(function ( response ) {
			if(mess_alert) {
				alert(response);
			}else {
				which.title = response;
			}
			which.style.cursor='default';
		}).fail(function(response) {
		 	which.title = "";
		 	which.style.cursor='default';
		});
	}
}
function showToolTip(which, url, persistence){
	
	if (!persistence || which.title == ""){
		which.style.cursor='wait';
		jQuery.ajax({  
			type: "POST",
			url: _APP_BASE + url + "&DECORATION=clean",
			data: jQuery("#" + APP_FORM).serialize()
			}).done(function ( response ) {  
		//new Ajax.Request(_APP_BASE + url + "&DECORATION=clean", {asynchronous:false, encoding:'UTF-8',method: 'post',evalScripts: true, parameters: jQuery("#"+APP_FORM).serialize(),
		//	 onSuccess: function(response) {
					which.title = response;
					which.style.cursor='default';
			 	})
			 	.fail(function(response) {
			 		which.title = "";
			 		which.style.cursor='default';
			 	}
			);
	}
	
}

// FUNZIONI PER IL TAB DA BOTTNE
/*function tabList(operazione) {
	if(focus_input) {
		var next = getNextTabIndex(focus_input, operazione);
		
		if(next) {
			next.focus();
			next.click();
		}
	}
}

function getNextTabIndex(obj, operazione) {
	if(!operazione) operazione = "dopo";
	var tabindex = obj.getAttribute('tabindex');
	var next = "";
	if(operazione == "prima") {
		next = jQuery('input[tabindex='+(parseInt(tabindex)-1)+']');
	}else {
		next = jQuery('input[tabindex='+(parseInt(tabindex)+1)+']');
	}
	
	return next;
}*/
//------------ FINE--------------

function hideToolTip(which, persistence){
	if (!persistence) which.title = "";
	which.style.cursor='default';
}


function openExtraRowDetail(idExtra, rowNumber){
	var trId = idExtra + "-" + rowNumber;
	if (window[trId] != null && window[trId] == true){
		jQuery("#"+trId).hide();
		window[trId] = false;
		document.getElementById(trId + "-img").src= "themes/common/images/grid/expand.png";
	}else{
		jQuery("#"+trId).show();
		window[trId] = true;
		document.getElementById(trId + "-img").src= "themes/common/images/grid/collapse.png";
	}
}

function hide_table_rows(idExtra, rowNumber){
	var trId = idExtra + "-" + rowNumber;
	if (window[trId] != null && window[trId] == true){
		window[trId] = false;
		document.getElementById(trId + "-img").src= "themes/common/images/grid/expand.png";
		
		var cusid_ele = document.getElementsByClassName('hide-table-row');
		for (var i = 0; i < cusid_ele.length; ++i) {
		    var item = cusid_ele[i];  
		    item.style.display = 'none';
		}
	}else{
		window[trId] = true;
		document.getElementById(trId + "-img").src= "themes/common/images/grid/collapse.png";
		
		var cusid_ele = document.getElementsByClassName('hide-table-row');
		for (var i = 0; i < cusid_ele.length; ++i) {
		    var item = cusid_ele[i];  
		    item.style.display = '';
	    	item.style.height = '30px';	
		}
	}
}
function openRowDetail(idList, rowNumber, persistence){
	var divId = idList + "-" + rowNumber + "-detail";
	if (window[divId] != null && window[divId] == true){
		jQuery("#" + divId).hide();
		window[divId] = false;
		resizeListRow(idList);
		document.getElementById(divId + "-img").src= "themes/common/images/grid/expand.png";
	}else{
		jQuery("#" + divId).show();
		window[divId] = true;
		resizeListRow(idList);
		document.getElementById(divId + "-img").src= "themes/common/images/grid/collapse.png";
	}
	
	// Ajax
	if ((window[divId] == true && window[idList + "_DETAIL_AJAX"] && trim(jQuery("#" + divId + "-html").html()) == "") || (persistence==false && window[divId] == true)){
		var rowDetailAction = eval(idList + "_DETAIL_AJAX");
		//blockBrowser(true);
		jQuery('#' + divId + "-html").html("<img src='themes/common/images/decode_loading.gif' hspace=5 vspace=5 border=0>");
		var keysToPass = document.getElementById(idList + "-" + rowNumber).value;
		//DEBUG window.open(_APP_BASE + "index.php?DECORATION=clean&DETAIL_KEY="+ keysToPass + "&IDLIST=" + idList + "&t=" + rowDetailAction.get("action") + "&f=" + rowDetailAction.get("form"));
		
		jQuery.ajax({  
			type: "POST",
			url: _APP_BASE + "index.php?DECORATION=clean&DETAIL_KEY="+ keysToPass + "&ROW_NUMBER="+ rowNumber + "&IDLIST=" + idList + "&t=" + rowDetailAction.get("action") + "&f=" + rowDetailAction.get("form")
				
			}).done(function ( data ) {  
				blockBrowser(false);
				jQuery("#" + divId + "-html").html(data);
				resizeListRow(idList);
			}).fail(function ( data ) {  
				alert("SERVER ERROR:"+data);
		 		blockBrowser(false);
			}); 
	}
	
}

function getRowStyle(idList, rowNumber, rowState){
	var defaultStyle = _ROW_BASE_STYLE  + rowState;
	var objectStyle  = window[ idList + "_style" + rowState];
	if (typeof(objectStyle) != "undefined"){
		defaultStyle = _ROW_BASE_STYLE + " " + objectStyle[rowNumber];
	}else if (rowState != _ROW_DEFAULT){
		defaultStyle = _ROW_BASE_STYLE + " " + defaultStyle;
	}
	
	if (parseInt(rowNumber / 2)+"" == ""+(rowNumber / 2)){
		defaultStyle = defaultStyle + " " + _ROW_BASE_STYLE + _ROW_PAIR;
	}
	return defaultStyle;
}

function selectGridRow(idList, rowNumber){
	var attr_class = "";
	var fix_row = jQuery("#"+idList+"-Fixed-"+rowNumber+"-tr");
	var checkBox = document.getElementById(idList + "-" + rowNumber + "-checkbox");
	var row = jQuery('#'+idList+"-"+rowNumber+"-tr");
	if (checkBox && checkBox.checked){
		attr_class = getRowStyle(idList, rowNumber, _ROW_SELECTED);
	}else{
		attr_class = getRowStyle(idList, rowNumber, _ROW_DEFAULT);
	}
	
	row.attr('class', attr_class);
	
	if(fix_row) {
		fix_row.attr('class', attr_class);
	}
}



function checkGridRow(idList, rowNumber, checked, idAction){
	if(!document.getElementById(idList + "-" + rowNumber)) return;
	
	document.getElementById(idList + "-" + rowNumber).disabled = false;
	var checkBox = document.getElementById(idList + "-" + rowNumber + "-checkbox");
	currentPaginationListKey = idList;
	document.getElementById("CURRENT_IDLIST").value=idList;
	var type_select = window[idList + "_ST"];
	
	// Se indicato checked comando la selezione/deselezione
	if (typeof (checked) != "undefined"){
		if (!checkBox.checked && checked) window[idList + "_SC"]++;
		if (checkBox.checked && !checked) window[idList + "_SC"]--;
		checkBox.checked = checked;
	}else{
		if (checkBox.checked) window[idList + "_SC"]++;
		if (!checkBox.checked)  window[idList + "_SC"]--;
	}
	
	if (checkBox.checked){
		if (type_select == "SINGLE") document.getElementById(idList).value = document.getElementById(idList + "-" + rowNumber).value;
		// Altrimnti ritorna il focus sulla prima cella della lista
		//document.getElementById(idList + "-" + rowNumber+ "-checkbox").focus();
	}else{
		if (type_select == "SINGLE") document.getElementById(idList).value = "";
	}

	selectGridRow(idList, rowNumber);
	window[idList + "_RRN"] = rowNumber; 
	
	var actionSelector = "";
	if(idAction) actionSelector = eval(idList + "_AL_" + idAction).get('selection');
	if (type_select == "SINGLE" || (type_select == "MULTIPLE" && idAction && actionSelector == 'SINGLE')){
		
		if (checkBox.checked){
			if (window[idList + "_CR"]>-1 && window[idList + "_CR"] != rowNumber){
				document.getElementById(idList + "-" + window[idList + "_CR"]).disabled = false;
				document.getElementById(idList + "-" + window[idList + "_CR"] + "-checkbox").checked = false;
				window[idList + "_SC"]--;
				selectGridRow(idList, window[idList + "_CR"]);
			}
			window[idList + "_CR"] = rowNumber;
		}else{
			window[idList + "_CR"] = -1;
		}
	}
	
	if(window[idList + "_SC"] == 0) {
		window[idList + "_CR"] = -1;
	}
}

function overGridRow(idList, rowNumber){
	var attr_class = "";
	var fix_row = jQuery("#"+idList+"-Fixed-"+rowNumber+"-tr");
	var checkBox = document.getElementById(idList + "-" + rowNumber + "-checkbox");
	var row = jQuery('#'+idList+"-"+rowNumber+"-tr");
	if (checkBox && checkBox.checked){
		attr_class = getRowStyle(idList, rowNumber, _ROW_OVER_SELECTED);
	}else{
		attr_class = getRowStyle(idList, rowNumber, _ROW_OVER);
	}

	row.attr('class', ''+attr_class);

	if(fix_row) {
		fix_row.attr('class', attr_class);
	}
}

function outGridRow(idList, rowNumber){
	selectGridRow(idList, rowNumber);
}

function selectGridAll(idList, checked){
	var rowCounter = 0;
	while (document.getElementById(idList + "-" + rowCounter + "-checkbox")){
		checkGridRow(idList, rowCounter, checked);
		rowCounter++;
	}
}

function selectTableAll(idTable, which){
	var rowCounter = 0;
	while (document.getElementById(idTable + "-" + rowCounter + "-checkbox")){
		document.getElementById(idTable + "-" + rowCounter + "-checkbox").checked = which.checked;
		rowCounter++;
	}
}

function refreshPagination(idList, firstStatus, prevStatus, nextStatus, lastStatus, currentPage, totalPages, noFocus){
	
	if (typeof(noFocus) == "undefined"){
		noFocus = false;
	}
	mydocument = self;//getFrameWindowById(idList);
	if (mydocument.document.getElementById(idList + "_FIRST_BUTTON")) {
		mydocument.document.getElementById(idList + "_FIRST_BUTTON").src = _THEME_DIR + "images/grid/first" + firstStatus + ".gif"
		mydocument.document.getElementById(idList + "_PREV_BUTTON").src = _THEME_DIR + "images/grid/prev" + prevStatus + ".gif";
		mydocument.document.getElementById(idList + "_NEXT_BUTTON").src = _THEME_DIR + "images/grid/next" + nextStatus + ".gif";
		mydocument.document.getElementById(idList + "_LAST_BUTTON").src = _THEME_DIR + "images/grid/last" + lastStatus + ".gif";
		
		mydocument.document.getElementById(idList + "_FIRST_BUTTON").disabled = (firstStatus == "_disabled");
		mydocument.document.getElementById(idList + "_PREV_BUTTON").disabled = (prevStatus == "_disabled");
		mydocument.document.getElementById(idList + "_NEXT_BUTTON").disabled = (nextStatus == "_disabled");
		mydocument.document.getElementById(idList + "_LAST_BUTTON").disabled = (lastStatus == "_disabled");
	
		mydocument.document.getElementById(idList + "_PAGINATION_LABEL").innerHTML = currentPage + " / " + totalPages;
		if (mydocument.document.getElementById(idList + "-0-checkbox") && !noFocus){
			try {
				mydocument.document.getElementById(idList + "-0-checkbox").focus();
			} catch (e) {
				//
			}
		}
	}
	
	blockBrowser(false, "", idList);
	setUpdateStatus(getUpdateStatus());
}

function refreshFilter(idList, hasFilter){
	mydocument = getFrameWindowById(idList);
	if (mydocument.document.getElementById(idList + "_SEARCH_IMG")){
		if (hasFilter){
			mydocument.document.getElementById(idList + "_SEARCH_IMG").src = _THEME_DIR + "images/grid/search.gif";
			mydocument.document.getElementById(idList + "_SEARCH_IMG").style.border = "2px solid #FFCC00";
			mydocument.document.getElementById(idList + "_SEARCH_IMG").style.background = "#FFCC00";
		}else{
			mydocument.document.getElementById(idList + "_SEARCH_IMG").src = _THEME_DIR + "images/grid/search_disabled.gif";
			mydocument.document.getElementById(idList + "_SEARCH_IMG").style.border = "0px";
			mydocument.document.getElementById(idList + "_SEARCH_IMG").style.background = "";
		}
	}
}

function refreshOrder(idList, hasOrder){
	mydocument = getFrameWindowById(idList);
	if (mydocument.document.getElementById(idList + "_ORDER_IMG")){
		if (hasOrder){
			mydocument.document.getElementById(idList + "_ORDER_IMG").src = "themes/common/images/grid/grid_order_enabled.gif";
			mydocument.document.getElementById(idList + "_ORDER_IMG").style.border = "2px solid #FFCC00";
			mydocument.document.getElementById(idList + "_ORDER_IMG").style.background = "#FFCC00";
		}else{
			mydocument.document.getElementById(idList + "_ORDER_IMG").src = "themes/common/images/grid/grid_order_disabled.gif";
			mydocument.document.getElementById(idList + "_ORDER_IMG").style.border = "0px";
			mydocument.document.getElementById(idList + "_ORDER_IMG").style.background = "";
		}
	}
}
function refreshListSelection(idList){
	if (document.getElementById(idList + "_SELECTION_IMG")){
		if (window[idList + "_SC"] > 0){
			jQuery('#' + idList + "_SELECTION_IMG").addClass("selected");
		}else{
			jQuery('#' + idList + "_SELECTION_IMG").removeClass("selected");
		}
	}
}
function removeListSelection(idList){
	if (window[idList + "_SC"] > 0){
		if (confirm("Rimuovere tutte le selezioni effettuate nella lista?")){
			doPagination(idList, "REMOVE_SELECTION");
		}
	}
}

function refreshCustomFilter(idList, customFilter){
	mydocument = getFrameWindowById(idList);
	if (mydocument.document.getElementById(idList + "_CUSTOM_FILTER")){
		mydocument.document.getElementById(idList + "_CUSTOM_FILTER").value = customFilter;
	}

}

function refreshTabIndex(idList, currentTabIndex){
	if (document.getElementById(idList + "_actionSelector")){
		currentTabIndex++;
		document.getElementById(idList + "_actionSelector").tabIndex = currentTabIndex;
	}
	
	if (document.getElementById(idList + "_actionConfirm")){
		currentTabIndex++;
		document.getElementById(idList + "_actionConfirm").tabIndex = currentTabIndex;
	}
}

function changeListAction(idList){
	var actionSelector = document.getElementById(idList + "_actionSelector");
	var actionConfirm  = document.getElementById(idList + "_actionConfirm");
	
	if (actionSelector.selectedIndex > 0){
		actionConfirm.disabled = false;
		actionConfirm.src = _THEME_DIR + "images/next.gif";
	}else{
		actionConfirm.disabled = true;
		actionConfirm.src = _THEME_DIR + "images/next_disabled.gif";
	}
	
}
// Seleziona ed esegue azione
function doSelectListAction(idList, rowNumber, idAction){
	 checkGridRow(idList, rowNumber, true, idAction);
	 doListAction(idList, idAction);
}

function doListAction(idList, which){
	var actionSelector = document.getElementById(idList + "_actionSelector");
	var actionArray = "";

	if (actionSelector == 0) return false;
	
	// Recupero un'azione dall'id
	if (typeof(which) != "undefined"){
		actionArray = eval(idList + "_AL_" + which);
	}else{
		actionArray = eval(idList + "_AL_" + actionSelector.value);
	}
	
	if (actionArray.get("selection") != "NONE" && window[idList + "_SC"] == 0){
		if (actionArray.get("selection") == "MULTIPLE"){
			alert(yav_config.FILTER_SEL_ONE);
		}else if (actionArray.get("selection") == "SINGLE"){
			alert(yav_config.FILTER_SEL_ONE);
		}
	}else if (actionArray.get("selection") == "SINGLE" && window[idList + "_SC"] > 1 && window[idList + "_ST"] != "SINGLE"){
		
		alert("Selezionare UN SOLO elemento della lista");
		
	}else{
		var checkSubmit = actionArray.get("validation");

		if (actionArray.get("target") == "WINDOW" || actionArray.get("target") == "TAB"){
			var w = +actionArray.get("width"); //Il + per convertire in intero
			var h = +actionArray.get("height");
			
			// Controllo messaggio conferma
			if (actionArray.get("confirmMessage") != ""){
				if (!confirm(actionArray.get("confirmMessage"))){
					return;
				}
			}
			var canClose = actionArray.get("canClose");
			var closeFunction = actionArray.get("closeFunction");
			var modale = actionArray.get("modale");
			
			var window_action = _APP_BASE + APP_SCRIPT + "?t=" + actionArray.get("action") 
			+ "&f=" + actionArray.get("form")
			+ "&g=" + actionArray.get("gateway") + "&"
				+ actionArray.get("parameters");
			var bigData = "";
			if (actionArray.get("serialize") == true){
				// Send to windows all form serialize
				//window_action += "&" + jQuery("#" + APP_FORM).serialize();
				bigData = jQuery("#" + APP_FORM).serialize();
			} else if (actionArray.get("selection") != "NONE"){
				// Include only current list selections
				var rowListCounter = 0;
				var hiddenFieldRow = document.getElementById(idList + "-" + rowListCounter);
				var checkBoxRow = document.getElementById(idList + "-" + rowListCounter + "-checkbox");
				//window_action += "&IDLIST=" + idList;
				while(checkBoxRow){
					if (checkBoxRow.checked){
						window_action += "&" + idList + "-" + rowListCounter + "=" + hiddenFieldRow.value; //hidden
						window_action += "&" + idList + "-" + rowListCounter + "-checkbox=true";// checkbox
					}else{
						window_action += "&" + idList + "-" + rowListCounter + "=" + hiddenFieldRow.value; //hidden
					}
					rowListCounter++;
					hiddenFieldRow = document.getElementById(idList + "-" + rowListCounter);
					checkBoxRow = document.getElementById(idList + "-" + rowListCounter + "-checkbox");
				}
			}
			//alert(actionArray.get("width"));
			window_action += "&IDLIST=" + idList;
			//openWindow(window_action, "actionList", actionArray.get("width"), actionArray.get("height"));
			if(actionArray.get("target") == "WINDOW") {
				openWindow(window_action, "actionList", w, h, modale, canClose,checkSubmit,closeFunction,bigData);
			}else {
				window.open(window_action+"&"+bigData+"&DECORATION=lookup");
			}

														
		}else if (actionArray.get("target") == "AJAX"){
			// Controllo stato browser
			if (checkBlockBrowser()){
				blockBrowser(true);
				
				// Cancello eventuali errori
				document.getElementById("messageArea").style.display = "none";
				
				// Ajax 
				jQuery.ajax({  
					type: "POST",
					url: _APP_BASE + APP_SCRIPT + "?t=" + actionArray.get("action") 
					+ "&f=" + actionArray.get("form")
					+ "&g=" + actionArray.get("gateway") + "&DECORATION=clean&"
						+ actionArray.get("parameters"),
					data: jQuery("#" + APP_FORM).serialize()
					}).done(function ( data ) { 
						blockBrowser(false);
						if ((data).indexOf("true")>=0){
							// Corretto
							setUpdateStatus("OFF");
							doPagination(idList, "REMOVE_SELECTION");
						}else{
							// Errori
							doPagination(idList, "RELOAD");
						}
						
						// Riporto selezione stato iniziale
						if(actionSelector != null) {
							actionSelector.selectedIndex = 0;
						}
					}).fail(function ( data ) {  
						doPagination(idList, "RELOAD");
				 		blockBrowser(false);
					}); 
			}
		}else{
			if (actionArray.get("script") != ""){
				eval(actionArray.get("script"));
			}else{
				doSubmit(actionArray.get("action")+"&IDLIST=" + idList + "&g=" + actionArray.get("gateway"), actionArray.get("form"), checkSubmit, false, actionArray.get("confirmMessage"));
			}
		}
	}
	
}

function manageList(idList){
	//w = 600;
	//h = 380;
	w = "";
	h = "";
	
	openWindow(_APP_BASE + APP_SCRIPT + "?IDLIST=" + idList + "&DECORATION=lookUp&t=MANAGELIST", "manageList", w, h);
}

function viewListSql(idList){
	//w = 800;
	//h = 500;
	w = 0;
	h = 0;
	
	openWindow(_APP_BASE + APP_SCRIPT + "?IDLIST=" + idList + "&DECORATION=lookUp&t=VIEW_LIST_SQL", "viewListSql", w, h);
}

function viewHelpTool(argomento, scheda, width, height) {
	openWindow(_APP_BASE + APP_SCRIPT + "?DECORATION=lookUp&t=HELP_TOOL&ARGOMENTO="+argomento+"&SCHEDA="+scheda, "viewHelpTool", width, height);
}

function ordersList(idList){
	//w = 600;
	//h = 360;
	w = "";
	h = "";
	
	openWindow(_APP_BASE + APP_SCRIPT + "?ORDLIST=" + idList + "&DECORATION=lookUp&t=ORDERS_LIST", "ordersList", w, h);
}

function exportList(idList){
	if (typeof(idList)=="undefined") idList="";
	w = 700;
	h = 550;
//	w = "";
//	h = "";
	openWindow(_APP_BASE + APP_SCRIPT + "?DECORATION=lookUp&t=EXPORTLIST&EXP_LIST="+idList, "exportList", w, h,undefined,undefined,undefined,undefined,jQuery("#" +APP_FORM).serialize());
	//openWindow(_APP_BASE + APP_SCRIPT + "?DECORATION=lookUp&t=EXPORTLIST&EXP_LIST="+idList+"&" + jQuery("#" +APP_FORM).serialize(), "exportList", w, h);
	
}

// Da modificare per farlo funzionare con ID del download e non con FILE_NAME
function getFile(fileName, contest){
	w = 100;
	h = 100;
	document.location.href=_APP_BASE + APP_SCRIPT + "?t=FILEDWN&FILE_NAME=" + fileName + "&CONTEST=" + contest + "&DECORATION=clean";
}

function exportListFile(idList){
	if (typeof(idList)=="undefined") idList="";
	if (checkBlockBrowser()){
		blockBrowser(true, "Esportazione in corso...");
		var formObj = document.getElementById(APP_FORM);
		formObj.action = _APP_BASE + APP_SCRIPT + "?t=EXPORTLIST&f=EXPORT&IDLIST="+idList;
		formObj.target = "_self";
		jQuery('#' + APP_FORM).append('<input type="hidden" name="WI400_HMAC" value="'+__WI400_HMAC+'" />');
		jQuery('#' + APP_FORM).append('<input type="hidden" name="WI400_SESSION" value="'+__WI400_SESSION+'" />');
		formObj.submit();
	}
}

function checkSelection(selectObj) {
	var value = selectObj.value;
	if(value == "EMPTY" || value == "NOT_EMPTY") {
		var id = (selectObj.name).split("_");
		jQuery("#FAST_FILTER_"+id[2]).val("");
	}
}


function setMultyFilter(multyFilter, idLista){
	/*document.getElementById("FAST_MULTI_FILTER").name = "FAST_FILTER_" + multyFilter.value;
	document.getElementById("FAST_MULTI_FILTER_OPTION").name = "FAST_FILTER_" + multyFilter.value + "_OPTION";
	document.getElementById("FAST_MULTI_FILTER").value = "";*/
	
	//Visualizzo solo il filtro che è stato selezionato
	jQuery('#'+idLista+'_slider .multi_filterFast_display').css("display", "none");
	jQuery('#TABLE_TR_'+multyFilter.value).css('display', 'block');
	
	//Ogni volta che cambio filtro sbianco i campi
	jQuery('#'+idLista+'_slider input[id^="FAST_FILTER_"]').val("");
	
	var checkBox = jQuery('input:checkbox[name^="FAST_FILTER_"]');
	if(checkBox.length) {
		checkBox.prop("checked", false);
	}
	
	var select = jQuery('select[id^="FAST_FILTER_"]');
	select.val('');
	
	//Tolgo il filtro fast attivo al momento per ripristinare la tabella normale
	jQuery('.filter-button')[1].click();
}

function goToPage(idList) {
	/*
	 * N.B. il contenuto del dialog viene stampato a video nel wi400List.php riga 924 con style none
	 * quando viene aperto il dialog il style display viene cambiato automaticamente
	*/
	h = 180;
	var x = (screen.height-h)/3;
	if(IS_MOBILE) x = 10;
	jQuery( "#"+idList+"_dialog" ).dialog({
		title: "Scegli la pagina",
		height: h,
		width: 400,
		modal: true,
		position: ["middle",x],
		show: {
			effect: "blind",
			duration: 300
		},
		buttons: {
			"Ok": function() {
				var num_page = jQuery('#'+idList+'_NUM_PAGE').val();
				jQuery('#'+idList+'_NUM_PAGE').val("");
				jQuery(this).dialog("close");
				var label_pag = jQuery("#"+idList+"_PAGINATION_LABEL").html();
				var dati_pagina = label_pag.split(" / ");
				if(dati_pagina[0] != num_page) {
					if(num_page && (num_page > 0 && num_page <= parseInt(dati_pagina[1]))) {
						doPagination(idList, "RELOAD", false, num_page);
					}else {
						if(num_page != null) {
							alert("Pagina non valida!");
						}
					}
				}
			},
			"Cancel": function() {
				jQuery(this).dialog( "close" );
			}
		},
		open: function() {
			jQuery('.ui-dialog-buttonpane').css({ "margin-top" : "0px", "padding": "0px" });
			jQuery(".ui-dialog-content").css("height", "33px");
		}
	});
}

function doPagination(idList, pagination, search, num_page, callback, position){
	if (checkBlockBrowser(idList)){
		if(!jQuery('.detail-row').find('input[detailid]')) {
			rules = [];
		}
		yav.startFrom = 0;
		if(!callback) callback = function() {};
		if(typeof(position) == "undefined") {
			posistion = true;
		}
		// Verifico possibilitÃ  di eseguire l'azione
		var prevButton = document.getElementById(idList+"_PREV_BUTTON");
		var nextButton =  document.getElementById(idList+"_NEXT_BUTTON");
		if ((pagination == _PAGE_PREV) && prevButton != null && prevButton.disabled){
			return;
		}else if ((pagination == _PAGE_NEXT) && nextButton != null && nextButton.disabled){
			return;
		}else{
			blockBrowser(true, "", idList);

			var saveScroll = 0;
			var objScroll = jQuery('#'+idList + "Scroll");
			if (objScroll.length){
				saveScroll = objScroll.scrollLeft();
			}
			
			// ricarica Pagina
			if (pagination == "RELOAD"){
				pagination = _PAGE_RELOAD;
			}
			
			// Ignora selezioni della pagina
			if (pagination == "IGNORE_SELECTION"){
				pagination = _PAGE_RELOAD + "&IGNORE_SELECTION=true";
			}
			
			// remove selection request parameter
			if (pagination == "REMOVE_SELECTION"){
				pagination = _PAGE_RELOAD + "&REMOVE_SELECTION=true";
			}
			var doNothing="";
			if (search=="SAVE") {
				doNothing="&NOTHING_TO_DO";
			}
			if(num_page) {
				pagination += "&NUM_PAGE="+num_page;
			}
			/*if (search=="SAVE") {
				blockBrowser(false, "", idList);
				top.resubmitPage();
				return;
			}*/
			var string;
			string = _APP_BASE + "index.php?IDLIST=" + idList + "&" + "SCROLL=" +saveScroll + "&PAGINATION=" + pagination+doNothing;
			string = "&wi400_validate_url="+string.length;
			jQuery.ajax({  
				type: "POST",
				url: _APP_BASE + "index.php?IDLIST=" + idList + "&" + "SCROLL=" +saveScroll + "&PAGINATION=" + pagination+doNothing,
				data: jQuery("#" + APP_FORM).serialize()+string
			}).done(function ( data ) {
				blockBrowser(false, "", idList);
		 		var parentObj = "";
		 		parentObj = getFrameWindowById(idList);
		 		// LZ Reload degli iFrame collegati alla lista
		 		// ???
		 		if (parentObj.document.getElementById(idList+"_GRIDSRCH_HEADER")) {
			 		var iframe = parentObj.document.getElementById(idList+"_GRIDSRCH_HEADER");
			 		iframe.src = iframe.src;
				}
				/*if (IFRAME_LOOKUP){
					var lookUpParent = document.getElementById("LOOKUP_PARENT");
					if (lookUpParent && lookUpParent.value != ""){
						// Sono in un lookup aperto da un altro lookup
						IFrameObj = parent.window.frames;
						for (x = 0; x < IFrameObj.length; x++){
							if(IFrameObj[x].name == lookUpParent.value+"_content"){
								parentObj = IFrameObj[x];
								break;
							}
						}
					}else{
						parentObj = parent;
					}
				}else{
					parentObj = window.opener;
				}
				if (parentObj=="") {
					parentObj=parent.window.frames;
				}*/
				if (search=="SAVE") {
					//alert(top.location.href);
					wi400top.location.href=wi400top.location.href;
					//jQuery.post(top.location.href);
					return;
				}
				if (search){
					//top.resubmitPage();
					//parentObj.document.getElementById(idList + "Container").innerHTML = data;
					parentObj.jQuery("#" + idList + "Container").html(data);
					//jQuery("#"+idList + "Container", parentObj.document).find("script").each(function(){
					//	  eval(jQuery(this, parentObj.document).text());
					//	 });
					closeLookUp();
				}else{
					/*var obj = jQuery("#" + idList + "Container");
					if(!obj.length)
						obj = parentObj.jQuery("#" + idList + "Container");*/
					//var obj = parentObj.jQuery("#" + idList + "Container");
					// Se non ho trovato nulla cerco un modo diverso di trovare la lista
					//console.log("PRIMA:"+obj.length);
					//if (obj.length==0) {
					//	obj = jQuery("#" + idList + "Container");
					//}
					var obj = getFrameWindowById(idList + "Container", false, true);
					//console.log(obj);
					//var obj = getFrameWindowById(idList + "Container").jQuery("#" + idList + "Container");
					//console.log("DOPO:"+obj.length);
					jQuery(obj).css('width', 1);
					try {
						jQuery(obj).html(data);
					} catch (e) {
					}
					//obj.html(data);
					//console.log("continuo di qua!");
					if (position==true) {
					
						jQuery(obj).ready(function() {
							if(typeof(globalScrollTop) != "undefined") {
								jQuery(document).scrollTop(globalScrollTop);
							}
							if(typeof(doubleScroll) != "undefined") {
								doubleScroll.doubleScroll("refresh");
							}
						});
					}
					callback();
				}
				
				if (typeof(bindUpdateListRowChange) == 'function') { 
					bindUpdateListRowChange();
				}
			}).fail(function ( data ) {
				alert("SERVER ERROR:"+data);
		 		blockBrowser(false);
			}); 
		}
	}
}

function doSaveSearch(idList){
	//w = 400;
	//h = 200;
	w = "";
	h = "";
	
	openWindow(_APP_BASE + "index.php?t=FILTER_SAVE&IDLIST=" + idList + "&DECORATION=lookup", "filterSave", w, h);
}

function doSearch(idList, action, next){
	if (typeof action == "undefined") action = "SEARCH";
	if (typeof next == "undefined") next = true;
	if (rules.length > 0 && !yav.performCheck('wi400Form', rules, "inline")){
		alert(yav_config.MODULE_ERROR);
		return;
	}else{
		if (document.getElementById("ACTION_FORM_VALIDATION")){
			document.getElementById("ACTION_FORM_VALIDATION").disabled = false;
		}
	}
    mydocument = getFrameWindowById(idList+ "_SEARCH");
	if (mydocument.document.getElementById(idList + "_SEARCH")){
		mydocument.document.getElementById(idList + "_SEARCH").value = action;
		doPagination(idList, _PAGE_FIRST, next);
	}
}

function doRemoveSearch(idList){
	if (document.getElementById(idList + "_SEARCH")){
		document.getElementById(idList + "_SEARCH").value = "REMOVE";
		doPagination(idList, _PAGE_FIRST, true);
	}
}
function doOrder(idList, orderColumn, alertDoOrder){
	if (checkBlockBrowser(idList)){

		if (!alertDoOrder || confirm("Cancellare gli ordinamenti avanzati?")){
		
			blockBrowser(true, "", idList);
			var saveScroll = jQuery("#" + idList + "Scroll").scrollLeft();
			

			jQuery.ajax({  
				type: "POST",
				url: _APP_BASE + "index.php?IDLIST=" + idList + "&SCROLL=" + saveScroll + "&ORDER=" + orderColumn,
				data: jQuery("#" + APP_FORM).serialize()
				}).done(function ( data ) {  
					jQuery("#" + idList + "Container").html(data);
				}).fail(function ( data ) {  
					alert("SERVER ERROR:"+data);

			 		blockBrowser(false);
				}); 
			
		}
	
	}
}

function doHeader(value, idList, column, action, form, callBack, type){
	
	// Controllo stato browser
	if (typeof(type) == "undefined") type = "ajax";
	var typelower = type.toLowerCase(); 
	if (checkBlockBrowser()){
		blockBrowser(true);
		
		// Cancello eventuali errori
		document.getElementById("messageArea").style.display = "none";

		switch (typelower) {
			case "ajax":
				jQuery.ajax({  
					type: "POST",
					url: _APP_BASE + APP_SCRIPT + "?t=" + action
					+ "&f=" + form
					+ "&DECORATION=clean&IDLIST=" + idList + "&VALUE=" + value + "&COL=" + column,
					data: jQuery("#" + APP_FORM).serialize()
					}).done(function ( data ) {  
						blockBrowser(false);
						doPagination(idList, "IGNORE_SELECTION");
						if (typeof(callBack) != "undefined") eval(callBack);
					});
				break;
			case "window":
				openWindow(_APP_BASE + "index.php?t="+ action +"&f=" + form + "&DECORATION=lookup"+ "&IDLIST="+ idList +"&VALUE=" + value + "&COL=" + column, "detailSave", 600, 500);
				blockBrowser(false);
				if (typeof(callBack) != "undefined") eval(callBack);
				break;
			case "action":
				blockBrowser(false);
				doSubmit(action+"&IDLIST=" + idList , form, true, false, "");
				//if (typeof(callBack) != "undefined") eval(callBack);
				break;	
		}
	}
}


function gridActionChange(idList){
	var actionSelector = document.getElementById(idList + "_actionSelector");

	if (actionSelector.value != ""){
		document.getElementById(idList + "_doAction").src= _THEME_DIR + "images/next.gif";
	}else{
		document.getElementById(idList + "_doAction").src= _THEME_DIR + "images/next_disabled.gif";
	}
}


function passValue(valueToPass, field_id, append, appendChar){
	var parentObj = "";
	if (typeof (append) == "undefined") append = false;
	
	parentObj = getFrameWindowById(field_id, true);
	var obj = parentObj.jQuery("#" + field_id);
	if (typeof (appendChar) == "undefined" || obj.val() == "") appendChar = "";
	if (append) valueToPass = obj.val() + appendChar + valueToPass;
	obj.val(valueToPass);
	obj.trigger('onchange');
	return;
	if (IFRAME_LOOKUP){
		var lookUpParent = document.getElementById("LOOKUP_PARENT");
		if (lookUpParent && lookUpParent.value != ""){
			// Sono in un lookup aperto da un altro lookup
			IFrameObj = parent.window.frames;
			for (var y = 0; y < IFrameObj.length; y++){
				if(IFrameObj[y].name == lookUpParent.value){
					parentObj = IFrameObj[y];
					break;
				}
			}
		}else{
			parentObj = parent;
		}
	}else{
		parentObj = window.opener;
	}
	if (parentObj=="" || parentObj==null) {
		parentObj = parent;
	}
	if (typeof (appendChar) == "undefined" 
			|| parentObj.document.getElementById(field_id).value == "") appendChar = "";
	if (append) valueToPass = parentObj.document.getElementById(field_id).value + appendChar + valueToPass;
	parentObj.document.getElementById(field_id).value = valueToPass;
}

//Passa la chiave al campo della pagina sottostante
function passKey(idList, rowNumber,jsFunction){
	if (typeof (jsFunction) == "undefined") jsFunction = "";
	
	var lookUpFieldsList = eval(idList + "_LOOKUP_FIELDS");

	var valuesToPass = document.getElementById(idList + "-" + rowNumber).value;
	//console.log(document.getElementById(idList + "-" + rowNumber+"-checkbox"));
	setTimeout(function() {
		jQuery("#"+idList + "-" + rowNumber+"-checkbox").attr('checked', false);
		jQuery("#"+idList + "-" + rowNumber+"-tr").removeClass('wi400-grid-row_selected');
	}, 400);
	
	var keyValues = valuesToPass.split("|");
	var is_multi = {length: 0};
	
	for (var x = 0; x < keyValues.length; x++){
		//var parentObj = parent;
		parentObj = getFrameWindowById(lookUpFieldsList[x], true);
		var obj = parentObj.jQuery("#" + lookUpFieldsList[x]);
		obj.val(keyValues[x]);
		is_multi = parentObj.jQuery('#'+obj.attr("id")+"_MULTIPLE");
		// Se detailid è vuoto significa che sono in una lista
		//if(!obj.attr("detailid")) {
			obj.trigger('onchange');
		//}
		
		// Passaggio descrizione
		if (document.getElementById(idList + "-" + rowNumber + "_DESCRIPTION") && x == 0
				&& parentObj.document.getElementById(lookUpFieldsList[x] + "_DESCRIPTION")){
				parentObj.document.getElementById(lookUpFieldsList[x] + "_DESCRIPTION").innerHTML = document.getElementById(idList + "-" + rowNumber + "_DESCRIPTION").value;
		}else{
			if (parentObj.document.getElementById(lookUpFieldsList[x] + "_DESCRIPTION")){
				parentObj.document.getElementById(lookUpFieldsList[x] + "_DESCRIPTION").innerHTML = "";
			}
		}
		
		parentObj.updateField(lookUpFieldsList[x]);
		// Per lanciare gli eventuali controlli client sul campo
		
		// Seleziono riga della lista modificata
		if (document.getElementById("FROM_LIST")){
			var fromList = document.getElementById("FROM_LIST").value;
			var fromRow  = document.getElementById("FROM_ROW").value;
			//parentObj.checkGridRow(fromList, fromRow, true);
		}
	}
	/*if (jsFunction!="") {
		eval ("wi400top."+jsFunction+";");
	}*/
	
	//Chiudo il lookup solo se non è multiField
	if(!jsFunction || is_multi.length == 0) {
		closeLookUp();
	}
}
function showSearch(idList, action, filterCounter){


	/*w = 600;
	h = (30 * filterCounter) + 200;
	if (filterCounter < 3){
		h = 180;
	}else if (filterCounter > 12){
		h = 450
	}*/
	w = "";
	h = "";
	openWindow(_APP_BASE + "index.php?t=" + action + "&IDLIST=" + idList + "&DECORATION=lookup", "searchList", w, h);

}

function showListTree(idList){
	w = 350;
	h = 500;
	//w = "";
	//h = "";
	
	openWindow(_APP_BASE + "index.php?t=TREELIST&IDLIST=" + idList + "&DECORATION=lookup", "filterTree", w, h);
}

	
// Per creare il lookup dinamicamente in caso di ajax
function addLookUpConfig(idLookUp, luAction, luJsParameters, fieldId){
	window[idLookUp] = new wi400Map();
	window[idLookUp].put("FIELD_ID", fieldId);
	window[idLookUp].put("ACTION", luAction);
	window[idLookUp].put("JS_PARAMETERS", luJsParameters);
}


function closeAndRefresh(){
	if (IFRAME_LOOKUP){
			wi400top.location.href=wi400top.location.href;
			//setTimeout("top.f_dialogClose();",300);
			closeLookUp();
	}else{
		window.opener.location.href=window.opener.location.href;
		//self.close();
		closeLookUp();
	}
}

function showDetail(idList, colNumber, rowNumber, action, action_form, width, height, modale, url_encode, col_key, can_close, gateway){
//function showDetail(idList, colNumber, rowNumber, action, action_form, width, height, modale, colKey){

	if (typeof(modale) == "undefined") modale = true;
	if (typeof(can_close) == "undefined") can_close = true;
	if (typeof(url_encode) == "undefined") url_encode = false;
	if (typeof(col_key) == "undefined") col_key = '';
	if (typeof(gateway) == "undefined") gateway = '';
	
	if (checkBlockBrowser()){
		
		var valuesToPass = document.getElementById(action + "-" + idList + "-" + colNumber + "-" + rowNumber).value;
		
		if (url_encode) valuesToPass = encodeURIComponent(valuesToPass);
		var parametersToPass = "";
		var parametersArray = window[idList+"_parameters"];
		
		for (var c = 0; c < parametersArray.length; c++){
			if (typeof (document.getElementById(parametersArray[c])) != "undefined"){
				parametersToPass += "&" + parametersArray[c] + "=" + document.getElementById(parametersArray[c]).value;
			}
		}
		
		w = "";
		h = "";
		
		if(width) w = width;
		if(height) h = height;
		
		if (typeof (action_form) != "undefined"){
			action_form = "&f=" + action_form;
		}else{
			action_form = "";
		}
		if (gateway != ""){
			gateway = "&g=" + gateway;
		}
/*		
		if (typeof (colKey) != "undefined"){
			col_id = "&COLUMN_KEY=" + colKey;
		}else{
			col_id = "";
		}
*/		
		openWindow(_APP_BASE + "index.php?t=" + action + action_form + gateway + "&ROW_NUMBER=" + rowNumber + "&COL_NUMBER=" + colNumber + "&DETAIL_KEY=" + valuesToPass + parametersToPass + "&COLUMN_KEY=" + col_key + "&PARENT_ID=" + idList, "showDetail", w, h, modale, can_close);
//		openWindow(_APP_BASE + "index.php?t=" + action + action_form + "&DETAIL_KEY=" + valuesToPass + parametersToPass + "&PARENT_ID=" + idList + col_id, "showDetail", w, h, modale);
	}
}

function onClickDetail(idList, colNumber, rowNumber, action, form, gateway, confirm_message, url_encode) {
//	parentObj = parent;
	
	var valuesToPass = document.getElementById(action + "-" + idList + "-" + colNumber + "-" + rowNumber).value;
	
	if (url_encode) valuesToPass = encodeURIComponent(valuesToPass);
	var parametersToPass = "";
	var parametersArray = window[idList+"_parameters"];
	
	for (var c = 0; c < parametersArray.length; c++){
		if (typeof (document.getElementById(parametersArray[c])) != "undefined"){
			parametersToPass += "&" + parametersArray[c] + "=" + document.getElementById(parametersArray[c]).value;
		}
	}

//	parentObj.checkGridRow(idList, rowNumber, true);
	
//	doSubmit(action + "&IDLIST=" + idList + "&g=" + gateway, form, false, false, confirm_message);
	doSubmit(action + "&IDLIST=" + idList + "&DETAIL_KEY=" + valuesToPass + parametersToPass + "&g=" + gateway, form, false, false, confirm_message);
}


function onClickSelAction(idList, colNumber, rowNumber, action, form, gateway, confirm_message, url_encode) {
//	parentObj = parent;
	
	var valuesToPass = document.getElementById(action + "-" + idList + "-" + colNumber + "-" + rowNumber).value;
	
	if (url_encode) valuesToPass = encodeURIComponent(valuesToPass);
	var parametersToPass = "";
/*
	var parametersArray = window[idList+"_parameters"];
	
	for (var c = 0; c < parametersArray.length; c++){
		if (typeof (document.getElementById(parametersArray[c])) != "undefined"){
			parametersToPass += "&" + parametersArray[c] + "=" + document.getElementById(parametersArray[c]).value;
		}
	}
*/
//	parentObj.checkGridRow(idList, rowNumber, true);
	
//	doSubmit(action + "&IDLIST=" + idList + "&g=" + gateway, form, false, false, confirm_message);
	doSubmit(action + "&IDLIST=" + idList + "&DETAIL_KEY=" + valuesToPass + parametersToPass + "&g=" + gateway, form, false, false, confirm_message);
}


// DOUBLE LIST

function dobuleListUpDown(){
	var rightObj = document.getElementById("double_list_RIGHT");
	selectedCount = 0;
	for(i=rightObj.length-1; i>=0; i--){
	  if(rightObj.options[i].selected) selectedCount++;
	}
	if (selectedCount == 1){
		if (rightObj.selectedIndex == 0){
			doubleButtonStatus("ARROW_UP", 	 "up", 	  true);
		}else{
			doubleButtonStatus("ARROW_UP", 	 "up", 	  false);
		}
		if (rightObj.selectedIndex == rightObj.length-1){
			doubleButtonStatus("ARROW_DOWN", "down",  true);
		}else{
			doubleButtonStatus("ARROW_DOWN", "down",  false);
		}
		doubleButtonStatus("COL_FIX",  	 "colfix",  false, "themes/common/");
	}else{
		doubleButtonStatus("ARROW_UP", 	 "up", 	  true);
		doubleButtonStatus("ARROW_DOWN", "down",  true);

		doubleButtonStatus("COL_FIX",  	 "colfix",  true, "themes/common/");
	}

}

var continuosScrollUp = false;
var continuosScrollDown = false;

function moveUpList() {
   if (continuosScrollUp){
	   var listField = document.getElementById("double_list_RIGHT");
	   if ( listField.length == -1) {  // If the list is empty
	   	  continuosScrollUp = false;
	      alert("There are no values which can be moved!");
	   } else {
	      var selected = listField.selectedIndex;
	      if (selected == -1) {
	      	 continuosScrollUp = false;
	         alert("You must select an entry to be moved!");
	      } else {  // Something is selected 
	         if ( listField.length == 0 ) {  // If there's only one in the list
	         	continuosScrollUp = false;
	            alert("There is only one entry!\nThe one entry will remain in place.");
	         } else {  // There's more than one in the list, rearrange the list order
	            if ( selected == 0 ) {
	            	continuosScrollUp = false;
	            } else {
	            	var blockedColumn = columnsMap.get(listField.options[selected].value);
	            	if (!blockedColumn){
		               // Get the text/value of the one directly above the hightlighted entry as
		               // well as the highlighted entry; then flip them
		               var moveText1 = listField[selected-1].text;
		               var moveText2 = listField[selected].text;
		               var moveValue1 = listField[selected-1].value;
		               var moveValue2 = listField[selected].value;
		               listField[selected].text = moveText1;
		               listField[selected].value = moveValue1;
		               listField[selected-1].text = moveText2;
		               listField[selected-1].value = moveValue2;
		               listField.selectedIndex = selected-1; // Select the one that was selected before
	               }
	            }  // Ends the check for selecting one which can be moved
	         }  // Ends the check for there only being one in the list to begin with
	      }  // Ends the check for there being something selected
	   }  // Ends the check for there being none in the list
	   dobuleListUpDown();
	   columnReorder();
   }
   if (continuosScrollUp) setTimeout("moveUpList()",150);
}


function moveDownList() {
   if (continuosScrollDown){
	   var listField = document.getElementById("double_list_RIGHT");
	   if ( listField.length == -1) {  // If the list is empty
	      continuosScrollDown = false;
		  alert("There are no values which can be moved!");
	   } else {
	      var selected = listField.selectedIndex;
	      if (selected == -1) {
	      	 continuosScrollDown = false;
	         alert("You must select an entry to be moved!");
	      } else {  // Something is selected 
	         if ( listField.length == 0 ) {  // If there's only one in the list
	      	 	continuosScrollDown = false;
	            alert("There is only one entry!\nThe one entry will remain in place.");
	         } else {  // There's more than one in the list, rearrange the list order
	            if ( selected == listField.length-1 ) {
	            	 continuosScrollDown = false;
	            } else {
	            	var blockedColumn = columnsMap.get(listField.options[selected].value);
	            	if (!blockedColumn){
		               // Get the text/value of the one directly below the hightlighted entry as
		               // well as the highlighted entry; then flip them
		               var moveText1 = listField[selected+1].text;
		               var moveText2 = listField[selected].text;
		               var moveValue1 = listField[selected+1].value;
		               var moveValue2 = listField[selected].value;
		               listField[selected].text = moveText1;
		               listField[selected].value = moveValue1;
		               listField[selected+1].text = moveText2;
		               listField[selected+1].value = moveValue2;
		               
		               listField.selectedIndex = selected+1; // Select the one that was selected before
		           }
	            }  // Ends the check for selecting one which can be moved
	         }  // Ends the check for there only being one in the list to begin with
	      }  // Ends the check for there being something selected
	   }  // Ends the check for there being none in the list
	   dobuleListUpDown();
	   columnReorder();
	   
   }
   if (continuosScrollDown) setTimeout("moveDownList()",150);
}


function doubleListClick(column){


	if (column == "RIGHT"){
		
		document.getElementById("double_list_LEFT").selectedIndex = -1;

		doubleButtonStatus("ADD", 	  	 "next",  true);
		doubleButtonStatus("REMOVE",  	 "prev",  false);
		dobuleListUpDown();
		
	}else{
		document.getElementById("double_list_RIGHT").selectedIndex = -1;
		
		doubleButtonStatus("ADD", 	  	 "next",  false);
		doubleButtonStatus("REMOVE",  	 "prev",  true);
		doubleButtonStatus("ARROW_UP", 	 "up", 	  true);
		doubleButtonStatus("ARROW_DOWN", "down",  true);
		doubleButtonStatus("COL_FIX",  	 "colfix",  true , "themes/common/");

	}

}

function addOption(theSel, theText, theValue)
{
  var newOpt = new Option(theText, theValue);
  var selLength = theSel.length;
  theSel.options[selLength] = newOpt;
}

function deleteOption(theSel, theIndex)
{ 
  var selLength = theSel.length;
  if(selLength>0)
  {
    theSel.options[theIndex] = null;
  }
}

function doubleListFix(){
	var theSelFrom = document.getElementById("double_list_RIGHT");
	var selLength = theSelFrom.length;
	var i;
	  
	for(i=selLength-1; i>=0; i--){
		var blockedColumn = columnsMap.get(theSelFrom.options[i].value);
		//if (!blockedColumn){ -->LZ POSSO FISSARE ANCHE COLONNA DI INPUT
			if(theSelFrom.options[i].selected){
				if (columnsFixMap.get(theSelFrom.options[i].value)){
					theSelFrom.options[i].className = "";
					columnsFixMap.put(theSelFrom.options[i].value, false);
				}else{
					theSelFrom.options[i].className = "select-option-fixed";
					columnsFixMap.put(theSelFrom.options[i].value, true);
				}
			}
		//}
	}
	
	columnReorder();
}

function doubleListMove(from, to, moveAll)
{
  theSelFrom = document.getElementById("double_list_"+from);
  theSelTo = document.getElementById("double_list_"+to);
  
  var selLength = theSelFrom.length;
  var selectedText = new Array();
  var selectedValues = new Array();
  var selectedCount = 0;
  
  var i;
  
  for(i=selLength-1; i>=0; i--)
  {
  	var blockedColumn = columnsMap.get(theSelFrom.options[i].value);
  	var fixedColumn = columnsFixMap.get(theSelFrom.options[i].value);
  	var inputColumn = columnsInputMap.get(theSelFrom.options[i].value);
  	
    if(((!blockedColumn && !fixedColumn) || from == "LEFT")&& (theSelFrom.options[i].selected || moveAll))
    {
      selectedText[selectedCount] = theSelFrom.options[i].text;
      selectedValues[selectedCount] = theSelFrom.options[i].value;
      deleteOption(theSelFrom, i);
      selectedCount++;
    }
  }
  
  for(i=selectedCount-1; i>=0; i--)
  {
    addOption(theSelTo, selectedText[i], selectedValues[i]);
  }
  	columnReorder();
  
}

function columnReorder(){
        var rightObj = document.getElementById("double_list_RIGHT");
        var keyList = new Array();
        var keyFixList = new Array();
        for(i=0; i < rightObj.length; i++){
               keyList[i] = rightObj.options[i].value;

               if (document.getElementById("columnsFix")){

                       var blockedColumn = columnsMap.get(rightObj.options[i].value);
                       var inputColumn = columnsInputMap.get(rightObj.options[i].value);
        
                       //if (blockedColumn || inputColumn){
                       if (blockedColumn){
                               rightObj.options[i].className = "select-option-disabled";
                       }else{
                               if (columnsFixMap.get(rightObj.options[i].value)){
                                       rightObj.options[i].className = "select-option-fixed";
                                      keyFixList.push(rightObj.options[i].value);
                                      columnsFixMap.put(rightObj.options[i].value, true);
                               }else{
                                      rightObj.options[i].className = "";
                                      columnsFixMap.put(rightObj.options[i].value, false);
                               }
                       }       
               }
        }
        
        document.getElementById("columnOrder").value = keyList.join("|");
        if (document.getElementById("columnsFix")){
                document.getElementById("columnsFix").value = keyFixList.join("|");
        }
}

function doubleButtonStatus(which, image, disabled, path){ 
if (document.getElementById(which)){
	if (typeof(path) == "undefined") path = _THEME_DIR; 
		disabledLabel = ""; 
		if (disabled) disabledLabel = "_disabled"; 
		document.getElementById(which).disabled = disabled; 
		document.getElementById(which).src = path + "images/grid/" + image + disabledLabel + ".gif"; 
	}
}

function sortListClick(column){
	dobuleListUpDown();
}

function startDraggableColumn(idList, columnKey, rowNumber) {
for (var x = 0; x < rowNumber; x++){
	if (document.getElementById(idList + '_' + columnKey + '_' + x)){
		jQuery("#" + idList + "_" + columnKey + "_" + x).draggable({ iframeFix: true,helper:'clone'});
		//new SubsDraggable(idList + '_' + columnKey + '_' + x, {dragelement:getDragElement});
	}else{
		break;
	}
}
}
function saveAjaxListConf(idList) {
	var stringa = [];
	var misure = [];
	var prefix ="";
	// Reperisco le colonne di destra
	jQuery('#table_'+idList).find('tr:first').find('th:not(.wi400-grid-header-first-cell), td').each(function (index){
		//_this = this;
		
		if(this.tagName == "TD") {
			jQuery('th[gruppo="'+(this.getAttribute('gruppo'))+'"]').each(function(j) {
				stringa.push(this.id);
				misure.push(jQuery(this).width());
			});
		}else {
			stringa.push(this.id);
			misure.push(jQuery(this).width());
		}
	});
	
	var fixed="";
	prefix ="";
	// Reperisco le colonne di sinistra
	jQuery('#table_fixed_'+idList).find('th').filter(':not(.nosort)').each(function (index){
		fixed = fixed + prefix + this.id;
		stringa.push(this.id);
		misure.push(jQuery(this).width());
		prefix = "|";
    });
	stringa = stringa.join("|");
	misure = misure.join("|");
	// Aggiornamento Ordinamento colonne via Ajax per successivi ordinamenti
	jQuery.ajax({  
		type: "GET",
		url: _APP_BASE + APP_SCRIPT + "?t=MANAGELIST&f=SAVE&DECORATION=clean&IDLIST="+idList+"&COLUMN_ORDER="+stringa+"&COLUMNS_FIX="+fixed+"&MISURE="+misure+"&AJAX_REQUEST=SI&NUM_ROWS="+window[idList+'_ROWS']
	}).done(function ( response ) {  
		//alert(response);
		//document.getElementById(div).innerHTML = response;
	}).fail(function ( data ) {  
		
	});
}
function resizeListRow(idList) {
	// Altezza delle varie righe
	var rows = jQuery("#table_fixed_"+idList).children('tbody').children('tr');
	var rows2 = jQuery("#table_"+idList).children('tbody').children('tr');
	/*var rowspan = jQuery("#table_"+idList +" tr.wi400-grid-header").find('th:first').attr('rowspan');
	if (typeof(rowspan) == "undefined") rowspan = 0;
	var start_fix = 0;
	var xj = 0;
	if (rowspan<=1) {
			start_fix = 0;
			xj = 0;
	}else {
			start_fix = 1;
			xj = rowspan;
	}*/
	
	var col_fix = jQuery("#table_fixed_"+idList +" tr.wi400-grid-header-fixed").find('th:first');
	if(col_fix.length) {
		// Recupero l'altezza della prima colonna della lista	
		var altezza = jQuery('.wi400-grid-header-first-cell').height();
		var altezza_fix = col_fix.height();
		
		//Controllo l'altezza più alta tra le 2 colonne
		if(altezza < altezza_fix) altezza = altezza_fix;
		
		// Forzo l'altezza delle colonne fixed con l'altezza della prima colonna
		col_fix.css( "height", Math.ceil(altezza)+"px");
		jQuery(".wi400-grid-header-first-cell").css( "height", Math.ceil(altezza)+"px");
	}
	// Altezza del primo
	var altFix = new Array();
	var altCon = new Array();
	//var altDet = new Array();
	// Metto su un array tutte le colonne fixed con altezza
	for (var i=0; i<rows.length; ++i) {
		altFix[i]=jQuery(rows[i]).height();
	}
	// Mettor su un array tutte le colonne normali
	var num = -1;
	var hasDetail=false;
	for (var i=0; i<rows2.length; ++i) {
		if (rows2[i].id.indexOf("-detail") !== -1) {
			hasDetail=true;
			if(jQuery('#'+rows2[i].id).is(':visible')) {
				altCon[num]=altCon[num]+jQuery(rows2[i]).height();
				//altDet[num]=jQuery(rows2[i]).height();;
			}
		} else {
			num = num+1;
			altCon[num]=jQuery(rows2[i]).height();
		}
	}
	if (hasDetail) {
		for (var i=0; i<altFix.length; ++i) {
			var fix = altFix[i];
			var con = altCon[i];
			var itemFix = rows[i];
			var itemCon = jQuery("#"+idList+"-"+i+"-tr");
			var itemDis = jQuery("#"+idList+"-"+i+"-detail");
			// Do per scontato che CON sia quella corretta
			if (fix > con) {
				jQuery(itemCon).css( "height", Math.ceil(con+1)+"px");
				jQuery(itemFix).css( "height", Math.ceil(con+1)+"px");
			}
			if (fix < con) {
				if(jQuery(itemDis).is(':visible')) {
				   // Non faccio niente	
				} else {	
					jQuery(itemCon).css( "height", Math.ceil(con)+"px");
				} 
				jQuery(itemFix).css( "height", Math.ceil(con)+"px");
			}	
		}
	} else {
		for (var i=0; i<rows.length; ++i) {
			var item = rows[i];	
			var altfix = jQuery(item).height();
			var altcon = jQuery(rows2.eq(i)).height();
			if (altfix > altcon) {
				jQuery(rows2.eq(i)).css( "height", Math.ceil(altfix)+"px");
				jQuery(item).css( "height", Math.ceil(altfix)+"px");
			}
			if (altfix < altcon) {
				jQuery(rows2.eq(i)).css( "height", Math.ceil(altcon)+"px");
				jQuery(item).css( "height", Math.ceil(altcon)+"px");
			}	
		}
	}
	//alert(altCon);
	//alert(altFix);
	//for each (var item in rows) {
	/*for (var i=0; i<rows.length; ++i) {
		var item = rows[i];	
		var altfix = jQuery(item).height();
		var altcon = jQuery(rows2.eq(i)).height();
		if (altfix > altcon) {
			jQuery(rows2.eq(i)).css( "height", Math.ceil(altfix)+"px");
			jQuery(item).css( "height", Math.ceil(altfix)+"px");
		}
		if (altfix < altcon) {
			jQuery(rows2.eq(i)).css( "height", Math.ceil(altcon)+"px");
			jQuery(item).css( "height", Math.ceil(altcon)+"px");
		}	
	}*/
	// New ... ma non tiene conto della spaced .. 
	/*for (var i=0; i<999; ++i) {
		if (fixRow = jQuery("#"+idList+'-Fixed-'+i+'-tr')) {
			var altfix = fixRow.height();
			var row = jQuery("#"+idList+'-'+i+'-tr');
			var altcon = row.height();
		} else {
			break;
		}
		if (altfix > altcon) {
			jQuery(row).css( "height", Math.ceil(altfix)+"px");
			jQuery(fixRow).css( "height", Math.ceil(altfix)+"px");
		}
		if (altfix < altcon) {
			jQuery(row).css( "height", Math.ceil(altcon)+"px");
			jQuery(fixRow).css( "height", Math.ceil(altcon)+"px");
		}	
	}*/
}
function resizeListColumn(idList1, idList2, fixedCol, level) {
	// Altezza delle righe della lista 1 e 2
	var rows =  jQuery("#table_"+idList1).children('tbody').children('tr:first');
	var a = jQuery("#table_"+idList1);
	var b = jQuery("#table_"+idList2);
	var offset = a.offset();
	//alert(offset.left);
	var offset2 = b.offset();
	//alert(offset.left);
	// 0.5B test solo con prima riga
	var rows2 = jQuery("#table_"+idList2).children('tbody').children('tr:first');
	//alert(idList1 + " - " + idList2);
	// Carico la lunghezza attuale delle CELLE e memorizzo il totale delle prime 4 che dovranno stare assieme
	var larFix = new Array();
	var totalWidth = 0;
	var first4 = 0;
	var first3 = 0;
	var totcell = 0;
	for (var i=0; i<rows[0].cells.length; ++i) {
		larFix[i]=Math.round(jQuery(rows[0].cells[i]).width());
		if (i<3) {
			first3 = first3 + larFix[i];
		}
		if (i<4) {
			first4 = first4 + larFix[i];
		}
		totcell++;
		totalWidth = totalWidth + Math.round(jQuery(rows[0].cells[i]).outerWidth(true));
	}
	first3 = (first3 - 10);
	// Ricalcolo la lunghezza delle celle per la sottotabella
	// Ciclo su tutte le righe
	var dif = 0;
	var Ccount = 0;
	for (var i=0; i<rows2.length; i++) {
		if (rows2[i].id.indexOf("-detail") !== -1) {
			continue;
		}
		// Numero Colonne Presenti sulla Lista di riferimento
		var c=rows[0].cells.length-1;
		Ccount=1;
		// Calcolo la lunghezza totale del TR delle sottoriga
		var lenTr=0;
		var lastCell=0;
		var variableCell=0;
		for(j=rows2[i].cells.length-1; j>=0; j--){
			var len = larFix[c];
			if (Ccount<fixedCol) {
				// Colonne Normali @todo Come identificare la colonna Descrizione
				if (jQuery(rows2[i].cells[j]).children('div:first').length > 0) {
					jQuery(rows2[i].cells[j]).children('div:first').css( "width", len+"px");
				} else {
					jQuery(rows2[i].cells[j]).wrapInner('<div class="wi400-grid-row-content" style="width:'+len+'px"></div>');
				}
			}
			c--;
			// Se per caso sfondo in negativo ESCO ... non dovrebbe mai succedere
			if (c<0) {
				break;
			}
			// Verifico se ho superato il numero di colonne che devo calcolare
			Ccount++;
			if (Ccount==fixedCol) {
				lastCell=rows2[i].cells.length-Ccount;
				variableCell=Math.round(jQuery(rows2[0].cells[lastCell]).width());
				break;
			}
		}
		// Ricalcolo la lunghezza del record appena modificato
		var totSotCell=0;
		var incremento = -5;
		for(j=rows2[i].cells.length-1; j>=0; j--){
			//alert(rows2[0].cells[j].id);
			totSotCell++;
			lenTr = lenTr + jQuery(rows2[0].cells[j]).outerWidth(true);
		}	
		var difCell=totcell-totSotCell;
		if (difCell>0) {
			//incremento = incremento + (difCell*-26);
		}
		//alert(lenTr);
		
		var lenRigaVar = ((variableCell + totalWidth) - lenTr)-(offset2.left-offset.left)+incremento;
		//var lenRigaVar = ((variableCell + totalWidth) - lenTr)-(31*level)+incremento;
		// se è l'ultimo livello senza apertura devo aggiungere 1
		// Colonne Normali @todo Come identificare la colonna Descrizione
		if (jQuery(rows2[0].cells[lastCell]).children('div:first').length > 0) {
			jQuery(rows2[0].cells[lastCell]).children('div:first').css( "width", lenRigaVar+"px");
		} else {
			jQuery(rows2[0].cells[lastCell]).wrapInner('<div class="wi400-grid-row-content" style="width:'+lenRigaVar+'px"></div>');
		}

		// Aggiorno la riga variabile
		
		
	// 0.5B Imposto solo la prima riga, il resto vien da se
	break;
	}
	/* VERSIONE 2 for (var i=0; i<rows2.length; i++) {
		if (rows2[i].id.indexOf("-detail") !== -1) {
			continue;
		}
		// Colonna finale oggetto lista
		var c=rows[0].cells.length-1;
		Ccount=0;
		for(j=rows2[i].cells.length-1; j>=0; j--){
			var len = larFix[c];
			if (j==3) {
				var dif = larFix[c]-first3;
				len = dif;
			}
			// Colonne Normali @todo Come identificare la colonna Descrizione
			if (jQuery(rows2[i].cells[j]).children('div:first').length > 0) {
				jQuery(rows2[i].cells[j]).children('div:first').css( "width", len+"px");
			} else {
				jQuery(rows2[i].cells[j]).wrapInner('<div class="wi400-grid-row-content" style="width:'+len+'px"></div>');
			}
			c--;
			// Se per caso sfondo in negativo ESCO ... non dovrebbe mai succedere
			if (c<0) {
				break;
			}
			// Verifico se ho superato il numero di colonne che devo calcolare
			Ccount++;
			if (Ccount==fixedCol) {
				break;
			}
		}
		// 0.5B Imposto solo la prima riga, il resto vien da se
		break;
	}
	VERSION 1
	/*for (var i=0; i<rows2.length; ++i) {
		var tot=0;
		//alert("COLONNE:"+rows2[i].cells.length+" ID ".rows.2);
		// Salto l'eventuale Dettaglio
		if (rows2[i].id.indexOf("-detail") !== -1) {
			continue;
		}
		// Devo Verificare se c'è la colonna di apertura
		// Ciclo su tutte le colonne
		for (var j=0; j<rows2[i].cells.length; ++j) {
			// La prima viene ignorata
			//if (j==0) {
			//	first3=first3+jQuery(rows2[i].cells[j]).width();
			//	continue;
			//}
			//alert("CICLO COLONNE SECONDA LISTA!");
			// le prime 3 sono ok @todo aggiungere il controllo del tipo cella
			if (j<3) {
				continue;
			}
			// quarta cella deve essere il totale meno lunghezza già usata
			if (j==3) {
				var dif = larFix[j]-first3;
				//alert(dif);
				if (jQuery(rows2[i].cells[3]).children('div:first').length > 0) {
					jQuery(rows2[i].cells[3]).children('div:first').css( "width", dif+"px");
				} else {
					jQuery(rows2[i].cells[3]).wrapInner('<div class="wi400-grid-row-content" style="width:'+dif+'px"></div>');
				}
			}
			// tutte le altre prendono la stessa lunghezza della lista principale
			// Verifico se c'è giù un DIV con la lunghezza altrimenti lo aggiungo
			if (jQuery(rows2[i].cells[j]).children('div:first').length > 0) {
				jQuery(rows2[i].cells[j]).children('div:first').css( "width", larFix[j]+"px");
			} else {
				jQuery(rows2[i].cells[j]).wrapInner('<div class="wi400-grid-row-content" style="width:'+larFix[j]+'px"></div>');
			}
		}
		break;
	}*/

}

function setSelectionRangeDevice(obj, tablet) {
	if(typeof(timeoutRange) == "undefined") timeoutRange = "";
	if(timeoutRange) clearTimeout(timeoutRange);
	
	var that = obj; 
	
	timeoutRange = setTimeout(function() {
		that.setSelectionRange(0, 9999);
	}, 10);
}

function prev_next2(focus, dati, next) {
	//var id = focus.attr("id");
	//var dati = id.split("-");
	
	var td = jQuery(focus).closest('td[class^="wi400-grid-row-cell"]');

	if(next) {
		next_td = td.next();
	}else {
		next_td = td.prev();
	}
	
	if(next_td.length) {
		var next_input = next_td.find('input[type="text"][id^="'+dati[0]+'"]');
		if(next_input.length) {
			return next_input;
		}else {
			return prev_next(next_td, dati, next);
		}
	}else {
		return null;
	}
}
function prev_next(focus, dati, next, pidList) {
	//var id = focus.attr("id");
	//var dati = id.split("-");
	var id = focus.attr("id");
	var posizione = id.split("-");
	var row = parseInt(posizione[1]);
	// Ciclo per capire quali sono le colonne di input
	var keys = new Array();
	var ix = 0;
	var now = -1;
	var max = 0;
	var field = posizione[2];
	// @todo se ultimo campo pagina giro la pagina
	var num_rows = window[currentPaginationListKey+'_ROWS'];
	// Aggiungo colonne Fixed
	jQuery("#table_fixed_"+pidList).find('th').each(function()
			  {
		      if (this.id!="") {
			  	//alert(this.id+ " - "+posizione[2]);
		    	  key = posizione[0]+"-"+posizione[1]+"-"+this.id;	
		    	  if (jQuery("#"+key).is("input") || jQuery("#"+key).is("select")) { 	
			    	  keys[ix] = this.id;
			    	  if (this.id.trim()==posizione[2]) {
			    		  now=ix;
			    	  }
			    	  ix = ix +1;  	
			      }
		    	  
		      }
	});	
	// Aggiungo colonne di Lista
	jQuery("#table_"+pidList).find('th').each(function()
			  {
		      if (this.id!="") {
		    	  //alert(this.id+ " - "+posizione[2]);
		    	  key = posizione[0]+"-"+posizione[1]+"-"+this.id;	
		    	  if (jQuery("#"+key).is("input") || jQuery("#"+key).is("select")) { 	
			    	  keys[ix] = this.id;
			    	  if (this.id.trim()==posizione[2]) {
			    		  now=ix;
			    	  }
			    	  ix = ix +1;  	
			      }
		      }
	});
	max = ix-1;
	// Avanti .. se sono in fondo riga nuova
	if (next) {
		  // Sono in fondo e vado a riga nuova
		  if (now==max) {
			  row=row+1;
			  if (row==num_rows) {
				  row=row-1;
			  } else {
				  field=keys[0];
			  }
		  } else {
			  field=keys[now+1];
		  }
	} else {
		  if (now==0) {
			  row=row-1;
			  if (row<=0) {
				  row=0;
			  } else {
				  field=keys[max];
			  }
		  } else {
			  field=keys[now-1];
		  }
	}
  	var next_id = posizione[0]+"-"+row+"-"+field;
  	jQuery("#"+next_id).focus().delay(500).select();
	setFocusOnInput(jQuery("#"+next_id));
}
function setFocusOnInput(campo) {
	setTimeout(function() { jQuery(campo).select(); }, 0);
}

function enableMovingWithKeys(listId) {
	document.getElementById(listId+'_slider').onkeydown = function(e) {
		var pk = e ? e.which : window.event.keyCode;
		var dove=0;
		//var frecce = ["37", "38", "39", "40"];
		//if(frecce.indexOf(pk)) {
			var focus = jQuery(':focus');
			var id = focus.attr("id");
			
			if(!id) return;
			var dati = id.split("-");
		//}
		if (currentPaginationListKey=="") {
    	     	currentPaginationListKey=arrayPaginationListKey[1];
    	 }
		var num_rows = window[currentPaginationListKey+'_ROWS'];
		var tasti = [38, 40, 39, 37, 34, 33];
		if(tasti.indexOf(pk) != -1) { 
			e.stopImmediatePropagation();
		}
		
		//SU
		if(pk == 38) {
			if (dati[1]>0) {
				dove = parseInt(dati[1])-1;
			} else {
				dove = num_rows-1;
			}	
			var next_id = dati[0]+"-"+dove+"-"+dati[2];
			jQuery("#"+next_id).focus();
			setFocusOnInput(jQuery("#"+next_id));
		} //GIU'
		else if(pk == 40) {
			if ((parseInt(dati[1])+1)<num_rows) {
				dove = parseInt(dati[1])+1;
			} else {
				dove = 0;
			}
			var next_id = dati[0]+"-"+dove+"-"+dati[2];
			jQuery("#"+next_id).focus().delay(500).select();
			setFocusOnInput(jQuery("#"+next_id));
		} //DESTRA
		else if(pk == 39) {
			var nextObj = prev_next(focus, dati, true, listId);
			/*if(nextObj) {
				jQuery(nextObj).focus();
				setFocusOnInput(jQuery(nextObj));
			}*/
		} //SINISTRA
		else if(pk == 37) {
			var nextObj = prev_next(focus, dati, false, listId);
			/*if(nextObj) {
				jQuery(nextObj).focus();
				setFocusOnInput(jQuery(nextObj));
			}*/
		}
		else if (pk==34){
	      	if (!document.getElementById(currentPaginationListKey+"_NEXT_BUTTON").disabled){
	      		doPagination(currentPaginationListKey,_PAGE_NEXT);
	      		var next_id = dati[0]+"-"+dati[1]+"-"+dati[2];
				setTimeout(function() {
	      		jQuery("#"+next_id).focus().delay(500).select();
				setFocusOnInput(jQuery("#"+next_id));
				}, 500);
	      	}
	      // Pagina indietro	
	     }else if (pk==33){
	    	if (!document.getElementById(currentPaginationListKey+"_PREV_BUTTON").disabled){
	      		doPagination(currentPaginationListKey,_PAGE_PREV);
	      		var next_id = dati[0]+"-"+dati[1]+"-"+dati[2];
				setTimeout(function() {
		      		jQuery("#"+next_id).focus().delay(500).select();
					setFocusOnInput(jQuery("#"+next_id));
					}, 500);
	     }
	     }
	};
}
function wi400_managePaste(e, pidList, pcallback, pageRows) {
	var pastedText = undefined;
	var callBack = pcallback;
	if (window.clipboardData && window.clipboardData.getData) { // IE
		pastedText = window.clipboardData.getData('Text');
	} else if (e.clipboardData && e.clipboardData.getData) {
		pastedText = e.clipboardData.getData('text/plain');
	}
	//alert(pastedText); // Process and handle text...
	//alert(e.target.id);
	var fields = pastedText.split("\n");  // in your case s.split("\t", 11) might also do
	//alert(fields.length);
	// Cerco eventualmente con separatore ;
	if (fields.length<=1) {
		fields = pastedText.split(";")
	}
	var posizione = e.target.id.split("-");
	var row = parseInt(posizione[1]);
	// Ciclo per capire quali sono le colonne di input
	var keys = new Array();
	var ix = 0;
	// Aggiungo colonne Fixed
	jQuery("#table_fixed_"+pidList).find('th').each(function()
			  {
		      if (this.id!="") {
			  	//alert(this.id);
		    	  key = posizione[0]+"-"+posizione[1]+"-"+this.id;	
		    	  if (jQuery("#"+key).is("input") || jQuery("#"+key).is("select")) { 	
			    	  keys[ix] = this.id.trim();
			    	  ix = ix +1;  	
			      }
		      }
	});	
	// Aggiungo colonne di Lista
	jQuery("#table_"+pidList).find('th').each(function()
			  {
		      if (this.id!="") {
			  	//alert(this.id);
		    	  key = posizione[0]+"-"+posizione[1]+"-"+this.id;	
		    	  if (jQuery("#"+key).is("input") || jQuery("#"+key).is("select")) { 	
			    	  keys[ix] = this.id.trim();
			    	  ix = ix +1;  	
			      }
		      }
	});
	// FINE CICLO PER PRENDERE LE COLONNE DI INPUT
	// REPERISCO IL NUMERO DI RIGHE MAX DELLA GRIGLIA VISUALIZZATA
	if (currentPaginationListKey=="") {
	     	currentPaginationListKey=arrayPaginationListKey[1];
	}
	// Tolgo eventuali righe bianche
	var ex_fields= new Array();
	var yy = 0;
	fields.forEach(function(item, index){
		//alert(item.hexEncode());
		if (item!="") {
			ex_fields[yy]=item;
			yy=yy+1;
		}	
	});
	fields = ex_fields;
	//var listmaxrow = window[currentPaginationListKey+'_ROWS'];
	var listmaxrow = pageRows;
	if (fields.length+row>listmaxrow) {
		alert('Troppe righe da incollare, aumentare le righe della griglia');
		return false;
	}	
	//
	var ciclo = 0;
	var ok = true;
	fields.forEach(function(item, index){
		if (ok==false) {
			return false;
		}	 
		// Verifico se MultiTab
		//alert(posizione[0]+"-"+row+"-"+posizione[2]);
		// Gestire ulteriori campi passati con il TAB .. SERVE INIZIALIZZARE UN ARRAY DI CAMPI AMMESSI E SEQUENZA
		items = item.split("\t")
		if (items.length<=1) {
			// Controllare se l'elemento esiste altrimenti mi fermo .. forse è la fine della lista
			var key = posizione[0]+"-"+parseInt(row)+"-"+posizione[2];
			// Controllo se il target è protetto
			if (jQuery("#"+key).attr("readonly")=="readonly") {
				alert("Non è possibile incollare dati su celle protette");
				ok=false;
				return false;
			}	
			var max = document.getElementById(key).maxLength;
			if (max>0) {
				document.getElementById(key).value = items[0].substring(0, max);
			} else {
				document.getElementById(key).value = items[0];
			}// Lancio l'evento ON Change per validazioni e scrittura SUBFILE .. forse devo attendere un pò ..
			setTimeout(function() {
				jQuery("#"+key).trigger("onchange");
			}, ciclo * 200);
		} else {
			// Metto i vari campi sugli impunt
			var key = posizione[0]+"-"+parseInt(row)+"-"+posizione[2];
			//alert(key);
			jj = keys.indexOf(posizione[2].trim());
			if (items.length>keys.length || (jj+items.length)>keys.length) {
				alert('Numero di colonne da incollare maggiore di quelle presenti sulla lista!');
				ok = false;
				return false;
			}
			items.forEach(function(item, index){ 
				// Controllare se l'elemento esiste altrimenti mi fermo .. forse è la fine della lista
				if (jQuery("#"+key).attr("readonly")=="readonly") {
					alert("Non è possibile incollare dati su celle protette");
					ok=false;
					return false;
				}	
				var maxl = document.getElementById(key).maxLength;
				max = parseInt(max);
				//alert(item + " key "+key);
				if (max>0) {
					document.getElementById(key).value = items[index].substring(0, max);
				} else {
					document.getElementById(key).value = items[index];
				}
				//jQuery("#"+key).val(document.getElementById(key).value).change();
				// Se è un campo select devo selezionarlo
				if (jQuery("#"+key).is("select")) {
					var key2 = '#'+key+ ' option[value="'+items[index]+'"]';
					//alert(key2);
					jQuery(key2).prop('selected', true);
				}	
				// Lancio l'evento ON Change per validazioni e scrittura SUBFILE .. sull'ultima colonna selezionata
				if ((index+1)==items.length) {
				//setTimeout(function() {
					jQuery("#"+key).trigger("onchange");
				//}, (ciclo+index+1) * 200);
				}
 				//}
				// Prendo il prossimo campi di input dove mettere il valore
				jj = jj +1;
				key = posizione[0]+"-"+parseInt(row)+"-"+keys[jj];
				//alert(jj);
				//alert(keys);
				//alert(key);
			});	
		}	
		ciclo = ciclo + 1;
		row = row + 1;
	});
	if (callBack != "") eval(callBack);		
	return false; // Prevent the default handler from running.
};

function selectRowEveryWhere(obj, event) {
	var fields = obj.id.split("-"); 
	// Get Element Focused
	var focus = jQuery(':focus');
	var id = focus.attr("id");
	
	// Se ho cliccato su un component che ha già l'attributo on click non faccio niente
	if (jQuery(event.target).attr("onClick") != undefined) {
		return true;
	} else {
		//console.log(jQuery("#"+id));
		if (jQuery("#"+id).is("input, select") ) { 
			//alert("is input");
		}else {	
			var checkBox = document.getElementById(fields[0] + "-" + fields[1] + "-checkbox");
			// Se non è settato lo setto
			if (checkBox && checkBox.checked){
				checkGridRow(fields[0], fields[1], false);
			}else {
				checkGridRow(fields[0], fields[1], true);
			}
		}
	}
}

//Viene richiamata quando premo invio e sono all'interno di un filtro fast
function enterFastFilter(e, idLista) {
	if(e.keyCode == 13) {
		var input = jQuery(e.target);

		doPagination(idLista, _PAGE_FIRST, "", "", function() {
			input.focus();
		});
	}
}

//Viene richiamata quando premo invio all'interno del campo di scelta numero pagina
function enterNumPageKey(e, idList) {
	if(e.keyCode == 13) {
		//jQuery('.ui-dialog-buttonset').find('button').first().click();
		jQuery('div[aria-describedby="'+idList+'_dialog"]').find('.ui-dialog-buttonset button:first').click();
	}
}

// Attiva la selezione per copiare i dati inseriti in una lista
function attivaCopiaDatiRiga(idList) {
	var selezionati = jQuery('.ui-selected');
	//if(jQuery('.selectableInputRowList.'))
	if(!jQuery('.ui-selectable').length) {
		jQuery(".selectableInputRowList").selectable({
			filter: ".wi400-grid-row-cell ",
			cancel: '.cancel',
			//autoRefresh: false,
			//noConflict : false,
			selecting: function( event, ui ) {
				//console.log(ui);
				selezionati.addClass('ui-selected');
			},
			selected: function( event, ui ) {
				//console.log(ui.selected);
				
				//console.log(jQuery(ui.selected));
				var obj = jQuery(ui.selected);
				var find = selezionati.filter(obj);
				
				if(find.length) {
					obj.removeClass('ui-selected');
				}
			},
			start: function(event, ui) {
//				console.log('start');
				selezionati = jQuery('.ui-selected');
				//console.log(selezionati);
			},
			stop: function(event, ui) {
				//console.log('stop');
				//copiaDati = jQuery('.ui-selected input[class="inputtext"]').not('input:disabled');
			}
		});
	}else {
		jQuery(".selectableInputRowList").selectable('destroy');
		
		jQuery('.wi400-grid-row-cell.ui-selected').removeClass('ui-selected');
	}
}

function copiaDatiSelezionati(idList) {
	copiaDati = [];
	var rigaDati = [];
	var testoAppunti = [];
	var rigaAppunti = [];
	var oldRiga = '';
	jQuery('.ui-selected').each(function(i) {
	    //console.log(this);
	    //console.log(i);
		
	    var that = jQuery(this);
	    var riga  = that.parent().attr('id').split('-')[1];
	    if(!oldRiga) {
	    	oldRiga = riga;
	    }else if(riga != oldRiga) {
	    	testoAppunti.push(rigaAppunti);
	    	copiaDati.push(rigaDati);
	    	
	    	rigaAppunti = [];
	    	rigaDati = [];
	    	oldRiga = riga;
	    }
	    var value = type = '';
	    
	    var objInput = that.find('input,select[class="inputtext"]');
	    if(objInput.length) {
	    	if(objInput.prop("tagName") == 'SELECT') {
	    		value = objInput.find('option:selected').text();
	    		type = 'select';
	    	}else {
	    		type = objInput.attr('type');
	    		if(type == 'text') {
	    			value = objInput.val();
	    		}else if(type == 'checkBox') {
	    			value = objInput.prop('checked'); 
	    		}
	    	}
	    }else {
	    	value = that.clone().find('script').remove().end().text();
	    	value = jQuery('<textarea />').html(value).text();
	    	type = 'normal';
	    }
	    
	    rigaDati.push({
    		'value': value,
    		'type': type
    	});
	    
	    if(typeof(value) == 'boolean') {
	    	value = value ? '1' : '0';
	    }
	    
	    rigaAppunti.push(value);
	});
	
	if(rigaAppunti) {
		testoAppunti.push(rigaAppunti);
		copiaDati.push(rigaDati);
	}
	
	for(var i in testoAppunti) {
		testoAppunti[i] = testoAppunti[i].join("\t");
	}
	var stringAppunti = testoAppunti.join("\r\n");
	copyToClipboard(stringAppunti);
	
	//console.log(copiaDati);
	
	attivaCopiaDatiRiga();
	
	jQuery('select').focusin(function() {
		LAST_FOCUSED_FIELD = this;
	});
}

function incollaDatiSelezionati(idList) {
	//console.log(copiaDati);
	
	if(copiaDati) {
		var focus = LAST_FOCUSED_FIELD.id;
		var focusSplit = focus.split('-');
		
		
		//jQuery('#TEST_ACCESS_LOG_LIST-2-TEST_DATA').closest('.wi400-grid-row-cell').next()
		for(var i in copiaDati) {
			var cella = jQuery('#'+focusSplit.join('-')).closest('.wi400-grid-row-cell');
			//console.log(cella);
			if(cella.length) {
				for(var j in copiaDati[i]) {
					var dati = copiaDati[i][j];
					//console.log(dati);
					
					var objInput = cella.find('input,select[class="inputtext"]');
					//console.log(objInput);
				    if(objInput.length) {
				    	if(objInput.prop("tagName") == 'SELECT') {
				    		value = objInput.find('option:selected').text();
				    		objInput.find("option").filter(function() {
			    			  //may want to use $.trim in here
			    			  return jQuery(this).text() == dati.value;
			    			}).prop('selected', true);
				    	}else {
				    		type = objInput.attr('type');
				    		if(type == 'text') {
				    			objInput.val(dati.value);
				    		}else if(type == 'checkBox' && dati.type == 'checkBox') {
				    			objInput.prop('checked', dati.value ? true : false); 
				    		}
				    	}
				    }else {
				    	//console.log(cella);
				    	//console.log('continua');
				    }
				    
				    cella = cella.next();
				    //console.log('next cella');
				    //console.log(cella);
				    if(!cella.length) break; 
				}
			}
			focusSplit[1]++;
			//console.log(focusSplit);
		}
	}
}

function copyToClipboard(string) {
	var $temp = jQuery("<textarea>");
	jQuery("body").append($temp);
	$temp.val(string).select();
	document.execCommand("copy");
	$temp.remove();
}