<?php
function getInizioPagina($header="", $footer="") {
	$html = '<page backtop="0mm" backbottom="0mm" backleft="0mm" backright="0mm">
			<page_header>
'.trim($header).'
			</page_header>
			<page_footer>'.trim($footer).'
			</page_footer>';
	//$html .= "<table cellpadding='0' cellspacing='0' border='0' width='730'><tr><td>";
	return $html;
}
function getInizioPaginaOld($header="", $footer="") {
	$html = '<page backtop="0mm" backbottom="0mm" backleft="0mm" backright="0mm">
			<page_header><div style="position: absolute; right: 5px;top: 20px; width: 130px; text-align: right; /*background: blue;*/">Pag.[[page_cu]]/[[page_nb]]
                </div>
'.trim($header).'
			</page_header>
			<page_footer>'.trim($footer).'
			</page_footer>';
	//$html .= "<table cellpadding='0' cellspacing='0' border='0' width='730'><tr><td>";
	return $html;
}
function chiudiPagina() {
	//$html = '</td></tr></table></page>';
	$html = '</page>';
	return $html;
}
function fxx($number, $dec, $suppress=True) {
	$numero = number_format($number, $dec , ",","");
	if ($numero == 0 && $suppress==True) {
		$numero = "";
	}
	return $numero;
}