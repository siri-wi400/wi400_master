<?php

/**
 * file:	formatting.php
 * 
 * info@siri-informatica.it
 * http:://www.siri-informatica.it
 * 
 */

function substituteFolderArray($subject, $arraySearch){
	foreach ($arraySearch as $search => $replace){
		$subject = str_replace("##".$search."##",$replace,$subject);
	}
	return $subject;
}

function html_translation_elements() {
	$trans = get_html_translation_table(HTML_ENTITIES);
	$trans[chr(130)] = '&sbquo;';    // Single Low-9 Quotation Mark
	$trans[chr(131)] = '&fnof;';    // Latin Small Letter F With Hook
	$trans[chr(132)] = '&bdquo;';    // Double Low-9 Quotation Mark
	$trans[chr(133)] = '&hellip;';    // Horizontal Ellipsis
	$trans[chr(134)] = '&dagger;';    // Dagger
	$trans[chr(135)] = '&Dagger;';    // Double Dagger
	$trans[chr(136)] = '&circ;';    // Modifier Letter Circumflex Accent
	$trans[chr(137)] = '&permil;';    // Per Mille Sign
	$trans[chr(138)] = '&Scaron;';    // Latin Capital Letter S With Caron
	$trans[chr(139)] = '&lsaquo;';    // Single Left-Pointing Angle Quotation Mark
	$trans[chr(140)] = '&OElig;';    // Latin Capital Ligature OE
	$trans[chr(145)] = '&lsquo;';    // Left Single Quotation Mark
	$trans[chr(146)] = '&rsquo;';    // Right Single Quotation Mark
	$trans[chr(147)] = '&ldquo;';    // Left Double Quotation Mark
	$trans[chr(148)] = '&rdquo;';    // Right Double Quotation Mark
	$trans[chr(149)] = '&bull;';    // Bullet
	$trans[chr(150)] = '&ndash;';    // En Dash
	$trans[chr(151)] = '&mdash;';    // Em Dash
	$trans[chr(152)] = '&tilde;';    // Small Tilde
	$trans[chr(153)] = '&trade;';    // Trade Mark Sign
	$trans[chr(154)] = '&scaron;';    // Latin Small Letter S With Caron
	$trans[chr(155)] = '&rsaquo;';    // Single Right-Pointing Angle Quotation Mark
	$trans[chr(156)] = '&oelig;';    // Latin Small Ligature OE
	$trans[chr(159)] = '&Yuml;';    // Latin Capital Letter Y With Diaeresis
	$trans['euro'] = '&euro;';    // euro currency symbol
//	$trans[chr(176)] = '&deg;';		// °	(176 o 186 ?)
	return $trans;
}

function fullUpper($string){
	return strtr(strtoupper($string), array(
		"à" => "À",
		"è" => "È",
		"ì" => "Ì",
		"ò" => "Ò",
		"ù" => "Ù",
		"á" => "Á",
		"é" => "É",
		"í" => "Í",
		"ó" => "Ó",
		"ú" => "Ú",
		"â" => "Â",
		"ê" => "Ê",
		"î" => "Î",
		"ô" => "Ô",
		"û" => "Û",
		"ç" => "Ç",
	));
}

function htmldecode($string) {
	$trans = html_translation_elements();
	$trans = array_flip($trans);
	return strtr($string, $trans);
}

function htmlencode($string) {
	$trans = html_translation_elements();
	return strtr($string, $trans);
}

// 1.000,00 -> 1000.00
function doubleViewToModel($value){
	global $settings;
	if ($value=="") $value="0";
	$value = str_replace($settings['thousand_separator'],"",$value);
	$value = str_replace($settings['decimal_separator'],".",$value);

	return trim($value);
}   

function booleanToString($value){
	if ($value) return "true";
	return "false";
}   

// 1000.00 -> 1.000,00
function doubleModelToView($value, $decimals){
	global $settings;
	
	if($value == "") return "";
	
	return number_format($value, $decimals, $settings['decimal_separator'], $settings['thousand_separator']);
}

// aaaammgg -> gg/mm/aaaa
function dateModelToView($value){
	global $settings;

	$value = str_pad($value, 8, "0", STR_PAD_LEFT); 
	$dd = substr($value, 6, 2); 
	$mm = substr($value, 4, 2);
	$yy = substr($value, 0, 4);
	
	$formattedDate = $settings['date_format'];
	$formattedDate = str_replace("dd", $dd, $formattedDate);
	$formattedDate = str_replace("mm", $mm, $formattedDate);
	$formattedDate = str_replace("YYYY", $yy, $formattedDate);
	
	return $formattedDate;
}

function dateViewToTimestamp($dateStr){
	$value = dateViewToModel($dateStr);
	
	$dd = intval(substr($value, 6, 2)); 
	$mm = intval(substr($value, 4, 2));
	$yy = intval(substr($value, 0, 4));
	
	return mktime(0,0,0,$mm,$dd,$yy);
}

// gg/mm/aaaa -> aaaammgg
function dateViewToModel($value){
	global $settings;

	if (trim($value)!='') {
		$yy = "";
		$mm = "";
		$dd = "";
		
		if($value!="") {
			$dateArray = explode("/",$value);
			$formatArray = explode("/",$settings['date_format']);
			
			$dd = $dateArray[array_search("dd",$formatArray)];
			$index = array_search("mm",$formatArray);
			if (isset($dateArray[$index])) {
				$mm = $dateArray[$index];
			}
			$index = array_search("YYYY",$formatArray);
			if (isset($dateArray[$index])) {
				$yy = $dateArray[$index];
			}
		}
		return $yy.$mm.$dd;
	} else {
		return '';
	}
}

function timeModelToView($value, $time=4){
	global $settings;
	
	$formattedTime = "";

	if (trim($value)!='') {
		$value = str_pad($value, $time, "0", STR_PAD_LEFT);
		$h = substr($value, 0, 2);
		$m = substr($value, 2, 2);
		
		if($time==4) {
			$formattedTime = $settings['short_hour_format'];
			$formattedTime = str_replace("MM", sprintf("%02s",$m), $formattedTime);
			$formattedTime = str_replace("HH", sprintf("%02s",$h), $formattedTime);
		}
		else if($time==6) {
			$s = substr($value, 4, 2);
			
			$formattedTime = $settings['hour_format'];
			$formattedTime = str_replace("MM", sprintf("%02s",$m), $formattedTime);
			$formattedTime = str_replace("HH", sprintf("%02s",$h), $formattedTime);
			$formattedTime = str_replace("ss", sprintf("%02s",$s), $formattedTime);
		}
	}

	return $formattedTime;
}

function timeViewToModel($value, $len=4){
	global $settings;
	
	$time = "";

	if (trim($value)!='') {
		$timeArray = explode(":",$value);
		
		if($len==4) {
			$formatArray = explode(":",$settings['short_hour_format']);
			
			$h = $timeArray[array_search("HH",$formatArray)];
			$m = $timeArray[array_search("MM",$formatArray)];
			
			$time = sprintf("%02s",$h).sprintf("%02s",$m);
		}
		else if($len==6) {
			$formatArray = explode(":",$settings['hour_format']);
				
			$h = $timeArray[array_search("HH",$formatArray)];
			$m = $timeArray[array_search("MM",$formatArray)];
			$s = $timeArray[array_search("ss",$formatArray)];
				
			$time = sprintf("%02s",$h).sprintf("%02s",$m).sprintf("%02s",$s);
		}
	}
	
	return $time;
}

// aaaammgg -> gg/mm/aaaa
function wi400_format_DATE($value){
	if(!empty($value))
		return dateModelToView($value);
	else
		return "";
}

// @todo ????? UGUALE A FARE wi400_format_DATE() ?????
function wi400_format_DATE_EMPTY($value){
	return wi400_format_DATE($value);
}

// aaAAmmgg (stringa da 8) -> gg/mm/AA
function wi400_format_SHORT_DATE($value){
	if(!empty($value)) {
		global $settings;
		
		$value = str_pad($value, 8, "0", STR_PAD_LEFT);
		$dd = substr($value, 6, 2);
		$mm = substr($value, 4, 2);
		$yy = substr($value, 2, 4);
		
		$formattedDate = $settings['date_format'];
		$formattedDate = str_replace("dd", $dd, $formattedDate);
		$formattedDate = str_replace("mm", $mm, $formattedDate);
		$formattedDate = str_replace("YYYY", $yy, $formattedDate);
		
		return $formattedDate;
	}
}

// ggmmaaaa -> gg/mm/aaaa
function wi400_format_DATE_CUSTOM_1($value){
	global $settings;

	if(!empty($value)) {
		if(strlen($value)<8)
			$value = sprintf("%08s", $value);
		
		$dd = substr($value, 0, 2); 
		$mm = substr($value, 2, 2);
		$yy = substr($value, 4, 4);
	
		$formattedDate = $settings['date_format'];

		$formattedDate = str_replace("dd", $dd, $formattedDate);
		$formattedDate = str_replace("mm", $mm, $formattedDate);
		$formattedDate = str_replace("YYYY", $yy, $formattedDate);
		
		return $formattedDate;
	}
}

// AAmmgg (stringa da 6) -> gg/mm/aaAA
function wi400_format_DATE_CUSTOM_2($value){
	global $settings;
	
	// trasforma la data da aammgg in ggmmAAaa (AA=19 se aa>=85 else AA=20)
	if(!empty($value)) {
		$yy = substr($value, 0, 2); 
		$mm = substr($value, 2, 2);
		$dd = substr($value, 4, 2);

		if ($yy >= 85) $yy = '19'.$yy;
		else $yy = '20'.$yy;
		
		$formattedDate = $settings['date_format'];

		$formattedDate = str_replace("dd", $dd, $formattedDate);
		$formattedDate = str_replace("mm", $mm, $formattedDate);
		$formattedDate = str_replace("YYYY", $yy, $formattedDate);
		
		return $formattedDate;
	}
}

function wi400_format_QUANTITY($value){
	if ($value == "0"){
		$value = "";
	}
	return $value;
}

// aaaa-mm-gg -> gg/mm/aaaa
function wi400_format_SHORT_TIMESTAMP($value){
	global $settings;

	if(!empty($value)) {
		$dd = substr($value, 8, 2); 
		$mm = substr($value, 5, 2);
		$yy = substr($value, 0, 4);
	
		$formattedDate = $settings['date_format'];
		$formattedDate = str_replace("dd", $dd, $formattedDate);
		$formattedDate = str_replace("mm", $mm, $formattedDate);
		$formattedDate = str_replace("YYYY", $yy, $formattedDate);
		
		return $formattedDate;
	}
}

// gg/mm/aaaa -> aaaa-mm-gg
function wi400_format_SHORT_TIMESTAMP_REVERSE($value){
	global $settings;
	$formattedDate="0001-01-01";
	if(!empty($value)) {
		$dd = substr($value, 0, 2);
		$mm = substr($value, 3, 2);
		$yy = substr($value, 6, 4);

		$formattedDate = $yy."-".$mm."-".$dd;
	}
	return $formattedDate;
}
function wi400_format_INTEGER_REVERSE($value){
	global $settings;
	if(!is_numeric($value))
		return '0';
		
		return doubleViewToModel($value);
}

// aaaa-mm-gg-HH.MM.ss.uuuuuu -> HH:MM:ss
function wi400_format_TIME_TIMESTAMP($value){
	global $settings;

	if(!empty($value)) {
		$h = substr($value, 11, 2);
		$m =  substr($value, 14, 2);
		$s = substr($value, 17, 2);
	
		$formattedDate = $settings['hour_format'];
		$formattedDate = str_replace("MM", $m, $formattedDate);
		$formattedDate = str_replace("HH", $h, $formattedDate);
		$formattedDate = str_replace("ss", $s, $formattedDate);
		
		return $formattedDate;
	}
}


// HHMMssuuu (stringa da 9) -> HH:MM
function wi400_format_TIME_HHMM($value) {
	global $settings;
	
	$value = str_pad($value, 4, "0", STR_PAD_LEFT);
	
	if(!empty($value)) {
		$h = substr($value, 0, 2);
		$m = substr($value, 2, 2);
	
		$formattedDate = $settings['hour_format'];
		$formattedDate = str_replace("MM", $m, $formattedDate);
		$formattedDate = str_replace("HH", $h, $formattedDate);
		$formattedDate = str_replace(":ss", "", $formattedDate);
	
		return $formattedDate;
	}
}

// HH:MM:ss.0000000 -> HH:MM
function wi400_format_TIME_HHMM_2($value) {
	if($value) {
		$hour = explode(":", $value);
		
		return $hour[0].":".$hour[1];
	}
	
	return "";
}

// HHMMssuuu (stringa da 9) -> HH:MM:ss
function wi400_format_TIME_INTEGER($value) {
	global $settings;
	
	$value = str_pad($value, 9, "0", STR_PAD_LEFT);
	
	if(!empty($value)) {
		$h = substr($value, 0, 2);
		$m = substr($value, 2, 2);
		$s = substr($value, 4, 2);
	
		$formattedDate = $settings['hour_format'];
		$formattedDate = str_replace("MM", $m, $formattedDate);
		$formattedDate = str_replace("HH", $h, $formattedDate);
		$formattedDate = str_replace("ss", $s, $formattedDate);
	
		return $formattedDate;
	}
}

// HHMMssuuu (stringa da 9) valorizzato a 000000000 in caso che il valore passato sia vuoto -> HH:MM:ss
function wi400_format_TIME_INTEGER_RIGHT($value) {
	global $settings;

	$value = str_pad($value, 6, "0", STR_PAD_LEFT);
	$value = str_pad($value, 9, "0", STR_PAD_RIGHT);
	
	if(!empty($value)) {
		$h = substr($value, 0, 2);
		$m = substr($value, 2, 2);
		$s = substr($value, 4, 2);
	
		$formattedDate = $settings['hour_format'];
		$formattedDate = str_replace("MM", $m, $formattedDate);
		$formattedDate = str_replace("HH", $h, $formattedDate);
		$formattedDate = str_replace("ss", $s, $formattedDate);
	
		return $formattedDate;
	}
}

// aaaa-mm-gg-HH.MM.ss.uuuuuu -> gg/mm/aaaa HH:MM
function wi400_format_TIMESTAMP($value){
	global $settings;

	if(!empty($value)) {
		$dd = substr($value, 8, 2); 
		$mm = substr($value, 5, 2);
		$yy = substr($value, 0, 4);
		$h = substr($value, 11, 2);
		$m =  substr($value, 14, 2);
		
		$formattedDate = $settings['time_stamp_format'];
		$formattedDate = str_replace("dd", $dd, $formattedDate);
		$formattedDate = str_replace("mm", $mm, $formattedDate);
		$formattedDate = str_replace("YYYY", $yy, $formattedDate);
		
		$formattedDate = str_replace("MM", $m, $formattedDate);
		$formattedDate = str_replace("HH", $h, $formattedDate);
		
		return $formattedDate;
	}
}

function wi400_format_TIMESTAMP_INZ_BLANK($value){
	if(!empty($value)) {
		if(substr($value, 0, 4)=="0001")
			return "";
		else
			return wi400_format_TIMESTAMP($value);
	}
}

// aaaa-mm-gg-HH.MM.ss.uuuuuu -> gg/mm/aaaa HH:MM:ss
function wi400_format_COMPLETE_TIMESTAMP($value){
	global $settings;

//	echo $value."<br>";
	
	if(!empty($value) && ( 
		preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})-([0-9]{2}).([0-9]{2}).([0-9]{2}).([0-9]{6})$/", $value)
		|| preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2}).([0-9]{6})$/", $value)
	)) {	
		$dd = substr($value, 8, 2); 
		$mm = substr($value, 5, 2);
		$yy = substr($value, 0, 4);
		$h = substr($value, 11, 2);
		$m = substr($value, 14, 2);
		$s = substr($value, 17, 2);
		
		$formattedDate = $settings['time_stamp_complete_format'];
		$formattedDate = str_replace("dd", $dd, $formattedDate);
		$formattedDate = str_replace("mm", $mm, $formattedDate);
		$formattedDate = str_replace("YYYY", $yy, $formattedDate);
		
		$formattedDate = str_replace("MM", $m, $formattedDate);
		$formattedDate = str_replace("HH", $h, $formattedDate);
		$formattedDate = str_replace("ss", $s, $formattedDate);
		
		return $formattedDate; 
	}
	else return $value;
}

// aaaammggHHMM -> gg/mm/aaaa HH:MM
function wi400_format_STRING_TIMESTAMP($value){
	global $settings;

	if(!empty($value)) {
		$yy = substr($value, 0, 4);
		$mm = substr($value, 4, 2);
		$dd = substr($value, 6, 2); 
		$h = substr($value, 8, 2);
		$m =  substr($value, 10, 2);
		
		$formattedDate = $settings['time_stamp_format'];
		$formattedDate = str_replace("dd", $dd, $formattedDate);
		$formattedDate = str_replace("mm", $mm, $formattedDate);
		$formattedDate = str_replace("YYYY", $yy, $formattedDate);
		
		$formattedDate = str_replace("MM", $m, $formattedDate);
		$formattedDate = str_replace("HH", $h, $formattedDate);
		
		return $formattedDate;
	}
}

// ggmmaaaaHHMM -> gg/mm/aaaa HH:MM
function wi400_format_STRING_TIMESTAMP_DATA_REV($value){
	global $settings;

	if(!empty($value)) {
		$value = str_pad($value, 14, "0", STR_PAD_LEFT);
		
		$yy = substr($value, 4, 4);
		$mm = substr($value, 2, 2);
		$dd = substr($value, 0, 2);
		$h = substr($value, 8, 2);
		$m =  substr($value, 10, 2);

		$formattedDate = $settings['time_stamp_format'];
		$formattedDate = str_replace("dd", $dd, $formattedDate);
		$formattedDate = str_replace("mm", $mm, $formattedDate);
		$formattedDate = str_replace("YYYY", $yy, $formattedDate);

		$formattedDate = str_replace("MM", $m, $formattedDate);
		$formattedDate = str_replace("HH", $h, $formattedDate);

		return $formattedDate;
	}
}

// aaaammggHHMMss -> gg/mm/aaaa HH:MM:ss
function wi400_format_STRING_COMPLETE_TIMESTAMP($value){
	global $settings;

	if(!empty($value)) {
		$yy = substr($value, 0, 4);
		$mm = substr($value, 4, 2);
		$dd = substr($value, 6, 2); 
		$h = substr($value, 8, 2);
		$m =  substr($value, 10, 2);
		$s = substr($value, 12, 2);
		
		$formattedDate = $settings['time_stamp_complete_format'];
		$formattedDate = str_replace("dd", $dd, $formattedDate);
		$formattedDate = str_replace("mm", $mm, $formattedDate);
		$formattedDate = str_replace("YYYY", $yy, $formattedDate);
		
		$formattedDate = str_replace("ss", $s, $formattedDate);
		$formattedDate = str_replace("MM", $m, $formattedDate);
		$formattedDate = str_replace("HH", $h, $formattedDate);
		
		return $formattedDate;
	}
}

// ggmmAA (stringa da 6) -> aaAAmmgg -> gg/mm/aaaa
function wi400_format_STRING_6_DATE($value){
	global $settings;

	if(!empty($value)) {
		$data = date6to8($value);
		
		$formattedDate = wi400_format_DATE($data);

		return $formattedDate;
	}
}

// ggmmAAHHMMss (stringa da 12) -> aaAAmmggHHMMss -> gg/mm/aaaa HH:MM:ss
function wi400_format_STRING_6_COMPLETE_TIMESTAMP($value){
	global $settings;

	if(!empty($value)) {
		$data = substr($value, 0, 6);
		$time = substr($value,6);

		$data = date6to8($value);

		$formattedDate = wi400_format_STRING_COMPLETE_TIMESTAMP($data.$time);

		return $formattedDate;
	}
}

// Data in formato DATE/DATEDB (AAAA-MM-GG)/TIMESTAMP -> aaaammgg -> gg/mm/aaaa 
function wi400_format_DBDATE_DATE($value) {
	if(!empty($value)) {
		$data = DBDate_to_date($value, "DATEDB");		
		return wi400_format_DATE($data);
	}
}

// @todo ????? UGUALE A wi400_format_STRING_COMPLETE_TIMESTAMP() ?????
// aaaammggHHMMss -> gg/mm/aaaa HH:MM:ss
function wi400_format_STRING_TIMESTAMP_SEPARATOR($value){
	global $settings;

	if(!empty($value)) {
		$yy = substr($value, 0, 4);
		$mm = substr($value, 4, 2);
		$dd = substr($value, 6, 2); 
		$h = substr($value, 8, 2);
		$m =  substr($value, 10, 2);
		$s = substr($value, 12, 2);
		
		$formattedDate = $settings['time_stamp_complete_format'];
		$formattedDate = str_replace("dd", $dd, $formattedDate);
		$formattedDate = str_replace("mm", $mm, $formattedDate);
		$formattedDate = str_replace("YYYY", $yy, $formattedDate);
		
		$formattedDate = str_replace("ss", $s, $formattedDate);
		$formattedDate = str_replace("MM", $m, $formattedDate);
		$formattedDate = str_replace("HH", $h, $formattedDate);
		
		return $formattedDate;
	}
}

function wi400_format_T_SEP_TIMESTAMP($value){
	global $settings;

	if(!empty($value) && preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $value)) {
		$dd = substr($value, 8, 2);
		$mm = substr($value, 5, 2);
		$yy = substr($value, 0, 4);
		$h = substr($value, 11, 2);
		$m = substr($value, 14, 2);
		$s = substr($value, 17, 2);

		$formattedDate = $settings['time_stamp_complete_format'];
		$formattedDate = str_replace("dd", $dd, $formattedDate);
		$formattedDate = str_replace("mm", $mm, $formattedDate);
		$formattedDate = str_replace("YYYY", $yy, $formattedDate);

		$formattedDate = str_replace("MM", $m, $formattedDate);
		$formattedDate = str_replace("HH", $h, $formattedDate);
		$formattedDate = str_replace("ss", $s, $formattedDate);

		return $formattedDate;
	}
	else return $value;
}

// timestamp unix -> aaaammggHHMM -> gg/mm/aaaa HH:MM
function wi400_format_UNIX_TIMESTAMP($value){
	global $settings;

	if(!empty($value)) {
		$time_string = date("YmdHi", $value);
		
		$time = wi400_format_STRING_TIMESTAMP($time_string);
		
		return $time;
	}
}

// timestamp unix -> aaaammggHHMMss -> gg/mm/aaaa HH:MM:ss
function wi400_format_UNIX_COMPLETE_TIMESTAMP($value){
	global $settings;

	if(!empty($value)) {
		$time_string = date("YmdHiu", $value);
		
		$time = wi400_format_STRING_COMPLETE_TIMESTAMP($time_string);
		
		return $time;
	}
}

// timestamp unix -> aaaammgg -> gg/mm/aaaa
function wi400_format_UNIX_DATE($value) {
	if(!empty($value)) {
		$data = date("Ymd",$value);
		return dateModelToView($data);
	}
}

function wi400_format_MILLS_TIME($mills) {
	global $settings;
	
	$seconds = 0;
	$minutes = 0;
	$hours = 0;
	$ht = 0;
	
	if (!empty($mills)) {
		$st = floor ( $mills / 1000000 );
		$seconds = floor ( $st % 60 );
		$mt = floor ( $st / 60 );
		$minutes = floor ( $mt % 60 );
		$ht = floor ( $mt / 60 );
		$hours = floor ( $ht % 24 );
		$days = floor ( $ht / 24 );
	
		$formattedDate = $settings['hour_format'];
		$formattedDate = str_replace("MM", sprintf("%02s",$minutes), $formattedDate);
		$formattedDate = str_replace("HH", $ht, $formattedDate);
		$formattedDate = str_replace("ss", sprintf("%02s",$seconds), $formattedDate);
		
		return $formattedDate;
	}
}

function wi400_format_SECONDS_SHORT_TIME($seconds) {
	global $settings;
	
	$minutes = 0;
	$mt = 0;
	$hours = 0;
	$ht = 0;
	
	if (!empty($seconds)) {
		$sec = floor ( $seconds % 60 );
		$mt = floor ( $seconds / 60 );
		$minutes = floor ( $mt % 60 );
		$ht = floor ( $mt / 60 );
	
		$formattedTime = $settings['short_hour_format'];
		$formattedTime = str_replace("MM", sprintf("%02s",$minutes), $formattedTime);
		$formattedTime = str_replace("HH", $ht, $formattedTime);
		
		return $formattedTime;
	}
}

function wi400_format_INTEGER_DATETIME($value) {
	if($value!="") {
		$date = substr($value,0,8);
		$time = substr($value,8);
		
		$date =  wi400_format_DATE($date);
		
		if($time!="")
			$date .= " ".wi400_format_TIME_INTEGER_RIGHT($time);
		
		return $date;
	}
}

function wi400_format_DATE_CUSTOM_1_TIME_INTEGER_RIGHT($value) {
	if($value!="") {
		$date = substr($value, 0, -6);
		$time = substr($value,-6);
	
		$date =  wi400_format_DATE_CUSTOM_1($date);
	
		if($time!="")
			$date .= " ".wi400_format_TIME_INTEGER_RIGHT($time);
	
		return $date;
	}
}

// aaaamm... -> <Nome del mese> aaaa
function wi400_format_MONTH($value, $prepare=true, $decode=true) {
	global $settings;
	
//	if($value!="") {
	if(!empty($value)) {
		// utilizzare setlocale() e strftime() per date in lingua locale
//		setlocale(LC_TIME, $settings['default_locale']);
		
		$anno = substr($value,0,4);
		$mese = substr($value,4,2);

//		$date = ucfirst(strftime("%B %Y", mktime(0, 0, 0, $mese, 1, $anno)));
		$date = wi400_format_DES_MONTH($mese, $prepare, $decode)." ".$anno;
		
		return $date;
	}
}

// aaaa-mm... -> <Nome del mese> aaaa
function wi400_format_TIMESTAMP_MONTH($value, $prepare=true, $decode=true) {
	global $settings;
	
	if($value!="") {
		// utilizzare setlocale() e strftime() per date in lingua locale
//		setlocale(LC_TIME, $settings['default_locale']);
		
		$anno = substr($value,0,4);
		$mese = substr($value,5,2);

//		$date = ucfirst(strftime("%B %Y", mktime(0, 0, 0, $mese, 1, $anno)));
		$date = wi400_format_DES_MONTH($mese, $prepare, $decode)." ".$anno;
		
		return $date;
	}
}

function wi400_format_TIMESTAMP_YEAR($value) {
	global $settings;
	
	if($value!="") {
		
		$anno = substr($value,0,4);

		$date = ucfirst(strftime("%Y", mktime(0, 0, 0, 1, 1, $anno)));
		
		return $date;
	}
}

// numero mese -> Nome del mese
function wi400_format_DES_MONTH($value, $prepare=true, $decode=true) {
	global $settings;
	
	if($value!="") {
		// utilizzare setlocale() e strftime() per date in lingua locale
//		setlocale(LC_TIME, $settings['default_locale']);
		
//		$date = ucfirst(strftime("%B", mktime(0, 0, 0, $value, 1, date("Y"))));

//		$date = ucfirst(prepare_string(nome_mese($value)));

//		echo "PREPARE:".$prepare."_DECODE:$decode<br>";
		$date = nome_mese($value);	
		if($prepare==true) {
//			echo "HERE<br>";
			$date = prepare_string($date, false, $decode);
		}
		$date = ucfirst($date);
		
		return $date;
	}
}

function wi400_format_WEEK_YEAR($value) {
	global $settings;
	
	if($value!="") {
		// utilizzare setlocae e strftime() per date in lingua locale
		setlocale(LC_TIME, $settings['default_locale']);
		
		$anno = substr($value,0,4);
		$week = substr($value,4);

		$date = sprintf("%02s", trim($week))." ".sprintf("%04s", $anno);
		
		return $date;
	}
}

function wi400_format_INTEGER($value){
	global $settings;
	if(!is_numeric($value)) 
		return '';
	return number_format($value, 0, $settings['decimal_separator'], $settings['thousand_separator']);
}

function wi400_format_MIXED_INTEGER($value){
	$res = wi400_format_INTEGER($value);
	
	if($res=="")
		return $value;
	
	return $res;
}

function wi400_format_DOUBLE_1($value){
	global $settings;
	if(!is_numeric($value)) 
		return '';
	if(!is_float($value))
		settype($value, "float");
	return number_format($value, 1, $settings['decimal_separator'], $settings['thousand_separator']);
}

function wi400_format_DOUBLE_2($value){
	global $settings;
	if(!is_numeric($value)) 
		return '';
	if(!is_float($value))
		settype($value, "float");
	return number_format($value, 2, $settings['decimal_separator'], $settings['thousand_separator']);
}

function wi400_format_DOUBLE_3($value){
	global $settings;
	if(!is_numeric($value)) 
		return '';
	if(!is_float($value))
		settype($value, "float");
	return number_format($value, 3, $settings['decimal_separator'], $settings['thousand_separator']);
}

function wi400_format_DOUBLE_4($value){
	global $settings;
	if(!is_numeric($value)) 
		return '';
	if(!is_float($value))
		settype($value, "float");
	return number_format($value, 4, $settings['decimal_separator'], $settings['thousand_separator']);
}

function wi400_format_DOUBLE_5($value){
	global $settings;
	if(!is_numeric($value)) 
		return '';
	if(!is_float($value))
		settype($value, "float");
	return number_format($value, 5, $settings['decimal_separator'], $settings['thousand_separator']);
}

function wi400_format_DOUBLE_6($value){
	global $settings;
	if(!is_numeric($value)) 
		return '';
	if(!is_float($value))
		settype($value, "float");
	return number_format($value, 6, $settings['decimal_separator'], $settings['thousand_separator']);
}

function wi400_format_DOUBLE_7($value){
	global $settings;
	if(!is_numeric($value))
		return '';
	if(!is_float($value))
		settype($value, "float");
	return number_format($value, 7, $settings['decimal_separator'], $settings['thousand_separator']);
}

function wi400_format_DOUBLE_8($value){
	global $settings;
	if(!is_numeric($value))
		return '';
	if(!is_float($value))
		settype($value, "float");
	return number_format($value, 8, $settings['decimal_separator'], $settings['thousand_separator']);
}

function wi400_format_DOUBLE_9($value){
	global $settings;
	if(!is_numeric($value))
		return '';
	if(!is_float($value))
		settype($value, "float");
	return number_format($value, 9, $settings['decimal_separator'], $settings['thousand_separator']);
}

function wi400_format_STRING($value, $length=0, $pad="", $pad_type=STR_PAD_LEFT){
	global $settings;
	
	return str_pad($value, $length, $pad, $pad_type);
}
function wi400_format_DOUBLE($value, $decimal){
	global $settings;
			if(!is_float($value))
		settype($value, "float");
	return number_format($value, $decimal, $settings['decimal_separator'], $settings['thousand_separator']);
}
/*
function wi400_format_FOLDER_HTML($folderType, $folderDesc, $folderContent){
	
	if ($folderType == 1){
		return substr(strip_tags($folderContent),0,20)." [...]";
	}else{
		return $folderDesc;
	}
	
}
*/
function wi400_format_FOLDER_HTML($folderType, $folderDesc, $folderContent, $len=20, $cont=true){

	if ($folderType == 1){
		$des = substr(strip_tags($folderContent),0,$len);
		if($cont===true)
			$des .= " [...]";

		return $des;
	}else{
		return $folderDesc;
	}

}
function wi400_format_POSTO($value,$glue=' - ',$delimiter='-') {
	if($glue==$delimiter)
		return $value;
		
	$parts = explode($delimiter,$value);
	
	$posto = implode($glue,$parts);
	
	return $posto;
}

function wi400_format_FILE_PATH_TO_FILE_NAME($value) {
	return basename($value);
}

function wi400_format_FILE_PATH_TO_NAME($value) {
	$ext = pathinfo($value, PATHINFO_EXTENSION);	
	$name = basename($value, ".".$ext);
	return $name;
}

function wi400_format_STRTOUPPER($value) {
	return strtoupper($value);
}

function wi400_format_STRTOLOWER($value) {
	return strtolower($value);
}

function wi400_format_CONCEAL($value) {
	return "";
}
// Barra percentuale
function wi400_format_PERC_GRAPH($value){
	return wi400Graphs::perc_bar($value);
}