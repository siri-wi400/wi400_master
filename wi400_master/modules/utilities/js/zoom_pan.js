var zoomfactor=0.05 //Enter factor (0.05=5%)

function zoomhelper(){
	if (parseInt(whatcache.style.width)>10&&parseInt(whatcache.style.height)>10){
		whatcache.style.width=parseInt(whatcache.style.width)+parseInt(whatcache.style.width)*zoomfactor*prefix
		whatcache.style.height=parseInt(whatcache.style.height)+parseInt(whatcache.style.height)*zoomfactor*prefix
	}
}


function callZoom(originalW, originalH, what, state, frameName){
	window.frames[frameName].zoom(originalW, originalH, what, state);
}

function callClearzoom(frameName){
	window.frames[frameName].clearzoom();
}

function zoom(originalW, originalH, what, state){
	
	
	if (!document.all&&!document.getElementById)
		return
	whatcache=eval("document.images."+what);
	prefix=(state=="in")? 1 : -1
	if (whatcache.style.width==""||state=="restore"){
		whatcache.style.width=originalW+"px"
		whatcache.style.height=originalH+"px"
		if (state=="restore")
			return
	}
	else{
		zoomhelper()
	}
	beginzoom=setInterval("zoomhelper()",100)
}

function clearzoom(){
	if (window.beginzoom)
		clearInterval(beginzoom)
}

<!--
var period=20;
var val;
var val1;
var frm;
function init() {
}
function stop() {
}
function run() {
}
function init() {
	running = false;
	setTimeout('scroll()',period);
}
function scroll() {
	if(running) {
		window.frames[frm].scrollBy(val,val1);
		setTimeout("scroll()",period);
	}
}
function stop() {
	running = false;
}
function run() {
	if(!running) {
		running=true;
		scroll();
	}
}
//-->