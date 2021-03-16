<?php
/**
 * Conversione della data da formato GG/MM/AAAA hh:mm:ss (parte orario non necessaria) a formato TIMESTAMP
 * Se $data='' o null => data e ora attuali
 * Se $data="*INZ" => data è 01/01/0001 00:00:00
 *
 * @param string $data
 * @return string
 */
function getDb2Timestamp($data=Null, $short=null) {
	global $db, $settings;
	return $db->getTimestamp($data, $short);
}
function getDb2Timestamp_AS400($data=null, $short=null) {
	global $settings;
	if ($short!==True) {
		if(isset($data) && !empty($data)) {
			if($data=="*INZ") {
				if($settings['platform']=="AS400")
					return "0001-01-01-00.00.00.000000";
				elseif ($settings['platform']=="WINDOWS")
					return "0001-01-01 00:00:00.0000000";
				else
					//			return "TIMESTAMP(CAST('".date("d.m.Y")."' AS VARCHAR(10)), '".date("H:i:s")."')";
					return "0001-01-01-00.00.00.00000";
			}
			else {
				$data_unix = time_to_unix_timestamp($data);
	
				if($settings['platform']=="AS400")
					return date("Y-m-d-H.i.s.000000", $data_unix);
				elseif ($settings['platform']=="WINDOWS")
					return date("Y-m-d H:i:s.0000000", $data_unix);
				else
					//				return "TIMESTAMP(CAST('".date("d.m.Y", $data_unix)."' AS VARCHAR(10)), '".date("H:i:s", $data_unix)."')";
					return date("Y-m-d-H.i.s.00000", $data_unix);
			}
		}
		else {
			if($settings['platform']=="AS400")
				return date("Y-m-d-H.i.s.000000");
			elseif ($settings['platform']=="WINDOWS")
				return date("Y-m-d H:i:s.0000000");
			else
				//			return "TIMESTAMP(CAST('".date("d.m.Y")."' AS VARCHAR(10)), '".date("H:i:s")."')";
				return date("Y-m-d-H.i.s.00000");
		}
	} else {
		if(isset($data) && !empty($data)) {
			if($data=="*INZ") {
				if($settings['platform']=="AS400")
					return "0001-01-01";
				elseif ($settings['platform']=="WINDOWS")
				return "0001-01-01";
				else
					//			return "TIMESTAMP(CAST('".date("d.m.Y")."' AS VARCHAR(10)), '".date("H:i:s")."')";
					return "0001-01-01";
			}
			else {
				$data_unix = time_to_unix_timestamp($data);
				if($settings['platform']=="AS400")
					return date("Y-m-d", $data_unix);
				elseif ($settings['platform']=="WINDOWS")
				return date("Y-m-d", $data_unix);
				else
					//				return "TIMESTAMP(CAST('".date("d.m.Y", $data_unix)."' AS VARCHAR(10)), '".date("H:i:s", $data_unix)."')";
					return date("Y-m-d", $data_unix);
			}
		}
		else {
			if($settings['platform']=="AS400")
				return date("Y-m-d");
			elseif ($settings['platform']=="WINDOWS")
			return date("Y-m-d");
			else
				//			return "TIMESTAMP(CAST('".date("d.m.Y")."' AS VARCHAR(10)), '".date("H:i:s")."')";
				return date("Y-m-d");
		}		
	}
}
// this function takes an integer value (the number of seconds) and prints out the days, hours, minutes, and seconds.
function showFormattedTime($seconds, $flag = 0) {
	global $lang_na, $lang_day, $lang_days, $lang_hour, $lang_hours, $lang_minute, $lang_minutes, $lang_second, $lang_seconds;

	if ($seconds <= 0) {
		echo "<b>$lang_na</b>";
	} else {
		$days = ( int ) ($seconds / (24 * 60 * 60));
		$remainder = $seconds % (24 * 60 * 60);

		$hours = ( int ) ($remainder / (60 * 60));
		$remainder = $remainder % (60 * 60);

		$minutes = ( int ) ($remainder / 60);
		$seconds = $remainder % 60;

		if ($days != 0) {
			echo "$days";
			if ($days != 1)
				echo " $lang_days";
			else
				echo " $lang_day";
			echo ", ";
		}

		if ($hours != 0) {
			echo "$hours";
			if ($hours != 1)
				echo " $lang_hours";
			else
				echo " $lang_hour";
			echo ", ";
		}

		if ($minutes != 0) {
			echo "$minutes";
			if ($minutes != 1)
				echo " $lang_minutes";
			else
				echo " $lang_minute";
			// comment all of these lines if you don't want to keep track of seconds as well.
			if ($flag == 0)
				echo ", ";
		}

		if ($flag == 0 && $minutes != 0) {
			echo " and $seconds";
			if ($seconds != 1)
				echo " $lang_seconds";
			else
				echo " $lang_second";
		} elseif ($flag == 0) {
			echo "$seconds";
			if ($seconds != 1)
				echo " $lang_seconds";
			else
				echo " $lang_second";
		}
	}
}
function get_week_first_day($year, $week) {
	//	$data = date("Ymd", strtotime($year."W".$week));
	$data = date("Ymd", strtotime($year."W".sprintf("%02s", $week)."1"));

	return $data;
}

function get_week_last_day($year, $week) {

	$data = date("Ymd", strtotime($year."W".sprintf("%02s", $week)."7"));
	return $data;
}

function get_week_from_date($date) {
	$anno = substr($date, 0, 4);
	$mese = substr($date, 4, 2);
	$giorno = substr($date, 6, 2);

	$week = (int)date("W", mktime(0, 0, 0, $mese, $giorno, $anno));

	return $week;
}
function giraData($value,$sep = '/', $sepa=False) {
	if ($sepa==False) {
		$dd = substr($value, 6, 2);
		$mm = substr($value, 4, 2);
		$yy = substr($value, 0, 4);
	} else {
		$dd = substr($value, 8, 2);
		$mm = substr($value, 5, 2);
		$yy = substr($value, 0, 4);		
	}

	$newdata = dateFormat($dd, $mm, $yy, $sep, 'N');

	return $newdata;
}
function formattaData($testo) {
	$newdata = substr ( $testo, 6, 4 ) . substr ( $testo, 3, 2 ) . substr ( $testo, 0, 2 );
	return $newdata;
}
function explodeData($testo, $ord = "A") {
	$data = array ();
	if ($ord == "A") {
		$data [0] = substr ( $testo, 0, 4 );
		$data [1] = substr ( $testo, 4, 2 );
		$data [2] = substr ( $testo, 6, 2 );
	} else {
		$data [2] = substr ( $testo, 0, 4 );
		$data [1] = substr ( $testo, 4, 2 );
		$data [0] = substr ( $testo, 6, 2 );
	}
	return $data;
}
/** Gira una data in formato AAAAMMGG
 * 
 * @param unknown $data
 * @param string $output
 */
function giraDataMulti($data, $input = "AAAAMMGG", $output="GGMMAAAA") {
	$dataout="";
	if ($input=="AAAAMMGG") {
		$anno = substr($data, 0, 4);
		$mese = substr($data, 4, 2);
		$giorno = substr($data,6,2);
	} elseif ($input=="GGMMAAAA") {
		$anno = substr($data, 4, 4);
		$mese = substr($data, 2, 2);
		$giorno = substr($data,0,2);		
	}
	if ($output=="AAAAMMGG") {
		$dataout = sprintf ( "%04s", $anno ) . sprintf ( "%02s", $mese ) .sprintf ( "%02s", $giorno );
	} elseif ($output=="GGMMAAAA") {
		$dataout = sprintf ( "%02s", $giorno ) . sprintf ( "%02s", $mese ) .sprintf ( "%04s", $anno );
	}
	return $dataout;	
}
// Funzione per il recupero del numero di giorni che costituiscono un mese di un certo anno
// (per considerare gli anni bisestili)
function giorni_in_mese($m, $y) {
	$mese = mktime( 0, 0, 0, $m, 1, $y );
	$num_giorni = intval(date("t",$mese));

	return $num_giorni;
}

function nome_mese($m, $locale="", $type='%B') {
	global $settings;
	if($locale=="")
		$locale = $settings['default_locale'];

	if (isset($_SESSION['CUSTOM_LANGUAGE'])) {
		$locale = getLocaleFromLanguage($_SESSION['CUSTOM_LANGUAGE']);
	}
	else if(isset($_SESSION["USER_LANGUAGE"])) {
		$locale = getLocaleFromLanguage($_SESSION['USER_LANGUAGE']);
	}

	// utilizzare setlocae e strftime() per date in lingua locale
	setlocale(LC_TIME, $locale);

	$mese = mktime( 0, 0, 0, $m, 1, date("Y"));

	$nome_mese = strftime($type, $mese);
	
	if($settings['utf8_encode']===true) {
		$nome_mese = utf8_encode($nome_mese);
	}

	return $nome_mese;
}

function nome_giorno($d, $m, $y, $locale="") {
	global $settings;

	if($locale=="")
		$locale = $settings['default_locale'];

	// utilizzare setlocae e strftime() per date in lingua locale
	setlocale(LC_TIME, $locale);

	$data = mktime( 0, 0, 0, $m, $d, $y);

	$nome_giorno = strftime("%A", $data);

	return $nome_giorno;
}

function short_nome_giorno($d, $m, $y, $locale="") {
	global $settings;

	if($locale=="")
		$locale = $settings['default_locale'];

	// utilizzare setlocae e strftime() per date in lingua locale
	setlocale(LC_TIME, $locale);

	$data = mktime( 0, 0, 0, $m, $d, $y);

	$nome_giorno = strftime("%a", $data);

	return $nome_giorno;
}

function get_giorni_in_periodo($data_ini, $data_fin) {
	//	echo "DATA INI: $data_ini - DATA_FIN: $data_fin<br>";

	$date = array();

	for($data=$data_ini; $data<=$data_fin; ) {
		$anno = substr($data,0,4);
		$mese = substr($data,4,2);
		$giorno = substr($data,6,2);

		$date[] = $data;

		$data = date("Ymd",mktime(0,0,0,$mese,$giorno+1,$anno));
	}

	//	echo "DATE:<pre>"; print_r($date); echo "</pre>";

	return $date;
}
/*
 * Calcolo delle differenza tra due date secondo l'intervallo temporale preferito
* (A=Anni, M=Mesi, S=Settimane, G=Giorni)
*/
function datediff($data_ini, $data_fin, $tipo="G") {
	switch ($tipo) {
		case "A" : $tipo = 365;
		break;
		case "M" : $tipo = (365 / 12);
		break;
		case "S" : $tipo = (365 / 52);
		break;
		case "G" : $tipo = 1;
		break;
	}

	$giorno_ini = getGiorno($data_ini);
	$mese_ini = getMese($data_ini);
	$anno_ini = getAnno($data_ini);

	$giorno_fin = getGiorno($data_fin);
	$mese_fin = getMese($data_fin);
	$anno_fin = getAnno($data_fin);

	//	$date_diff = mktime(12, 0, 0, $mese_fin, $giorno_fin, $anno_fin) - mktime(12, 0, 0, $mese_ini, $giorno_ini, $anno_ini);
	$date_diff = mktime(0, 0, 0, $mese_fin, $giorno_fin, $anno_fin) - mktime(0, 0, 0, $mese_ini, $giorno_ini, $anno_ini);
	$date_diff  = floor(($date_diff / 60 / 60 / 24) / $tipo);
	$date_diff += 1;

	return $date_diff;
}
// Compongo la data da AAAAMMGG
function dateString($giorno, $mese, $anno) {
	return sprintf ( "%04s", $anno ) . sprintf ( "%02s", $mese ) . sprintf ( "%02s", $giorno );
}
// Compongo la data da mettere a video
function dateFormat($giorno, $mese, $anno, $sep = '/', $reverse='N') {
	if ($reverse == 'N') {
		return sprintf ( "%02s", $giorno ) . $sep . sprintf ( "%02s", $mese ) . $sep . sprintf ( "%04s", $anno );
	} else {
		return sprintf ( "%04s", $anno ) . $sep . sprintf ( "%02s", $mese ) . $sep . sprintf ( "%02s", $giorno );

	}
}

// Cambio la data da formato GG/MM/AAAA a formato TIMESTAMP
function dateToTimestamp($data, $date=False) {

	$formato = '';
	$anno = getAnno ( $data );
	$mese = getMese ( $data );
	$giorno = getGiorno ( $data );
	$formato = sprintf ( "%04s", $anno ) . "-" . sprintf ( "%02s", $mese ) . "-" . sprintf ( "%02s", $giorno );
	if (!$date) {
		$formato .= "-00.00.00.000000";
	}
	return $formato;
}

// Cambio la data da formato AAAAMMGG a formato DATE
function dateToDBdate($data) {
	$formato = '';

	if($data=="*INZ") {
		$anno = "0001";
		$mese = "01";
		$giorno = "01";
	}
	else {
		$anno = substr($data, 0, 4);
		$mese = substr($data, 4, 2);
		$giorno = substr($data, 6, 2);
	}

	$formato = sprintf ( "%04s", $anno ) . "-" . sprintf ( "%02s", $mese ) . "-" . sprintf ( "%02s", $giorno );

	return $formato;
}
function timeToDBTime($time) {
	// NOT IMPLEMENTED
}
function get_date_previous_day($data, $type="") {
	$data = DBDate_to_date($data, $type);

	$anno = substr($data, 0, 4);
	$mese = substr($data, 4, 2);
	$giorno = substr($data, 6);

	$prev_day = date("Ymd", mktime(0, 0, 0, $mese, $giorno-1, $anno));

	return $prev_day;
}

// Data da formato DATE/DATEDB (AAAA-MM-GG)/TIMESTAMP a formato AAAAMMGG
function DBDate_to_date($data, $type="") {
	if(in_array($type, array("DATEDB", "TIMESTAMP"))) {
		if($type=="TIMESTAMP")
			$data = substr($data, 0, 10);
			
		$data = dateViewToModel(wi400_format_SHORT_TIMESTAMP($data));
	}
	else if($type=="DATE") {
		$data = dateViewToModel($data);
	}

	return trim($data);
}

// Ritorno l'anno da una data GG/MM/AAAA
function getAnno($data) {
	return substr ( $data, 6, 4 );
}
// Ritorno l'anno da una data GG/MM/AAAA
function getMese($data) {
	return substr ( $data, 3, 2 );
}
// Ritorno l'anno da una data GG/MM/AAAA
function getGiorno($data) {
	return substr ( $data, 0, 2 );
}

// Converto una data di lunghezza 6 GGMMYY in una di lunghezza 8 YYYYMMGG
function date6to8($data) {
	$data = sprintf("%06s", $data);
	return date("Ymd", mktime(0, 0, 0, substr($data,2,2), substr($data,0,2), substr($data,4,2)));
}

function date6to8_rev($data) {
	$data = sprintf("%06s", $data);
	return date("Ymd", mktime(0, 0, 0, substr($data,2,2), substr($data,4,2), substr($data,0,2)));
}
// Converto una data di lunghezza 8 YYYYMMGG in una di lunghezza 6 GGMMYY
function date8to6($data) {
	$data = sprintf("%08s", $data);
	return date("dmy", mktime(0, 0, 0, substr($data,4,2), substr($data,6,2), substr($data,0,4)));
}
function date_6to8_time($date,$time,$rev=true) {
	//	echo "DATA:".$date."_ORA:".$time."<br>";
	if($rev===true)
		$date = date6to8_rev($date);
	else
		$date = date6to8($date);
	$date = dateModelToView($date);
	$time = wi400_format_TIME_INTEGER($time);
	//	echo "DATA:".$date."_ORA:".$time."<br>";

	$unix_time = time_to_unix_timestamp($date, $time);

	$data_reg = "";
	if($unix_time!=0)
		$data_reg = date("YmdHis", $unix_time);
	//	echo "DATA:".$date."_ORA:".$time."_DATA:".$data_reg."<br>";

	return $data_reg;
}

// Conversione da formato AAJJJ (ultimi 2 caratteri dell'anno+giorno giuliano) a AAAAMMGG
function yz_to_date($data) {
	//	$data = date("yz");
	$date = date("Ymd", mktime(0, 0, 0, 1, substr($data,2), substr($data,0,2)));
	//	echo "DATA: $data - NEW DATA: $date<br>";
	return $date;
}

// Converte il tempo passato in millesimi di secondo in formato INTEGER (ddHHMMss...)
function mills_to_time($mills) {
	$seconds = 0;
	$minutes = 0;
	$hours = 0;

	if (! empty ( $mills )) {
		$st = floor ( $mills / 1000000 );
		$seconds = floor ( $st % 60 );
		$mt = floor ( $st / 60 );
		$minutes = floor ( $mt % 60 );
		$ht = floor ( $mt / 60 );
		$hours = floor ( $ht % 24 );
		$days = floor ( $ht / 24 );
	}

	$time = sprintf("%02s",$days).sprintf("%02s",$hours).sprintf("%02s",$minutes).sprintf("%02s",$seconds);

	return $time;
}

// Conversione di data e ora (separate o in formato GG/MM/AAAA hh:mm:ss)
// in formato Timestamp Unix
function time_to_unix_timestamp($date, $time=null) {
	//	echo "DATE:".$date."_TIME:$time<br>";

	$giorno = 0;
	$mese = 0;
	$anno = 0;

	$ore = 0;
	$minuti = 0;
	$secondi = 0;

	if(isset($date) && !empty($date)) {
		if(strlen($date)!=10) {
			$time = substr($date,11);
			$date = substr($date,0,10);
		}

		$anno = getAnno($date);
		$mese = getMese($date);
		$giorno = getGiorno($date);
	}

	if (isset($time) && !empty($time)) {
		$time_parts = explode(":", $time);
		//		echo "TIME_PARTS:<pre>"; print_r($time_parts); echo "</pre>";
		if(!empty($time_parts)) {
			$ore = $time_parts[0];
			if(count($time_parts)>=2)
				$minuti = $time_parts[1];
			if(count($time_parts)>=3)
				$secondi = $time_parts[2];
		}
	}
	//	echo "ORE:".$ore."_MINUTI:".$minuti."_SECONDI:".$secondi."<br>";
	$timestamp_unix = mktime((int)$ore,(int)$minuti,(int)$secondi,(int)$mese,(int)$giorno,(int)$anno);

	return $timestamp_unix;
}

// Conversione di data e ora (separate o in formato GG/MM/AAAA hh:mm:ss)
// in formato Timestamp Unix
function time_to_timestamp($date, $time=null) {
	if(empty($date))
		return 0;

	$timestamp_unix = time_to_unix_timestamp($date, $time);

	$timestamp = date("Y-m-d-H.i.s.u", $timestamp_unix);

	return $timestamp;
}

function time_diff($array_start_time, $array_end_time){
	
	
}

/*
	Trasforma data (formato video 16/12/2008) in
	as400 timestamp

* */
function strDateToASTimestamp($strDate){
	global $settings;//"d/m/Y"
	$timeStamp = ExtractDateTimeByFormat($varDate,$settings['date_php_format']);
	
	$dateTime = new DateTime();
	
	dateViewToModel($strDate);
	
	$data = date("Y")."-".date("m")."-".date("d")."-".date("H").".".date("i").".".date("s").".00000";
	return $data;
	
	
}

function ExtractDateTimeByFormat($strDateTime, $strFormat="dmYHis"){ 
 //extract the format 
 $i = 0; 
 $aFieldOrder = array(); 
 $nFields = 0; 
        $strExtraction = ""; 
        while(isset($strFormat[$i])) 
        { 
            $strField = $strFormat[$i]; 
            switch ( strtolower($strField) ) 
            { 
                case "D"; 
                case "d"; 
                    $aFieldOrder[$nFields] = "d"; 
                    $nFields++; 
                    $strExtraction .= "%d"; 
                    if(isset($strFormat[$i+1])) 
                    { 
                        $strExtraction .= "%*1c"; 
                    } 
                break; 

                case "M"; 
                case "m"; 
                    $aFieldOrder[$nFields] = "m"; 
                    $nFields++; 
                    $strExtraction .= "%d"; 
                    if(isset($strFormat[$i+1])) 
                    { 
                        $strExtraction .= "%*1c"; 
                    } 
                break; 

                case "y"; 
                case "Y"; 
                    $aFieldOrder[$nFields] = "y"; 
                    $nFields++; 
                    $strExtraction .= "%4d"; 
                    if(isset($strFormat[$i+1])) 
                    { 
                        $strExtraction .= "%*1c"; 
                    } 
                break; 

                case "h"; 
                case "H"; 
                    $aFieldOrder[$nFields] = "h"; 
                    $nFields++; 
                    $strExtraction .= "%d"; 
                    if(isset($strFormat[$i+1])) 
                    { 
                        $strExtraction .= "%*1c"; 
                    } 
                break; 

                case "i"; 
                    $aFieldOrder[$nFields] = "i"; 
                    $nFields++; 
                    $strExtraction .= "%d"; 
                    if(isset($strFormat[$i+1])) 
                    { 
                        $strExtraction .= "%*1c"; 
                    } 
                break; 

                case "S"; 
                case "s"; 
                    $aFieldOrder[$nFields] = "s"; 
                    $nFields++; 
                    $strExtraction .= "%d"; 
                    if(isset($strFormat[$i+1])) 
                    { 
                        $strExtraction .= "%*1c"; 
                    } 
                break; 
            } 
            $i++; 
        } 

    $aValues = array(); 
    $aValues = sscanf($strDateTime,$strExtraction); 

    return array_combine($aFieldOrder,$aValues);
} 

function getASTimestamp($timeStamp = "") {

	if ($timeStamp == ""){
		$timeStamp = mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
	}
	
	return date( "Y-m-d-H.i.s.00000" ,$timeStamp );
}	

function udate($format, $utimestamp = null)
{
    if (is_null($utimestamp))
        $utimestamp = microtime(true);

    $timestamp = floor($utimestamp);
    $milliseconds = round(($utimestamp - $timestamp) * 1000000);

    return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
}
?>