<?php
	$scriptWindow = function($url, $url_open, $param) {
		echo "<script>
					url = '$url';
					var concat = '&';
					if(url.indexOf('?') == -1) concat = '?'; 
					
					url = url + concat +'CHECKID=' + decodeURIComponent(url).length;
 					window.open(url, '$url_open', '$param');
					closeLookUp();
				</script>";
	};
	
	$dati_azione = rtvAzione($_REQUEST['LINK']);
	$size = "";
	if(isset($_REQUEST['TYPE']) && $_REQUEST['TYPE']) {
		$width = 1000;
		$height = 400;
		if(isset($_REQUEST['AZIONE_5250'])) {
			$width = 10000;
			$height = 10000;
		}else {
			if(isset($_REQUEST['WIDTH'])) $width = $_REQUEST['WIDTH'];
			if(isset($_REQUEST['HEIGHT'])) $height = $_REQUEST['HEIGHT'];
		}
		
		
		$size = "scrollbars=yes, resizable=yes, width=$width, height=$height";
	}
	
	if($dati_azione['TIPO'] == "L" && $dati_azione['URL']) {
		$scriptWindow($dati_azione['URL'], $dati_azione['URL_OPEN'], $size);
	}else {
		if($size) {
			$scriptWindow($appBase."index.php?t=".$dati_azione['AZIONE']."&f=&DECORATION=lookup", "_blank", $size);
		}else {
			$actionContext->gotoAction($dati_azione['AZIONE'], "", false, true);
		}
	}
?>
