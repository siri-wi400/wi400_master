var colori = {
	'TESTATA': ['.portlet-header', 'background'],
	'TESTO_TESTATA': ['.portlet-header', 'color'],
	'BORDO_TESTATA': ['.portlet-header', 'border'],
	'BODY': [null, 'background'],
	'BORDO': [null, 'border']
};
jQuery( function($) {
	var clickFunction = "";
	var objLabel = "";
	if(typeof(from_announce) != "boolean") {
		$(".sortable").sortable({
			//connectWith: ".column",
			handle: ".portlet-header",
			cancel: ".portlet-toggle",
			//placeholder: "portlet-placeholder ui-corner-all",
			start: function(event, ui) {
				objLabel = ui.item.find('.label-header');
				//console.log(jQuery._data(objLabel[0], "events"), "check click");
				if(jQuery._data(objLabel[0], "events") === undefined) {
					objLabel = "";
					return;
				} 
				clickFunction = jQuery._data(jQuery(objLabel)[0], "events").click[0].handler;
				objLabel.unbind('click');
			},
			stop: function(event, ui) {
				//var objLabel = ui.item.find('.label-header');
				if(!objLabel) return;
				objLabel.unbind('click');
				
				function prova(objLabel, funzione) {
					objLabel.click(funzione);
				}
				
				setTimeout(prova.bind(this, objLabel, clickFunction), 400);
				//console.log(ui.item.find('.label-header'));
				//ui.item.find('.label-header').bind("click");
			}
		});
	}
 
	$(".portlet")
		.addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
		.find(".portlet-header")
		.addClass( "ui-widget-header ui-corner-all");
//		.prepend("<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");
 
		$(".portlet-toggle").on("click", function() {
		var icon = $(this);
		icon.toggleClass("ui-icon-minusthick ui-icon-plusthick");
		icon.closest(".portlet").find(".portlet-content").toggle();
	});

	$(".refresh").on("click", function() {
		var icon = $(this).parent().parent();
		icon.find(".portlet-content").html("<center><i class='fa fa-spinner fa-pulse fa-3x fa-fw'></i></center>");
		//icon.find(".label-header").html("Refresh");
	});
	
	setInterval(function() {
		var rs = jQuery(".portlet-error:contains('1')");
		var display = "none";
		if(rs.length > 0) display = "inline-block";
		
		jQuery('.cont-circle-widget').css("display", display);
	}, 5000);
});

function setPortlet(rs, id) {
	var portlet = jQuery('#'+id);
	var label = portlet.find('.label-header');
	label.html(rs.TITLE);
	portlet.find('.portlet-content').html(rs.BODY);
	var portletError = portlet.find('.portlet-error');
	if(rs.RESULT == "ERROR" || rs.RESULT == "WARNING") {
		portletError.html('1');
	}else {
		portletError.html('0');
	}
	if(rs.MINIMIZED) {
		portlet.find('.ui-icon-minusthick').css("display", "block");
	}
	if(rs.COLOR) {
		if(rs.COLOR == "remove")
			cambiaColore(id, colori, true);
		else 
			cambiaColore(id, rs.COLOR);
	}
	if(+rs.INTERVAL) {
		portlet.find('.refresh').css("display", "none");
		setTimeout(function() {
			creaAjax(id, (rs.RESULT != "SUCCESS"));
		}, rs.INTERVAL);
	}else if(rs.RELOAD) {
		portlet.find('.refresh').off('click').click(function() {
			//nascondo il tasto refresh
			if(rs.RESULT == "ERROR") jQuery(this).css("display", "none");
			creaAjax(id, (rs.RESULT != "SUCCESS"));
		}).css("display", "block");
	}
	if(rs.ONCLICK) {
		label.off('click').click(function() {
			var azione = id.split("-")[0];
			doSubmit(azione, "DEFAULT&g=WI400_WIDGET"); 
			//console.log("click DEFAULTg=WIDGET"); 
		}).css("cursor", "pointer");
	}
}

function creaAjax(id, error) {
	var onError = "";
	if(error) onError = "&ON_ERROR=1";
	jQuery.ajax({
		type: "GET",
		url: _APP_BASE + APP_SCRIPT + "?t=AJAX_WIDGET&DECORATION=clean&ID_WIDGET="+id+onError
	}).done(function (response) {
		var rs = jQuery.parseJSON(response);
		//console.log(rs);
		setPortlet(rs, id);
	}).fail(function (data) {
		console.log("Ajax "+id+" in errore");
		//var refresh = jQuery("#"+id).find(".refresh").css("display");
		var azione = id.split("-")[0];
		
		var rs = {
			'RESULT': "ERROR",
			'TITLE': azione,
			'BODY': "Widget in errore!",
			'INTERVAL': 2000,
			'COLOR': {
				'TESTATA': 'red',
				'TESTO_TESTATA': 'white',
				'BODY': 'white'
			}
		};
		setPortlet(rs, id);
	});
}

function cambiaColore(id, obj, remove) {
	var portlet = jQuery('#'+id);
	
	for(var ele in obj) {
		var dati = colori[ele];
		var find = "";
		if(dati[0]) {
			find = portlet.find(dati[0]);
		}else {
			find = portlet;
		}
		if(remove)
			find.removeAttr("style");
		else
			find.css(dati[1], ""+obj[ele]);
	}
}

function getWidgetPosition() {
	var pos = [];
	jQuery('.portlet').each(function(i, obj) {
		var dati = obj.id.split("-");
		pos.push({
			'azione': dati[0],
			'progressivo': +dati[1],
			'riga': i,
			'user': dati[2]
		});
	});
	
	return pos;
}

function savePositionWidget() {
	var pos = getWidgetPosition();
	jQuery.ajax({
		type: "POST",
		url: _APP_BASE + APP_SCRIPT + "?t=WIDGET&DECORATION=clean&f=SAVE_POSITION",
		data: {
			'POSIZIONI': pos
		}
	}).done(function (response) {
		//console.log(response);
		jQuery("#messageArea")
				.html('<br/><div class="messageLabel_SUCCESS">Posizionamento salvato con successo!</div><br/>')
				.attr("class", "messageArea_SUCCESS")
				.css("display", "block");
	}).fail(function (data) {
		console.log("Ajax save position widget in errore");
	});
}