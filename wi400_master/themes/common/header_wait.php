<DIV ID="cache" onClick="cacheOff()" style="display:none"><TABLE WIDTH=400 BGCOLOR=#000000 border="0" CELLPADDING=0 CELLSPACING=0><TR><TD ALIGN=center 
VALIGN=middle><TABLE WIDTH=100% BGCOLOR=#000000  border="0" CELLPADDING=10 
CELLSPACING=10><TR><TD ALIGN=center VALIGN=middle><FONT FACE="Verdana" SIZE=3 
COLOR=#FFFFFF><img src="<?=  $temaDir ?>images/loading.gif"><BR><b><?echo _t("ATTENDERE_CARICAMENTO")?></b><BR></FONT></TD></TR></TABLE></TD></TR></TABLE></DIV>
<script type="text/javascript" language="JavaScript">
var checkCache = true;
var nava = (document.layers);
var dom = (document.getElementById);
var iex = (document.all);
if (nava) { cach = document.cache;}
else if (dom) {cach = document.getElementById("cache").style;}
else if (iex) {cach = cache.style;}
largeur = screen.width;
cach.left = Math.round((largeur/2)-200);
setTimeout(function(){
	if (checkCache) {
		cach.display = "block";
	}
},1000);
function cacheOff(){
	checkCache = false;
	cach.innerHTML = "";
	cach.display = "none";
}

// Funzione per il logout automatico
window.onunload = function() {
	if (window.opener && window.opener != null 
			&& window.opener.open && !window.opener.closed && window.opener["_CHECK_LOGOUT"]){
		window.opener.unloadApplication();
	}
}

window.onresize = escreenResize;
window.onscroll = escreenResize;
</script>