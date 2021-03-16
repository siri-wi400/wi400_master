<?php
	
	$onChange = "if(this.value == 'HOME') {
					jQuery('#TESACT').attr({
						class: 'inputtextDisabled',
						readonly: true,
						disabled: true
					});
				}else {
					jQuery('#TESACT').attr({
					  class: 'inputtext',
					  readonly: false,
					disabled: false
					});
				}
			";
	
	//$mod_path = "themes/common/css/images/";
	
	$tipo_img = array(
		"TESTO" => get_image_path("text.png"),
		"PDF" => "pdf_th.gif",
		"SEND_MAIL" => get_image_path("mail_light_right.png"),
		"COPY" => get_image_path("copy.png")
	);
	
	$ctt = "select ctttxt from zmsgctt where cttid=? and cttrig=1";
	$cth = "select cthid from zmsgcth where cthid=?";
	$act = "select atcid from zmsgatc where atcid=?";
	$dst = "select dstid from zmsgdst where dstid=?";
	$stmt_params = "select prmid from zmsgprm where prmid=?";		// @todo Parametri Aggiuntivi
	
	$stmt_query = array(
		"TESTO" => array($db->prepareStatement($ctt), $db->prepareStatement($cth)),
		"PDF" => $db->prepareStatement($act),
		"SEND_MAIL" => $db->prepareStatement($dst),
		"COPY" => $db->prepareStatement($stmt_params)
	);

	function getCssButton($topColor = "#FFFFFF", $bottomColor = "#A6A6A6", $color = "black", $border = "#A8A8A8") {
		if(!$color) {
			$color = "black";
		}
		
		$cssButton = "border: solid 1px $border;
						color: $color;
						text-shadow: none;
						width: auto;
						background-color: $topColor;
						background: -webkit-linear-gradient($topColor, $bottomColor);
						background: linear-gradient($topColor, $bottomColor);
						background: -o-linear-gradient($topColor, $bottomColor);
						background: -moz-linear-gradient($topColor, $bottomColor);";
		
		return $cssButton;
	}
	
	
	function getGroupsMessage() {		
		$gruppi = array();
		foreach($_SESSION['WI400_GROUPS'] as $valore) {
			if(strpos($valore, "MSG_") !==false) {
				array_push($gruppi, $valore);
			}
		}
		
		return $gruppi;
	}
	