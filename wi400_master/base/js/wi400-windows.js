var N_BASEZINDEX = 0;
var RE_PARAM = /^\s*(\w+)\s*\=\s*(.*)\s*$/;

function escreenResize(){
	if (window.e_screen != null && document.getElementById('eScreen')) {
		var shadowDiv = document.getElementById('eScreen');
		var a_docSize = f_documentSize();
		shadowDiv.style.left = a_docSize[2] + 'px';
		shadowDiv.style.top = a_docSize[3] + 'px';
		shadowDiv.style.width = a_docSize[0] + 'px';
		shadowDiv.style.height = a_docSize[1] + 'px';
	}
}

// this function makes the document numb to the mouse events by placing the transparent layer over it
function f_putScreen (b_show) {

	var n_nesting = a_windows.length - 1;
	if (b_show == null && !window.b_screenOn)
		return;

	if (b_show == false) {
		window.b_screenOn = false;
		if (window.e_screen != null) e_screen.style.display = 'none';
		return;
	}

	// create the layer if doesn't exist
	if (window.e_screen == null) {
		window.e_screen = document.createElement("div");
		e_screen.innerHTML = "&nbsp;";
		document.body.appendChild(e_screen);
		e_screen.style.position = 'absolute';
		e_screen.id = 'eScreen';
	}

	// set properties
	var a_docSize = f_documentSize();

	e_screen.style.left = a_docSize[2] + 'px';
	e_screen.style.top = a_docSize[3] + 'px';
	e_screen.style.width = a_docSize[0] + 'px';
	e_screen.style.height = a_docSize[1] + 'px';
	
	e_screen.style.zIndex = N_BASEZINDEX + a_windows.length * 2 - 1;

	e_screen.style.display = 'block';
	
	_BLOCK_BROWSER = false;
	
	// WI400 IMPLEMENTS
	// Draggable by scriptacolous
	var idDrag = "lookupDrag_" + (a_windows.length - 1);
	if (document.getElementById(idDrag)){
	// @todo Gestire draggable con nuovo metodo new Draggable(idDrag,{starteffect: false,endeffect: false});
	}
	// END WI400 IMPLEMENTS
}

// returns the size of the document
function f_documentSize () {

var n_scrollX = 0,
	n_scrollY = 0;

	if (typeof(window.pageYOffset) == 'number') {
		n_scrollX = window.pageXOffset;
		n_scrollY = window.pageYOffset;
	}
	else if (document.body && (document.body.scrollLeft || document.body.scrollTop )) {
		n_scrollX = document.body.scrollLeft;
		n_scrollY = document.body.scrollTop;
	}
	else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
		n_scrollX = document.documentElement.scrollLeft;
		n_scrollY = document.documentElement.scrollTop;
	}

	if (typeof(window.innerWidth) == 'number')
		return [window.innerWidth, window.innerHeight, n_scrollX, n_scrollY];
	if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight))
		return [document.documentElement.clientWidth, document.documentElement.clientHeight, n_scrollX, n_scrollY];
	if (document.body && (document.body.clientWidth || document.body.clientHeight))
		return [document.body.clientWidth, document.body.clientHeight, n_scrollX, n_scrollY];
	return [0, 0];
}

function f_dialogOpen (s_url, s_title, s_features, can_close, close_function) {
	_BLOCK_BROWSER = true;
	if (!window.a_windows)
		window.a_windows = [];
		
	// IMPLEMENTAZIONE WI400: Default can close
	if (typeof(can_close) == "undefined"){
		can_close = true;
	}
	var imgClose = '<img src="' + _APP_BASE + 'themes/common/images/spacer.gif" width=5>';
	if (can_close){
		 imgClose = '<img src="' + _THEME_DIR + 'images/close_icon.gif" hspace=5 onclick="' + close_function +'" onmousedown="return false;" style="cursor:pointer;">';		
	}
	// FINE IMPLEMENTAZIONE
	
	// parse parameters
	var a_featuresStrings = s_features.split(',');
	var a_features = [];
	for (var i = 0; i < a_featuresStrings.length; i++)
	
		if (a_featuresStrings[i].match(RE_PARAM)){
			a_features[String(RegExp.$1).toLowerCase()] = RegExp.$2;
			}
	// create element for window
	var n_nesting = a_windows.length;

	// IMPLEMENTAZIONE WI400
	var spanSpaceT = 0;
	var spanSpaceL = 0;
	if (a_windows.length > 0){
		spanSpaceT = 50*a_windows.length;
		spanSpaceL = 10*a_windows.length;
	}
	// FINE IMPLEMENTAZIONE

	var e_window = document.createElement("div");
	e_window.style.position = 'absolute';
	var n_width  = a_features.width  ? parseInt(a_features.width)  : 300;
	var n_height = a_features.height ? parseInt(a_features.height) : 200;
	var a_docSize = f_documentSize ();
	e_window.style.left = (a_features.left ? parseInt(a_features.left) : ((a_docSize[0] - n_width)  / 2) + a_docSize[2] + spanSpaceL) + 'px';
	e_window.style.top  = (a_features.top  ? parseInt(a_features.top)  : ((a_docSize[1] - n_height) / 2) + a_docSize[3] + spanSpaceT) + 'px';
	e_window.style.zIndex = N_BASEZINDEX + a_windows.length * 2 + 2;
	
	e_window.innerHTML = 
		'<div id="lookupDrag_' + n_nesting + '"><table border="0" class="' + (a_features.css ? a_features.css : 'dialogWindow') + '"><tr>' +
		'<td align="right" class="detail-header" style="cursor: move;">'+
		imgClose +
		'</td></tr><tr><td><iframe name="window_' + n_nesting + '" width="' + n_width +
		'" height="' + n_height +
		'" src="' + s_url + '" frameborder="0" onload="this.contentWindow.focus()"></iframe></td></tr></table></div>';
	// @todo Srittura dati del post .... TENTATIVO DI SISTEMARE IL PASSAGGIO DEI DATI ALLA FINESTRA , SE REQUEST TROPPO LUNGA DA ERRORE	
    /*jQuery('#'+APP_FORM).each( function (index){
        var mapInput = document.createElement("input");
            mapInput.type = "hidden";
            mapInput.name = "marameo_hidden";
            mapInput.value = jQuery('#'+APP_FORM).serialize();
            alert(this.val);
            e_window.appendChild(mapInput);
        });*/
    document.body.appendChild(e_window);
	a_windows[n_nesting] = e_window;
	setTimeout("parent.f_putScreen(true)",500);
	// Posizionamento focus su finestra aperta
	var iframe = jQuery('iframe', window.parent.document);
	iframe[n_nesting].focus();	
}

function f_dialogClose() {

	closeLookUp(false);
	return;
	/*var n_nesting = a_windows.length - 1;
	// destroy element
	if (a_windows[n_nesting].removeNode){
		a_windows[n_nesting].removeNode(true);
	} else if (document.body.removeChild){
		document.body.removeChild(a_windows[n_nesting]);
	}
	
	a_windows[n_nesting] = null;
	a_windows.length = n_nesting;

	// Implementazione WI400
	if (a_windows.length == 0){
		if (window.e_screen != null) {
			childObj =  window.e_screen;
			if (document.body.childObj){
				document.body.removeChild(window.e_screen);
			}
		}	
	}*/
	// Fine Implementazione WI400
	/*if (window.a_windows) {
		var n_nesting = a_windows.length;
		if (n_nesting>0) {
			var iframe = jQuery('iframe', window.parent.document);
			iframe[n_nesting-1].focus();
		} else {
			window.focus();
		}
	}*/	
	// move the screen
	//f_putScreen(n_nesting ? true : false);
}
