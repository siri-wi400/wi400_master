
var _APP_NAME = "WI400";
var _APP_URL  =  "/" + _APP_NAME + "/index.php";
var _LOGOUT_ACTION = _APP_URL + "?t=LOGOUT";
var _CHECK_LOGOUT = true;

function unloadApplication(){
	if (_CHECK_LOGOUT){
		window.setTimeout("checkLogout()", 1000);
	}else{
		_CHECK_LOGOUT = true;
	}
}

function checkLogout(){
	if (typeof(wi400_window) == "undefined" || wi400_window.closed) {
		document.getElementById("unloader").src = _LOGOUT_ACTION;
		alert("ATTENZIONE!\r\nL'applicazione e' stata chiusa senza utilizzare il tasto di uscita.\r\nIl logout verra' eseguito automaticamente.");
	}
}

function loadApplication(){
	if (typeof(wi400_window) == "undefined" || wi400_window.closed) {
		var rand_no = Math.ceil(Math.random() * 10000);
		wi400_window = window.open(_APP_URL, "wi400_window"+rand_no, "scrollbars=1,resizable=1,width="+screen.width+",height="+screen.height+", left=0,top=0");
	}else{
		wi400_window.focus();
	}
}

var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		},
		{
			string: navigator.platform,
			subString: "iPhone",
			identity: "iphone"
		},
		{
			string: navigator.platform,
			subString: "iPad",
			identity: "ipad"
		}
	]
};

BrowserDetect.init();

function browserDetection(){
	for (var i=0;i<BrowserDetect.dataBrowser.length;i++)	{
		disabled = "_disabled";
		title = "";
		linkStart = "";
		linkEnd   = "";
		checkBrowser = BrowserDetect.dataBrowser[i];
		if (checkBrowser.identity ==  BrowserDetect.browser){
			disabled = "";
			title =  BrowserDetect.browser + ' ' + BrowserDetect.version + ' ('+ BrowserDetect.OS +')';
			linkStart = "<a href='javascript:loadApplication()'>";
			linkEnd   = "</a>";
		}
		if (typeof checkBrowser.identity != "undefined"){
			document.write(linkStart + '<img src="images/' + BrowserDetect.dataBrowser[i].identity + disabled + '.png" title="' + title + '" hspace="4" border="0">' + linkEnd);
		}
	}
}


function systemDetection(){
	for (var i=0;i<BrowserDetect.dataOS.length;i++)	{
		disabled = "_disabled";
		title = "";
		linkStart = "";
		linkEnd   = "";
		checkBrowser = BrowserDetect.dataOS[i];
		if (checkBrowser.identity ==  BrowserDetect.OS){
			disabled = "";
			title =  BrowserDetect.OS;
			linkStart = "<a href='javascript:loadApplication()'>";
			linkEnd   = "</a>";
		}
		if (typeof checkBrowser.identity != "undefined"){
			document.write('<img src="images/' + (BrowserDetect.dataOS[i].identity).toLowerCase() + disabled + '.png" title="' + title + '" hspace="4" border="0">');
		}
	}
}