<?php
function getwebscadenza($weblif,$webtiu) {
	$scadenza="";
	if (isset($weblif) && is_numeric($weblif)){
	$scadenza = date('d-m-Y h:i:s').", ".$webtiu.$weblif;
	}
		
	return $scadenza;
}