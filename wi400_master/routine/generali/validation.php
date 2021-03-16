<?php

/**
 * file:	validation.php
 * 
 * info@siri-informatica.it
 * http:://www.siri-informatica.it
 * 
 */

class wi400Validation {

	static $VALIDATION_REQUIRED = "required";
	static $VALIDATION_EMAIL    = "email";
	static $VALIDATION_URL   	= "url";
	static $VALIDATION_NUMERIC  = "numeric";
	static $VALIDATION_INTEGER  = "integer";
	static $VALIDATION_DOUBLE   = "double";
	static $VALIDATION_DATE     = "date";
	static $VALIDATION_IP_CMP 	= "IPcmp";
	static $VALIDATION_CODICE_FISCALE = "codice_fiscale";
	static $VALIDATION_PARTITA_IVA 	= "partita_iva";	
	
	static function validateForm($idDetail){
			global $messageContext, $viewContext, $routine_path, $base_path, $settings;
				if (existDetail($idDetail)){
				$detailSessionObj = getDetail($idDetail);
				$fieldsList = $detailSessionObj["FIELDS"];
				
				$fieldCounter = 0;
				foreach($fieldsList as $fieldId => $field){
					if (sizeof($field->getValidations()) > 0){ 
						foreach ($field->getValidations() as $validation){
							// Se multi value c'è un array
							if (is_array($field->getValue())){
								$inputValues = $field->getValue();
								$fieldArrayCounter = 0;
								foreach ($inputValues as $inputValue){
									$fieldArrayCounter++;
									if ($inputValue != ""){
										if (is_callable("wi400_validate_".$validation,false)){
											$testField = new wi400Input();
											$testField->setValue($inputValue);
											$testField->setId($fieldId."_".$fieldArrayCounter);
											if (!call_user_func("wi400_validate_".$validation, $testField)) break;
										}else{
											echo "<br>Funzione di controllo wi400_validate_".$validation." non implementata.";
											exit();
										}
									}
								}
							// Caso normale
							}else{
								if (is_callable("wi400_validate_".$validation,false)){
									if (!call_user_func("wi400_validate_".$validation, $field)) break;
									
								}else{
									echo "<br>Funzione di controllo wi400_validate_".$validation." non implementata.";
									exit();
								}
							}
						}
					}
			
					if ((isset($_POST[$fieldId]) && ($_POST[$fieldId])!="")) {
						if ($field->getDecode() != ""){
								// Nuova decodificA
								$decodeParameters = $field->getDecode();
								$decodeType = "table";
								if (!isset($decodeParameters['NODECODE'])) {
									if (isset($decodeParameters["TYPE"])){
										$decodeType = $decodeParameters["TYPE"];
									}
									//require_once $routine_path.'/decoding/siad/'.$decodeType.".php";
									//require_once $base_path.'/package/'.$settings['package'].'/decodingclass/'.$decodeType.".php";
									require_once p13nPackage($decodeType);
														
									$decodeClass = new $decodeType();
									$decodeClass->setDecodeParameters($decodeParameters);
									$decodeClass->setFieldLabel($field->getLabel());
									if (is_array($field->getValue())){
										$inputValues = $field->getValue();
										$fieldArrayCounter = 0;
										foreach ($inputValues as $inputValue){
											$fieldArrayCounter++;
											
											if ($inputValue != ""){
												$decodeClass->setFieldId($fieldId."_".$fieldArrayCounter);
												$decodeClass->setFieldValue($inputValue);								
												$decodeResult = $decodeClass->decode();
												if ($decodeResult!==False){
													$viewContext->__set($fieldId."_DESCRIPTION", $decodeResult);
												}else{
													$messageContext->addMessage("ERROR", $decodeClass->getFieldMessage(), $fieldId);
												}
											}else{
												$viewContext->__set($fieldId."_".$fieldArrayCounter."_DESCRIPTION", "");
											}
										}
									}else{
										if ($field->getValue() !== ""){
											
											$decodeClass->setFieldId($fieldId);
											$decodeClass->setFieldValue($_POST[$fieldId]);								
											$decodeResult = $decodeClass->decode();
											// Verifico se il campo chiave è stato modificato
											$retrunFieldValue = $decodeClass->decodeFields();
											if (isset($retrunFieldValue[$fieldId]) && $_POST[$fieldId]!==$retrunFieldValue[$fieldId]) {
													wi400Detail::setDetailValue($idDetail, $fieldId, $retrunFieldValue[$fieldId]);
											}
											if ($decodeResult !== False){
												$viewContext->__set($fieldId."_DESCRIPTION", $decodeResult);
											}else{
												$messageContext->addMessage("ERROR", $decodeClass->getFieldMessage(), $fieldId);
											}
										
										}else{
											$viewContext->__set($fieldId."_DESCRIPTION", "");
										}
									}
							}
						}
					}
					
					
					
				} // End foreach
				
			}
	}

}

// Controllo se il campo è richiesto
function wi400_validate_required($field){
	global $messageContext;
	if (!is_array($field->getValue()) && trim($field->getValue()) == ""){
		$messageContext->addMessage("ERROR", "Il campo ".$field->getLabel()._t("DEVE_ESSERE_VALORIZZATO"), $field->getId());
	    return false;
	}
	return true;
}
// Controllo se il campo è una mail formattata valida
function wi400_validate_email($field)
{
	global $messageContext;
	
	if (trim($field->getValue())!="" && !validEmail($field->getValue())){
		$messageContext->addMessage("ERROR", $field->getLabel()._t("DEVE_ESSERE_UNA_MAIL_VALIDA"), $field->getId());
		return false;
	}
	return true;
}
// Controllo se il campo è un url formattato valido
function wi400_validate_url($field)
{
	global $messageContext;

	if (trim($field->getValue())!="" && !validUrl($field->getValue())){
		$messageContext->addMessage("ERROR", $field->getLabel()._t("DEVE_ESSERE_UN_URL_VALIDO"), $field->getId());
	 	return false;
	}
	return true;
}
// Controllo se il campo è un indirizzo IP formattato valido
function wi400_validate_IPcmp($field)
{
	global $messageContext;

	if ($field->getValue() != "" && !validIPcmp($field->getValue())){
		$messageContext->addMessage("ERROR", $field->getLabel()._t("DEVE_ESSERE_UN_IP_VALIDO"), $field->getId());
	 	return false;
	}
	return true;
}
// Controllo se il campo è numerico
function wi400_validate_numeric($field)
{
	global $messageContext;
	if ($field->getValue() != "" && !is_numeric($field->getValue())){
		$messageContext->addMessage("ERROR", $field->getLabel()._t("DEVE_ESSERE_UN_NUMERO"), $field->getId());
	 	return false;
	}
	return true;
}
// Controllo se il campo è numerico
function wi400_validate_integer($field)
{
	global $messageContext;
	if ($field->getValue() != "" && !wi400_is_integer($field->getValue())){
		$messageContext->addMessage("ERROR", $field->getLabel()._t("DEVE_ESSERE_UN_NUMERO"), $field->getId());
	 	return false;
	}
	return true;
}
// Controllo se il campo è mascherato
function wi400_validate_mask($field)
{
	global $messageContext;
	
	if ($field->getValue() != "" && !is_integer($field->getValue())){
		$messageContext->addMessage("ERROR", $field->getLabel()._t("DEVE_ESSERE_UN_NUMERO"), $field->getId());
		return false;
	}
	return true;
}
/**
* check a date in the System or user format
*/
function wi400_validate_date($field){
	global $messageContext,$settings;
	$date = $field->getValue();
    if (!isset($date) || $date==""){
    	return true;
    }else{
	    
    	$dateArray = explode("/",$date);
		$formatArray = explode("/",$settings['date_format']);
		
		$dd = $dateArray[array_search("dd",$formatArray)];
		$mm = $dateArray[array_search("mm",$formatArray)];
		$yy = $dateArray[array_search("YYYY",$formatArray)];
		
	    if ($dd!="" && $mm!="" && $yy!="" && checkdate($mm,$dd,$yy)){
	        return true;
	    }
		$messageContext->addMessage("ERROR", $field->getLabel()._t("NON_E_UNA_DATA_VALIDA"), $field->getId());
    	return false;
    }
}

/**
* check a time in the System or user format
*/
function wi400_validate_time($field){
	global $messageContext,$settings;
	$time = $field->getValue();
    if (!isset($date) || $date==""){
    	return true;
    }else{
	    
    	$timeArray = explode(":",$time);
		$formatArray = explode(":",$settings['short_hour_format']);
		
		$hh = $timeArray[array_search("HH",$formatArray)];
		$mm = $timeArray[array_search("MM",$formatArray)];
		
	    if ($hh!="" && $mm!=""){
	        return (is_numeric($hh) && is_numeric(mm) 
	        			&& $hh >= 0 && $hh <= 23 
	        				&& $mm >= 0 && $mm <=59);
	    }
		$messageContext->addMessage("ERROR", $field->getLabel()._t("NON_E_UN_ORARIO_VALIDO"), $field->getId());
    	return false;
    }
}

function wi400_validate_double($field){
	global $messageContext;
	$fieldValue = doubleViewToModel($field->getValue());
    $m_factor=pow(10,$field->getDecimals());
    if($field->getValue() == "" || (int)($field->getValue()*$m_factor)==$field->getValue()*$m_factor)
        return true;
    else{
        return false;
 		$messageContext->addMessage("ERROR", $field->getLabel()._t("DEVE_ESSERE_UN_DECIAMALE"), $field->getId());
    }
}

/*
 * Controllo che la data iniziale sia precedente a quella finale 
 * Data iniziale e finale espresse in formato GG/MM/AAAA
 */
function check_data_ini_prec_fin($data_val_ini, $data_val_fin, $campo) {
	global $messageContext;

	$errore = false;
	
	$data_ini = getASTimestamp ( dateViewToTimestamp ( $data_val_ini ) );
	$data_fin = getASTimestamp ( dateViewToTimestamp ( $data_val_fin ) );
	
	if ($data_ini > $data_fin)
		$messageContext->addMessage ( "ERROR", "La data di INIZIO deve essere precedente a quella di FINE.", $campo, true );
	
	if ($messageContext->getSeverity () == "ERROR") {
		$errore = true;
	}
	
	return ! $errore;
}

/*
 * Controllo che il periodo selezionato abbia data iniziale precedente a quella finale
 * Data iniziale e finale espresse in formato GG/MM/AAAA
 */
function check_periodo($data_ini, $data_fin, $campo=null){
    global $messageContext;

    $errore = false;
    
	if(checkDateFormat($data_ini,"DATE")===true) {
   		$data_ini = dateViewToModel($data_ini);
	}
	if(checkDateFormat($data_fin,"DATE")===true) {
   		$data_fin = dateViewToModel($data_fin);
	}
	
	if($data_ini>$data_fin)
		return false;
	else
		return true;
}

function check_time($time_ini, $time_fin, $campo=null){
	global $messageContext;

	$errore = false;

	$time_ini = timeViewToModel($time_ini);
	$time_fin = timeViewToModel($time_fin);
//	echo "TIME INI: $time_ini - TIME FIN: $time_fin<br>";

	if($time_ini>$time_fin)
		return false;
	else
		return true;
}

function check_periodo_timestamp($ts_ini, $ts_fin, $campo=null){
	global $messageContext;

	$errore = false;
	
	$ts_ini = wi400_format_COMPLETE_TIMESTAMP($ts_ini);
	$ts_fin = wi400_format_COMPLETE_TIMESTAMP($ts_fin);
	
	$data_ini = substr($ts_ini, 0, 10);
	
	$time_ini = substr($ts_ini, 11);
	$time_ini = str_replace(":", "", $time_ini);
	
	$data_fin = substr($ts_fin, 0, 10);
	
	$time_fin = substr($ts_fin, 11);
	$time_fin = str_replace(":", "", $time_fin);

	if(checkDateFormat($data_ini,"DATE")===true) {
		$data_ini = dateViewToModel($data_ini);
	}
	
	if(checkDateFormat($data_fin,"DATE")===true) {
		$data_fin = dateViewToModel($data_fin);
	}

	if($data_ini.$time_ini>$data_fin.$time_fin)
		return false;
	else
		return true;
}

function checkDateFormat($date, $format="SHORT_TIMESTAMP") {
	if($format=="SHORT_TIMESTAMP") {
		//yyyy-mm-dd
		//match the format of the date
		if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
			//check whether the date is valid or not
			if(checkdate($parts[2],$parts[3],$parts[1]))
				return true;
			else
				return false;
		}
		else
			return false;
	}
	else if($format=="DATE") {
		//dd/mm/yyyy
		//match the format of the date
		if (preg_match ("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/", $date, $parts)) {
//			echo "PARTS:"; print_r($parts); echo "<br>";
			//check whether the date is valid or not
			if(checkdate($parts[2],$parts[1],$parts[3]))
				return true;
			else
				return false;
		}
		else
			return false;
	}
	else if($format=="DATE_STRING") {
		//yyyymmdd
		//match the format of the date
		if (preg_match ("/^([0-9]{4})([0-9]{2})([0-9]{2})$/", $date, $parts)) {
			//check whether the date is valid or not
			if(checkdate($parts[2],$parts[3],$parts[1]))
				return true;
			else
				return false;
		}
		else
			return false;
	}
	else if($format=="SHORT_DATE") {
		//dd/mm/yy
		//match the format of the date
		if (preg_match ("/^([0-9]{2})\/([0-9]{2})\/([0-9]{2})$/", $date, $parts)) {
//			echo "PARTS:"; print_r($parts); echo "<br>";
			//check whether the date is valid or not
			$year = date("Y", mktime(0, 0, 0, $parts[2], $parts[1], $parts[3]));
			if(checkdate($parts[2],$parts[1],$year))
				return true;
			else
				return false;
		}
		else
			return false;
	}
}

function wi400_is_integer($int){
    // First check if it's a numeric value as either a string or number
    if(is_numeric($int) === TRUE){
        
        // It's a number, but it has to be an integer
        if((int)$int == $int){

            return TRUE;
            
        // It's a number, but not an integer, so we fail
        }else{
        
            return FALSE;
        }
    
    // Not a number
    }else{
    
        return FALSE;
    }
}

/**
 * Validazione e-mail.
 */
function validEmail($address) {
//	if (ereg ( '^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+' . '@' . '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.' . '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $address ))
//	if (preg_match ( '/^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+' . '@' . '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.' . '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+/', $address ))
	if (preg_match( "/^([-_.!#$%&\'*=?+\/{|}~a-zA-Z0-9]+)@([-_!#$%&\'*=?+\/{|}~a-zA-Z0-9]+)([.]{0,1})([-_!#$%&\'*=?+\/{|}~a-zA-Z0-9]+)/" , $address))
		return true;
	else
		return false;
}
/**
 * Validazione url.
 */
function validUrl($address) {
	if (preg_match ( "/^(http|https|ftp):\/\/([0-9]{1,3}\.){3}([0-9]{1,3}):([0-9]{1,3})\/([-_.!#$%&\'*=?+\/{|}~a-zA-Z0-9]+)/" , $address ))
		return true;
	else
		return false;
}

function validIPcmp($address) {
	if (preg_match ( "/^([0-9]{1,3}\.){3}([0-9]{1,3})/" , $address ))
		return true;
	else
		return false;
/*	
	$ip_parts = explode(".", $address);
//	echo "IP PARTS:<pre>"; print_r($ip_parts); echo "</pre>"; 
//	echo "COUNT: ".count($ip_parts)."<br>";
	if(count($ip_parts)!=4)
		return false;
	
	foreach($ip_parts as $part) {
		$part = (int)$part;
//		echo "PART:$part<br>"; continue;
		if($part<0 || $part>999)
			return false;
	}

	return true;
*/
}

// Funzione is_float corretta, per controllare se una variabile è di tipo float
function is_true_float($val) {
	//		if(is_float($val) || ( (float)$val>(int)$val || strlen($val)!=strlen((int)$val)) && (int)$val!=0) 
	if (is_float ( $val ) || (( float ) $val > ( int ) $val || strlen ( $val ) != strlen ( ( int ) $val )) && strpos ( $val, '.' ) !== false)
		return true;
	else
		return false;
}
function getErrorList($field, $fields) {
	$found = strpos($fields['IS_ERROR_FIELDS'], $field);
	if ($found===False) {
		return false;
	} else {
		return True;
	}
}
function checkImage($ObjType, $ObjCode, $ImgType=null) {
	global $db;
	
	static $stmt;
	
	if (!isset($stmt)) {
		$sql = "SELECT * FROM OBJ_IMG WHERE OBJ_CODE=? AND OBJ_TYPE=?";
		$sql .= " AND IMG_TYPE is null";
		$stmt = $db->prepareStatement($sql);
	}
	$result = $db->execute($stmt, array($ObjCode,$ObjType));
	$row = $db->fetch_array($stmt);
	
	if(!$row)
		return false;
	else
		return $row;
}

function checkFilePresence($path, $exceptions=array()) {
	$fileExists = false;
	
	if(file_exists($path)) {
		$dir_handle = opendir($path);
	    if ($dir_handle) {		
			while(($file = readdir($dir_handle))!==false) {	
				if($file!="." && $file!=".." && !in_array($file, $exceptions)) {
					$fileExists = true;
				}
			}
			closedir($dir_handle);
		}
	}
	return $fileExists;
}


function wi400_validate_ip($field){
	global $messageContext;
	
	if ($field->getValue() != ""){
		$ipArray = explode(".", $field->getValue());
		foreach ($ipArray as $ipNum){
			
			if ($ipNum != "*" && (!is_numeric($ipNum) || (int)$ipNum > 255 || (int)$ipNum < 0)){
 				$messageContext->addMessage("ERROR", $field->getLabel()." non è un ip valido", $field->getId());
				return false;
			}
		}
	}
	return true;
}
function wi400_validate_codice_fiscale($field){
	global $messageContext;
	$cf = $field->getValue();
	if($cf=='')
		return false;
	if(strlen($cf)!= 16) {
		$messageContext->addMessage("ERROR", $field->getLabel()." LUNGHEZZA NON VALIDA", $field->getId());
		return false;
	}	
	$cf=strtoupper($cf);
	if(!preg_match("/[A-Z0-9]+$/", $cf)) {
		$messageContext->addMessage("ERROR", $field->getLabel()." CONTIENE CARATTERI NON VALIDI", $field->getId());
		return false;
	}	
	$s = 0;
	for($i=1; $i<=13; $i+=2){
		$c=$cf[$i];
		if('0'<=$c and $c<='9')
			$s+=ord($c)-ord('0');
		else
			$s+=ord($c)-ord('A');
	}
	
	for($i=0; $i<=14; $i+=2){
		$c=$cf[$i];
		switch($c){
			case '0':  $s += 1;  break;
			case '1':  $s += 0;  break;
			case '2':  $s += 5;  break;
			case '3':  $s += 7;  break;
			case '4':  $s += 9;  break;
			case '5':  $s += 13;  break;
			case '6':  $s += 15;  break;
			case '7':  $s += 17;  break;
			case '8':  $s += 19;  break;
			case '9':  $s += 21;  break;
			case 'A':  $s += 1;  break;
			case 'B':  $s += 0;  break;
			case 'C':  $s += 5;  break;
			case 'D':  $s += 7;  break;
			case 'E':  $s += 9;  break;
			case 'F':  $s += 13;  break;
			case 'G':  $s += 15;  break;
			case 'H':  $s += 17;  break;
			case 'I':  $s += 19;  break;
			case 'J':  $s += 21;  break;
			case 'K':  $s += 2;  break;
			case 'L':  $s += 4;  break;
			case 'M':  $s += 18;  break;
			case 'N':  $s += 20;  break;
			case 'O':  $s += 11;  break;
			case 'P':  $s += 3;  break;
			case 'Q':  $s += 6;  break;
			case 'R':  $s += 8;  break;
			case 'S':  $s += 12;  break;
			case 'T':  $s += 14;  break;
			case 'U':  $s += 16;  break;
			case 'V':  $s += 10;  break;
			case 'W':  $s += 22;  break;
			case 'X':  $s += 25;  break;
			case 'Y':  $s += 24;  break;
			case 'Z':  $s += 23;  break;
		}
	}
	if( chr($s%26+ord('A'))!=$cf[15] ) {
		$messageContext->addMessage("ERROR", $field->getLabel()." CODICE FISCALE NON VALIDO", $field->getId());
		return false;
	}	
	//die("$cf");	
	return true;
}
function wi400_validate_partita_iva($field){
	global $messageContext;
	
	$variabile = $field->getValue();
	if($variabile=='')
		return true;
	
	//la p.iva deve essere lunga 11 caratteri
	if(strlen($variabile)!=11) {
		$messageContext->addMessage("ERROR", $field->getLabel()." LUNGHEZZA NON VALIDA", $field->getId());
		return false;
	}
	
	//la p.iva deve avere solo cifre
	//if(!ereg("^[0-9]+$", $variabile)) {
	if(!preg_match("/^[0-9]+$/", $variabile)) {	
		$messageContext->addMessage("ERROR", $field->getLabel()." CONTIENE CARATTERI NON VALIDI", $field->getId());
		return false;
	}
	
	$primo=0;
	for($i=0; $i<=9; $i+=2) {
		$primo+= ord($variabile[$i])-ord('0');
	}
	
	for($i=1; $i<=9; $i+=2 ){
		$secondo=2*( ord($variabile[$i])-ord('0') );
	
		if($secondo>9)
			$secondo=$secondo-9;
		$primo+=$secondo;
	
	}
	
	if( (10-$primo%10)%10 != ord($variabile[10])-ord('0') ) {
		$messageContext->addMessage("ERROR", $field->getLabel()." NON VALIDA", $field->getId());
		return false;
	}	
	
	return true;
}
?>