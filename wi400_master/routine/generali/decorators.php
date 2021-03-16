<?php

	$p13n ="";
	if(isset($_SESSION['my_p13n'])) {
		$p13n = $_SESSION['my_p13n'];
	}
	
	$decorators_array = array(
		"ADD" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("add.png")
		),
		"ARROW_RIGHT" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("arrow_right.png")
		),
		"ARROW_LEFT" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("arrow_left.png")
		),
		"ASSOCIA" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("relationship.png")
		),
		"BIN" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("bin.png")
		),
		"BOOK" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("help.png")
		),
		"CLEAN" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("clean.png")
		),
		"CONVERT" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("convert.png")
		),
		"COPY" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("copy.png")
		),
		"DOLLAR" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("yav/dollar.png")
		),
		"DOWN" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("down.gif")
		),
		"DUPLICA" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("duplicate.png")
		),
		"ERROR" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("yav/error.gif")
		),
		"EURO" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("euro.png")
		),
		"FLAG_BLUE" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("yav/flag_blue.png")
		),
		"FLAG_GREEN" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("yav/flag_green.png")
		),
		"FLAG_RED" => array(
			"TYPE" => "IMG",
//			"URL" => get_image_path("yav/flag_red.gif")
			"URL" => get_image_path("yav/flag_red.png")
		),
		"FLAG_ORANGE" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("yav/flag_orange.png")
		),
		"FLAG_YELLOW" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("yav/flag_yellow.png")
		),
		"FOLDER" => array(
			"TYPE" => "IMG",
			"URL" => "folder.gif"
		),
		"GRAPH" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("graph.png")
		),
		"GRID" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("table-select-row.png")
		),
		"IMAGE" => array(
			"TYPE" => "IMG",
			"URL" => "tag-image.gif"
		),
		"IMPORT" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("import.png")
		),
		"LIST" => array(
			"TYPE" => "IMG",
//			"URL" => get_image_path("lookup.gif")
			"URL" => get_image_path("lookup.png")
		),
		"LOCKED" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("locked.png")
		),
		"MATH_IS_EQUAL" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("equal.png")
		),
		"MATH_NOT_EQUAL" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("is-not-equal-to.png")
		),
		"MATH_GREATER_THAN" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("is-greater-than.png")
		),
		"MATH_LESS_THAN" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("is-less-than.png")
		),
		"MINUS" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("minus.png")
		),
		"MODIFICA" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("pencil.png")
		),
		"MODIFICA_GREY" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("pencil_disabled.png")
		),
		"NO" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("yav/block.gif")
		),
		"PALLET" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("palet_16.png")
		),
		"PAPERCLIP" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("paperclip.png")
		),
		"PLAY" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("play.png")
		),
		"PLAY_GREY" => array(
			"TYPE" => "IMG",
			"URL" => "play_th.gif"
		),
		"PLUS" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("plus.png")
		),
		"PDF" => array(
			"TYPE" => "PDF",
			"URL" => "pdf_th.gif"
		),
		"PDF_GREY_TH" => array(
			"TYPE" => "PDF",
			"URL" => "pdf_grey_th.png"
		),
		"PDF_GREY" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("pdf_grey.png")
		),
		"PRINT" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("grid/printer.gif")
		),
		"REFRESH" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("refresh.png")
		),
		"RELOAD" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("arrow_curve.png")
		),
		"REMOVE" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("remove.png")
		),
		"SAVE" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("save.png")
		),
		"SCARICHI" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("handle4_16.png")
		),
		"SEARCH" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("zoom.png")
		),
		"SEND_MAIL" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("mail_light_right.png")
		),
		"SQL" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("sql.png")
		),
		"BLACK_TAG" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("black_tag.png")
		),
		"BLUE_TAG" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("blue_tag.png")
		),
		"GREEN_TAG" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("green_tag.png")
		),
		"RED_TAG" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("red_tag.png")
		),
		"YELLOW_TAG" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("yellow_tag.png")
		),
		"TESTO" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("text.png")
		),
		"TREE" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("tree.gif")
		),
		"TXT" => array(
			"TYPE" => "IMG",
			"URL" => "txt_th.jpg"
		),
		"UNLOCKED" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("opened.png")
		),
		"UP" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("up.gif")
		),
		"WARNING" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("yav/invalid.gif")
		),
		"XLS" => array(
			"TYPE" => "XLS",
			"URL" => "xls_th.gif"
		),
		"XML" => array(
			"TYPE" => "XML",
			"URL" => "xml_th.png"
		),
		"YES" => array(
			"TYPE" => "IMG",
			"URL" => get_image_path("yav/valid.gif")
		),
		"ZIP" => array(
			"TYPE" => "ZIP",
			"URL" => "zip_th.png"
		),
	);
	
	function dispose_decorator($type, $url, $br=true) {
		if($url!="") {
			$filesIco = new wi400Image($type);
			$filesIco->setUrl($url);
		
			return $filesIco->getHtml($br);
		}
	}
	
	function get_deco_array($deco) {
		global $decorators_array;
		
		$deco_array = array();
		if(array_key_exists($deco, $decorators_array))
			$deco_array = $decorators_array[$deco];
		
		return $deco_array;
	}
	
	function dispose_deco($deco, $type="", $url="", $br=true) {
		global $decorators_array;
		
		if($deco!="") {
			$deco_array = get_deco_array($deco);
			
			if(!empty($deco_array)) {
				$type = $deco_array['TYPE'];
				$url = $deco_array['URL'];
//				echo "DECO:".$deco."_TYPE:".$type."_URL:".$url."<br>";
			}
		}
		
		return dispose_decorator($type, $url, $br);
	}
	
	function decorator_check($value, $deco, $check="X", $type="", $url="") {
		if($value==$check) {
			return dispose_deco($deco, $type, $url);
		}
		
		return "";
	}
	
	function get_image_url($deco) {
		if($deco!="") {
			$deco_array = get_deco_array($deco);
	
			$url = "";
			if(!empty($deco_array)) {
				$type = $deco_array['TYPE'];
				$url = $deco_array['URL'];
			}
			
			if($url!="") {
				$filesIco = new wi400Image($type);
				$filesIco->setUrl($url);
				$filesIco->getHtml();
	
				return $filesIco->getUrl();
			}
		}
	}
	
	function wi400_decorator_ICONS($value, $br=true) {
		if($value!="") {
			return dispose_deco($value, "", "", $br);
		}
	}
	
	// Simbolo dell'euro €
	function wi400_decorator_CURRENCY($value){
		return "&euro; ".$value;
	}
	
	function wi400_decorator_DOC_ICO($value){
		if($value!="") {
			return dispose_deco($value);
		}
	}
	
	function wi400_decorator_EDIT_ICONS($value){
		if($value!="") {
			$deco = "";
			$type = "";
			$url = "";
			
			switch($value) {
				case "C":
					$deco = "YES";
					break;
				case "F":
					$deco = "XLS";
					break;
				case "I":
					$deco = "RELOAD";
					break;
				case "N":
					$deco = "NO";
					break;
				case "X":
					$deco = "MODIFICA";
					break;
			}
			
			return dispose_deco($deco, $type, $url);
		}
	}
	
	function wi400_decorator_FLAG_BLUE_ICO($value){
		return decorator_check($value, "FLAG_BLUE");
	}
	
	function wi400_decorator_FLAG_GREEN_ICO($value){
		return decorator_check($value, "FLAG_GREEN");
	}
	
	function wi400_decorator_FLAG_RED_ICO($value){
		return decorator_check($value, "FLAG_RED");
	}
/*	
	function wi400_decorator_FLAG_RED_ICO_NOT_NULL($value){
		if($value!="")
			$deco = wi400_decorator_FLAG_RED_ICO("X");
		else
			$deco = wi400_decorator_FLAG_RED_ICO("");
		
		return $deco;
	}
*/	
	function wi400_decorator_FLAG_YELLOW_ICO($value){
		return decorator_check($value, "FLAG_YELLOW");
	}
	
	function wi400_decorator_FOLDER($value){
		return decorator_check($value, "FOLDER");
	}
	
	function wi400_decorator_IMAGE($value){
		return decorator_check($value, "IMAGE");
	}
	
	function wi400_decorator_INVALID_ICO($value){
		return decorator_check($value, "WARNING");
	}
	
	function wi400_decorator_NOTE_ICONS($value, $parameters = array()) {
		$type = "NOTE-ICON";
		if(isset($parameters["ROW"])) {
			$type .= "-".$parameters["ROW"];
		}
		
		$url = "themes/common/images/pencil.png";
		if($value=="") {
			$url = "themes/common/images/pencil_disabled.png";
		}
		
		return dispose_deco("", $type, $url);
	}
	
	function wi400_decorator_NOTE_ICONS_EMPTY($value, $parameters = array()) {
		$type = "NOTE-ICON";
		if(isset($parameters["ROW"])) {
			$type .= "-".$parameters["ROW"];
		}
	
		$url = "themes/common/images/pencil.png";
		if($value=="") {
			$url = "";
		}
	
		return dispose_deco("", $type, $url);
	}
	
	// Barra percentuale
	function wi400_decorator_PERC_GRAPH($value){
		return wi400Graphs::perc_bar($value);
	}
	
	// Barra percentuale che però non supera la lunghezza del 100% (+ o -) in caso di valori > o < di 100% (+ o -)
	// per limitare lo spazio in caso di barre troppo lunghe 
	function wi400_decorator_PERC_GRAPH_LIMITED($value){
		return wi400Graphs::perc_bar_limited($value, 100, -100);
	}
	
	function wi400_decorator_STATO_MSG($value){
		if ($value != "") {
			$deco = "";
			
			switch($value) {
				case "0":
//					$deco = "WARNING";
					break;
				case "1":
					$deco = "RELOAD";
					break;
			}

			return dispose_deco($deco);
		}
	}
	
	function wi400_decorator_TIPO_FILE($value) {
		if($value!="") {
			switch($value) {
				case "DIR":
					$deco = "FOLDER";
					break;
				case "PDF":
					$deco = "PDF";
					break;
				case "XLS":
				case "XLSX":
					$deco = "XLS";
					break;
				case "ZIP":
					$deco = "ZIP";
					break;
				default:
					return $value;
			}
	
			return dispose_deco($deco);
		}
	}
	
	function wi400_decorator_TREE($value){
		return decorator_check($value, "TREE");
	}
	
	function wi400_decorator_UP_DOWN_IMAGE($value){
		if($value!="") {
			$deco = "";
			$type = "";
			$url = "";
			
			switch($value) {
				case "block":
					$deco = "NO";
					break;
				default:
					$type = "IMG";
					$url = "themes/common/images/".$value.".gif";
					break;
			}
			
			return dispose_deco($deco, $type, $url);
		}
	}
	
	function wi400_decorator_VALID_ICO($value){
		return decorator_check($value, "YES");
	}
	
	function wi400_decorator_YES_NO_ICO($value) {
		$deco = "";
		if($value!="") {
			switch($value) {
				case "1":
				case "S":
					$deco = "YES";
					break;
				case "0":
				case "N":
					$deco = "NO";
					break;
			}
			
			return dispose_deco($deco);
		}
	}
	
	function wi400_decorator_YES_NO_ICO_NULL($value) {
		if($value!="")
			$deco = wi400_decorator_YES_NO_ICO($value);
		else
			$deco = wi400_decorator_YES_NO_ICO("N");
		
		return $deco;
	}
	
	// DECORATORS USATI UNA SOLA VOLTA (da mettere tra le funzioni personalizzate)
	function wi400_decorator_FREE_ICONS($value) {
		if($value!="") {
			$deco = "MODIFICA";
		}
		else {
			$deco = "NO";
		}
	
		return dispose_deco($deco);
	}
	
	function wi400_decorator_STATI_PALLET_ICONS($value){
		if($value!="") {
			$deco = "";
			$type = "";
			$url = "";
			
			switch($value) {
				case "9": // cessati
					$type = "IMG";
					$url = "themes/common/images/button_cancel.png";
					break;
				case "A": // in abbasamento
//					$type = "IMG";
//					$url = "themes/common/images/down.gif";
					$deco = "DOWN";
					break;
				case "B": //bloccati
					$type = "IMG";
					$url = "themes/common/images/locked.png";
					break;
				case "N": //allocazione
//					$type = "IMG";
//					$url = "themes/common/images/up.gif";
					$deco = "UP";
					break;
				case "R": //ricevimento
					$type = "IMG";
					$url = "themes/common/images/construction.png";
					break;
				case "D": //disponibili
					$type = "IMG";
					$url = "themes/common/images/opened.png";
					break;
			}
				
			return dispose_deco($deco, $type, $url);
		}
	}
	
	function wi400_decorator_TRAINSTO_IN_OUT_ICONS($value){
		if($value!="") {
			$type = "IMG";
			
			switch($value) {
				case "O":	// cessati
					$url = "themes/common/images/transito_in.png";
					break;
				case "P":	// cessati
					$url = "themes/common/images/transito_out.png";
					break;
			}
			
			return dispose_decorator($type, $url);
		}
	}
/*	
	function wi400_decorator_IMAGE_ART($value) {
		if($value!="") {
			$parts = explode("_", $value);
//			echo "VALUE:$value<br>";
//			echo "PARTS:<pre>"; print_r($parts); echo "</pre>";
				
			$file = $value;
			if(count($parts)>1) {
				$w = $parts[0];
				$h = $parts[1];
				$file = $parts[2];
			}
				
			$filesIco = new wi400Image("ART");
				
			$filesIco->setUrl($value);
			$filesIco->setObjType("ART");
//			$filesIco->setWidth(150);
//			$filesIco->setHeight(150);
			$filesIco->setShowZoom(true);
			$filesIco->setZoomUrl($file);
				
//			echo "IMG:".$filesIco->getHtml()."<br>";
				
			return $filesIco->getHtml();
		}
	}
*/