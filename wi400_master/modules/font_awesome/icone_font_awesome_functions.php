<?php


	function creaIcona($codice, $valore, $showLabel) {
		
		$html = '';
		
		$html .= "<div class='contDatiIcona'>";
			$html .= "<i class='fa $valore' codice='$codice'></i>
					<label class='labelValore'>$valore</label>";
					//<label class='labelCodice'>$codice</label>";
		$html .= "</div>";
		
		return $html;
	}