<?php
/**
 * @desc Converte una stringa da EBCDIC in ASCII
 * @param string $e: Stringa in EBCDCID
 * @return string Stringa convertita in ASCII
 * 
 * @todo : Sdoppiare le funzioni per generare direttamente la stringa in EBCDIC
 * @todo : Gestire Generazione ASCII con dentro campi PACKED
 */
function e2a ($e) {
	global $settings, $routine_path;
	static $e2a;
	if (!isset($e2a)) {
		$path = $routine_path."/conversion/ucstable/cp1144.map";
		$mappa = file_get_contents($path);
		$e2a = unserialize($mappa);
	}
	/*$e2a = array(0, 1, 2, 3,156, 9,134,127,151,141, 
	142, 11, 12, 13, 14, 15,16, 17, 18, 
	19,157,133, 8,135, 24, 25,146,143, 
	28, 29, 30, 31,128,129,130,131,132, 10, 23, 27,136,137,138,139,140, 5, 6, 7,144,145, 22,147,148,149,150, 4,152,153,154,155, 20, 21,158, 26,32,160,161,162,163,164,165,166, 
	167,168, 91, 46, 60, 40, 43, 33,38,169,170,171,172,173,174,175, 
	176,177, 93, 36, 42, 41, 59, 94,45, 47,178,179,180,181,182,183,184,185, 
	124, 44, 37, 95, 62, 63,186,187,188, 
	189,190,191,192,193, 
	194, 96, 58, 35, 64, 39, 61, 34,195, 
	97, 98, 99,100,101,102,103,104,105, 
	196,197, 
	198,199,200,201,202,106,107,108,109, 
	110,111,112,113,114,203,204,205,206, 
	207,208,209,126,115,116,117,118,119, 
	120,121,122,210,211,212,213,214,215, 
	216,217,218,219,220,221,222,223,224, 
	225,226,227,228,229,230,231,123, 65, 
	66, 67, 68, 69, 70, 71, 72, 73,232,233, 
	234,235,236,237,125, 74, 75, 76, 77, 
	78, 79, 80, 81, 82,238,239,240,241,242, 
	243,92,159, 83, 84, 85, 86, 87, 88, 89, 90,244,245,246,247,248,249,48, 49, 
	50, 51, 52, 53, 54, 55, 56, 57,250,251, 
	252,253,254,255);
	/*$e2a = array( '0' => '0' , '1' => '1' , '2' => '2' , '3' => '3' , '4' => '156' , '5' => '9' , '6' => '134' , '7' => '127' , '8' => '151' , '9' => '141' , '10' => '142' , '11' => '11' , '12' => '12' , '13' => '13' , '14' => '14' , '15' => '15' , '16' => '16' , '17' => '17' , '18' => '18' , '19' => '19' , '20' => '157' , '21' => '133' , '22' => '8' , '23' => '135' , '24' => '24' , '25' => '25' , '26' => '146' , '27' => '143' , '28' => '28' , '29' => '29' , '30' => '30' , '31' => '31' , '32' => '128' , '33' => '129' , '34' => '130' , '35' => '131' , '36' => '132' , '37' => '10' , '38' => '23' , '39' => '27' , '40' => '136' , '41' => '137' , '42' => '138' , '43' => '139' , '44' => '140' , '45' => '5' , '46' => '6' , '47' => '7' , '48' => '144' , '49' => '145' , '50' => '22' , '51' => '147' , '52' => '148' , '53' => '149' , '54' => '150' , '55' => '4' , '56' => '152' , '57' => '153' , '58' => '154' , '59' => '155' , '60' => '20' , '61' => '21' , '62' => '158' , '63' => '26' , '64' => '32' , '65' => '160' , '66' => '161' , '67' => '162' , '68' => '163' , '69' => '164' , '70' => '165' , '71' => '166' , '72' => '167' , '73' => '168' , '74' => '91' , '75' => '46' , '76' => '60' , '77' => '40' , '78' => '43' , '79' => '33' , '80' => '38' , '81' => '169' , '82' => '170' , '83' => '171' , '84' => '172' , '85' => '173' , '86' => '174' , '87' => '175' , '88' => '176' , '89' => '177' , '90' => '93' , '91' => '36' , '92' => '42' , '93' => '41' , '94' => '59' , '95' => '94' , '96' => '45' , '97' => '47' ,
			'98' => '194' , '99' => '196' , '100' => '192' , '101' => '193' , '102' => '195' , '103' => '197' , '104' => '184' , '105' => '185' , '106' => '124' , '107' => '44' , '108' => '37' , '109' => '95' , '110' => '62' , '111' => '63' , '112' => '186' , '113' => '201' , '114' => '188' , '115' => '189' , '116' => '200' , '117' => '191' , '118' => '192' , '119' => '193' , '120' => '194' , '121' => '96' , '122' => '58' , '123' => '35' , '124' => '64' , '125' => '39' , '126' => '61' , '127' => '34' , '128' => '195' , '129' => '97' , '130' => '98' , '131' => '99' , '132' => '100' , '133' => '101' , '134' => '102' , '135' => '103' , '136' => '104' , '137' => '105' , '138' => '196' , '139' => '197' , '140' => '198' , '141' => '199' , '142' => '200' , '143' => '201' , '144' => '202' , '145' => '106' , '146' => '107' , '147' => '108' , '148' => '109' , '149' => '110' , '150' => '111' , '151' => '112' , '152' => '113' , '153' => '114' , '154' => '203' , '155' => '204' , '156' => '205' , '157' => '206' , '158' => '207' , '159' => '208' , '160' => '209' , '161' => '126' , '162' => '115' , '163' => '116' , '164' => '117' , '165' => '118' , '166' => '119' , '167' => '120' , '168' => '121' , '169' => '122' , '170' => '210' , '171' => '211' , '172' => '212' , '173' => '213' , '174' => '214' , '175' => '215' , '176' => '216' , '177' => '217' , '178' => '218' , '179' => '219' , '180' => '220' , '181' => '221' , '182' => '222' , '183' => '223' , '184' => '224' , '185' => '225' , '186' => '226' , '187' => '227' , '188' => '228' , '189' => '229' , '190' => '230' , '191' => '231' , '192' => '123' , '193' => '65' , '194' => '66' , '195' => '67' , '196' => '68' , '197' => '69' , '198' => '70' , '199' => '71' , '200' => '72' , '201' => '73' , '202' => '232' , '203' => '233' , '204' => '234' , '205' => '235' , '206' => '236' , '207' => '237' , '208' => '125' , '209' => '74' , '210' => '75' , '211' => '76' , '212' => '77' , '213' => '78' , '214' => '79' , '215' => '80' , '216' => '81' , '217' => '82' , '218' => '238' , '219' => '239' , '220' => '240' , '221' => '241' , '222' => '242' , '223' => '243' , '224' => '92' , '225' => '159' , '226' => '83' , '227' => '84' , '228' => '85' , '229' => '86' , '230' => '87' , '231' => '88' , '232' => '89' , '233' => '90' , '234' => '244' ,
			'235' => '212' , '236' => '214' , '237' => '210' , '238' => '211' , '239' => '213' , '240' => '48' , '241' => '49' , '242' => '50' , '243' => '51' , '244' => '52' , '245' => '53' , '246' => '54' , '247' => '55' , '248' => '56' , '249' => '57' , '250' => '250' ,
			'251' => '219' , '252' => '220' , '253' => '217' , '254' => '218' , '255' => '255');*/
	
	$a = ''; 
	for ($i = 0 ; $i < strlen($e) ; $i++) { $a .= chr($e2a[ord(substr($e,$i,1))]); } 
	return $a; 
}
/**
 * @desc Converte una stringa ASCII in EBCDIC
 * @param string $e: Stringa in ASCII
 * @return string Stringa convertita in EBCDIC
 */
function a2e ($a) { 
	global $settings, $routine_path;
	static $a2e;
	if (!isset($a2e)) {
		$path = $routine_path."/conversion/ucstable/cp1144.map";
		$mappa = file_get_contents($path);
		$e2a = unserialize($mappa);
		$a2e = array_flip($e2a);
	}
	/*$a2e = array(0, 1, 2, 3, 55, 45, 46, 47, 22, 5, 37, 11, 12, 13, 14, 15,16, 17, 
	18, 19, 60, 61, 50, 38, 24, 
	25, 63, 39, 28, 29, 30, 31, 
	64, 79,127,123, 91,108, 80, 
	125, 77, 93, 92, 78,107, 96, 75, 97,240,241,242,243,244,245,246, 
	247,248,249,122, 94, 76,126,110, 
	111,124,193,194,195, 
	196,197,198,199,200,201,209, 
	210,211,212,213,214,215,216, 
	217,226,227,228,229,230,231,232, 
	233, 74,224, 90, 95,109,121,129,130,131,132, 
	133,134,135,136,137,145,146,147, 
	148,149,150,151,152,153,162,163, 
	164,165,166,167,168,169,192,106, 
	208,161, 7,32, 33, 34, 35, 36, 21, 
	6, 23, 40, 41, 42, 43, 44, 9, 10, 27, 
	48, 49, 26, 51, 52, 53, 54, 8, 56, 57, 
	58, 59, 4, 20, 62,225,65, 66, 67, 68, 
	69, 70, 71, 72, 73, 81, 82, 83, 84, 85, 86, 87,88, 89, 98, 99,100,101,102,103,104,105,112,113, 
	114,115,116,117,118,119,120,128,138, 
	139,140,141,142,143,144,154,155,156, 
	157,158,159,160,170,171,172,173,174, 
	175,176,177,178,179,180,181,182,183, 
	184,185,186,187,188,189,190,191,202, 
	203,204,205,206,207,218,219,220,221, 
	222,223,234,235,236,237,238,239,250, 
	251,252,253,254,255); */
	$e = ''; 
	for ($i = 0 ; $i < strlen($a) ; $i++) { $e .= chr($a2e[ord(substr($a,$i,1))]); } 
	return $e; 
} 
function hextostr($hex)
{
	$str='';
	for ($i=0; $i < strlen($hex)-1; $i+=2)
	{
		$str .= chr(hexdec($hex[$i].$hex[$i+1]));
	}
	return $str;
}
function strtohex2($string){
	$hex = '';
	for ($i=0; $i<strlen($string); $i++){
		$ord = ord($string[$i]);
		$hexCode = dechex($ord);
		$hex .= substr('0'.$hexCode, -2);
	}
	return strtoupper($hex);
}
function strtohex($string) {
	$hexstr = unpack('H*', $string);
	return array_shift($hexstr);
}
function ds2string($dati, $ds) {
	// Ciclo su tutti gli elementi dell'array
	$start = 0;
	$mystring = "";
	foreach ($ds as $array) {
		$intdec = explode('.',$array['Length']);
		// Recupero le caratteristiche della stringa
		$tipo = $array['Type'];
		if ($tipo == I5_TYPE_CHAR) {
			$mystring .= str_pad($dati[$array['Name']], $array['Length'], " ");
		} elseif ($tipo == I5_TYPE_PACKED) {
			$mystring .= string2Packed($dati[$array['Name']], $intdec[0]);
		} elseif ($tipo == I5_TYPE_ZONED) {
			//error_log(string2Zoned($dati[$array['Name']], $intdec[0]));
			$mystring .= string2Zoned($dati[$array['Name']], $intdec[0], $intdec[1]);
		}
	}
	return $mystring;
}
function string2Ds($string, $ds) {
	// Ciclo su tutti gli elementi dell'array
	$start = 0;
	$myds = array();
	foreach ($ds as $array) {
		// Recupero le caratteristiche della stringa
		$tipo = $array['Type'];
		$dati = explode('.',$array['Length']);
		if ($tipo == I5_TYPE_CHAR) {
			$segmento = e2a(substr($string, $start, $dati[0]));
			$start = $start + $dati[0];
			$myds[$array['Name']] = $segmento;
		} elseif ($tipo == I5_TYPE_PACKED) {
			$lunghezza = floor($dati[0]/2)+1;
			//echo "<br>Rec:".$array['Name']." decimali ".$dati[1]. " interi ".$dati[0]. " lunghezza ".$lunghezza;
			$segmento = substr($string, $start,$lunghezza);
			$start = $start + $lunghezza;
			$myds[$array['Name']] = packed2String($segmento, $dati[1]);
		} elseif ($tipo == I5_TYPE_ZONED) {
			$segmento = substr($string, $start, $dati[0]);
			$start = $start + $dati[0];
			$myds[$array['Name']] = zoned2String($segmento, $dati[1]);
		}
	}
	return $myds;
}

/**
 *
 * stringToDec: Conversione da stringa numerica a decimale
 *
 **/
function string2Zoned($s, $length, $decimal=0) {
	$positive = False;
	$segno = "";
	if ($s>=0) {
		$positive = True;
		//$segno ="F";
	} else {
		$s = $s * -1;
		$segno ="D";
	}
	//echo "<br>D1 in hex:".hexdec("0xD1");
	//echo "<br>CARATTER 80:".e2a(chr(hexdec("0xD1")));
	
	//echo "<br>Positivo:".$positive;
	//$decimal = explode(".", $s);
	//echo "<br/>Numero decimali: ".strlen($decimal[1]);
	$numero = number_format($s, $decimal, "", "");
	//echo "<br>NUMERO:".$numero;
	//echo "<br>Numero:".$numero;
	$numero_padded = str_pad($numero, $length, "0", STR_PAD_LEFT);
	//echo "<br>NUMERO_PADDED:".$numero_padded;
	//echo "<br>Numero padded:".$numero_padded;
	//echo "<br>Numero Zoned:".substr($numero_padded,0, strlen($numero_padded)-1). chr("0x$ultimo$segno");
	if ($positive) {
		return $numero_padded;
	} else {
		$ultimo = substr($numero_padded, strlen($numero_padded)-1, 1);
		//echo "<br>CARATTERE HEX AS400:".e2a(chr(ord("0x$segno$ultimo")));
		return substr($numero_padded,0, strlen($numero_padded)-1). e2a(chr(hexdec("0x$segno$ultimo")));
	}
}

/**
 *
 *stringToPacked: Conversione da stringa numerica a decimale compatto
 *
 **/
function string2Packed($s, $length) {
	if(!$length) {
		$length = strlen($s);
	}
	
	$decimal = explode(".", $s);
	
	$positive = False;
	$segno = "D";
	if ($s>0) {
		$positive = True;
		$segno ="F";
	} else {
		$s = $s * -1;
	}
	
	//echo "<br>Positivo:".$positive;
	$numero = number_format($s, strlen($decimal[1]), "", "");
	if(!($length%2)) {
		$length++;
	}
	$n_pad = str_pad($numero, $length, "0", STR_PAD_LEFT);
	//echo "<br>Numero padded:".$n_pad."<br/>";
	$hex = "";
	for($i = 0; $i<strlen($n_pad); $i = $i+2) {
		if($n_pad[$i+1] != "") {
			//echo $n_pad[$i].$n_pad[$i+1]." // ".chr("0x".$n_pad[$i].$n_pad[$i+1])."<br/>";
			$hex .= chr("0x".$n_pad[$i].$n_pad[$i+1]);
		}else {
			//echo $n_pad[$i].$segno." // ".chr("0x".$n_pad[$i].$segno)."<br/>";
			$hex .= chr("0x".$n_pad[$i].$segno);
		}
	}
	return $hex;
}

/**
 *
 *packedToString: Conversione da decimale compatto a stringa numerica
 *
 **/
function packed2String($string, $decimal)
{
	// @todo Ultimo Byte per stabilire il segno del valore numerico
	$hex='';
	$mult = 1;
	if ($decimal ="") {
		$decimal = 0;
	}
	//echo "<br>String:".$string;
	for ($i=0; $i < strlen($string); $i++)
	{
		$hex .= dechex(ord($string[$i]));
	}
	//echo "<br>Hex:".$hex;
	$byte = strlen($hex)-1;
	if (substr($hex, $byte,1)!="f") {
		$mult = -1;
	}
	$numero = substr($hex, 0, $byte) / pow(10, $decimal);
	$numero = $numero * $mult;
	return $numero;
}
/**
 *
 * decToString: Conversione da decimale a stringa numerica
 *
 **/
function zoned2String($string, $decimal)
{
	// @todo Ultimo Byte per stabilire il segno del valore numerico
	//echo "<br>".$string;
	$mult = 1;
	$numero = e2a(substr($string,0, strlen($string)-1));
	$last = dechex(ord($string[strlen($string)-1]));
	//echo "<br>Last:".$last;
	$numero = $numero.$last[1];
	if ($last[0]!='f') {
		$mult = -1;
	}
	//$numero = $string / pow(10, $decimal);
	return $numero*$mult;
}
function PrintHEX($cmd){
	for ( $x=0; $x<strlen($cmd); $x++ ) {
		printf("0x%x ", ord($cmd[$x]));
	}
	print "<br>";
}
