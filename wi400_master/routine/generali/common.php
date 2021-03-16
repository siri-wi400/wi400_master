<?php
/**
 * file:	common.php
 * 
 * info@siri-informatica.it
 * http:://www.siri-informatica.it
 * 
 */
// sanitize a string for SQL input (simple slash out quotes and slashes)
function sanitize_sql_string($string) {
	global $settings;
	if ($settings['database']=='DB2AS400') {
		return db2_escape_string ( $string );
	}else if($settings['database']=='DB2_PDO') { //Database UNICOMM

		$string  = str_replace("'", "''", $string);
		//$db::quote($string);
		//$string = addslashes($string);
		return $string;
	}else {
		return $string;
	}
}
/**
 * @desc Reperisce il link per il collegamento
 */
function getLoginUrl() {
	global $appBase, $currentHMAC, $moduli_path, $db;
	session_destroy();
	// Nuova sessione
	//session_start();
	//require_once $moduli_path."/auth/logout_model.php";
	$link = "Sessione scaduta. Effettura nuovamente il <a href=\"{$appBase}index.php?t=LOGIN\">LOGIN</a>";
	return $link;
} 

function showArray($a) {
	echo "<pre>"; print_r($a); echo "</pre>";
}
/**
 * @desc isHtml: Verifico se una stringa contiene codice HTML
 * @param string $string
 * @return boolean Ritorna True se la stringa contiene codice HTML
 */
function isHtml($string)
{
	if ( $string != strip_tags($string) )
	{
		return true; // Contains HTML
	}
	return false; // Does not contain HTML
}
function goHeader($string) {
	global $appBase,$settings, $currentHMAC, $currentSESSION;
	if (isset($settings['security_check_url']) && $settings['security_check_url']==True) {
		$_SESSION['GO_URL']=$string;
	}
	/*$request = array();
	$dati = parse_url($string);
	$get_string = $dati['query'];
	parse_str($get_string, $get_array);
	$id = putSerializeRequest($get_array);
	header("Location:index.php?CONT=".$id);*/
	header("Location:".$string);
	header("WI400_HMAC:".$currentHMAC);
	header("WI400_SESSION:".$currentSESSION);
}
function getSerializeRequest($id) {
	$filename = wi400File::getUserFile("request", $id);
	$dati = fileSerialized($filename);
	if (file_exists($filename)) {
	unlink($filename);
		if (isset($dati)) {
			return $dati;
		} else {
			return array();
		}
	} else {
		return array();
	}
}
function putSerializeRequest($request) {
	$id = uniqid("WI", True);
	$filename = wi400File::getUserFile("request", $id);
	put_serialized_file($filename, $request);
	return $id;
}
function curPageURL() {
 $pageURL = 'http';
 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 //  @todo sostituito $_SERVER["SERVER_ADDR"] perchè in caso di NAT non restituisce il nome corretto
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= getServerAddress().":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= getServerAddress().$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

function isIpad(){
	$isIpad = false;
	if(strstr($_SERVER['HTTP_USER_AGENT'],"iPad")){
       $isIpad = true;
	}
	return $isIpad;
}

function isIE(){
	$isIE = false;
	if(isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'],"MSIE")){
       $isIE = true;
	}
	// Verifico se per caso è IE11
	if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false) {
		$isIE = true;
	}
	return $isIE;
}

/*
 * @desc ritorno true se si sta usando un dispositivo mobile o tablet
 * questa funzione è usata nel presentation per settare le variabili di navigazione TABLET
 * 
 */
function isMobile() {
	if(preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|sagem|sharp|sie-|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $_SERVER['HTTP_USER_AGENT']))
		return true;
	else
		return false;
}


/**
 * @desc Restituisce un campo di detail valorizzato da un template
 * 
 * @param $template string: Nome del template da caricare
 * @param $nameField string: Nome della variabile, facoltativo
 * @param $parameters string: Parametri aggiuntivi, facoltativo
 * 
 * @return $myField object: Campo di detail.
 */
function getTemplateField($template, $nameField=null, $parameters=null) {
	
	$file = p13nPackage($template, "templatefield", $parameters);
	if ($file) {
		require_once $file;
		$func = "wi400_template_".$template;
		$myField=$func($nameField);
	}	 
	if (isset($myField)) {
		return $myField;
	} else {
		$myField = new wi400InputText($nameField);
		$myField->setLabel("TEMPLATE_ERROR_".$nameField);
		$myField->setReadonly(true);
		$myField->setValue("*ERROR*");
		return $myField;
	}	
}

function getParamAzione($azione, $parametro) {
	if(!$azione ) {
		die("non hai passato il nome dell'azione");
	}
	if(!$parametro) {
		die("non hai passato il nome del parametro");
	}
	
	global $db;
	
	$sql_1 = "SELECT * FROM ZTABTABE left join ZWIDETPA on TABELLA=WIDAZI AND WIDDOL='P' AND ";
	$sql_2 = " AND ELEMENTO=WIDREQ  
				WHERE TABELLA=? AND ELEMENTO=?";
	
	$utente = strtoupper($_SESSION['user']);
	$stmt_utente = $db->prepareStatement($sql_1."WIDKEY='{$utente}'".$sql_2);
	$stmt_gruppo = $db->prepareStatement($sql_1."WIDKEY in ('".implode("', '", $_SESSION['WI400_GROUPS'])."')".$sql_2);
	$stmt_all = $db->prepareStatement($sql_1."WIDKEY='*ALL'".$sql_2);
	
	$debug = false;
	
	//Utente
	$dati = array();
	$rs = $db->execute($stmt_utente, array($azione, $parametro));
	if($debug) echo "while utente<br/>";
	while($row = $db->fetch_array($stmt_utente)) {
		if($row['WIDDFT']) $dati[] = $row;
		//$dati[] = $row;
	}
	
	//Guppo
	if(!$dati) {
		$rs = $db->execute($stmt_gruppo, array($azione, $parametro));
		if($debug) echo "while gruppo<br/>";
		while($row = $db->fetch_array($stmt_gruppo)) {
			if($row['WIDDFT']) $dati[] = $row;
			//$dati[] = $row;
		}
	}
	
	//*ALL
	if(!$dati) {
		if($debug) echo "while all<br/>";
		$rs = $db->execute($stmt_all, array($azione, $parametro));
		while($row = $db->fetch_array($stmt_all)) {
			$dati[] = $row;
		}
	}
	
	$valori = false;
	if($dati) {
		if($debug) showArray($dati);
		$getValueParam = function ($row) {
			if($row['WIDDFT']) return $row['WIDDFV'];
			
			return $row['DEFAULT'];
		};
		
		//Controllo se è un valore multiplo
		if($dati[0]['MULTI']) {
			$valori = array();
			
			//Restituisco l'array di valori
			foreach($dati as $row) {
				$valori[] = $getValueParam($row);
			}
		}else {
			//Valore singolo
			$valori = $getValueParam($dati[0]);
		}
	}
	
	return $valori;
}

function create_OTM($user = null, $insert=true) {
	if(!$user) {
		developer_debug('Parametro $user nullo in create_OTM');
		return false;
	}else {
		global $users_table, $db;

		$sql = "SELECT USER_NAME FROM SIR_USERS WHERE USER_NAME='".$user."'";
			
		$rs = $db->singleQuery($sql);
		if(!$db->num_rows($rs)) {
			developer_debug("Utente inesistente in create_OTM");
			return false;
		}else {
			$id = uniqid();
			//echo $id."<br/>";
				
			$timestamp = getDb2Timestamp();
			//echo $timestamp."<br/>";
				
			if($insert===true) {
				$sql = "INSERT INTO SIR_OTM(OTMID, OTMUSR) VALUES ('$id', '$user')";
				//$sql = "SELECT * FROM SIR_OTM ";
				$succ = $db->query($sql);
				if($succ) {
					return $id;
				}else {
					developer_debug("Query INSERT create_OTM in errore");
					return false;
				}
			}
			else {
				return $id;
			}
		}
	}
}

function developer_debug($mess) {
	global $settings;

	if(isset($settings['developer_debug']) && $settings['developer_debug']) {
		$mess = "Wi400DevDebug: ".$mess;
		switch($settings['mod_developer_debug']) {
			case 1:
				$errorLogString = $mess;

				if (isset($_REQUEST["t"])){
					$errorLogString .= " [ACTION:".$_REQUEST["t"]."]";
				}
				if (isset($_REQUEST["f"])){
					$errorLogString .= " [FORM:".$_REQUEST["f"]."]";
						
				}
				if (isset($_SESSION['user'])){
					$errorLogString .= " [USER:".$_SESSION['user']."]";
				}

				$errorLogString .= "\r\n";

				foreach(debug_backtrace() as $k=>$v){
					if($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require"){
						$errorLogString .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."] \r\n";
					}else{
						$errorLogString .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']." \r\n";
					}
				}
				error_log($errorLogString);
				break;
			case 2: echo $mess."<br/>";
			break;
			case 3: error_log($mess);
			break;
			default:
				error_log($mess);
				break;
		}
	}
}

// Carica un file PHP in gerarchia di pacchetto
function p13nPackage($packageFile, $path="decodingclass", $parameters=null) {
	global $settings, $base_path, $root_path;
	static $array;
	$p13UserFile ="";
	$p13File ="";
	$fileName="";
	// Patch perchè nella chiamate iniziali di architettura non ho ancora impostato il p13n utente
	if (isset($_SESSION['my_p13n']) && $_SESSION['my_p13n']!="" && $_SESSION['my_p13n']!="default") {
		$settings['p13n_path']= "p13n/".$_SESSION['my_p13n']."/";
	}
	// Fine patch
	$packageFile = strtolower($packageFile);

	if (!isset($array[$packageFile.$path])) {
	// @todo Gestione ricerca personalizzazione per utente ...	
	$fileName = $base_path.'/package/'.$settings['package']."/$path/".$packageFile.".php";
	$p13File = $root_path . $settings['p13n_path'].'base/package/'.$settings['package']."/$path/".$packageFile.".php";
	if (isset($_SESSION['user'])) {
		$p13UserFile = $root_path . $settings['p13n_path'].$_SESSION['user'].'/base/package/'.$settings['package']."/$path/".$packageFile.".php";
	}
    // Se non esiste il file sul pacchetto base significa che cerco sul default   
	if (!is_file($fileName)) {
		$fileName = $base_path.'/package/default/decodingclass/'.$packageFile.".php";
		$p13File = $root_path . $settings['p13n_path']."base/package/default/$path/".$packageFile.".php";
		if (isset($_SESSION['user'])) {
			$p13UserFile = $root_path . $settings['p13n_path'].$_SESSION['user']."/base/package/default/$path/".$packageFile.".php";
		}	
	}
	// Cerco per utente
	//echo $p13UserFile;
	if ($p13UserFile !="" && file_exists ($p13UserFile)) {
		$array[$packageFile.$path]=$p13UserFile;		
		return $p13UserFile;
	} else if (file_exists ($p13File)) {
		$array[$packageFile.$path]=$p13File;		
		return $p13File;		 
	}else {
		if (file_exists ($fileName)) {
			$array[$packageFile.$path]=$fileName;		
			return $fileName;
		}  else {
			$array[$packageFile.$path]= false;			
			return false;
		}
	}
	} else {
		return $array[$packageFile.$path];
	}
}
// Carica un file controllando la personalizzazione
function p13n($fileName) {
	global $settings, $root_path;
	static $array;
	
	if (!isset($array[$fileName])) {
	if (stripos ( $fileName, "/" ) == 0)
		$fileName = substr ( $fileName, 1 );
	// Cerco personalizzazione per utente
	if (isset($_SESSION['user']) && file_exists ( $root_path . $settings['p13n_path'] . $_SESSION['user']."/".$fileName )) {
		$array[$fileName]= $root_path . $settings['p13n_path'] .$_SESSION['user']."/". $fileName;
		//die($root_path . $settings['p13n_path'] . $_SESSION['user']."/". $fileName);
		return $root_path . $settings['p13n_path'] . $_SESSION['user']."/". $fileName;
	}	
	else if (file_exists ( $root_path . $settings['p13n_path'] . $fileName )) {
		$array[$fileName]= $root_path . $settings['p13n_path'] . $fileName;
		return $root_path . $settings['p13n_path'] . $fileName;
	} else {
		if (file_exists ($root_path . $fileName)) {
			$array[$fileName]= $root_path . $fileName;
			return $root_path . $fileName;
		}  else {
			$array[$fileName]= false;
			return false;
		}
	}
	} else {
		return $array[$fileName];
	}
}
/**
 * function checkPassword():
 */
function checkPwd($pwd1, $pwd2) {
	if ($pwd1 == $pwd2)
		return true;
	else
		return false;
}
function loadUserLibraries($userName) {
	$user_libl = $userName;
	if (isset ( $_SESSION ['USER_LIBL'] ) && $_SESSION ['USER_LIBL'] != '') {
		$user_libl = $_SESSION ['USER_LIBL'];
	}
	$_SESSION ["lista_librerie"] = retrive_sysinf ( $user_libl );
	if (! isset ( $_SESSION ['sistema_informativo'] )) {
		$_SESSION ['sistema_informativo'] = retrive_sysinf_name ( $user_libl );
		$start = strpos($_SESSION ['sistema_informativo'], ' ');
		if ($start ===False) {
			$_SESSION ['sysinfname']= $_SESSION ['sistema_informativo'];
		} else {
			$_SESSION ['sysinfname']= substr($_SESSION ['sistema_informativo'],0,$start);
		}
	}
}

function checkFieldEnableOnDetail($curr_azione, $widid, &$field, $widdol, $tipo = "") {
	global $db, $settings;

	$returnValue = null;

	static $stmt_utente, $stmt_gruppo, $stmt_all, $stmt_utente_generale, $stmt_all_generale, $cache_file;
	static $parametri_array = array();

	if(!isset($settings['check_field_enable_on_detail']) || $settings['check_field_enable_on_detail']===true) {

		if (!isset($cache_file)) {
			$cache_file = wi400File::getCommonFile("checkFieldEnabled", $_SESSION['user']."_".session_id());
			$parametri_array = unserialize(file_get_contents($cache_file));
			if ($parametri_array ==null) {
				put_serialized_file($cache_file, array());
				$parametri_array= array();
			}
		}

		if(getType($field) == "object") {
			$id = $field->getId();
			$type = $field->getType();
		}else {
			$id = $field;
			$type = $tipo;
		}

		$chiave_cache = $curr_azione."|".$widid."|".$id."|".$widdol."|".$type;

		if(!isset($parametri_array[$chiave_cache])) {
			if (!isset($stmt_utente) || !isset($stmt_gruppo) || !isset($stmt_all) || !isset($stmt_all_generale)) {
				$sql = "select * from ZWIDETPA where WIDAZI='$curr_azione' and WIDID=? and ";
				$stmt_utente = $db->prepareStatement($sql."WIDKEY='{$_SESSION['user']}'");
				$stmt_gruppo = $db->prepareStatement($sql."WIDKEY in ('".implode("', '", $_SESSION['WI400_GROUPS'])."')");
				$stmt_all = $db->prepareStatement($sql."WIDKEY='*ALL'");

				$sql = "select * from ZWIDETPA where WIDAZI=? ";
				$stmt_utente_generale = $db->prepareStatement($sql." and WIDKEY='{$_SESSION['user']}'");
				$stmt_all_generale = $db->prepareStatement($sql." and WIDKEY='*ALL'");
			}
				
			$checkValori = function ($row, &$campo) {
				if($row['WIDSTA']) {
					$isObject = getType($campo) == "object" ? true : false;
					$isDetail = $row['WIDDOL'] == 'D' ? true : false;
					if($row['WIDHID'] && $isDetail) { //Nascondo campi del dettaglio
						$default_value = $row['WIDDFT'] ? $row['WIDDFV'] : null;
						return array('hide', $default_value);
					}else if(!$row['WIDABI']) {
						if($isObject) {
							if(in_array($campo->getType(), array("BUTTON", "FILE"))) {
								return "disabled"; //Detail buttoni e input file
							}else {
								if($row['WIDDFT']) {
									return array("readonly", $row['WIDDFV']); //Detail campi input
								}else {
									return array("readonly", null);
								}
							}
						}else {
							return false;
						}
					}else if($row['WIDABI'] && $row['WIDHID'] && !$isDetail) { //Colonne nascoste
						if($row['WIDDFT']) {
							return array("hide",  $row['WIDDFV'], $row['WIDSEQ']);
						}else {
							return array("hide", null, $row['WIDSEQ']);
						}
					}else if($row['WIDDFT']) { //Controllo se è abilitato il flag "abilita default"
						return array(true, $row['WIDDFV'], $row['WIDSEQ'], $row['WIDFIL']);
					}else {
						//Tutto è andato bene
						if(!$isObject) {
							return array(true, null, $row['WIDSEQ'], $row['WIDFIL']); //se si tratta di un parametro da settare. (true => abilitato, null => default value non presente)
						}
					}
				}
				return true;
			};
				
			$all_azione = $widdol == "D" ? "*ALL_DETAIL" : "*ALL_LISTA";
			
			$rowAbil = array();
			
			$searchDati = function($stmt_prepare, $widid, $checkValori) {
				global $db;
				
				$dati = array();
				
				$rs = $db->execute($stmt_prepare, array($widid));
				while($row = $db->fetch_array($stmt_prepare)) {
					//echo "utente<br/>";
					$chiave = $row['WIDAZI']."|".$row['WIDID']."|".$row['WIDREQ']."|".$row['WIDDOL']."|".$row['WIDTYP'];
					
					if(!in_array($row['WIDTYP'], array('TOOL', 'ACTION', 'COLUMN'))) {
						$obj = new wi400Text($row['WIDREQ']);
						$obj->setType($row['WIDTYP']);
					}else {
						$obj = $row['WIDREQ'];
					}
					
					$returnValue = $checkValori($row, $obj);
					
					$dati[$chiave] = $returnValue;
				}
				
				return $dati;
			};
			
			$array_stmt = array(
				array($stmt_utente, $widid),
				array($stmt_gruppo, $widid),
				array($stmt_all, $widid),
				array($stmt_all_generale, $all_azione),
				array($stmt_all_generale, $all_azione),
			);
			foreach($array_stmt as $val) {
				$rowAbil = $searchDati($val[0], $val[1], $checkValori);
				//showArray($rowAbil);
				
				if($rowAbil) break;
			}
				
			//Esiste questa volta????
			if(!isset($rowAbil[$chiave_cache])) {
				//developer_debug("Abilitazioni campi: $chiave_cache!");
				
				//echo "non trovo neanche la seconda volta $field<br/>";
				$rowAbil[$chiave_cache] = in_array($field, array("Nascondi lista", "Nascondi detail")) ? false : true;
			}
			
			$returnValue = $rowAbil[$chiave_cache];
			
			/*echo "parametri array";
			showArray($parametri_array);*/
			
			$parametri_array = array_merge($parametri_array, $rowAbil);
			put_serialized_file($cache_file, $parametri_array);
		}else {
			//echo $chiave_cache."___cache1<br/>";
			/*showArray($parametri_array[$chiave_cache]);
			if(is_array($parametri_array[$chiave_cache])) {
				echo "is_array<br/>";
				showArray($parametri_array[$chiave_cache]);
			}else if(is_bool($parametri_array[$chiave_cache])) {
				echo "is_bool<br/>";
				echo $parametri_array[$chiave_cache] ? 'trueee' : 'falseee';
				echo "<br/>"; 
			}else {
				echo gettype($parametri_array[$chiave_cache])."__<br/>";
			}*/
			$returnValue = $parametri_array[$chiave_cache];
		}
	}else {
		$returnValue = true;
	}

	return $returnValue;
}

function checkUser($name, $pwd) {
	global $settings, $db, $users_table, $messageContext, $base_path, $wi400_sel_groups;
	
	$metodo = $settings['auth_method'];
	
	// Verifico se l'utente che mi hanno passato ha una metodo di autorizzazione diverso
	// Verifico se l'utente è presente su DB
	//$sql = "select * from " . $users_table . " where user_name='" . strtoupper ( $name ) . "'";
	//$result = $db->singleQuery ( $sql );
	// Verifico se l'utente è presente su DB
	// @todo attenzione che va in errore se utente più lungo della dimensione prevista su DB
	$sql = "select * from " . $users_table . " where user_name=?";
	$result = $db->singlePrepare( $sql );
	$do = $db->execute($result, array(strtoupper( $name )));
	if($do)
		$row = $db->fetch_array ( $result );
	// Se l'utente non è codificato in tabella WI400 e non sono previsti utenti esterni esco    
//	if (! $row && $settings['only_wi400_user']) {
	if ((!isset($row) || empty($row)) && $settings['only_wi400_user']) {
		if(isset($messageContext) && !empty($messageContext))
			$messageContext->addMessage ( "ERROR", "Utente non abilitato per accesso ad architettura WI400", false );
		return false;
	} else {
		if (($row ['AUTH_METOD'] != $metodo) && ($row ['AUTH_METOD'] != '*DEFAULT') && ($row ['AUTH_METOD'] != '')) {
			$metodo = $row ['AUTH_METOD'];
		}
		$_SESSION ['USER_LIBL'] = $row ['USER_GROUP'];
	}
	// iNIZIALIZZO L'ARRAY DEI GRUPPI
	$_SESSION ['WI400_GROUPS']=array();
	if (isset ( $row ['WI400_GROUPS'] ) && $row ['WI400_GROUPS'] != "") {
		// Questo valore sarà sovrascritto in caso di autorizzazione con LDAP
		$_SESSION ['WI400_GROUPS'] = explode ( ";", $row ['WI400_GROUPS'] );
		// Salvo gli originali per ulteriori controlli nel caso vengano sovrascritti da LDAP
		$_SESSION ['WI400_GROUPS_BACKUP'] =  explode ( ";", $row ['WI400_GROUPS'] );
	}
	else {
		$_SESSION ['WI400_GROUPS'] = $wi400_sel_groups;
	}
//	echo "WI400_GROUPS:<pre>"; print_r($_SESSION['WI400_GROUPS']); echo "</pre>";die();
	
	$_SESSION ['AUTH_METOD'] = $metodo;
	$stringa = 'checkUser'.$metodo;
	require_once $base_path."/checkuser/checkUser$metodo.php";
	$check = new $stringa();
	// Merge dei gruppi se è cambiato qualcosa
	if (isset($_SESSION ['WI400_GROUPS_BACKUP'])) {
		$_SESSION['WI400_GROUPS'] = array_unique(array_merge($_SESSION ['WI400_GROUPS_BACKUP'],$_SESSION['WI400_GROUPS']), SORT_REGULAR);
	}
	return $check->checkUser($name, $pwd);
	
	return false;
}
// Recupero un array con le informazioni sull'utente        
function rtvUserInfo($user) {
	global $db, $users_table, $defualt_language;
	static $stmtUser;
	$menu = array ();
	// Recupero il menu associato all'utente
	if (!isset($stmtUser)) {
		$sql = "select MENU, USER_GROUP, USER_MENU, TIME_OFFSET, LANGUAGE, DEFAULT_ACTION, THEME, OFFICE, PACKAGE, ADMIN, WI400_GROUPS from " . $users_table . " where user_name=?";
		$stmtUser = $db->singlePrepare($sql);
	}
	$resultusr = $db->execute($stmtUser, array("user_name"=>$user));
	$userrow = $db->fetch_array ( $stmtUser );
	if (! $user) {
		return null;
	}
	if (!isset($userrow['TIME_OFFSET']))
		$userrow ['TIME_OFFSET'] = 0;
	
	return $userrow;
}
function isDeveloper($abilitaEscape=True){
	global $moduli_path;
	$abilitazioni = p13n("/modules/developer/abilitazioni.php");
	$users = parse_ini_file($abilitazioni);
	if (isset($_SESSION['user']) && in_array($_SESSION['user'], $users)) {
		return True;
	}
	if (isset($_SESSION['WI400_GROUPS'])) {
		if (in_array("DEVELOPER", $_SESSION['WI400_GROUPS'])) {
			return True;
		}
	}
	if (isset($settings['abilita_developer_all']) && $settings['abilita_developer_all']==True) {
		return True;
	}
	return false;
}
function getPreferitiHtml(){
	global $temaDir, $appBase;
	$htmlCode = "";
	
	$preferitiFile = wi400File::getUserFile("config", "preferiti.txt");
	
	$preferitiArray = wi400ConfigManager::readConfig('preferiti', 'preferiti', '', $preferitiFile);
	
	if($preferitiArray && sizeof($preferitiArray) > 0){
		$htmlCode.= "<table>";
		foreach ($preferitiArray as $actionObj){
			$htmlCode.= "<tr>";
			$htmlCode.= "<td><img style='cursor:pointer' onClick='doPreferiti(\"REMOVE\", \"left_menu_content_0\", \"".$actionObj["ACTION"]."\")' src='".$temaDir."images/remove.png' title='"._t('REMOVE')."'></td>";
			$htmlCode.= "<td class='left-menu-content-text'>";
			if($actionObj["ACTION"] == "LOG_ERROR") {
				$htmlCode .= "<a href=\"javascript:openWindow('".$appBase."index.php?t=OPEN_LINK&LINK=LOG_ERROR&TYPE=popup&WIDTH=90000&HEIGHT=600', '', '', '', false)\">".$actionObj["LABEL"]."</a></td>";
			}else {
				$htmlCode.= "<a href=\"javascript:doSubmit('".$actionObj["ACTION"]."&LCK_DLT=true','".$actionObj["FORM"]."', false, true)\">".$actionObj["LABEL"]."</a></td>";
			}
			$htmlCode.= "</tr>";
		}
		$htmlCode.= "</table>";
	}else {
		//echo "non c'è niente";
	}

   	/*if (file_exists($preferitiFile)) {
   		// Caricamento array da file serializzato
		$handle = fopen($preferitiFile, "r");
		$contents = "";
		while (!feof($handle)) {
			$contents.= fgets($handle, 4096);
		} 
		fclose($handle);
		$preferitiArray = unserialize($contents);
		if ($preferitiArray && sizeof($preferitiArray) > 0){
			$htmlCode.= "<table>";
			foreach ($preferitiArray as $actionObj){
				$htmlCode.= "<tr>";
				$htmlCode.= "<td><img style='cursor:pointer' onClick='doPreferiti(\"REMOVE\", \"left_menu_content_0\", \"".$actionObj["ACTION"]."\")' src='".$temaDir."images/remove.png' title='"._t('REMOVE')."'></td>";
				$htmlCode.= "<td class='left-menu-content-text'>";
				if($actionObj["ACTION"] == "LOG_ERROR") {
					$htmlCode .= "<a href=\"javascript:openWindow('".$appBase."index.php?t=OPEN_LINK&LINK=LOG_ERROR&TYPE=popup&WIDTH=90000&HEIGHT=600', '', '', '', false)\">".$actionObj["LABEL"]."</a></td>";
				}else {
					$htmlCode.= "<a href=\"javascript:doSubmit('".$actionObj["ACTION"]."&LCK_DLT=true','".$actionObj["FORM"]."', false, true)\">".$actionObj["LABEL"]."</a></td>";
				}
				$htmlCode.= "</tr>";
			}
			$htmlCode.= "</table>";
		}
   	}*/

   	return $htmlCode;
}

// Ritorno il menu          
function rtvMenu($codmenu) {
	global $db, $AS400_menu, $settings;
	
	static $stmt;

	if (! isset ( $stmt )) {
		$mylanguage = $_SESSION['USER_LANGUAGE'];
		if (isset($_SESSION['CUSTOM_LANGUAGE'])) {
			$mylanguage = $_SESSION['CUSTOM_LANGUAGE'];
		}
		$extra_language = getLanguageID($mylanguage);
		if (isset($settings['multi_language']) && $settings['multi_language']==True && $extra_language) {
			$sql = "select * from " . $AS400_menu . " left join FLNGTRST ON MENU=KEY AND LANG='".
			$extra_language."' AND ARGO='MENU' where MENU=?";
		} else {
			$sql = "select * from " . $AS400_menu . " where MENU=?";
		}
		$sql = $db->escapeSpecialKey($sql);
		$stmt = $db->prepareStatement ( $sql );
	}
	//$result = $db->singleQuery($sql);
	$result = $db->execute ( $stmt, array (strtoupper ( $codmenu ) ) );
	$row = $db->fetch_array ( $stmt, null, True );
	if (! $row) {
		return False;
	}
	// Stringa in lingua
	if (isset($row['STRING']) && $row['STRING']!="") {
		$row['DESCRIZIONE'] = $row['STRING'];
	}

	return $row;
}


// Conta le azioni di un determinato menu
function countMenu($codmenu, $stmt = ""){
	global $AS400_azioni, $db;
	$menu = rtvMenu ( $codmenu );
	$azioniMenu = array();
	if ($stmt == "") {
		$sql = "select * from " . $AS400_azioni . " where AZIONE=?";
		$stmt = $db->prepareStatement ( $sql );
	}
	if ($menu) {
		$azioniMenu = explode ( ";", $menu ['AZIONI'] );
	}
	return sizeof($azioniMenu);
}

// Esplodo il menu con la azioni presenti
function explodoMenu($codmenu, $stmt = "") {
	global $AS400_azioni, $db, $settings;
	static $countItem;
	if (isset($countItem)) {
		$countItem=0;
	}
	$menu = rtvMenu ( $codmenu );

	if ($stmt == "") {
		$mylanguage = $_SESSION['USER_LANGUAGE'];
		if (isset($_SESSION['CUSTOM_LANGUAGE'])) {
			$mylanguage = $_SESSION['CUSTOM_LANGUAGE'];
		}
		$extra_language = getLanguageID($mylanguage);
		if (isset($settings['multi_language']) && $settings['multi_language']==True && $extra_language) {
			$sql = "select * from " . $AS400_azioni . " left join FLNGTRST ON AZIONE=KEY AND LANG='".$extra_language."' AND ARGO='AZIONI' where AZIONE=?";
			$sql = $db->escapeSpecialKey($sql);
			$stmt = $db->prepareStatement ( $sql );
		} else {
			$sql = "select * from " . $AS400_azioni . " where AZIONE=?";
			$stmt = $db->prepareStatement ( $sql );
		}

	}
	// Verifico se e' un menu
	if ($menu) {
	    $testo = "<b>".$menu['DESCRIZIONE']."<\/b>";
		$node = new HTML_TreeNode ( array ('text' => $testo, 'icon' => dfti ( $menu ['ICOMENU'] ), 'link' => dftl ( $menu ['AZIONE'] ), 'expandedIcon' => dfte ( $menu ['EXPICO'] ) ) );
		//	Leggo le azioni del menu
		$componenti = array ();
		$componenti = explode ( ";", $menu ['AZIONI'] );
		foreach ( $componenti as $k => $v ) {
			// verifico se c'è un form particolare
			$arrayAzione = explode(":", $v);
			$azione = $arrayAzione[0];
			$des_agg = "";
			$form="";
			if (isset($arrayAzione[1])) {
				$des_agg = " ".str_replace("_", " ", $arrayAzione[1]);
				$form = $arrayAzione[1]; 
			}
			$result = $db->execute ( $stmt, array (trim ( $azione ) ) );
			$azioni = $db->fetch_array ( $stmt, Null, True );
			
			// Testo il tipo per evitare possibili dump o costruzioni anomale del menu
			if ($azioni['TIPO'] == "A" || $azioni['TIPO'] == "M" || $azioni['TIPO'] == "L" || $azioni['TIPO'] == "T" || $azioni['TIPO'] == 'G') {
				if ($azioni ['TIPO'] == "A" || $azioni['TIPO'] == "L" || $azioni['TIPO'] == "T" || $azioni['TIPO'] == 'G') {
					$options = array ();
					$script = array ();
					$des_azione = $azioni['DESCRIZIONE'];
					if (isset($azioni['STRING']) && $azioni['STRING']!="") {
						$des_azione = $azioni['STRING'];
					}
					//$options ['text'] = prepare_string($des_azione). $des_agg;
					$options ['text'] = $des_azione.$des_agg;
					$options ['expandedIcon'] = dfte ( $azioni ['EXPICO'] );
// 					$options ['azione'] = $azioni ['AZIONE'];
					// Salvo tracciato azione in sessione per ottimizzazione accessi DB
					if (! isset ( $_SESSION ['LIST_ACTION'] [$azioni ['AZIONE']] )) {
						$_SESSION ['LIST_ACTION'] [$azioni ['AZIONE']] = $azioni;
					}
					// Salvo nell'array l'azione abilitata
					$_SESSION ["auth_action"] [] = $azioni ['AZIONE'];
					
					if($azioni['TIPO'] == "T") {
						$azione5250 = rtvAzione5250($azioni['AZIONE']);
						
						$azioni['AZIONE'] = "TELNET_5250&g=FROM_MENU&AZIONE_5250=".$azioni['AZIONE'];
					}else if($azioni['TIPO'] == 'G') {
						//$azione5250 = rtvAzione5250($azioni['AZIONE']);
						
						$azioni['AZIONE'] = "CONSOLE_GIWI400&g=FROM_MENU&AZIONE_5250=".$azioni['AZIONE'];
					}
					
					// Controllo se l'azione è abilitata -- DISABILITATO PER IL MOMENTO
					/*$check = chkUserAzione ( $azioni );
					if ($check->flg == "0") {
						$options ['link'] = dftl ( $azioni ['AZIONE'] );
					} else if ($check->flg == '2') {
						$options ['icon'] = "lockoverlay.png";
						$script ['onclick'] = "alert('" . $check->msg . "'); return false";
					} else
						continue;*/
					$options ['link'] = dftl($azioni['AZIONE'], $form, $azioni['URL_OPEN'], $azioni['URL_MODAL']);
					
					if($azioni['TIPO'] == 'T' && $azione5250['CONFERMA']) {
						$options['link'] = "wi400_conferma_azione_5250(\'".base64_encode($options['link'])."\');";
					}
					//$addnode = &new HTML_TreeNode(array('text' => $azioni['DESCRIZIONE'], 'link' => dftl($azioni['AZIONE']), 'expandedIcon' => dfte($azioni['EXPICO'])));
					$addnode = new HTML_TreeNode ( $options, $script );
					$countItem++;
					if ($countItem>9999) {
						die("ERRRORE RICORSIVITA' NEI MENU! CONTATTARE ASSISTENZA");
					}
				}else {
					$addnode = explodoMenu ( $azioni ['AZIONE'], $stmt );
				}
				$node->addItem ( $addnode );
			}
		}
		
		//db2_free_stmt ( $result );
		return $node;
	}
}
function chkUserAzione($azioni) {
	global $routine_path;
	
	require_once "$routine_path/classi/wi400CheckPgm.cls.php";
	$test = new checkPgm ( );
	
	if (trim ( $azioni ['CHKPGM'] ) != "") {
		$test = new checkPgm ( );
		$pgm = trim ( $azioni ['CHKPGM'] );
		if (method_exists ( $test, trim ( $azioni ['CHKPGM'] ) )) {
			$test->$pgm ();
		}
	}
	return $test;
}
function dfti($icona) {
	// Default
	$icon = 'folder.gif';
	if (empty ( $icona ))
		return $icon;
	else
		return $icona;
}
function dfte($icona) {
	// Default
	$expandedIcon = 'folder-expanded.gif';
	if (empty ( $icona ))
		return $expandedIcon;
	else
		return $icona;
}
function dftl($link, $form="", $target="", $modale='1') {
	global $appBase;
	// Default
	if (substr ( $link, 0, 4 ) == 'http')
		return $link;
	
	//error_log($link."___alberto");
	if($target == "_self" || !$target) {
		$pagina = "doSubmit(\'".$link."&LCK_DLT=true\', \'".$form."\', false, true)";
	}else {
		$modale = $modale == '1' ? "true" : "false";
		$typeWin = "";
		if($target == "_popup") 
			$typeWin = "&TYPE=popup";
		$pagina = "openWindow(\'".$appBase."index.php?t=OPEN_LINK&LINK=".$link.$typeWin."\', \'\', \'\', \'\', $modale)";
	}
	return $pagina;
}
function rtvAzione($actionName, $batch=False) {
	global $db, $AS400_azioni, $messageContext, $appBase, $settings, $wi400Lang, $language, $wi400ModuleLang;

	$actionArray = explode(":", $actionName);
	
	// file di log
	$file_error_path = get_log_file_path("LOG_ERROR");
	
	if(!file_exists($file_error_path)) {
		wi400_mkdir($file_error_path, 777, true);
	}
	
	$file_error_name = get_log_file_name("LOG_ERROR");
	
	$file_log = $file_error_path.$file_error_name;
	
	$log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] ";

	$actionName = $actionArray[0];
	if (! isset ( $_SESSION ['LIST_ACTION'] [$actionName] )) {
		//
		//$sql = "select * from " . $AS400_azioni . " where AZIONE='" . strtoupper ( $actionName ) . "'";
		$mylanguage = "";
		if (isset($_SESSION['USER_LANGUAGE'])) {
			$mylanguage = $_SESSION['USER_LANGUAGE'];			
		}
		if (isset($_SESSION['CUSTOM_LANGUAGE'])) {
			$mylanguage = $_SESSION['CUSTOM_LANGUAGE'];
		}
		$extra_language = getLanguageID($mylanguage);
		if (isset($settings['multi_language']) && $settings['multi_language']==True && $extra_language) {
			//$sql = "select * from " . $AS400_azioni . " left join FLNGTRST ON AZIONE=KEY AND LANG='".$extra_language."' AND ARGO='AZIONI' where AZIONE='" . strtoupper ( $actionName ) . "'";
			$sql = "select * from " . $AS400_azioni . " left join FLNGTRST ON AZIONE=KEY AND LANG='".$extra_language."' AND ARGO='AZIONI' where AZIONE=?";
			$sql = $db->escapeSpecialKey($sql);
			$result = $db->singlePrepare($sql);
			$do = $db->execute($result, array(strtoupper ( $actionName )));
		} else {
			//$sql = "select * from " . $AS400_azioni . " where AZIONE='" . strtoupper ( $actionName ) . "'";
			$sql = "select * from " . $AS400_azioni . " where AZIONE= ?";
			$result = $db->singlePrepare($sql);
			$do = $db->execute($result, array(strtoupper ( $actionName )));
		}		
		//$result = $db->singleQuery ( $sql );
		$row = $db->fetch_array ( $result, Null, True );
		
		// Descrizione Wizard
		if (count($actionArray)>1){
			if (!isset($row["DESCRIZIONE"])){
				$row["DESCRIZIONE"] = $row["DESCRIZIONE"]." ".$actionArray[1];
			}
		}
		
		$_SESSION ['LIST_ACTION'] [$actionName] = $row;
		//db2_free_result ( $result );
	} else {
		$row = $_SESSION ['LIST_ACTION'] [$actionName];
	}
	if (!$row) {
		// Azione non trovata
		
		$log_msg .= "Azione ".$actionName." inesistente.\r\n";
		
		// fopen() deve essere impostato ad "a" per scrivere sul file senza però riscrivere la stessa riga
		$log_handle = fopen($file_log, "a");
		fwrite($log_handle, $log_msg);
		fclose($log_handle);
		
		return false;
	}
	// Controllo se l'azione esiste e, nel caso esista, se i file indicati sono presenti su server
	$chkActionAuth = true;
	if (! isset ( $row ['MODULO'] )) {
		$chkActionAuth = false;
	} else {
		$viewFile = p13n ( "/modules/" . $row ['MODULO'] . "/" . $row ['VIEW'] );
		$modelFile = p13n ( "/modules/" . $row ['MODULO'] . "/" . $row ['MODEL'] );
		if (! $row || ($row ['VIEW'] != "" && ! file_exists ( $viewFile )) || ($row ['MODEL'] != "" && ! file_exists ( $modelFile ))) {
			$chkActionAuth = false;
		} else if (isset ( $_SESSION ["auth_action"] ) && $row ['TIPO'] == "A" && $row['SYSTEM']!="1" && in_array ( strtoupper ( $actionName ), $_SESSION ["auth_action"] ) == "") {
			$chkActionAuth = false;
		}
	}
	
	
	// Se controllo autorizzazione primo livello andata a buon fine..
	if ($chkActionAuth) {
		
		// Se definiti recupero gruppi autorizzati
		if ($row ['WI400_GROUPS'] != "" && ((isset($_SESSION['WI400_GROUPS']) && count($_SESSION['WI400_GROUPS'])>0) || isset($_SESSION ["WI400_GROUPS_BACKUP"]))) {
			// Gruppi di appartenenza dell'utente loggato
			$userGroups = array ();
			if (isset($_SESSION ["WI400_GROUPS"]) && $_SESSION["WI400_GROUPS"]!="") {
			$userGroups = $_SESSION ["WI400_GROUPS"];
			// Attacco i gruppi impostasti da gestione utenti AS400
			if (isset($_SESSION['WI400_GROUPS_BACKUP'])) {
				$userGroups =array_merge($userGroups, $_SESSION['WI400_GROUPS_BACKUP']);
			}
			// Gruppi autorizzati all'esecuzione dell'azione
			$authGroups = explode ( ";", $row ["WI400_GROUPS"] );
			$chkActionAuth = false;
			foreach ( $authGroups as $groupName ) {
				if (in_array ( $groupName, $userGroups )) {
					$chkActionAuth = true;
					break;
				}
			}
			}
		}
	
	}
	
	if (! $chkActionAuth) {
		$log_msg .= "Azione ".$actionName." inesistente o non applicabile all'utente.\r\n";
		
		// fopen() deve essere impostato ad "a" per scrivere sul file senza però riscrivere la stessa riga
		$log_handle = fopen($file_log, "a");
		fwrite($log_handle, $log_msg);
		fclose($log_handle);
		if ($batch==True) {
			$messageContext->addMessage ( "ERROR", $actionName . " inesistente o non applicabile all'utente." );
			// Elimino eventuale wizard
			sessionUnregister("WIZARD");
			$nextUrl = $appBase . "index.php";
			header ( "Location:" . $nextUrl );
			exit ();
		} else {
			return false;
		}
	}
	//db2_free_result($result);
	//$lic = chkLicenza ( strtoupper ( $row ["MODULO"] ) );
	// Loading modules Language
	if (isset($settings['language_tree'])) {
			// Package language
			if ($settings['language_tree']>=3) {
				$module = $row['MODULO'];
				$path = "/modules/$module/lang/$language.lang.php";
				$lang_package=p13n($path);
				if ($lang_package) {
						require_once "$lang_package";
						$wi400Lang = array_merge($wi400Lang, $wi400ModuleLang);				
				}
			}
		}
	return $row;
}
function getPrivatePoolingId($DTAQKey, $timeout=300) {
	global $settings;
	
	$msgtype_receive=1;
	$maxsize=1000;
	$message='';
	$serialize_needed=True;
	$block_send=false;
	$msgtype_send=1;
	$option_receive=MSG_IPC_NOWAIT;
	$seg = msg_get_queue($DTAQKey);
	$queue_status=msg_stat_queue($seg);
	$time_start = microtime(true);
	if (isset($settings['i5_toolkit'])) {
		$privateId=0;
	} else {
		$privateId=uniqid("POOL_");
	}
	// Controllo se in coda c'è già qualcosa
	if ($queue_status['msg_qnum']>0) {
		// Cerco una connessione non scaduta
		$i=0;
		for ($i; $i<=$queue_status['msg_qnum']; $i++){
			if (msg_receive($seg,$msgtype_receive ,$msgtype_erhalten,$maxsize,$daten,$serialize_needed, $option_receive, $err)===true) {
				$valori = explode("/", $daten);
				$diff = time() - $valori[1];
				if ($diff < 300) {
					//$this->privateId=intval($valori[0]);
					if (isset($settings['i5_toolkit'])) {
						$privateId=intval($valori[0]);
					} else {
						$privateId = $valori[0];
					}
					break;
				}
			} else {
				break;
			}
		}
	}
	return $privateId;
}
function rtvAzione5250($actionName) {
	global $db;
	
	static $stmt_azi5250;

	if(!isset($stmt_azi5250)) {
		$sql_5250 = "SELECT * FROM FAZI5250 WHERE AZIONEWI=?";
		$stmt_azi5250 = $db->singlePrepare($sql_5250,0,true);
	}
	
	$r = $db->execute($stmt_azi5250, array($actionName));
	$azione5250 = $db->fetch_array($stmt_azi5250);
	
	return $azione5250;
}

function rtvModuloAzione($actionName) {
	global $db, $AS400_azioni, $messageContext, $appBase, $settings, $wi400Lang, $language, $wi400ModuleLang;

	$actionArray = explode(":", $actionName);

	$actionName = $actionArray[0];
	if (! isset ( $_SESSION ['LIST_ACTION'] [$actionName] )) {
		//
		//$sql = "select * from " . $AS400_azioni . " where AZIONE='" . strtoupper ( $actionName ) . "'";
		$mylanguage = "";
		if (isset($_SESSION['USER_LANGUAGE'])) {
			$mylanguage = $_SESSION['USER_LANGUAGE'];
		}
		if (isset($_SESSION['CUSTOM_LANGUAGE'])) {
			$mylanguage = $_SESSION['CUSTOM_LANGUAGE'];
		}
		$extra_language = getLanguageID($mylanguage);
		if (isset($settings['multi_language']) && $settings['multi_language']==True && $extra_language) {
			$sql = "select * from " . $AS400_azioni . " left join FLNGTRST ON AZIONE=KEY AND LANG='".$extra_language."' AND ARGO='AZIONI' where AZIONE='" . strtoupper ( $actionName ) . "'";
		} else {
			$sql = "select * from " . $AS400_azioni . " where AZIONE='" . strtoupper ( $actionName ) . "'";
		}
		$sql = $db->escapeSpecialKey($sql);
		$result = $db->singleQuery ( $sql );
		$row = $db->fetch_array ( $result, Null, True );

		// Descrizione Wizard
		if (count($actionArray)>1){
			if (!isset($row["DESCRIZIONE"])){
				$row["DESCRIZIONE"] = $row["DESCRIZIONE"]." ".$actionArray[1];
			}
		}

		$_SESSION ['LIST_ACTION'] [$actionName] = $row;
		//db2_free_result ( $result );
	} else {
		$row = $_SESSION ['LIST_ACTION'] [$actionName];
	}
	if (!$row) return false;
	// Controllo se l'azione esiste e, nel caso esista, se i file indicati sono presenti su server
	$chkActionAuth = true;
	if (! isset ( $row ['MODULO'] )) {
		$chkActionAuth = false;
	} else {
		$viewFile = p13n ( "/modules/" . $row ['MODULO'] . "/" . $row ['VIEW'] );
		$modelFile = p13n ( "/modules/" . $row ['MODULO'] . "/" . $row ['MODEL'] );
		if (! $row || ($row ['VIEW'] != "" && ! file_exists ( $viewFile )) || ($row ['MODEL'] != "" && ! file_exists ( $modelFile ))) {
			$chkActionAuth = false;
		} else if (isset ( $_SESSION ["auth_action"] ) && $row ['TIPO'] == "A" && in_array ( strtoupper ( $actionName ), $_SESSION ["auth_action"] ) == "") {
				
			//$chkActionAuth = false;

		}
	}

	// Se controllo autorizzazione primo livello andata a buon fine..
	if ($chkActionAuth) {

		// Se definiti recupero gruppi autorizzati
		if ($row ['WI400_GROUPS'] != "") {
				
			// Gruppi di appartenenza dell'utente loggato
			$userGroups = array ();
			if (isset($_SESSION ["WI400_GROUPS"]) && $_SESSION["WI400_GROUPS"]!="") {
				$userGroups = $_SESSION ["WI400_GROUPS"];
				// Gruppi autorizzati all'esecuzione dell'azione
				$authGroups = explode ( ";", $row ["WI400_GROUPS"] );
				$chkActionAuth = false;
				foreach ( $authGroups as $groupName ) {
					if (in_array ( $groupName, $userGroups )) {
						$chkActionAuth = true;
						break;
					}
				}
			}
		}

	}

	if (! $chkActionAuth) {
		$messageContext->addMessage ( "ERROR", $actionName . " inesistente o non applicabile all'utente." );
		// Elimino eventuale wizard
		sessionUnregister("WIZARD");
		$nextUrl = $appBase . "index.php";
		header ( "Location:" . $nextUrl );
		exit ();
	}
/*	
	//db2_free_result($result);
	//$lic = chkLicenza ( strtoupper ( $row ["MODULO"] ) );
	// Loading modules Language
	if (isset($settings['language_tree'])) {
		// Package language
		if ($settings['language_tree']>=3) {
			$module = $row['MODULO'];
			$path = "/modules/$module/lang/$language.lang.php";
			$lang_package=p13n($path);
			if ($lang_package) {
				require_once "$lang_package";
				$wi400Lang = array_merge($wi400Lang, $wi400ModuleLang);
			}
		}
	}
*/
	return $row['MODULO'];
}

function posto_string($zona,$corridoio,$bay,$post,$separator="-") {
	return $zona.$separator.$corridoio.$separator.$bay.$separator.$post;
}

function getMD5() {
	global $settings, $pass;
	if (isset ( $_POST ['password'] )) {
		if ($settings['auth_method'] == "LDAP" || "AD")
			$pass = $_POST ['password'];
		if ($settings['auth_method'] == "DB")
			$pass = md5 ( $_POST ['password'] );
	}
	return $pass;
}
function setTimeOffset($name) {
	global $db, $users_table;
	
	$field = array ("time_offset" );
	$key = array ("user_name" => $name );
	$stmt = $db->prepare ( "UPDATE", $users_table, $key, $field );
	$campi = array (time () );
	$result = $db->execute ( $stmt, $campi );

}
/**
 * Inizia a loccare 
 * Riceve come input una chiave di lock e scrive un record con la sessione. Riceve in input
 * un boolena per comunicare che con la chiamata si vuole anche allocare la chiave.
 * 
 * Ritorna i seguenti valori
 * 0 chiave allocabile o allocazione effettuata
 * 1 chiave già allocata dall sessione corrente
 * 2 chiave allocata da un'altra sessione
 * 3 chiave non allocata in modo esclusivo
 * 4 errore nel reperimento dei valori di lock o nell'esecuzione del lock
 */
function startLock($context, $key, $lock = True, $type = 'E') {
	global $db, $messageContext;
	
	// Verifico se esiste già un lock sulla chiave
	$lockSqlKey = "";
	if ($key != "") {
		$lockSqlKey = $lockSqlKey . " AND LOCKKEY='" . $key . "' ";
	}
	if ($context != "") {
		$lockSqlKey = $lockSqlKey . " AND LOCKCON='" . $context . "' ";
	}
	
	$sql = "SELECT * FROM TABLOCK WHERE LOCKSTA = '1' " . $lockSqlKey. " ORDER BY LOCKTIM DESC";
	$result = $db->query ( $sql );
	// Leggo il primo lock dispomibile
	$row = $db->fetch_array ( $result );
	// Se non ho letto niente la chiave è allocabile
	if (! $row) {
		// Verifico se devo allocare il record
		if ($lock) {
			$field = array ("LOCKSES", "LOCKCON", "LOCKKEY", "LOCKUSR", "LOCKTIM", "LOCKSTA", "LOCKTYP" );
			$stmt = $db->prepare ( "INSERT", "TABLOCK", Null, $field );
			$campi = array (session_id (), $context, $key, $_SESSION ['user'], getDb2Timestamp (), "1", $type );
			$result = $db->execute ( $stmt, $campi );
			if ($result) {
				return '0';
			} else {
				return '4';
			}
		} else {
			return '0';
		}
	} else {
		if ($row ['LOCKSES'] == session_id ()) {
			return '1';
		} else {
			
			/*
			 * TODO: CALCOLARE SECONDI PASSATI DAL LOCK PER ELIMINARE IL LOCK SE MAGGIORE DI N SECONDI
			 * 
			*/
			$lockArray = ExtractDateTimeByFormat ( $row ['LOCKTIM'], "Y-m-d-H.i.s." );
			$lockTime = mktime ( $lockArray ["h"], $lockArray ["i"], $lockArray ["s"], $lockArray ["m"], $lockArray ["d"], $lockArray ["y"] );
			$lockDate = date ( "YmdHis", $lockTime );
			$now = date ( "YmdHis" );
			$timeUntil = ($now - $lockDate) / 60;
			
			if ($timeUntil > 30) {
				// Cancello vecchio lock
				endLock ( $context, $key );
				// Creo nuovo lock
				return startLock ( $context, $key, $lock, $type );
			
			} else {
				if ($row ['LOCKTYP'] == 'E') {
					$messageContext->addMessage ( "ERROR", _t("W400006") );
					return '2';
				} else {
					if($row ['LOCKTYP'] == 'S' && $type=="S") {
						$field = array ("LOCKSES", "LOCKCON", "LOCKKEY", "LOCKUSR", "LOCKTIM", "LOCKSTA", "LOCKTYP" );
						$stmt = $db->prepare ( "INSERT", "TABLOCK", Null, $field );
						$campi = array (session_id (), $context, $key, $_SESSION ['user'], getDb2Timestamp (), "1", $type );
						$result = $db->execute ( $stmt, $campi );
						return '0';
					}
					if($row ['LOCKTYP'] == 'S' && $type=="E") {
						$messageContext->addMessage ( "ERROR", "Impossibile allocare il record in modo esclusivo" );
						return '3';
					}
				}
			}
		}
	}
	
	return '4';
}
/**
 * Fine del lock
 * Riceve come input una chiave di lock e e cerca di toglierlo per la sessione
 * 
 * Ritorna i seguenti valori
 * 0 chiave disallocata
 * 1 chiave non disallocata perchè non trovata
 * 2 chiave non disallocata per errori 
 */
function endLock($context = "", $key = "", $sessione = "", $user = "") {
	global $db;
	
	$lockSqlKey = "";
	if ($sessione != "") {
		$lockSqlKey = $lockSqlKey . " AND LOCKSES='" . $sessione . "' ";
	}
	if ($user != "") {
		$lockSqlKey = $lockSqlKey . " AND LOCKUSR='" . $user . "' ";
	}
	if ($key != "") {
		$lockSqlKey = $lockSqlKey . " AND LOCKKEY='" . $key . "' ";
	}
	if ($context != "") {
		$lockSqlKey = $lockSqlKey . " AND LOCKCON='" . $context . "' ";
	}
	$sql = "DELETE FROM TABLOCK WHERE LOCKSTA = '1' " . $lockSqlKey;
	$result = $db->query ( $sql );
	
	if ($result) {
		// Se ho letto qualcosa la chiave è stata disallocata
		return '0';
	} else {
		// Se non ho letto niente la chiave è già disallocata
		return '1';
	}
	return '2';
}

/**
 * Elimina tutti i lock di una sessione
 */
function destroyLock($session) {
	global $db;
	
	$sessione = session_id ();
	// Verifico se esiste già un lock sulla chiave
	$sql = "DELETE * FROM TABLOCK WHERE LOCKSES='$sessione'";
	$result = $db->query ( $sql );
	if ($result) {
		return '0';
	} else
		return '1';
	return '2';
}

/**
 * A Hall
 * customAddSlashes takes 1 argument - $text and handles slashes, ', ", \ etc
 * depending on the database being used.  Required for cross db compatibility
 */
function customAddSlashes($text) {
	global $settings;
	// mysql specific addslashes
	if ($settings['database'] == "mysql" or $settings['database'] == 'mysql5') {
		$text = addslashes ( $text );
	} elseif ($settings['database'] == "sqlite") {
		$text = sqlite_escape_string ( $text );
	}
	
	return $text;
}

/**
 * A Hall
 * stripMagicQuotes takes 1 argument - $arr and undoes what magic_quotes does if
 * it is enabled in the php.ini file
 */
function stripMagicQuotes($arr) {
	foreach ( $arr as $k => $v ) {
		if (is_array ( $v )) {
			$arr [$k] = stripMagicQuotes ( $v );
		} else {
			$arr [$k] = htmlspecialchars ( $v );
			$arr [$k] = stripslashes ( $v );
		}
	}
	
	return $arr;
}

/**
 * A Hall
 * addslashesRecursive takes 1 argument - $arr and adds slashes
 * depending on database type using customAddSlashes
 */
function addslashesRecursive($arr) {
	if (is_array ( $arr )) {
		foreach ( $arr as $index => $val ) {
			$arr [$index] = addslashesRecursive ( $val );
		}
		return $arr;
	} else {
		return customAddSlashes ( $arr );
	}
}
// Recupero il sistema informativo legato all'utente
function retrive_sysinf($user) {
	global $settings, $architettura;
	
	$myarray = array ();
	// Prima di tutto verifico se per caso l'avevo già caricato per l'utente in questione
	$putfile = False;
	$filename = wi400File::getCommonFile( "serialize", "SYSINF_" . $user . ".dat" );
	$desc = fileSerialized ( $filename );
	if ($desc != null)
		return $desc;
	// Se arrivo qui devo ricaricre il descrittore e quindi apro il file per la scrittura  
	$arc = strtoupper ( $settings['architettura'] );
	$myarray = $architettura->retrive_sysinf( $user );
	// Scrittura del file
	put_serialized_file($filename, $myarray);
	// Ritorno il descrittore recuperato dalla routine

	return $myarray;
}
// Recupero il sistema informativo legato all'utente
function retrive_sysinf_by_name($name) {
	global $settings, $architettura;
	
	$myarray = array ();
	// Prima di tutto verifico se per caso l'avevo già caricato per l'utente in questione
	$putfile = False;
	$filename = wi400File::getCommonFile ( "serialize", "SYSINF_NAME_" . $name . ".dat" );
	$desc = fileSerialized ( $filename );
	if ($desc != null)
		return $desc;
	// Se arrivo qui devo ricaricre il descrittore e quindi apro il file per la scrittura  
	$arc = strtoupper ( $settings['architettura'] );
	$myarray = $architettura->retrive_sysinf_by_name($name);
	// Scrittura del file
	put_serialized_file($filename, $myarray);
	// Ritorno il descrittore recuperato dalla routine

	return $myarray;
}
// Recupero il sistema informativo legato all'utente
function retrive_sysinf_name($user, $isName = False) {
	global $settings, $architettura;
	
	$myarray = _t("W400007");
	$arc = strtoupper ( $settings['architettura'] );
	$myarray = $architettura->retrive_sysinf_name($user, $isName );
	return $myarray;
}
// Imposta il tracciato se valorizzato
function impostaTracciato($array, $valori) {
	$tracciato = array ();
	// Valorizzo i campi modificati a video con i campi del tracciato di output
	foreach ( $array as $key => $valore ) {
		if (isset ( $valori [$key] )) {
			if ($valore['DATA_TYPE']=='2' or $valore['DATA_TYPE']=='3' or $valore['DATA_TYPE']=='12') {
		    	$tracciato [$key] = doubleViewToModel($valori [$key]);
		    } else {
				$tracciato [$key] = $valori [$key];
		    }		    
		} else
		    if ($valore['DATA_TYPE']=='2' or $valore['DATA_TYPE']=='3' or $valore['DATA_TYPE']=='12') {
		    	$tracciato [$key] = 0;
		    } else {
				$tracciato [$key] = "";
		    }
	}
	return $tracciato;
}
/**
 * Costruisce automaticamente il descrittore di una DS legata ad un file reperendo automaticamente la sua
 * struttura 
 * 
 * @param $file      string:file di cui costruire il descrittore
 * @param $db        object:oggetto di connessione al DB
 * @param $connzend  string:connessione a ZEND. Non viene usato $this->Connezend perchè la funzione viene usata anceh esternamente
 * @param $libre     string:libreria del file. Se non passata viene ricercata 
 * 
 * @return array     Array contenente la descrizine dei campi del file
 */
function create_descriptor($file, $connzend, $libre = Null, $desc = False) {
	global $db, $settings, $connzend;
	$dati = $db::create_descriptor($file, $connzend, $libre, $desc);
	/*$systemdbpers="";
	$desc="";
	if (isset($settings['systemdbpers'])) {
		$systemdbpers = $settings['systemdbpers'];
	}
	if ($systemdbpers=="") $systemdbpers="OS400";
	if (is_callable("create_descriptor_".$systemdbpers)) {
		$desc = call_user_func("create_descriptor_".$systemdbpers, $file, $connzend, $libre, $desc);
	} else {
		die("create_descriptor call user func not valid: $systemdbpers");
	}*/
	return $dati;
}
function getJobInfo($reset=False) {
	global $db, $settings, $connzend;
	$systemdbpers="";
	$desc="";
	if (isset($settings['systemdbpers'])) {
		$systemdbpers = $settings['systemdbpers'];
	}
	if ($systemdbpers=="") $systemdbpers="OS400";
	if (is_callable("getJobInfo_".$systemdbpers)) {
		$desc = call_user_func("getJobInfo_".$systemdbpers, $reset);
	} else {
		die("getJobInfo call user func not valid $systemdbpers");
	}
	return $desc;
}
function clearPHPTEMP($session_id) {
	global $db, $settings;
	$dati = $db->clearPHPTEMP($session_id);
	/*$systemdbpers="";
	if (isset($settings['systemdbpers'])) {
		$systemdbpers = $settings['systemdbpers'];
	}
	if ($systemdbpers=="") $systemdbpers="OS400";
	if (is_callable("clearPHPTEMP_".$systemdbpers)) {
		$do = call_user_func("clearPHPTEMP_".$systemdbpers, $session_id);
	} else {
		die("clearPHPTEMP call user func not valid");
	}*/
}
function rtvLibre($file, $conn) {
	global $db, $settings, $connzend;
	$systemdbpers="";
	$libre="";
	if (isset($settings['rtvlibre_withdb'])&& $settings['rtvlibre_withdb']==true) {
		$libre = $db->rtvLibre($file);
	} else {	
		if (isset($settings['systemdbpers'])) {
			$systemdbpers = $settings['systemdbpers'];
		}
		if ($systemdbpers=="") $systemdbpers="OS400";
		if (is_callable("rtvLibre_".$systemdbpers)) {
			$libre = call_user_func("rtvLibre_".$systemdbpers, $file, $conn);
		} else {
			die("rtvLibre call user func not valid: $systemdbpers");
		}
	}
	return $libre;
}
function getSequence($name) {
	global $db, $settings, $connzend;
	/*$systemdbpers="";
	$sequence = "";
	if (isset($settings['systemdbpers'])) {
		$systemdbpers = $settings['systemdbpers'];
	}
	if ($systemdbpers=="") $systemdbpers="OS400";
	if (is_callable("getSequence_".$systemdbpers)) {
		$sequence = call_user_func("getSequence_".$systemdbpers, $name);
	} else {
		die("getSEquencce call user func not valid");
	}
	return $sequence;*/
	$sequence = $db->getSequence("ZCNUMERI", $name);
	return $sequence;		

}
function getSysSequence($name) {
	global $db, $settings, $connzend;
	/*$systemdbpers="";
	$sequence = "";
	if (isset($settings['systemdbpers'])) {
		$systemdbpers = $settings['systemdbpers'];
	}
	if ($systemdbpers=="") $systemdbpers="AS400";
	if (is_callable("getSysSequence_".$systemdbpers)) {
		$sequence = call_user_func("getSysSequence_".$systemdbpers, $name);
	} else {
		die("getSEquencce call user func not valid");
	}
	return $sequence;*/
	$sequence = $db->getSequence("ZSYSNUME", $name);
	return $sequence;
	
}
function getDetailKeyArray($idList = "") {
	if (isset ( $_GET ['DETAIL_KEY'] )) {
		return explode ( "|", $_GET ['DETAIL_KEY'] );
	} else {
		if ($idList != "" && existList($idList)){
			//$wi400SessionList = $_SESSION [$idList];
			$wi400SessionList = getList($idList);
			foreach ( $wi400SessionList->getSelectionArray () as $key => $value ) {
				return explode ("|", $key );
			}
			// Nessuna selezione
			return array();
		}else{
			return array ();	
		}
	}
}
/*
 * Lasciata per compatibilità con vecchissimo software WI400 v 4.0
 */
function getDetailKey($index = 0) {
	if (isset ( $_GET ['DETAIL_KEY'] )) {
		$keyArray = explode ( "|", $_GET ['DETAIL_KEY'] );
		if (isset ( $keyArray [$index] )) {
			return $keyArray [$index];
		} else {
			return "";
		}
	}
}

function getListKeyArray($idList) {
	//if (isset ( $_SESSION [$idList] )) {
	if (existList($idList)) {
		//$wi400SessionList = $_SESSION [$idList];
		$wi400SessionList = getList($idList);
//		echo "SEL_ROWS:<pre>"; print_r( $wi400SessionList->getSelectionArray()); echo "</pre>";
		foreach ( $wi400SessionList->getSelectionArray () as $key => $value ) {
			$keyValues = explode ( "|", $key );
			$keyCounter = 0;
			foreach ( $wi400SessionList->getKeys () as $keyName => $keyColumn ) {
				if (isset ( $keyValues [$keyCounter] )) {
					$keyValues [$keyName] = $keyValues [$keyCounter];
					$keyCounter ++;
				}
			}
			return $keyValues;
		}
	}
}
function getListKeyArrayMulti($idList) {
	//if (isset ( $_SESSION [$idList] )) {
	if (existList($idList)) {
		//$wi400SessionList = $_SESSION [$idList];
		$wi400SessionList = getList($idList);
		$count = 0;
		$myArray = array();
		foreach ( $wi400SessionList->getSelectionArray () as $key => $value ) {
			$keyValues = explode ( "|", $key );
			$keyCounter = 0;
			$myArray[$count] = $keyValues;
			foreach ( $wi400SessionList->getKeys () as $keyName => $keyColumn ) {
				if (isset ( $keyValues [$keyCounter] )) {
					$myArray[$count] [$keyName] = $keyValues [$keyCounter];
					$keyCounter ++;
				}
			}
			$count ++;
			}
		return $myArray;	
	}
}

function getListKey($idList, $index = 0) {
	//if (isset ( $_SESSION [$idList] )) {
	if (existList($idList)) {
		$keyValues = getListKeyArray ( $idList );
		if (isset ( $keyValues [$index] )) {
			return $keyValues [$index];
		} else {
			return "";
		}
	} else {
		return "";
	}
}

function getSubfileTotals($idSubfile) {
	$totalArray = array ();
	$subFileTotal = wi400Session::load(wi400Session::$_TYPE_SUBFILE, idSubfile);
	if ($subFileTotal !== false){
		$totalArray = $subFileTotal->getTotals ();
	}
	return $totalArray;
}

/**
 * meglio non mettere subfileDelete nella pagina stessa di creazione del subfile
 * in quanto se c'è un ToolTip Ajax viene ricaricato il model con il subfileDelete che cancella la lista
 * ma non passando dal view non viene ricaricata la lista e quindi il subfile rimane vuoto 
 * e si perdono i dati per eseguire azioni quali l'esportazione di lista
 * 
 */
function subfileDelete($idSubfile) {
	$deletesubfile = wi400Session::load(wi400Session::$_TYPE_SUBFILE, $idSubfile);
	if ($deletesubfile !== false){
		$deletesubfile->delete();
	}
}

function create_file_download_link($file_path, $contest="", $extras="") {
	global $appBase, $currentHMAC;
	
	$file_parts = pathinfo($file_path);
	$fileType = $file_parts['extension'];
	
	$myid = create_download_id($file_path, $fileType, $contest);
	
	$link = $appBase."index.php?t=FILEDWN&ID=".urlencode($myid)."&DECORATION=clean".$extras."&WI400_HMAC=".$currentHMAC;
	
	if($contest!="") {
		$link .= "&CONTEST=".$contest;
	}
	
	return $link;
}

function downloadDetail($fileType, $file, $contest="", $msg="", $common="", $email="", $campi=array(), $disable=array(), $preview = false) {	
	global $appBase, $db, $actionContext;
	if($preview) {
		$pos = strpos(strtoupper($fileType), "PDF");
		if ($pos!==False) {
		$myid = create_download_id($file, $fileType, $contest);
		?>
		<script>
			window.location.href = _APP_BASE+"index.php?t=FILEVIEW&DECORATION=clean&APPLICATION=pdf&CONTEST=<?=$contest?>&ID=<?=$myid?>";
		</script> 
<?php 
		die();
		}
	}
	
	// Recupero un ID da utilizzare per il download, non si utilizza più il file direttamente
    $myid = create_download_id($file, $fileType, $contest);
            
	$download_detail_url = $appBase."index.php?t=DOWNLOAD_DETAIL";
	$download_detail_url .=  "&FILE_TYPE=".$fileType;
	//$download_detail_url .=  "&FILE=".urlencode($file);
	$download_detail_url .= "&ID=".$myid;
	$download_detail_url .=  "&CONTEST=".$contest;
	$download_detail_url .=  "&DECORATION=iframe";
	$download_detail_url .=  "&MSG=".$msg;
	$download_detail_url .=  "&COMMON=".$common;
	$download_detail_url .=  "&EMAIL=".$email;
	$download_detail_url .=  "&CAMPI=".urlencode(serialize($campi));
	$download_detail_url .=  "&DISABLE=".urlencode(serialize($disable));
	$download_detail_url .=  "&CHECKID=".strlen(urldecode($download_detail_url));
	
?>
<iframe name="IFRAME_DETAIL" id="IFRAME_DETAIL" src="<?= $download_detail_url ?>"  frameBorder="0" style="border:0px;width:100%;height:100%;overflow:hidden;"></iframe>
	<script>
	jQuery("#IFRAME_DETAIL").load( function() {
		jQuery("#IFRAME_DETAIL").height(jQuery("#IFRAME_DETAIL").contents().find("body").height());
	});
	</script>
<?
}
//function downloadDetail($fileType, $file, $contest="", $msg, $common="", $email="", $campi=array(), $LookUpEmail=true) {
//function showDownloadDetail($fileType, $file, $contest="", $msg="", $common="", $email="", $campi=array(), $disable=array()) {
function showDownloadDetail($fileType, $id, $contest="", $msg="", $common="", $email="", $campi=array(), $disable=array()) {
	global $db, $appBase, $siri_server_settings, $settings, $routine_path, $currentHMAC;

	//$param_file = $file;
	$param_id = $id;
	$param_contest = $contest;
	// ***************************************************
	// DOWNLOAD DETAIL	
	// ***************************************************	
	
	$file = get_file_from_id($id);
	
	$onClickPdf = "";
	if(in_array($fileType, array("pdf", "pdf.png"))) {
		$onClickPdf = "openWindow(_APP_BASE + APP_SCRIPT + '?t=FILEVIEW&DECORATION=clean&APPLICATION=pdf&CONTEST=$contest&FILE_NAME=$file', 'PREVIEW_PDF',-100, -50)";
	}
	
	$fileType = get_file_type($fileType, $file);
	$param_fileType = $fileType;	
	
	$downloadDetail = new wi400Detail("DownlaodDetail");
	$downloadDetail->setTitle($msg);
	$fileTypeImage = new wi400Image("fileType");
	$fileTypeImage->setUrl("$fileType");
	$fileTypeImage->setOnClick($onClickPdf);
	$downloadDetail->addImage($fileTypeImage);
	//$fileLink = new wi400Text("fileLink", "Download:", basename($file), "", $appBase."index.php?t=FILEDWN&FILE_NAME=".urlencode($file)."&CONTEST=".$contest."&DECORATION=clean&f=".$common);
	$fileLink = new wi400Text("fileLink", "Download:", basename($file), "", $appBase.
	"index.php?t=FILEDWN&ID=".urlencode($param_id)."&CONTEST=".$contest."&WI400_HMAC=".$currentHMAC."&DECORATION=clean&f=".$common);
	$downloadDetail->addField($fileLink);
	$downloadDetail->addParameter("FILE_TYPE", $param_fileType);
	//$downloadDetail->addParameter("FILE", $param_file);
	$downloadDetail->addParameter("ID", $param_id);
	$downloadDetail->addParameter("CONTEST", $param_contest);
	$downloadDetail->dispose();
	
//	echo "EMAIL SETTINGS: ".(int)$settings['mail_export']."<br>";
//	echo "EMAIL: $email<br>";
// @todo Gestire l'ID sull'invio della mail .. come si può fare	
	if(($email!="S" && isset($settings['mail_export']) && $settings['mail_export']===true) || $email=="S") {
		$spacer = new wi400Spacer();
		$spacer->dispose();
		require_once $routine_path.'/classi/wi400invioEmail.cls.php';		
//		require_once $routine_path.'/classi/wi400invioConvert.cls.php';

		//$disable['FROM'] = true;
		//$disable['LOOK_UP_EMAIL'] = true;
		$disable['BCC'] = true;

//		wi400invioEmail::prepareInvioEmail($file, $contest, $common, $campi, $LookUpEmail);		
//		wi400invioEmail::prepareInvioEmail($file, $contest, $common, $campi, $disable);
		wi400invioEmail::prepareInvioEmail($id, $contest, $common, $campi, $disable);
		
//		wi400invioConvert::prepareInvioEmail($file, $contest, $common, $campi, $disable);
//		wi400invioConvert::prepareInvioEmail($id, $contest, $common, $campi, $disable);
	}
}

function create_download_id($file, $fileType, $contest) {
	global $db;
	
	// Recupero un ID da utilizzare per il download, non si utilizza più il file direttamente
	$myid = uniqid("D_", true);
	
	$timeStamp = getDb2Timestamp();
	
	// Scrivo l'ID nel file di log per il successivo recupero per il download
	// INSERT ARTICOLO
	$fieldLog = getDS("ZDLOGFIL");
	
	$stmtLog = $db->prepare("INSERT", "ZDLOGFIL", null, array_keys($fieldLog));
	
	$fieldLog['ZDID']= $myid;
	$fieldLog['ZDTIMI']= $timeStamp;
	$fieldLog['ZDTIMD']= $timeStamp;
	$fieldLog['ZDFILE']= $file;
	$fieldLog['ZDTYPE']= $fileType;
	$fieldLog['ZDCONT']= $contest;
	$fieldLog['ZDUTE']= $_SESSION['user'];
	$fieldLog['ZDNBR']= 0;
	
	$result = $db->execute($stmtLog, $fieldLog);
	
	return $myid;
}

function get_file_from_id($id) {
	global $db;
	
	// Recupero il nome del file per il download
	// Recupero il nome del file dell'ID passato
	$sql = "SELECT * FROM ZDLOGFIL WHERE ZDID='".trim($id)."'";
	$result = $db->singleQuery($sql);
	$row = $db->fetch_array($result);
	$file = $row['ZDFILE'];
	
	return $file;
}

function get_file_type($type="", $filename="") {
	$FileTypeImage_array = array(
		"" => "null.png",
		"csv" => "csv.png",
		"pdf" => "pdf.png",
		"png" => "png.gif",
		"xls" => "xls.png",
		"xlsx" => "xls.png",
		"xml" => "xml.png",
		"zip" => "zip.png",
		"log" => "log.png",
		"txt" => "txt.png",
		"err" => "txt.png",
		"car" => "txt.png",
		"jpg" => "jpg.png",
		"ppt" => "ppt.gif"
	);
	
	$fileType = "";
	if($type!="") {
		$type = strtolower($type);
//		echo "TYPE: $type<br>";
		$fileType_parts = pathinfo($type);
		if(isset($fileType_parts['extension'])) {
			$type = $fileType_parts['filename'];
		}
				
		if(array_key_exists($type, $FileTypeImage_array))
			$fileType = $FileTypeImage_array[$type];
	}
	else if($filename!="") {
		$fileType_parts = pathinfo($filename);
		if(isset($fileType_parts['extension'])) {
			$extension = $fileType_parts['extension'];
			if(array_key_exists($extension, $FileTypeImage_array))
				$fileType = $FileTypeImage_array[$extension];
		}
	}
//	echo "FILE TYPE: $fileType<br>";
	
	return $fileType;
}

function getColumnListFromArray($idSubfile, $modulo=False, $parameters = array()) {
	
	global $routine_path, $moduli_path;
	
	if ($modulo) {
//	    require_once $moduli_path.'/'.$modulo.'/subfile/'.$idSubfile.".cls.php";
	     
	    $path = p13n("modules/".$modulo.'/subfile/'.$idSubfile.".cls.php");
	    require_once $path;
	} else {	
		require_once $routine_path . "/classi/subfile/$idSubfile.cls.php";
	}
	// Ripristinato .. se min getArrayCampi c'è un $this si spacca tutto.
	$classe = new $idSubfile ($parameters);
	//$classe = $idSubfile;
	$desc1 = array();
	//$cols = $classe::getArrayCampi ();	
	//$desc1 = array();
	$cols = $classe->getArrayCampi ();
	foreach ( $cols as $key => $valore ) {
		$descrizione = $valore ['REMARKS'];
		if ($descrizione == "")
			$descrizione = $key;
		$mycol = new wi400Column ( $key, $descrizione );
		// Campi Packed
		if (strtoupper ( $valore ['DATA_TYPE_STRING'] ) == 'DECIMAL' or strtoupper ( $valore ['DATA_TYPE_STRING'] ) == 'NUMERIC') {
			$len = $valore ['LENGTH_PRECISION'];
			$dec = $valore ['NUM_SCALE'];
			$mycol->setAlign ( 'right' );
			$mycol->setFormat (getNumericFormatoByDecimal($dec));
		} // ID DATATYPE
		$desc1 [$key] = $mycol;
	}
	// Ritorno il descrittore recuperato dalla routine
	return $desc1;
}

function getNumericFormatoByDecimal($dec) {
	$format ="";
			switch ($dec) {
				case 0 :
					$format= 'INTEGER';
					break;
				case 1 :
					$format= 'DOUBLE_1';
					break;
				case 2 :
					$format='DOUBLE_2';
					break;
				case 3 :
					$format='DOUBLE_3';
					break;
				case 4 :
					$format='DOUBLE_4';
					break;
				case 7 :
					$format= 'DOUBLE_7';
					break;
				case 8 :
					$format= 'DOUBLE_8';
					break;
			}
	return $format;		
}	
/**
 * @desc  Recupero l'elenco delle colonne per lista WI400Lista da una tabella fisica
 * @param $table: string Nome tabella
 * @param $libre: string Libreria. Se non passata viene cercata nel sistema informativo attuale
 */		
function getColumnListFromTable($table, $libre=Null) {
	global $settings, $connzend, $db;
	
	if (! isset ( $libre ) || $libre=='') {
		$libre = rtvLibre ( $table, $connzend );
	}
	return $db->getColumnListFromTable($table, $libre);
	
}
function make_serialized_file($sql, $filename, $key, $multi=False) {
	global $db;
	
	// Accedo alla tabella su AS400 per recuperarne la struttura
	$result = $db->query ( $sql );
	// Verifico se ho trovato qualcosa   
	if (! $result) {
		return false;
	}
	$desc1 = array ();
	// Ciclo di costruzione e caricamento del descrittore della DS da utilizzare	
	while ( $info = $db->fetch_array ( $result ) ) {
		$mykey = "";
		$sepa = "";
		foreach ( $key as $chiave => $valore ) {
			$mykey .= $sepa . $info [$valore];
			$sepa = '-';
		}
		if ($multi==False) {
			$desc1 [$mykey] = $info;
		} else {
			$desc1 [$mykey][] = $info;
		}
	}
	//db2_free_result($result);
	// Scrittura del file
	put_serialized_file($filename, $desc1);	
	// Ritorno il descrittore recuperato dalla routine
	return $desc1;
}

function _debug($var){
	global $settings, $debugContext;
	if($settings['debug'] || isset($_SESSION["DEBUG"])) {
		$debugLine = array();
		$debugLine["VARIABLE"] = $var;
		$debugLine["TIME"] = time();
		$debugContext[] = $debugLine;
	}
}

function _t($tag, $values=Null, $noError=False) {
	
	global $wi400Lang;
	$stringa ="";
	// Controllo se mi hanno passato un array di stringhe da tradurre
    if (is_array($tag)) {
		  $end ="";
	      foreach ($tag as $key) {
	      	if(array_key_exists($key,$wi400Lang)) {
		      	$stringa .=$end.$wi400Lang[$key];
		      	$end= " ";
	      	}
	      }	
	} else {
		if (is_array($wi400Lang)) {
			if(array_key_exists($tag,$wi400Lang)) {
				$stringa = $wi400Lang[$tag];
			}
		}
	}
	// Se mi hanno passato dei valori li sostituisco ai segna posto
	if (isset($values)){
/*		
		$start = 0; 
		foreach ($values as $key=>$valore) {
			$pos = strpos($stringa, "%i", $start);
			if ($pos) {
				$stringa = substr($stringa, 0 , $pos).$valore." ".substr($stringa, $pos+2);
			} else {
				break;
			}
			$start = $pos + 1+ strlen($valore);
		}
		//$stringa = str_replace(array("%i"), $values, $stringa);
*/
//		echo "STRINGA: $stringa<br>";
//		echo "VALUES:<pre>"; print_r($values); echo "</pre>";
		$stringa = vsprintf($stringa,$values);
//		echo "RESULT: $stringa<br>";
	}
	// Se la stringa è vuota ritorno un messaggio di errore
	if ($stringa=="" && $noError==False) {
		$stringa = "**&lt;TAG:".$tag."&gt;** not found";
	} elseif ($stringa=="" && $noError==True) {
        $stringa = $tag;
	}

//	$stringa = prepare_string($stringa);
	
	return ucfirst($stringa);	 	
}
/* Cosa Server !?!?
function _tEn($tag, $values=Null) {
	$stringa = _t($tag, $values);
	$stringa = prepare_string($stringa);
	return $stringa;
}*/

// load di un file XML per poterne fare il parse
function load_XML_file($file_path) {
	if(!file_exists($file_path))
		return false;
		
	$path_parts = pathinfo($file_path);
	
	if(!isset($path_parts['extension']) || !in_array($path_parts['extension'],array("txt","xml")))
		return false;
		
	$file_handle = fopen($file_path, "r+");	
	
	// Lock del file XML
	if(!flock($file_handle, LOCK_EX))
		return false;
	$contents = "";	
	// Lettura del file XML recuperato
	while(!feof($file_handle)) {
		$contents .= fread($file_handle, 8192);
	}
	fclose($file_handle);
	// Parse del file XML
	// Carico l'XML e comincio a parsarlo
	$dom_xml = new DomDocument('1.0');
	
	// Gestione degli errori con chiamata alla funzione errorHandler()
	$error = "";
	set_error_handler('XML_errorHandler');
				
	// Caricamento del corpo della response XML in un DOM 
	$dom_xml->loadXML($contents);
	
	restore_error_handler();
	if($error!="") 
		throw new SoapFault('wi400WsSiriAtg', "XML non valido:" . $error);
	
	return $dom_xml;
}

function XML_errorHandler($errno, $errstr, $errfile, $errline) {
	$pos = strpos($errstr,"]:") ;   
	if($pos) {   
		$errstr = substr($errstr, $pos + 2);   
	}   
	$error = $errstr;
}

function throw_soap_fault($params) {
	if(!$params) 
		throw new SoapFault('wi400WsSiriAtg', 'XML non contiene parametri validi oppure incompleto');
}
function strnpos($haystack, $needle, $nth=1, $offset = 0) { 
    for ($retOffs=$offset-1; ($nth>0)&&($retOffs!==FALSE); $nth--) 
    	$retOffs = strpos($haystack, $needle, $retOffs+1); 
    return $retOffs; 
}
function sessionUnregister($var) {
    unset($_SESSION[$var]);	
}
/*function transformRequest($request) {
	$transformed = array();
	foreach ($request as $key => $value) {
		if (is_array($value)) {
			$transformed[$key] =transformRequest($value);
		} else {
		//if(mb_check_encoding($value,'UTF-8')===True) {
				$transformed[$key]= utf8_decode($value);
		//	} else {
		//		$transformed[$key]= $value;
		//	}
		}
	}
	return $transformed;
}*/
function prepare_string($string, $hasHtml=False, $decode=false) {
	global $settings;

	if ($hasHtml) $string = strip_tags($string);
	
	if ($hasHtml) $string = htmldecode($string);
	
	if ($hasHtml) $string = htmlspecialchars($string);
	
	if(mb_check_encoding($string,'UTF-8')===false) {
//		echo "ENCODE<br>"; 
		$string = utf8_encode($string);
	}
	
//	echo "DECODE:$decode<br>";
	if($decode==true) {
//		echo "DECODE UTF-8: ".$settings['utf8_decode']."<br>";
		if(isset($settings['utf8_decode']) && $settings['utf8_decode']===true) {
//			echo "DECODE UTF-8<br>";
			$string = utf8_decode($string);
		}
	}
	
	if ($hasHtml) $string = strip_tags($string);
	
	if ($hasHtml) $string = htmlspecialchars_decode($string);
	
	$string = str_replace(array('<br>','</br>'), " \n", $string);
	
	$string = strtr($string, normalizeChars());

	return $string;
}

function prepare_string_PDF($string, $hasHtml=False) {
	if ($hasHtml) $string = strip_tags($string);

	if(mb_check_encoding($string,'UTF-8')===false)
		$string = utf8_encode($string);
	
	// Asteriscato perchè c'erano problemi di sposizionamento sul PDF delle immagini
//	$string = str_replace(array('<br>','</br>'), " \n", $string);
	$string = strtr($string, normalizeChars());
	
	return $string;
}

function prepare_string_CSV($string) {
	global $settings;
	
//	$string = str_replace(array('<br>','</br>', "\r\n", "\n", "\r"), " ", $string);
	$string = str_replace(array('<br>','</br>'), " \n", $string);

	if(mb_check_encoding($string,'UTF-8')===true)
		$string = utf8_decode($string);

	return $string;
}

function normalizeChars() {
	$normalizeChars = array(
	    'ï¿½'=>'S', 'ï¿½'=>'s', 'ï¿½'=>'Dj','ï¿½'=>'Z', 'ï¿½'=>'z', 'ï¿½'=>'A', 'ï¿½'=>'A', 'ï¿½'=>'A', 'ï¿½'=>'A', 'ï¿½'=>'A', 
	    'ï¿½'=>'A', 'ï¿½'=>'A', 'ï¿½'=>'C', 'ï¿½'=>'E', 'ï¿½'=>'E', 'ï¿½'=>'E', 'ï¿½'=>'E', 'ï¿½'=>'I', 'ï¿½'=>'I', 'ï¿½'=>'I', 
	    'ï¿½'=>'I', 'ï¿½'=>'N', 'ï¿½'=>'O', 'ï¿½'=>'O', 'ï¿½'=>'O', 'ï¿½'=>'O', 'ï¿½'=>'O', 'ï¿½'=>'O', 'ï¿½'=>'U', 'ï¿½'=>'U', 
	    'ï¿½'=>'U', 'ï¿½'=>'U', 'ï¿½'=>'Y', 'ï¿½'=>'B', 'ï¿½'=>'Ss','ï¿½'=>'a', 'ï¿½'=>'a', 'ï¿½'=>'a', 'ï¿½'=>'a', 'ï¿½'=>'a', 
	    'ï¿½'=>'a', 'ï¿½'=>'a', 'ï¿½'=>'c', 'ï¿½'=>'e', 'ï¿½'=>'e', 'ï¿½'=>'e', 'ï¿½'=>'e', 'ï¿½'=>'i', 'ï¿½'=>'i', 'ï¿½'=>'i', 
	    'ï¿½'=>'i', 'ï¿½'=>'o', 'ï¿½'=>'n', 'ï¿½'=>'o', 'ï¿½'=>'o', 'ï¿½'=>'o', 'ï¿½'=>'o', 'ï¿½'=>'o', 'ï¿½'=>'o', 'ï¿½'=>'u', 
	    'ï¿½'=>'u', 'ï¿½'=>'u', 'ï¿½'=>'y', 'ï¿½'=>'y', 'ï¿½'=>'b', 'ï¿½'=>'y', 'ï¿½'=>'f', '¤'=>'€'
	);
	
	return $normalizeChars;
}

function cleanForShortURL($toClean) {
	$normalizeChars = normalizeChars(); 
	
	$toClean = str_replace('°', '-', $toClean);
	$toClean = str_replace('&', '-and-', $toClean);
	$toClean = trim(preg_replace('/[^\w\d_ -]/si', '', $toClean));//remove all illegal chars
	$toClean = str_replace(' ', '-', $toClean);
	$toClean = str_replace('--', '-', $toClean);
	
	return strtr($toClean, $normalizeChars);
}

//function clean_string($string) {
function clean_string($string, $encode=true) {
	$string= html_entity_decode($string);
	
	$string = str_replace(array('<br>','</br>', "\r\n", "\n", "\r"), " ", $string);
	
//	if(mb_check_encoding($string,'UTF-8')===false)
	if(mb_check_encoding($string,'UTF-8')===false && $encode===true)
		$string= utf8_encode($string);

	return $string;
}

// Start del cronometro
function startwatch() {
	$temp = explode(" ", microtime());
	$start = bcadd($temp[0], $temp[1], 6);
	return $start;
}
// Recupera locale per lingua
function getLocaleFromLanguage($lang, $key=True) {
	$language = array("Italian"=>"it_IT", "French"=>'fr_FR');
	if ($key) {
		if (isset($language[$lang])) {
			return $language[$lang];
		} else {
			return False;
		}
	} else {
		$pos = array_search($lang, $language);
		if ($pos !== false) {
			return $pos;
		}
	}
}
// Recupera l'ID lingua 
function getLanguageID($lang, $key=True) {
	$language = array("Italian"=>False, "French"=>'0006', "English"=>'0007');
    if ($key) {
	if (isset($language[$lang])) {
		return $language[$lang];
	} else {
		return False;
	}
    } else {
    	$pos = array_search($lang, $language);
    	if ($pos !== false) {
    		return $pos;
    	}
    }
}
function getDefaultLanguage() {
	global $settings;
	
	$lang ='it';
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	}
    if ($lang =='it') {
        $lang = 'Italian';
    }else if ($lang =='en') {
        $lang = 'English';
    }else if ($lang =='fr') {
        $lang = 'French';
    }else{
     	$lang = $settings['default_language'];
    }
	return $lang;
}
// Stampa in un file di log delle informazioni di ora, tempo del cronometro, memoria utilizzata
// serve avere il tempo di inizio del cronometro che si ottiene con la funzione startwatch()
function stopwatch($start, $des, $file_log=null) {
	global $root_path;
	
	// file di log
	if(!isset($file_log) || $file_log=="") {
		$file_debug_path = get_log_file_path("LOG_DEBUG");
		
		if(!file_exists($file_debug_path)) {
			wi400_mkdir($file_debug_path, 777, true);
		}
		
		$file_debug_name = get_log_file_name("LOG_DEBUG");
		
		$file_log = $file_debug_path.$file_debug_name;		
	}
		
	$log_handle = fopen($file_log, "a");
	$temp = explode(" ", microtime());
	$stop = bcadd($temp[0], $temp[1], 6);
	$time = bcsub($stop, $start, 6);
	
	$log_msg = date("Y-m-d H:i:s")." - $des: $time - MEMORY: ".(memory_get_usage(true)/1024/1024)."MB\r\n";
	fwrite($log_handle, $log_msg);
	fclose($log_handle);
}
/*
// Stampa di un commento in un file di log
function print_log($msg, $file_log=null, $clean_msg=false) {
	global $root_path;
	
	// file di log
	if(!isset($file_log) || $file_log=="") {
		$file_debug_path = get_log_file_path("LOG_DEBUG");
		
		if(!file_exists($file_debug_path)) {
			wi400_mkdir($file_debug_path, 777, true);
		}
		
		$file_debug_name = get_log_file_name("LOG_DEBUG");
		
		$file_log = $file_debug_path.$file_debug_name;		
	}
		
	$log_handle = fopen($file_log, "a");
	
	$log_msg = date("Y-m-d H:i:s")." - $msg\r\n";
	if($clean_msg===true)
		$log_msg = $msg."\r\n";
	
	fwrite($log_handle, $log_msg);
	fclose($log_handle);
}
*/
// Stampa di un commento in un file di log
function print_log($msg, $file_log=null, $clean_msg=false, $log_type="LOG_DEBUG") {
	global $root_path;

	// file di log
	if(!isset($file_log) || $file_log=="") {
		$file_path = get_log_file_path($log_type);

		if(!file_exists($file_path)) {
			wi400_mkdir($file_path, 777, true);
		}

		$file_name = get_log_file_name($log_type);

		$file_log = $file_path.$file_name;
	}

	$log_handle = fopen($file_log, "a");

	$log_msg = date("Y-m-d H:i:s")." - $msg\r\n";
	if($clean_msg===true)
		$log_msg = $msg."\r\n";

	fwrite($log_handle, $log_msg);
	fclose($log_handle);
}

// Modifica il formato numerico da #.##0,000 a #,##0.000 (es: per Excel)
function change_num_format($value) {
	$value = str_replace(".","",$value);
	$value = str_replace(",",".",$value);
	settype($value, "float");
	
	return $value;
}

function explode_string($string,$sub_len,$pad=false,$pad_char=" ") {
/*	
	$array = array();
	$x=0;			    
	for ($i=1; $i<=$num_elem; $i++) {
		$array[] = substr($string, $x, $sub_len);
		$x += $sub_len;	
	}
	return $array;
*/
	$array = array();
	$string = rtrim($string);
	$array = str_split($string,$sub_len);
	if($pad===true) {
		$array[count($array)-1] = str_pad($array[count($array)-1], $sub_len, $pad_char,STR_PAD_RIGHT);
	}
	return $array;
}
function disable_text_selection() {

	echo '<script language="JavaScript">
	document.onselectstart=new Function ("return false")
	</script>';
}


/**
 * @Desc Scorciatoia per salvare l'oggetto lista
 * @param string: Id lista da salvare
 * @param wi400List: oggetto lista da salvare
 */

function saveList($idList, $object) {
	return wi400Session::save(wi400Session::$_TYPE_LIST, $idList, $object);
}
/**
 * @Desc Scorciatoia per caricare l'oggetto lista
 * @param string: Id lista da caricare
 */

function getList($idList) {
	$list = wi400Session::load(wi400Session::$_TYPE_LIST, $idList);
	if ($list===False) {
		developer_debug("Lista $idList non trovata");
	}
	return $list;
}
/**
 * @Desc Scorciatoia per verificare se una lista esiste
 * @param string: Id lista da verificare
 */

function existList($idList) {

         return wi400Session::exist(wi400Session::$_TYPE_LIST, $idList);
}

/**
 * @Desc Scorciatoia per cancellare una lista esiste
 * @param string: Id lista da caricare
 */

function deleteList($idList, $subTree=false) {

		 if ($subTree==True) {
		 	$wi400List = getList($idList);
		 	$subTreeList = $wi400List->getTreeSubList();
		 	foreach ($subTreeList as $key => $value) {
		 		wi400Session::delete(wi400Session::$_TYPE_LIST, $key);
		 	}
		 }
         return wi400Session::delete(wi400Session::$_TYPE_LIST, $idList);
}
/**
 * @Desc Scorciatoia per salvare l'oggetto dettaglio
 * @param string: Id dettaglio da salvare
 * @param wi400Detail: oggetto dettaglio da salvare
 */

function saveDetail($idDetail, $object) {
         return wi400Session::save(wi400Session::$_TYPE_DETAIL, $idDetail, $object);
}
/**
 * @Desc Scorciatoia per caricare l'oggetto dettaglio
 * @param string: Id dettagio da caricare
 * New GESTIONE CACHED ID
 */

function getDetail($idDetail, $idField = "") {
    static $cachedetail, $id;
	if (!isset($id)) $id ="";
	//if ($id == $idDetail) {
	//    $detailSessionObj = $cachedetail;
	//} else {
		$detailSessionObj = wi400Session::load(wi400Session::$_TYPE_DETAIL, $idDetail);
		$cachedetail = $detailSessionObj;
		$id = $idDetail;
	//}
	if ($idField == ""){
		 if (!$detailSessionObj){
		 	return array();
		 }else{
         	return $detailSessionObj;
		 }
	}else{
 		return $detailSessionObj["FIELDS"][$idField];
	}
}


/**
 * @Desc Scorciatoia per verificare se un dettaglio esiste
 * @param string: Id dettaglio da verificare
 */

function existDetail($idDetail, $idField = "") {
	 if (wi400Session::exist(wi400Session::$_TYPE_DETAIL, $idDetail)){
	 	$detailSessionObj = getDetail($idDetail);
	 	if ($idField == ""){
	 		if (isset($detailSessionObj["FIELDS"])){
	 			return true;
	 		}else{
	 			return false;
	 		}
	 	}else{
	 		if (isset($detailSessionObj["FIELDS"][$idField])){
	 			return true;
	 		}else{
	 			return false;
	 		}
	 	}
	 }else{
	 	return false;
	 }
}
    /**
     * @desc Creare Array con key di una tabella e la inizializza nei value, se passata 
     *       una altra DS sostituisce i valori trovati
     * @param array:$DS
     * @param array:$OLD_DS
     * 
     * @return array:$field
     */
	function getDs($DS ,$OLD_DS=Null, $reset=False, $default=False) {
		global $connzend, $db, $connzend;
		
		$field = array();
		if ($reset ==True) {
				$libre = rtvLibre($DS, $connzend);
				$filename = wi400File::getCommonFile( "serialize", "DB_" . $DS . "_".$libre.".dat" );
				unlink($filename);
		}
		$desc1 = $db->columns($DS);
        foreach ($desc1 as $key=>$value) {
 			$valore = $db->inzDsValue($value['DATA_TYPE_STRING']);
        	$field[$key]=$valore;
        }
        if (isset($OLD_DS)) {
        	foreach ($OLD_DS as $key=>$value) {
        		if (isset($field[$key]) && $value!=Null) {
        			$field[$key] = $value;
        		}
        	}
        }
        // Controllo se ci sono dei default da caricare sulla DS
        if ($default) {
        	// Codice per impostare i default
        }
        //showArray($field);die();
        return $field; 
	}
/*	
function getUserMail($userName) {
    global $db, $settings;
    
	$sql_1 = "SELECT EMAIL FROM SIR_USERS WHERE USER_NAME='$userName'";
	$res_1 = $db->singleQuery($sql_1);
	if($row_1 = $db->fetch_array($res_1)) {
		if(isset($row_1['EMAIL']) && $row_1['EMAIL']!="") {
			return trim($row_1['EMAIL']);
		}
		else if(!isset($settings['mail_tab_abil']) || 
			(isset($settings['mail_tab_abil']) && in_array("JPROFADF", $settings['mail_tab_abil']))
		) {
			$sql_2 = "select MAILAD from ".$settings['lib_architect']."/JPROFADF where NMPRAD='$userName'";
			$res_2 = $db->singleQuery($sql_2);
			if($row_2 = $db->fetch_array($res_2)) {
				if(isset($row_2['MAILAD']) && $row_2['MAILAD']!="") {
					return trim($row_2['MAILAD']);
				}
			}
		}
	}
	
	return "";
}
*/
//function getUserMail($userName) {
function getUserMail($userName, $locale="", $area_fun="") {
	global $db, $settings;
	
//	echo "USER_NAME: $userName<br>";

	if(!isset($settings['email_pers']) || empty($settings['email_pers'])) {
		$sql_1 = "SELECT EMAIL FROM SIR_USERS WHERE USER_NAME='$userName'";
		$res_1 = $db->singleQuery($sql_1);
		if($row_1 = $db->fetch_array($res_1)) {
//			echo "RIGA - SIR_USERS<br>";
			if(isset($row_1['EMAIL']) && $row_1['EMAIL']!="") {
//				echo "EMAIL - SIR_USERS<br>";
				return trim($row_1['EMAIL']);
			}
			else if(!isset($settings['mail_tab_abil']) ||
				(isset($settings['mail_tab_abil']) && in_array("JPROFADF", $settings['mail_tab_abil']))
			) {
//				echo "EMAIL - JPROFADF<br>";
				$sql_2 = "select MAILAD from ".$settings['lib_architect']."/JPROFADF where NMPRAD='$userName'";
				$res_2 = $db->singleQuery($sql_2);
				if($row_2 = $db->fetch_array($res_2)) {
//					echo "RIGA - JPROFADF<br>";
					if(isset($row_2['MAILAD']) && $row_2['MAILAD']!="") {
//						echo "FOUND<br>";
						return trim($row_2['MAILAD']);
					}
				}
			}
		}
	}
	else {
		require_once p13n("/modules/email/email_user_function.php");
		
//		$userMail = getUserMail_pers($userName);
		$userMail = getUserMail_pers($userName, $locale, $area_fun);
//		echo "EMAIL - PERS: $userMail<br>";
		
		return $userMail;
	}

	return "";
}
/**
 * @desc dsLen Recupero la lunghezza di un DS RPG/AS400
 * @param struct: $tracciato Tracciato in formato i5
 * @return integer: $len Lunghezza della DS
 */
function dsLen($tracciato) {
	$len = 0;
	// Path tracciato Easy Aura
	if (isset($tracciato['DSParm'])) {
		$tracciato = $tracciato['DSParm'];
	}
	foreach($tracciato as $field) {
		$len +=dsFieldLen($field);
		/*switch ($field["Type"]) {
			case I5_TYPE_CHAR:
				$len += $field["Length"];
				break;
			case I5_TYPE_ZONED:
				$len += $field["Length"];
				break;				
			case I5_TYPE_PACKED:
				$lunghezza= floor($field["Length"]/2)+1;
				$len +=$lunghezza;
				break;	
			default:
				$len += $field["Length"];
				break;					
		}*/
	}
	return $len;
}
function dsFieldLen($field) {
	$len = 0;
	switch ($field["Type"]) {
		case I5_TYPE_CHAR:
			$len = $field["Length"];
			break;
		case I5_TYPE_ZONED:
			$len = $field["Length"];
			break;
		case I5_TYPE_PACKED:
			$lunghezza= floor($field["Length"]/2)+1;
			$len =$lunghezza;
			break;
		default:
			$len = $field["Length"];
			break;
	}
	return $len;
}
function getServerAddress($returnPort=False) {
    global $settings;
    $address ="";
    if (strpos($_SERVER["HTTP_HOST"], ":")!==False ) {
   		$address =  substr($_SERVER["HTTP_HOST"], 0, strpos($_SERVER["HTTP_HOST"], ":"));
    } else {
    	$address = $_SERVER["HTTP_HOST"];
    }
    if ($address =="" || $address =="127.0.0.1" || strtoupper($address)=="LOCALHOST") {
        $address =  $_SERVER["SERVER_NAME"];
	    $pos = strpos($address, "as400");
	    if ($pos!==False) {
	    	//
	    } else {
	    	$address = "";
	    }
    }
    if ($address =="" || $address =="127.0.0.1" || strtoupper($address)=="LOCALHOST") {
		if (isset($_SERVER["SERVER_ADDR"])) {
	    	$address =  $_SERVER["SERVER_ADDR"];
	    }
    }
    if ($settings['platform']=='AS400') {
	    if ($address =="" || $address =="127.0.0.1" || strtoupper($address)=="LOCALHOST") {
	 	   $address = $settings['server_zend_ip'];
	    }   
    }
    if ($returnPort==True) {
		if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']!="") {
			$address = $address . ":".$_SERVER['SERVER_PORT']; 
        }
    }
    return $address;
}

function File_Size($file, $type_size = false, $show = false) {
	$FZ = ($file && @is_file($file)) ? filesize($file) : NULL;
	$FS = array("B","KB","MB","GB","TB","PB","EB","ZB","YB");
	
	if($type_size===false) {
		$val = number_format($FZ/pow(1024, $I=floor(log($FZ, 1024))), ($i >= 1) ? 2 : 0);
		
		if($show===true)
			$val .= ' ' . $FS[$I];
	}
	else {
		$ts = array_search($type_size, $FS);
		
		if($ts==0)
			$val = number_format($FZ);
		else
			$val = number_format($FZ/pow(1024, $ts), ($ts >= 1) ? 2 : 0 );
		
		if($show===true)
			$val .= ' ' . $FS[$ts];
	}
	
	return $val;
}

function format_size($size, $type=null, $show=false) {
	$sizes = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
	
	if ($size == 0) {
		return('n/a');
	} 
	else {
		if(isset($type) && $type!="") {
			$ts = array_search($type, $sizes);
			
			$val = round($size/pow(1024, $ts), $ts > 1 ? 2 : 0);
			
			if($show===true)
				$val .= $sizes[$ts];
			
			return " " . $val;
		}
		else {
			return (round($size/pow(1024, ($i = floor(log($size, 1024)))), $i > 1 ? 2 : 0) . ' ' . $sizes[$i]);
		}
	}
}
/**
 * @desc Recupero in un array i parametri dl DB legato al sistema informativo
 * @return multitype:string
 */
function getDBENV() {
	$dati = array();
	$ret = data_area_read("*LIBL/DB_ENV", 1 , 5);
	if ($ret) {
		$dati['DB-ENV']=strtoupper($ret);
	} else {
		$dati['DB-ENV']="";
	}
	$ret = data_area_read("*LIBL/DB_ENV2", 1, 3);
	if ($ret) {
		$dati['DB-NAZ']=strtoupper($ret);
	} else {
		$dati['DB-NAZ']="";
	}
	return $dati;
}
function getUploadPath($force = False) {
	global $settings;
	$upload_path = "/upload/";
	if (isset($_SESSION['DB-ENVIRONMENT']) and  $_SESSION['DB-ENVIRONMENT']== 'TEST') {
		$upload_path = "/upload_test/";
	} else {
		$upload_path = $settings['uploadPath'];
	}
	if ($force == true) {
	//if ($force == true and !isset($_SESSION['DB-ENVIRONMENT'])) {
			$ret = data_area_read("*LIBL/DB_ENV");
			if ($ret) {
				if (strtoupper($ret)=="TEST") {
					$upload_path = "/upload_test/";
				}
			}
	}
	return $upload_path;
}
function getEnvironment($force = False) {
	global $settings;
	$environment = "";
	if (isset($_SESSION['DB-ENVIRONMENT']) and  $_SESSION['DB-ENVIRONMENT']== 'TEST') {
		$environment = "TEST";
	} else {
		$environment = "";
	}
	if ($force == true and !isset($_SESSION['DB-ENVIRONMENT'])) {
			$ret = data_area_read("*LIBL/DB_ENV");
			if ($ret) {
				if (strtoupper($ret)=="TEST") {
					$environment = "TEST";
				}
			}
	}
	return $environment;
}
function logAction($message, $myUser, $address) {
	global $connzend, $settings, $db;
	if (isset($settings['noroutine'])) {
		if (isset($settings['log_diag_message']) && $settings['log_diag_message']==True) {
			if (isset($settings['log_separate_action']) && $settings['log_separate_action']==True) {
				$errorPath = ini_get('error_log');
				$file_parts = pathinfo($errorPath);
				$log_action_file = $file_parts['dirname']."/log_action.txt";
				$handle = fopen($log_action_file, "w+");
				fwrite($handle, $message. " ". $myUser. " ".$address. " ".session_id());
				fclose($handle);
			} else { 
			 	error_log($message. " ". $myUser. " ".$address. " ".session_id());
			 }
		}
	} else 	{
		if (isset($settings['log_diag_message']) && $settings['log_diag_message']==True) {
			// Verifoc se usare UDFT
			if (isset($settings['log_diag_with_udft'])) {
				$message = substr(str_pad($message, 200, " "), 0, 170).session_id();
				$sql = "CALL SYSTOOLS.LPRINTF('$message LPRINTF')";  
				die($sql);
				$db->query($sql);
			} else {
				$pgm = new wi400Routine("ZDIAGMSG", $connzend);
				$pgm->load_description();
				$pgm->prepare();
				$message = substr(str_pad($message, 200, " "), 0, 170).session_id();
				$pgm->set('MESSAGE',$message);
				$pgm->set('IP', $address);
				$pgm->set('USER', $myUser);
				$pgm->call();
			}
			// Aggiornamento campo sessione sul file ->update per non modificare la routine principale
			// @todo Aggiungere aggiornamento del campo!!!
		}
	}
}

function check_IP_existence_in_range($ip, $from, $to) {
	$ip_parts = explode(".", $ip);
	foreach($ip_parts as $key => $val) {
		$ip_parts[$key] = str_pad($val, 3, "0", STR_PAD_LEFT);
	}
	$ip = implode("", $ip_parts);
//	echo "IP: $ip<br>";
	
	$from_parts = explode(".", $from);
	foreach($from_parts as $key => $val) {
		$from_parts[$key] = str_pad($val, 3, "0", STR_PAD_LEFT);
	}
	$from = implode("", $from_parts);
//	echo "FROM: $from<br>";
	
	$to_parts = explode(".", $to);
	foreach($to_parts as $key => $val) {
		$to_parts[$key] = str_pad($val, 3, "0", STR_PAD_LEFT);
	}
	$to = implode("", $to_parts);
//	echo "TO: $to<br>";

	if($ip>=$from && $ip<=$to)
		return true;
	else
		return false;
}
function return_array_element($array, $cur_elem, $type="C") {		// C= CURRENT, N=NEXT, P=PREV
	$elem = "";
		
	foreach($array as $val) {
		if($val==$cur_elem) {
			if($type=="C") {
				$elem = current($array);
			}
			else if($type=="P") {
				$elem = prev($array);
			}
			else if($type=="N") {
				$elem = next($array);
			}

			break;
		}
		else {
			each($array);
		}
	}

	return $elem;
}

function array_search_substr($needle, $haystack, $all=false) {
	$found = array();
	
	foreach($haystack as $key => $val) {
		if(strpos($val, $needle)!==false) {
//		if(strncmp($val, $needle, strlen($needle))==0) {
			if($all===false) {
				return $key;
			}
			else {
				$found[] = $key;
			}	
		}
	}
	
	return $found;
}

function array_find($needle, $haystack) {
	foreach($haystack as $item) {
		if(strpos($item, $needle) !== FALSE) {
			return $item;
			break;
		}
	}
}

function get_log_path() {
	global $settings, $root_path, $appBase;
	
	// file di log
	$log_path = $root_path;
	if(isset($settings['log_root']) && $settings['log_root']!="") {
		$log_path = $settings['log_root'];
	
		$log_path = decode_path($log_path);
	}
	
//	echo "LOG PATH: $log_path<br>";
	
	return $log_path;
}

function decode_path($path) {
	global $appBase, $doc_root, $data_path, $moduli_path;
	
	if(strpos($path, "##APP_BASE##")!==false) {
		$path = str_replace("##APP_BASE##", str_replace("/", "", $appBase), $path);
	}
	
	if(strpos($path, "##USER##")!==false && isset($_SESSION['user'])) {
		$path = str_replace("##USER##", $_SESSION['user'], $path);
	}
	
	if(strpos($path, "##DOC_ROOT##")!==false) {
		$path = str_replace("##DOC_ROOT##", $doc_root, $path);
	}
	
	if(strpos($path, "##DATA_PATH##")!==false) {
		$path = str_replace("##DATA_PATH##", $data_path, $path);
	}
	
	if(strpos($path, "##MODULES##")!==false) {
		$path = str_replace("##MODULES##", $moduli_path, $path);
	}
	
	if(strpos($path, "##P13N_MODULES##")!==false) {
		$path = str_replace("##P13N_MODULES##", 'modules', $path);
		$path = p13n($path);
	}
	
//	echo "PATH: $path<br>";
	
	return $path;
}

function get_log_file_paths() {
	global $settings;

	$log_path = get_log_path();

	if(!isset($settings['log_paths'])) {
		$log_files_paths = array(
			"LOG_ERROR" => $log_path."logs/error/",
			"LOG_DEBUG" => $log_path."logs/debug/",
			"LOG_EMAIL" => $log_path."logs/email/",
			"LOG_CLEAN" => $log_path."logs/clean/",
			"LOG_FUNZIONALE" => $log_path."logs/funzionale/",
			"LOG_SQL" => $settings['log_sql'],
//			"LOG_ALLERGENI" => $log_path."logs/allergeni/",
		);
	}
	else {
		$log_files_paths = array();
		foreach($settings['log_paths'] as $key => $val) {
			$log_files_paths[$key] = $log_path.decode_path($val);
		}
	
		$log_files_paths["LOG_SQL"] = $settings['log_sql'];
	}
	
//	echo "LOG FILES PATHS:<pre>"; print_r($log_files_paths); echo "</pre>";

	return $log_files_paths;
}

function get_log_file_path($type) {
	$log_files_paths = get_log_file_paths();
	
	return $log_files_paths[$type];
}

function get_log_file_names() {
	global $settings;
	
	if(!isset($settings['log_file_names'])) {
		$log_files_array = array(
			"LOG_ERROR" => "php_error_".date("Ymd").".log",
			"LOG_DEBUG" => "php_debug_".date("Ymd").".log",
			"LOG_EMAIL" => "cvtspool_invio_".date("Ymd").".log",
			"LOG_CLEAN" => "log_clean_".date("Ymd").".log",
			"LOG_FUNZIONALE" => "log_funzionale_".date("Ymd").".log",
		);
	}
	else {
		$log_files_array = array();
		foreach($settings['log_file_names'] as $key => $val) {
			$log_files_array[$key] = decode_path($val);
		}
	}
	
	if(isset($_SESSION['user'])) {
		$log_files_array["LOG_SQL"] = $_SESSION['user']."_".session_id().".txt";
//		$log_files_array["LOG_ALLERGENI"] = "allergeni_".$_SESSION['user']."_".date("Ymd").".log";
	}

//	echo "LOG FILES:<pre>"; print_r($log_files_array); echo "</pre>";

	return $log_files_array;
}

function get_log_file_name($type) {
	$log_files_array = get_log_file_names();
	
	return $log_files_array[$type];
}
function goTopPage($seconds=1) {
?>
  <script>
  setTimeout(function(){document.body.scrollTop = document.documentElement.scrollTop = 0;},<?= $seconds*1000 ?>);
  </script>
<?php
}

function get_image_path($file_image) {
	global $settings;

//	echo "FILE_IMAGE:$file_image<br>";
	
	$file_name = basename($file_image);
	$sub_dir = str_replace("/", "", dirname($file_image));
//	echo "FILE_NAME:",$file_name."_SUB_DIR:".$sub_dir."<br>";

	if(in_array($sub_dir, array(".", ".."))) {
		$sub_dir = "";
	}

	$file = "";
	if($settings['p13n']!="")
		$file = check_image_path($file_name, $settings['p13n'], $sub_dir);
//	echo "FILE:$file<br>";

	if($file=="") {
		$file = check_image_path($file_name, "common", $sub_dir);
	}
//	echo "FILE:$file<br>";

	return $file;
}

function check_image_path($file_name, $dir, $sub_dir="") {
	$file_path = "themes/".$dir."/images/";
	
	if($sub_dir!="")
		$file_path .= $sub_dir."/";
//	echo "FILE_PATH:$file_path<br>";

	$file = $file_path.$file_name;
//	echo "FILE:$file<br>";

	if(file_exists($file)) {
		return $file;
	}
	
	return "";
}

function wi400_mkdir($pathname, $mode = 0777, $recursive = false) {
	$oldmask = umask(0);
	$rs = False;
	if (!file_exists($pathname)) {
		$rs = mkdir($pathname, 0777, $recursive);
		chmod($pathname, 0777);
		umask($oldmask);
	}
	
	/*$file = fopen('/www/zendsvr/htdocs/wi400_pasin/zAlberto.txt', "a");
	
	//chmod($pathname, 0777);
	if($mode == 0777 || $mode == 777) {
		fwrite($file, "UGUALE 0777 ->");
		$rs = mkdir($pathname, 0777, $recursive);
	}else {
		fwrite($file, "UGUALE $mode ->");
		$rs = mkdir($pathname, $mode, $recursive);
	}
	$perms1 = substr(sprintf('%o', fileperms($pathname)), -4);
	umask($oldmask);
	fwrite($file, "$pathname -> $mode -> $recursive -> scritto con $perms1\r\n");
	fclose($file);*/
	return $rs;
}

function wi400_filter_date_fast_filter($field, $date, $option){
	$dateModel = dateViewToModel($date);
	
	$filter = $field." $option ".$dateModel;
//	echo "FILTER: $filter<br>";

	return $filter;
}

function empty_dir($path) {
	if(file_exists($path)) {
		$dir_handle = opendir($path);

		// Recupero dei file della directory
		while(($file_name = readdir($dir_handle))!==false) {
			if($file_name!="." && $file_name!="..") {
//				echo "FILE_NAME:$file_name<br>";

				$file = $path."/".$file_name;
//				echo "FILE:$file<br>";
				
				if(is_dir($file)) {
					remove_dir($file);
				}
				else {
					unlink($file);
				}
			}
		}
			
		closedir($dir_handle);
	}
}

function remove_dir($path) {
	empty_dir($path);
	
	rmdir($path);
}

function check_download_file_abil($filename) {
	global $settings;
	
	$abilitato = false;
	if(isset($settings['download_dir']) && !empty($settings['download_dir'])) {
		$separetor = array('\\' => '/', '/' => '\\');
		$filename = str_replace($separetor[DIRECTORY_SEPARATOR], DIRECTORY_SEPARATOR, $filename);
		
		foreach($settings['download_dir'] as $dir) {
			$dir = decode_path($dir);
			//echo "DIR: $dir<br>";
			//echo "FILENAME: $filename<br>";
			//echo "LEN: ".strlen($dir)."<br>";
			if(strncasecmp($dir, $filename, strlen($dir))==0) {
				//echo "ABILITATO<br>";
				$abilitato = true;
				break;
			}
			else {
				//echo "NON ABILITATO<br>";
				continue;
			}
		}
	}
	
	return $abilitato;
}

function signal_event($type, $des, $notify=array()) {
	global $db, $settings, $messageContext;
	
	// Inserisco evento
	$table = "FEVENTLST";
	
	// ID
	$id = getSysSequence("EVENTI");
	$id = substr($id, 1);
	$id = "E".str_pad($id, 9, "0", STR_PAD_LEFT);
//	echo "ID: $id<br>";

//	echo "DES: "; var_dump($des); echo "<br>";

	$des_len = 300;
	if(isset($_settings['event_des_len']))
		$des_len = $_settings['event_des_len'];
//	echo "LEN: $des_len<br>";
	
	$new_des = $des;
	if(!empty($des_len) && strlen($des)>$des_len)
		$new_des = substr(trim($des), 0, $des_len);
//	echo "NEW DES: "; var_dump($new_des); echo "<br>";

//	$fieldsValue = getDs($table);
	$fieldsValue = array(
		"ID" => $id,
		"TIPO" => $type,
		"DES" => $new_des,
		"STATO" => "0",
		"DATA_INS" => date("Ymd"),
		"ORA_INS" => date("his")
	);

	$stmt_ins = $db->prepare("INSERT", $table, null, array_keys($fieldsValue));
	
	$result = $db->execute($stmt_ins, $fieldsValue);
	
	// Inserisco a chi notificare l'evento
	$table = "FEVENTNTF";
	
//	$fieldsValue = getDs($table);
	$fieldsValue = array(
		"ID" => $id,
		"EMAIL" => "",
		"TIPO" => ""
	);
	
	$stmt_ntf = $db->prepare("INSERT", $table, null, array_keys($fieldsValue));
	
	if(!empty($notify)) {
		foreach($notify as $val) {
			$campi = $fieldsValue;

			$campi['EMAIL'] = $val['EMAIL'];
			$campi['TIPO'] = $val['TIPO'];
	
//			echo "INSERT - FIELDS:<pre>"; print_r($fieldsValue); echo "</pre>";
		
			$result = $db->execute($stmt_ntf, $campi);
		}
	}	
}
function setPidFile($reset=True) {
    global $db, $settings;
    if ($reset == True) {
		//unlink($file_path);
	}
	if (isset($_SESSION['user'])) {
		// Array informazioni da salvare
		$id_db = $db->getInfoDb();		

		$dati = array();
		$dati['PID']= posix_getpid();
		$dati['JOBINFO']=implode("_",getJobInfo(True));
		$dati['DBINFO'] = str_replace('/', '_', $id_db);
		$dati['USER'] = $_SESSION['user'];
		$dati['TIMESTAMP'] = time();
		$dati['SESSION']=session_id();
		// Recupero nome file
		// @todo aggiungere is callable ..
		$file_pid = wi400File::getCommonFile("USRPIDS", "PID_".posix_getpid().".txt");
		$file_job = wi400File::getCommonFile("USRPIDS", "JOB_".$dati['JOBINFO'].".txt");
		$file_db = wi400File::getCommonFile("USRPIDS", "DBI_".$dati['DBINFO'].".txt");
		$_SESSION['wi400_pid_file']= array($file_pid, $file_job, $file_db);
		$ds = serialize($dati);
		// Scrivo il file
		file_put_contents($file_pid, $ds);
		file_put_contents($file_job, $ds);
		file_put_contents($file_db, $ds);
		
		if(isset($settings['pid_monitor']) && $settings['pid_monitor']==True) {
			// Scrittura database
			$file_path = p13n( "/modules/shutdown/run_process_functions.php");
			if (file_exists($file_path)) {
				
				$tab_runproc = 'ZRUNPROC';
				require_once $file_path;
			
				//runproc_shutdown(session_id());
				$exist = runproc_exist_picFile(session_id());
				$id_as400 = explode("_", $dati['JOBINFO']);
				$id_as400 = $id_as400[2].'/'.$id_as400[0].'/'.$id_as400[1];
				if(!$exist) {
					runproc_insert_pidFile(session_id(), $dati['PID'], $id_as400, $id_db);
				}else {
					runproc_update_pidFile(session_id(), $dati['PID'], $id_as400, $id_db);
				}
			}
		}
	}
	
}
// This is our shutdown function, in here we can do any last operations before the script is complete.
function shutdown() {
	global $settings, $db;
	
	$last_error = error_get_last();
	if (isset($last_error)) {
		if(($last_error['type']===E_ERROR) || ($last_error['type']===E_PARSE)) {
			// fatal error
			$log_msg = $last_error['message']. " - ". $last_error['file']." - " .$last_error['line'];		
			
			// Aggiunta del messaggio di errore alla lista di eventi da notificare
			$event = "event_fatal_error";
			if(isset($settings[$event]) && $settings[$event]===true) {
				$notify = array();
				if(isset($settings[$event."_notify"]) && !empty($settings[$event."_notify"]))
					$notify = $settings[$event."_notify"];
			
				signal_event("FATAL ERROR", $log_msg, $notify);
			}		
		}
	}
	// Azzeramento file PID sessione terminale
	if (isset($settings['pid_monitor']) && $settings['pid_monitor']==True) {
		// ELimino il file
		if (isset($_SESSION['wi400_pid_file'])) {
			foreach ($_SESSION['wi400_pid_file'] as $key => $file) {
				@unlink($file);
			}
		}
	}
	// Operazioni finali personalizzate
	$do = p13n( "/modules/shutdown/shutdown_function.php");
	if (file_exists($do)) {
		require_once $do;    
	}

	// Scrittura database
	if(isset($settings['pid_monitor']) && $settings['pid_monitor']==True) {
		$file_path = p13n( "/modules/shutdown/run_process_functions.php");
		if (file_exists($file_path)) {
			require_once $file_path;
			
			runproc_shutdown(session_id());
		}
	}
}
/*
function manage_eval_condition($cond, $row, $common_value=array(), $key=null) {
	$value = "";
	
//	echo "CONDITION:<pre>"; print_r($cond); echo "</pre>";
//	echo "COMMON_VALUE:<pre>"; print_r($common_value); echo "</pre>";
	
	if(is_array($cond)>0) {
		$condition = false;
		foreach($cond as $rowCondition) {
//			echo "COND:".$rowCondition[0]."<br>";
			$evalValue = substr($rowCondition[0], 5).";";
			
			if(!empty($common_value))
				$evalValue = manage_eval_condition_common($evalValue, $common_value);
//			echo "EVAL_VALUE:$evalValue<br>";

			eval('$condition='.$evalValue.';');			
			
//			echo "EVAL_RES: $condition<br>";
			
			if($condition) {
				$value = $rowCondition[1];
				break;
			}
		}
	}
	else if(strpos($cond, "EVAL:")===0) {
		$evalValue = substr($cond, 5).";";
		
		if(!empty($common_value))
			$evalValue = manage_eval_condition_common($evalValue, $common_value);
		
		eval('$value='.$evalValue);
	}
	else {
		if(isset($key)) {
			if(!isset($row[$key])){
				$value = $cond;
			}
			else {
				$value = $row[$key];
			}
		}
		else {
			$value = $cond;
		}
	}
	
	return $value;
}
*/
/**
 * Gestisce la composizione di un EVAL comune a una o più condizioni di una o più colonne della lista
 * in modo che questo venga eseguito una volta sola per riga e poi il risultato venga sostituito al marker ##COMMON_LIST## o ##COMMON_COLUMN##
 * in più eval specifici che hanno bisogno di eseguire lo stesso controllo ogni volta (es: readonly, default value, ...)
 *//*
function manage_eval_condition_common($evalValue, $common_value) {
//	echo "COMMON COND:<pre>"; print_r($common_value); echo "</pre>";

	if(!empty($common_value)) {
		if(strpos($evalValue, "##COMMON_LIST##")!==false) {
			if($common_value['LIST']=="")
				$common_value['LIST'] = "''";
				
			$evalValue = str_replace("##COMMON_LIST##", $common_value['LIST'], $evalValue);
		}
	
		if(strpos($evalValue, "##COMMON_COLUMN##")!==false) {
			if($common_value['COLUMN']=="")
				$common_value['COLUMN'] = "''";
	
			$evalValue = str_replace("##COMMON_COLUMN##", $common_value['COLUMN'], $evalValue);
		}
	
//		echo "EVAL: $evalValue<br>";
	}
	
	return $evalValue;
}
*/
function get_switch_bool_value($detail, $campo, $start_true=false) {
	$obj = wi400Detail::getDetailField($detail, $campo);
//	echo "SWITCH:<pre>"; var_dump($obj); echo "</pre>";
	
//	echo "<font color='red'>SWITCH ID:</font> ".$obj->getId()."<br>";
//	echo "SWITCH VAL: "; var_dump($obj->getValue()); echo "<br>";
//	echo "SWITCH CHECK: "; var_dump($obj->getChecked()); echo "<br>";
	
	$check = false;
	if($obj!="") {
		$check = $obj->getChecked();
	}
	else if($start_true===true) {
		return true;
	}
	
	return $check;
}

function get_switch_value($detail, $campo, $start_true=false, $si="S", $no="N") {
	$check = get_switch_bool_value($detail, $campo, $start_true);

	$val = get_switch_check_value($check, $si, $no);

	return $val;
}

function get_switch_check_value($check, $si="S", $no="N") {
	$val = $no;
	if($check!=false)
		$val = $si;

	return $val;
}

//function get_checkbox_values($campo, $azione, $form, $last_action, $last_form, $step_rif, $array_campo) {
//function get_checkbox_values($campo, $last_action, $last_form, $step_rif, $array_campo) {
function get_checkbox_values($check_box, $last_step, $step_rif, $array_campo) {
//function get_checkbox_values($check_az, $campo, $last_step, $step_rif, $array_campo) {
	global $actionContext;
	
	$array_sel = array();
	
//	echo "REQUEST:<pre>"; print_r($_REQUEST); echo "</pre>";

//	echo "<font color='green'>STEP RIF:</font> $step_rif<br>";
//	echo "<font color='blue'>LAST STEP:</font> $last_step<br>";
	
	$azione_c = "";
	if(isset($_REQUEST['CURRENT_ACTION']))
		$azione_c = $_REQUEST['CURRENT_ACTION'];
	
	$form_c = "";
	if(isset($_REQUEST['CURRENT_FORM']))
		$form_c = $_REQUEST['CURRENT_FORM'];
	
	$step_prov = $azione_c."_".$form_c;
//	echo "<font color='red'>STEP PROV:</font> $step_prov<br>";
	
	$current_step = $azione_c."_".$form_c;
//	echo "<font color='red'>CURRENT STEP:</font> $current_step<br>";
/*	
	$azione = "";
	if(isset($_REQUEST['t']))
		$azione = $_REQUEST['t'];
	
	$form = "";
	if(isset($_REQUEST['f']))
		$form = $_REQUEST['f'];
	
	$current_step = $azione."_".$form;
//	echo "<font color='red'>CURRENT STEP:</font> $current_step<br>";
*/	
//	$check_box = $azione."_".$campo;
//	echo "CHECK BOX: $check_box<br>";
	
//	echo "SESSION CHECK BOZ:<pre>"; print_r($_SESSION[$check_box]); echo "</pre>";
/*	
	if($current_step==$step_rif && isset($_SESSION[$check_box])) {
//		echo "HERE1<br>";
		$array_sel = $_SESSION[$check_box];
	}
	else if($last_action!="" && $last_action!=$azione) {
//		echo "HERE2<br>";
		$array_sel = $_SESSION[$check_box];
	}
	else {
		if($current_step==$step_rif && !isset($_SESSION[$check_box])) {
//			echo "HERE3<br>";
//			$array_sel = array_keys($tipo_recapito_array);
//			$array_sel = array();
		}
		else if(isset($last_step) && $last_step==$step_rif) {
*//*
			if(isset($_SESSION[$check_box])) {
				echo "HERE4<br>";
				$array_sel = $_SESSION[$check_box];
			}
			else {
*//*
//				echo "HERE5<br>";
				foreach($array_campo as $key => $val) {
					if(isset($_REQUEST[$key])){
						$array_sel[] = $key;
					}
				}
//			}
		}
		
//		echo "HERE<br>";
	
		$_SESSION[$check_box] = $array_sel;
	}
*/	
//	if(isset($last_step) && $last_step==$step_rif && $current_step==$step_rif) {
	if($step_prov==$step_rif) {
//		echo "SAVE IN SESSION<br>";

//		echo "CAMPO: $campo<br>";
		
		foreach($array_campo as $key => $val) {
			if(isset($_REQUEST[$key])){
				$array_sel[] = $key;
			}
/*
			$key_val = $campo."_".$key;	
			if(isset($_REQUEST[$key_val])){
				$array_sel[] = $key;
			}
//			echo "CHIAVE: $key_val - VAL: $key<br>";
*/		
		}
	
		$_SESSION[$check_box] = $array_sel;
	}	
	else {
//		echo "GET FROM SESSION<br>";
		
//		$check_box = $check_az."_".$campo;

		if(isset($_SESSION[$check_box]))
			$array_sel = $_SESSION[$check_box];
	}
//	echo "<font color='orange'>ARRAY SEL:</font><pre>"; print_r($array_sel); echo "</pre>";
	
	return $array_sel;
}
/*
function get_checkbox_values($detail, $campo) {
//	echo "<font color='red'>CHECKBOX ID:</font> $campo<br>";

	$values = wi400Detail::getDetailValue($detail, $campo);
	
//	echo "VALUES:<pre>"; print_r($values); echo "</pre>";

	return $values;
}
*/
function get_text_condition($campo, $azione) {
	$option = "";
	if(isset($_REQUEST[$campo.'_OPTION'])) {
		$option = $_REQUEST[$campo.'_OPTION'];
		$_SESSION[$azione.'_'.$campo.'_OPTION'] = $option;
	}
	else if(isset($_SESSION[$azione.'_'.$campo.'_OPTION'])) {
		$option = $_SESSION[$azione.'_'.$campo.'_OPTION'];
	}
		
	return $option;
}

function get_text_condition_array() {
	$text_option_array = array(
		"INCLUDE"=> _t("CONTIENE"),
		"START"=> _t("INIZIA_PER"),
//		"END"=> _t("FINISCE_PER"),
		"EQUAL"=> _t("UGUALE_A"),
		"NOT_INCLUDE" => _t("NON_CONTIENE"),
		"NOT_START" => _t("NON_INIZIA_PER"),
		"NOT_EQUAL" => _t("DIVERSO_DA"),
		"EMPTY" => _t("VUOTO"),
		"NOT_EMPTY" => _t("NON_VUOTO")
	);

	return $text_option_array;
}

function get_text_condition_des($option, $valore) {
	$des_option = get_text_condition_array();
	$des_option["END"] = _t("FINISCE_PER");

	if(in_array($option, array("EMPTY", "NOT_EMPTY")))
		$text = $des_option[$option];
	else
		$text = $des_option[$option].": $valore";

	return $text;
}

function where_text_condition($option, $value, $campo, $case_sen=true) {
	global $settings;
	
	$filterWhere = "";

//	echo "OPTION: $option - VALUE: $value - CAMPO: $campo<br>";

	if(in_array($option, array("EMPTY", "NOT_EMPTY")) || (isset($value) && $value!="")) {
		$valueToSearch = $value;
		
		$filterWhere = $campo;
		
		if($case_sen===true) {
			$valueToSearch = strtoupper($value);
			$filterWhere = "UPPER($campo)";
		}
/*		
		// @todo SERVE ?????
		$encoding = 'UTF-8';
		if(isset($settings['encoding']))
			$encoding = $settings['encoding'];
		$valueToSearch = mb_strtoupper($valueToSearch, $encoding);
*/
		if (in_array($option,array("EQUAL","EMPTY")))
			$filterWhere .= " = ";
		else if (in_array($option,array("START","INCLUDE","END")))
			$filterWhere .= " LIKE ";
		else if (in_array($option,array("NOT_START","NOT_INCLUDE")))
			$filterWhere .= " NOT LIKE ";
		else if (in_array($option,array("NOT_EQUAL","NOT_EMPTY")))
			$filterWhere .= " <> ";

		$filterWhere .= "'";

		if (in_array($option,array("INCLUDE","NOT_INCLUDE","END")))
			$filterWhere .= "%";
		else if (in_array($option,array("EMPTY","NOT_EMPTY"))) {
			$valueToSearch = "";
		}

//		$filterWhere .= $valueToSearch;
		$filterWhere .= sanitize_sql_string($valueToSearch);

		if (in_array($option,array("START","INCLUDE","NOT_START","NOT_INCLUDE")))
			$filterWhere .= "%";

		$filterWhere .= "'";
	}

	return $filterWhere;
}

function write_file_txt($file, $dati) {
	$handle = fopen($file, "a");
	fwrite($handle, $dati."\r\n");
	fclose($handle);
}
/*
function get_query_case_cond($values_array, $campo, $new_campo, $def="''") {
	$cond_array = array();
	foreach($values_array as $key => $val) {
//		$cond_array[] = "(case when $campo='$key' then '$val'";
		$cond_array[] = "(case when $campo='$key' then '".utf8_decode($val)."'";
	}
		
	$cond = implode(" else ", $cond_array);
	
	$cond .= " else $def";
		
	foreach($values_array as $key => $val) {
		$cond .= " end) ";
	}
		
	$cond .= " as $new_campo";
		
	return $cond;
}
*/
function get_query_case_cond($values_array, $campo, $new_campo, $def="''") {
	$cond = "(case";
	foreach($values_array as $key => $val) {
		if($campo!="")
			$cond .= " when $campo='$key' then '".utf8_decode($val)."'";
		else 
			$cond .= " when $key then '".utf8_decode($val)."'";
	}
	$cond .= " else $def";
	$cond .= " end)";
	$cond .= " as $new_campo";
	
//	echo "COND: $cond<br>";
	
	return $cond;
}

function check_load_file($campo, $types=array(), $required=true) {
	global $messageContext;
	
//	echo "CAMPO: $campo<br>";
//	echo "FILE: <pre>"; print_r($_FILES['IMPORT_FILE']); echo "</pre>";
	
	if(isset($_FILES[$campo])) {
		$file = $_FILES[$campo];

		if(is_uploaded_file($file['tmp_name'])) {
			$check = check_file_cond($file['tmp_name'], $file['name'], $file['size'], $types);
//			echo "CHECK: "; var_dump($check); echo "<br>";

			if($check!==false) {
				return $file;
			}
		}
		else {
			switch($HTTP_POST_FILES[$campo]['error']){
				case 0: //no error; possible file attack!
					echo $msg = "There was a problem with your upload.";
					break;
				case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
					echo $msg = "The file you are trying to upload is too big.";
					break;
				case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
					echo $msg = "The file you are trying to upload is too big.";
					break;
				case 3: //uploaded file was only partially uploaded
					echo $msg = "The file you are trying upload was only partially uploaded.";
					break;
				case 4: //no file was uploaded
					echo $msg = "You must select an image for upload.";
					break;
				default: //a default error, just in case!  :)
					echo $msg = "There was a problem with your upload.";
					break;
			}
			
			$messageContext->addMessage("ERROR", $msg);

			$messageContext->addMessage("ERROR",_t('ERROR_FILE_UPLOAD'));
		}
	}
	else if($required===true) {
		$messageContext->addMessage("ERROR",_t('ERROR_FILE_UPLOAD'));
	}
	
	return false;
}

function check_file_cond($file, $file_name, $size, $types=array()) {
	global $messageContext, $settings;
	
	$up_max_size = 1200000000;
	if(isset($settings['UPLOAD_FILE_MAX_SIZE']))
		$up_max_size = $settings['UPLOAD_FILE_MAX_SIZE'];
	
	// Controllo che il file non superi i 2 MB
	if($size > $up_max_size) {
		$messageContext->addMessage("ERROR",_t('ERROR_FILE_DIMENSION',array(10)));
	}
	else {
		$file_parts = pathinfo($file_name);
//		echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
	
		$extension = $file_parts['extension'];
			
		if(!empty($types) && !in_array(strtoupper($extension), $types)) {
			$formati = implode("/", $types);
			$messageContext->addMessage("ERROR",_t('ERROR_FILE_FORMAT', array($formati)));
		}
		else if(!file_exists($file)) {
			$messageContext->addMessage("ERROR",_t('FILE_NOT_FOUND'));
		}
		else {
			return $file;
		}
	}
	
	return false;
}

function checkIfZipLoaded() {
	// Estensioni necessarie per il funzionamento delle classi di PHPExcel
	// Example loading an extension based on OS
	if (function_exists("dl")) {
		if (!extension_loaded('sqlite3')) {
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				dl('php_zip.dll');
				dl('php_xml.dll');
				dl('php_gd2.dll');
			} else {
				if (!extension_loaded('zip'))
					dl('zip.so');
				/*
				dl('xmlreader.so');
				dl('xmlwriter.so');
				dl('xmlrpc.so');
				dl('xsl.so');
				dl('iconv.so');
				dl('mbstring.so');
				*/
			}
		}
	}
}

/**
 * Recupero i dati di un detail
 * @param unknown $idDetail
 */
function getDatiFromDetail($idDetail) {
	$dati = array();
	if (existDetail($idDetail)){
		$detailSessionObj = getDetail($idDetail);
		$fieldsList = $detailSessionObj["FIELDS"];
		foreach($fieldsList as $fieldId => $field){
			$dati[$field->getName()]=$field->getValue();
		}
	}
	return $dati;
}
/**
 * @desc Serialize data and return unique ID
 **/
function serializeAndGetUinqueID($dati=null) {
	$unique_id = uniqid("AJP_", true);
	if (isset($dati)) {
		$info = $dati;
	} else {
		$info = $_POST;
	}
	$filename = wi400File::getUserFile("ajax_post", $unique_id.".post");
	$handle = fopen($filename, "w");
	fwrite($handle, serialize($info));
	fclose($handle);
	return $unique_id;
}
/**
 * getSerialzed data
 */
 function serializeGetIDData($id) {
	$filename = wi400File::getUserFile("ajax_post", $id.".post");
	$custom_data = unserialize(file_get_contents($filename));
	$_REQUEST = array_merge($_REQUEST, $custom_data);
}

/**
 * Funzione per la connessione ad un server esterno tramite ftp
 * @param unknown $ip_addr: Indirizzo IP del server remoto a cui connetters
 * @param unknown $user: Utente per la connessione
 * @param unknown $pwd: Password per la connessione
 * @param unknown $chdir: Se necessario, directori sulla quale spostarsi all'interno del server remoto 
 * 	Notare che il path iniziale è /home/<cartella>/ 
 * 	e che la directory sulla quale spostarsi deve essere indicata con path relativo <cartella 1>/<cartella 2>/
 * 	(es: $chdir = "edi/in/pdf"; // Path iniziale è /home/crm/ )
 * @param string $ftp_on: flag per indicare se la connessione tramite ftp è abilitata o meno
 * 	(di solito viene ricavato da parametri di configurazione personalizzati del cliente)
 * @return resource|boolean $connection: Ritorna la connessione al server remoto o false se questa è fallita
 */
function get_ftp_connection($ip_addr, $user, $pwd, $chdir="", $ftp_on=true, $clean_con=False, $secure=False) {
	global $messageContext;
//	global $ftp_on;

	static $connection;
	static $login;
	
	static $chiave;
	
	if($clean_con===true) {
//		echo "<font color='orange'>CLEAN CONNESSIONE</font><br>";

		unset($connection);
		unset($login);
		unset($chiave);
	}

//	echo "IP:$ip_addr<br>";
//	echo "USER:$user<br>";
//	echo "PWD:$pwd<br>";
//	echo "CHDIR:$chdir<br>";
//	echo "FTP_ON:$ftp_on<br>";

	set_time_limit(0);

	if($ftp_on==true) {
		$cur_chiave = $ip_addr.$user.$chdir;

//		echo "<font color='green'>FTP_ON</font><br>";
		if(!isset($connection) || $chiave!=$cur_chiave) {
			$chiave = $cur_chiave;

			if ($secure==True && function_exists('ftp_ssl_connect')) {
				$connection = ftp_ssl_connect($ip_addr);
            } else {
				$connection = ftp_connect($ip_addr);
			}	
			if($connection!==false) {
				$login = ftp_login($connection, $user, $pwd);
/*
				if($login!==false) {
					ftp_chdir($connection, $chdir);
				}
*/
//				echo "<font color='red'>CONNECTION</font><br>";
			}
		}
		
//		echo "Current directory is now: " . ftp_pwd($connection) . "<br>";
		
		if($chdir!="") {
			if($connection!==false) {
				if($login!==false) {
//					ftp_chdir($connection, $chdir);
					if (ftp_chdir($connection, $chdir)) {
//						echo "Current directory is now: " . ftp_pwd($connection) . "<br>";
					}
					else {
						$messageContext->addMessage("ERROR", "Non è stato possibile cambiare l'indirizzo nel server remoto");
					}
				}
			}
		}
			
		return $connection;
	}

	return false;
}

function get_file_image($filepath, $filename="", $remote="", $zoom=false, $height="", $width="") {
	if($filename=="")
		$path_img = $filepath;
	else
		$path_img = $filepath."/".$filename;
//	echo "PATH IMG: $path_img<br>";

	if($remote!="")
		$path_img = $remote.$path_img;
//	echo "PATH IMG: $path_img<br>";
	
	$wi400Image = new wi400Image("IMG");
	$wi400Image->setUrl($path_img);
	
	if($height!="")
		$wi400Image->setHtmlHeight($height);
	if($width!="")
		$wi400Image->setHtmlWidth($width);
	
	$wi400Image->setShowZoom($zoom);
	if($zoom===true)
		$wi400Image->setZoomUrl($path_img);
	
//	echo "URL: ".$wi400Image->getUrl()."<br>";
//	echo "IMG: ".$wi400Image->getHtml()."<br>";
	
	return $wi400Image->getHtml();
}

function delete_dir_files($dir) {
	$mydir = $dir."*";
	if($objs = @glob($mydir)){
		foreach($objs as $obj) {
			@is_dir($obj)? rmdir($obj) : @unlink($obj);
//			apc_delete($obj);
		}
	}
	@rmdir($dir);
}
function writeLogAccess($name) {
	global $settings, $db;
	// Scrittura Log Accesso
	$values= array();
	$values['ZSUTE']= $name; //UTENTE
	$values['ZSESI']= 'OK'; // ESITO LOG
	$values['ZSSWS']= 'WEB'; //USER
	$values['ZSIP']= $_SERVER['REMOTE_ADDR']; //INDIRIZZO IP
	$values['ZDEV']= ''; //DEVICE ?
	$values['ZTIME']= getDb2Timestamp();  //TIMESTAMP
	// Reperisco gli attributi del lavoro
	if (isset($settings['XMLSERVICE'])) {
		$array = getJobInfo();
		$values['ZUSR']=$array['USR'];
		$values['ZJOB']=$array['JOB'];
		$values['ZNBR']=$array['NBR'];
		$values['ZFRE']='';
	}

	$stmtDoc = $db->prepare("INSERT", "ZSLOG", null, array_keys($values));
	$result = $db->execute($stmtDoc, $values);
}
/**
 * @desc logUserAction: Loggo su file tutte le azioni che gli utenti lanciano
 */
function logUserAction($actionRow) {
	global $settings, $db, $INTERNALKEY, $CONTROLKEY;
	// Scrittura Log Accesso
	$values= array();
	$values['ZEUTE']="";
	// Verifico se ho una lista di utenti da loggare
	if (isset($settings['log_user_action_profile']) && isset($_SESSION['user'])) {
		if (in_array("*ALL", $settings['log_user_action_profile']) || in_array($_SESSION['user'], $settings['log_user_action_profile'])) {
        	//
		} else {
			return;
        }
    } else {
			return;
    }
  	if (isset($_SESSION['user'])) $values['ZEUTE']= $_SESSION['user'];
	$values['ZEIP']= $_SERVER['REMOTE_ADDR']; //INDIRIZZO IP
	$values['ZEAZI']= "";
	$values['ZEFRM']= "";
	$values['ZEGTW']= "";
	if (isset($_GET['t'])) $values['ZEAZI']=substr($_GET['t'],0, 30);
	if (isset($_GET['f'])) $values['ZEFRM']=substr($_GET['f'],0, 20);
	if (isset($_GET['g'])) $values['ZEGTW']=substr($_GET['g'],0, 20);
	
	$values['ZETIM']= getDb2Timestamp();  //TIMESTAMP
	$values['ZESES']= session_id();
	// Dimensione URL max 500 byte
	$values['ZEURL']=substr($_SERVER['PHP_SELF']."&".$_SERVER['QUERY_STRING'],0, 500);
	// Reperisco gli attributi del lavoro
	if (isset($settings['xmlservice']) && !isset($_SESSION['CURRENT_JOB'])) {
		//global $JOB, $USER, $NBR;
		$array = getJobInfo();
		$values['ZEUSR']=$array['USR'];
		$values['ZEJOB']=$array['JOB'];
		$values['ZENBR']=$array['NBR'];
	} 
	if (isset($settings['log_user_action_req']) && $settings['log_user_action_req']==True) {
		$file = uniqid("REQ_", True).".txt";
        $fileOutput = wi400File::getCommonFile("REQUEST", $file);
        file_put_contents($fileOutput, serialize($_REQUEST));
        $values['ZEREQ'] = $file;
    }
	$stmtDoc = $db->prepare("INSERT", "ZSLOGEXT", null, array_keys($values));
	$result = $db->execute($stmtDoc, $values);
}
/**
* @desc is_serialized() : Veirifica se un stringa è in formato serializzato
* @param string : $value  Stringa da verificare
* @param string : $result Stringa unserializzata
* @return boolean : True/False
**/

function is_serialized($value, &$result = null)
{
	// Bit of a give away this one
	if (!is_string($value))
	{
		return false;
	}
	// Serialized false, return true. unserialize() returns false on an
	// invalid string or it could return false if the string is serialized
	// false, eliminate that possibility.
	if ($value === 'b:0;')
	{
		$result = false;
		return true;
	}
	$length	= strlen($value);
	$end	= '';
	switch ($value[0])
	{
		case 's':
			if ($value[$length - 2] !== '"')
			{
				return false;
			}
		case 'b':
		case 'i':
		case 'd':
			// This looks odd but it is quicker than isset()ing
			$end .= ';';
		case 'a':
		case 'O':
			$end .= '}';
			if ($value[1] !== ':')
			{
				return false;
			}
			switch ($value[2])
			{
				case 0:
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
				case 7:
				case 8:
				case 9:
					break;
				default:
					return false;
			}
		case 'N':
			$end .= ';';
			if ($value[$length - 1] !== $end[0])
			{
				return false;
			}
			break;
		default:
			return false;
	}
	if (($result = @unserialize($value)) === false)
	{
		$result = null;
		return false;
	}
	return true;
}

function get_coda_stampa($coda_def="") {
//	echo "CODA_DEF:$coda_def<br>";
	
	//$coda_user = get_coda_stampa_user();
	$dati = get_coda_stampa_user();
	$coda_copie = 1;
	$coda_user = "";
	if (isset($dati[0])) {
    	$coda_user = $dati[0];
	}
	if (isset($dati[1])) {
		$coda_copie = $dati[1];
	}
//	echo "CODA_USER:$coda_user<br>";

	$coda = "";
	if(isset($_SESSION['DEFAULT_PRINTER']) && !empty($_SESSION['DEFAULT_PRINTER'])) {
		// Coda già impostata di default o modificata
		$coda = $_SESSION['DEFAULT_PRINTER'];
	}
	else {
		if($coda_user!="") {
			// Coda di default dell'untente
			$coda = $coda_user;
		}
		else if($coda_def!="") {
			// Coda di default generica
			$coda = $coda_def;
		}

		if($coda!="") {
			$_SESSION['DEFAULT_PRINTER'] = $coda;
		}
	}
//	echo "CODA:$coda<br>";
	
	$default = check_coda_stampa_def($coda, $coda_def, $coda_user);
	
	$coda_array = array(
		"CODA" => $coda,
		"DEFAULT" => $default,
		"CODA_DEF" => $coda_def,
		"CODA_USER" => $coda_user,
		"COPIE" => $coda_copie
	);

	return $coda_array;
}

function check_coda_stampa_def($coda, $coda_def, $coda_user) {
	$default = false;
	
	if($coda!="") {
		if($coda==$coda_def)
			$default = "GEN";
		else if($coda==$coda_user)
			$default = "USER";
	}
	
	return $default;
}

function get_coda_stampa_user() {
	$coda_user = "";
	
	$file = get_file_default_printer_user();
	
	$myarray = array();
	if(file_exists($file)) {	
		$handle = fopen($file, "r");
		$from_contents = fread($handle, filesize($file));
		fclose($handle);
//		echo "CONTENTS:$from_contents<br>";
		$dati = unserialize($from_contents);
		//$coda_user = unserialize($from_contents);
		$myarray = explode("|", $dati);
		/*if (isset($myarray['0'])) {
			$coda_user = $myarray['0'];
		}*/
	}
	//showArray($myarray);die();
	return $myarray;
}

function get_file_default_printer_user() {
	$file = wi400File::getUserFile("config", "default_printer.txt");
//	echo "FILE: $file<br>";
	
	return $file;
}
/**
 * @desc javascript_escape: Normalizza Javascript per apici e doppi apici
 * @param stromg $str
 * @return string
 */
function javascript_escape($str) {
	$new_str = '';

	$new_str = htmlspecialchars($str);

	return $new_str;
}

function get_user_abils($tabella) {
	global $db;

	$sql = "select * from $tabella where USRUSR='".$_SESSION['user']."'";
	$result = $db->singleQuery($sql);
	if($row = $db->fetch_array($result))
		return $row;
	else
		return array();
}

/**
 * @desc recupero il template da utilizzare
 * @param string $tipo
 */
function get_template_html($argomento, $tipo, $pers="") {
	global $db;

	static $templateSTMT;

	$template = "";
	$found=False;
	if (!isset($templateSTMT)) {
		$sqlTheme = "SELECT FLD_TEMPLATE FROM ZFLDHTML WHERE OBJ_ARGO=? and OBJ_TYPE=?";
		$templateSTMT = $db->prepareStatement($sqlTheme);
	}
	// Cerco per personalizzazione se presente e passata come parametro
	if ($pers!="") {
		$result = $db->execute($templateSTMT, array($argomento."_".$pers, $tipo));
		if ($row = $db->fetch_array($templateSTMT)) {
			$template = $row['FLD_TEMPLATE'];
		}
	}
	// Cerco 
	if ($template =="") {
		$result = $db->execute($templateSTMT, array($argomento, $tipo));
		if ($row = $db->fetch_array($templateSTMT)) {
			$template = $row['FLD_TEMPLATE'];
		}
	}
	return $template;
}
/**
 * @desc Sostituzione dei <@REQUEST so lookup e decoding Class
 * @param char $where: Stringa da controllare
 * @param array $request: array con parametri
 */
function substituteRequestParmLookup($where, $request) {
	$start = 0;
	$pos = strpos($where, "<@REQUEST(", $start);
	//	echo "LEN: ".strlen($where)."<br>";
	while ($pos  !== false) {
		$fine = strpos($where, "@>", $pos);
		//		echo "FINE: $fine<br>";
		$start = $fine;
		$index = substr($where, $pos + 10, $fine-($pos+11));
		//		echo "INDEX: $index<br>";
		if (isset($request[$index])) {
			if($request[$index]!="") {
				$where = substr($where,0,$pos).$_REQUEST[$index].substr($where, $fine+2);
				/*
					// @todo Passaggio di valori multipli
				if(is_array($_REQUEST[$index])) {
				$glue = ", ";
				if(substr($where,$pos-1, 1)=="'")
					$glue = "', '";
				$where = substr($where,0,$pos).implode($glue, $_REQUEST[$index]).substr($where, $fine+2);
				}
				else {
				$where = substr($where,0,$pos).$_REQUEST[$index].substr($where, $fine+2);
				}
				*/
			}
			else {
				//$where = "";
				$where = substr($where,0,$pos)."".substr($where, $fine+2);
				error_log("LOOKUP @REQUEST KEY $index NOT FOUND!!");
			}
		} else {
			$where = substr($where,0,$pos)."".substr($where, $fine+2);
			error_log("LOOKUP @REQUEST KEY $index NOT FOUND!!");
		}
		//		$pos = @strpos($where, "<@REQUEST(", $start);
		$pos = @strpos($where, "<@REQUEST(", 0);
		//		echo "NEW POS: "; var_dump($pos); echo "<br>";
	}
	return $where;
}

/**
 * @desc applicaFunzioni: Cerca in una stringa i marker con le funzioni da eseguire
 * @param string $testo Testo da tradurre
 */
function applicaFunzioni($testo) {
	global $settings, $root_path, $base_path, $wi400Lang, $language;
	
	$pattern = '/#F(.*?)F#/i';
	$function = "esegui_funzione";
	$testo = preg_replace_callback($pattern, $function, $testo);
	// Ripristino vocabolario originale
	
	return $testo;
	
}

function esegui_funzione($match) {
	global $wi400Lang;

	$esegui = $match[1];
	//print_r($match);

	$last = "";

	// Tolgo caratteri speciali encodizzati in HTML che arrivano dall'editor
	$esegui = html_entity_decode($esegui);

	if (substr(trim($esegui), -1)!==";")
		$last=";";

	//$esegui = str_replace('"', "'", $esegui);
	//$esegui = "number_format(12.0000, 2, ',', '')";
	//die($esegui.$last);

//	eval('$rowValue='.trim($esegui).$last);

	try {
		eval('$rowValue='.trim($esegui).$last);
	}
	catch(Throwable $t) {
//		$rowValue = null;
		$rowValue = trim($esegui);
	}

	return $rowValue;
}

function getFirma($documentHtml, $numFirme, $azione_crea, $form_crea, $size = array()) {
	$html = '<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<script src="routine/jSignature/jSignature.min.noconflict.js"></script>
			<script type="text/javascript" src="routine/jSignature/flashcanvas.js"></script>
						
			<style>
				.center {
					background: #676767;
				}
				.pdf {
					position: relative;
					width: 700px;
					background: white;
					padding-left: 10px;
					padding-right: 10px;
				}
			</style>';
	
	$documentHtml = str_replace('border="0.5"', 'border="1"', $documentHtml);
	
	$html .= "<center class='center'><br/><br/><div class='pdf'>".$documentHtml."</div><br/><br/></center>
				<br/><center><button onClick='getFirma(jQuery);creaPdf();'>Salva</button></center>";

	$html .= '<script type="text/javascript">
				function getFirma2($) {
					var datapair = $sigAutotrasp.jSignature("getData", "image");
					$("#wi400Form").append("<input type=\"hidden\" name=\"FIRMA_AUTOTRASP\" value=\""+datapair[1]+"\">");
				
					datapair = $sigOperatore.jSignature("getData", "image");
					$("#wi400Form").append("<input type=\"hidden\" name=\"FIRMA_OPERATORE\" value=\""+datapair[1]+"\">");
				}
					
				function getFirma($) {
					for(var ele in objFirme) {
						var datapair = objFirme[ele].jSignature("getData", "image");
						$("#wi400Form").append("<input type=\"hidden\" name=\"FIRME["+(parseInt(ele)+1)+"]\" value=\""+datapair[1]+"\">");
					}
				}
				
				function creaPdf() {
					doSubmit("'.$azione_crea.'", "'.$form_crea.'", false, false, "", false, "");
				}
					
				var objFirme = [];
				(function($) {
					var sizeFirm = '.json_encode($size).';
					for(var i=1; i<='.$numFirme.';i++) {
						var $sig = $("#FIRMA_"+i);
						objFirme.push($sig);
						var optionSignature = {"UndoButton":true};
						if(sizeFirm.length) {
							if(sizeFirm[i-1].length == 2) {
								optionSignature.height = sizeFirm[i-1][0];
								optionSignature.width = sizeFirm[i-1][1];
							}
						}
						$sig.jSignature(optionSignature);
					}
					
					$(".jSignature").css("border", "2px dotted black");
					console.log(objFirme);
				})(jQuery);
			</script>';
	
	return $html;
}

function insertTimbroFirmaDigitalePdf($fileName, $print_path, $dati) {
	global $routine_path, $temaDir;

	require_once $routine_path."/FPDF/tcpdf/tcpdf.php";
	require_once $routine_path."/FPDF/fpdi.php";
	
	$pdf = new FPDI("","mm","A4");
	
	// set document information
	$pdf->SetCreator($dati['CREATOR']);
	$pdf->SetAuthor($dati['AUTHOR']);
	$pdf->SetTitle($dati['TITLE']);
	
	$pdf->SetProtection(array('copy', 'modify'), '', null, 0, null);
	//$pdf->SetFont('Courier', '' ,$char);
	$pdf->SetRightMargin(0);
	$pdf->SetAutoPageBreak(True, 10);
	
	//		$pdf->SetStartRowY($this->start);
	$pdf->SetTopMargin(10);
	
	$pdf->setPrintHeader(False);
	$pdf->setPrintFooter(False);
	
	$pdf->setImageScale(1.33);
	
	
	$separetor = array('\\' => '/', '/' => '\\');
	$fileName = str_replace($separetor[DIRECTORY_SEPARATOR], DIRECTORY_SEPARATOR, $fileName);
	$pages = $pdf->setSourceFile($fileName);
	$pdf->SetFont('helvetica', '', 12);
	
	for($i=1; $i<=$pages; $i++) {
		$pdf->AddPage();
		$page = $pdf->ImportPage($i);
		$pdf->useTemplate($page, $i, $i);
	}
	
	$y_firme = 250;
	if($pages > 1) {
		$y_firme = 173;
	}
	
	// create content for signature (image and/or text)
	$pdf->Image('/trilog/www'.$temaDir.'images/timbro.png', 96, $y_firme, 20, 20, 'PNG');
	// define active area for signature appearance
	$pdf->setSignatureAppearance(96, $y_firme, 20, 20);
	
	$pdf->SetXY("7", $y_firme+30);
	$pdf->writeHTML("Firmato il ".date('d/m/Y')." alle ".date("H:i:s"));
	
	// set certificate file
	$certificate = 'file://C:/trilog/data/_CERT/trilog.crt';
	// set additional information
	$info = array(
		'id' => $fileName,
		'Name' => $dati['TITLE'],
		'Location' => $dati['LOCATION'],
		'Reason' => 'Firma documento '.$dati['TITLE'],
		'ContactInfo' => $dati['CONTACT'],
	);
	
	// set document signature
	$pdf->setSignature($certificate, $certificate, '', '', 2, $info);
	
	$file = explode(DIRECTORY_SEPARATOR, $fileName);
	$file = explode(".", $file[count($file)-1]);
	$fileNameFirme = $print_path.$file[0]."_firmato.pdf";
	$pdf->Output($fileNameFirme, 'F');
	
	return $fileNameFirme;
}
function getPerformanceTime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}
function get_my_ip($session_id) {
	$dati = array();
	$lines = "";

	$dati = get_session_data($session_id);
	if(!empty($dati))
		$lines = $dati['LINES'];

	$pos_i = strpos($lines,'MY_IP');
	$dato = substr($lines,$pos_i+12,20);

	$pos_f = strpos($dato,'"');
	$ip = substr($dato,0,$pos_f);

	$match = preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])" .
			"(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $ip);

	if($match==1)
		return $ip;
	else if($match==0)
		return "";
}
function isSecure() {
	return
	(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
	|| $_SERVER['SERVER_PORT'] == 443;
}
function wi400GetSysBoolParameter($parametro, $societa="", $sito="", $deposito="", $interlocutore="") {
	global $db;

	$param = wi400GetSysParameter($parametro, $societa, $sito, $deposito, $interlocutore);
	$enable=filter_var($param['VALORE'],FILTER_VALIDATE_BOOLEAN);

	return $enable;
}
function wi400GetSysStringParameter($parametro, $societa="", $sito="", $deposito="", $interlocutore="") {
	global $db;

	$param = wi400GetSysParameter($parametro, $societa="", $sito="", $deposito="", $interlocutore="");
	$string=$param['VALORE'];
	if (!isset($string)) $string="";
	return $string;
}
function wi400GetSysParameter($parametro, $societa="", $sito="", $deposito="", $interlocutore="") {
	global $db, $tri_anaprz;

	static $parametri_array = array();
	static $stmt_prz;
	static $cache_file;
	// Reperisco il nome del file di cache
	if (!isset($cache_file)) {
		$cache_file = wi400File::getCommonFile("usercache", session_id());
		$parametri_array = fileSerialized($cache_file);
		if ($parametri_array ==null) {
			put_serialized_file($cache_file, array());
			$parametri_array= array();
		}
	}
	if(!$stmt_prz) {
		$sql = "select *
				from ZSYSPARM
				where PARAMETRO=? and SOCIETA=? AND SITO=? AND DEPOSITO=? AND INTERLOCUTORE=?";
		//			echo "SQL: $sql<br>";
		$stmt_prz = $db->singlePrepare($sql, 0, true);
	}

	$chiave = $parametro."|".$societa."|".$sito."|".$deposito."|".$interlocutore;

	if((!array_key_exists($chiave, $parametri_array)) ) {
		// Faccio i vari tentativi
		$found = False;
		// I sempre per chiave completa
		//echo "<br>Tentativo chiave completa!". $chiave;
		$res_prz = $db->execute($stmt_prz, array($parametro, $societa, $sito, $deposito, $interlocutore));
		if($row_rpz = $db->fetch_array($stmt_prz)) {
			//				echo "ROW PRZ:<pre>"; print_r($row_rpz); echo "</pre>";
			$parametri_array[$chiave] = $row_rpz;
			$found = True;
		}
		If ($found==False) {
			// Se non ci sono chiavi specifiche esco con quello che ho trovato
			if ($deposito=="" && $sito=="" && $interlocutore=="" && $societa=="") {
				// Chiave Generica
				//						echo "<br>Tentativo senza sito!";
				$res_prz = $db->execute($stmt_prz, array($parametro, "", "", "", ""));
				if($row_rpz = $db->fetch_array($stmt_prz)) {
					//				echo "ROW PRZ:<pre>"; print_r($row_rpz); echo "</pre>";
					$parametri_array[$chiave] = $row_rpz;
					$found = True;
				}
			} else {
				// II Tentativo per Interlocutore
				if ($interlocutore !="") {
					// Tolgo il sito e cerco il prezzo
					If ($sito!="") {
						//
						//							echo "<br>Tentativo Interlocutore senza deposito!";
						$res_prz = $db->execute($stmt_prz, array($parametro, $societa, $sito, "", $interlocutore));
						if($row_rpz = $db->fetch_array($stmt_prz)) {
							//				echo "ROW PRZ:<pre>"; print_r($row_rpz); echo "</pre>";
							$parametri_array[$chiave] = $row_rpz;
							$found = True;
						}
						// Cerco senza Sito
						if ($found==False) {
							//								echo "<br>Tentativo interlocutore senza sito!";
							$res_prz = $db->execute($stmt_prz, array($parametro, $societa, "", "", $interlocutore));
							if($row_rpz = $db->fetch_array($stmt_prz)) {
								//				echo "ROW PRZ:<pre>"; print_r($row_rpz); echo "</pre>";
								$parametri_array[$chiave] = $row_rpz;
								$found = True;
							}
						}
					}
				}
				//echo "<br>PASSO DI QUA! S:$societa - SITO:$sito - DEPOSITO - $deposito";
				// Cerco togliendo il deposito e l'interlocuore
				if ($found==False) {
					//						echo "<br>Tentativo senza deposito! $servizio, $societa, $sito, $deposito, $interlocutore, $data_rif";
					$res_prz = $db->execute($stmt_prz, array($parametro, $societa, $sito, $deposito, ""));
					if($row_rpz = $db->fetch_array($stmt_prz)) {
						//				echo "ROW PRZ:<pre>"; print_r($row_rpz); echo "</pre>";
						$parametri_array[$chiave] = $row_rpz;
						$found = True;
					}
				}
				if ($found==False) {
					//						echo "<br>Tentativo senza deposito! $servizio, $societa, $sito, $deposito, $interlocutore, $data_rif";
					$res_prz = $db->execute($stmt_prz, array($parametro, $societa, $sito, "", ""));
					if($row_rpz = $db->fetch_array($stmt_prz)) {
						//				echo "ROW PRZ:<pre>"; print_r($row_rpz); echo "</pre>";
						$parametri_array[$chiave] = $row_rpz;
						$found = True;
					}
				}
				// Cerco togliendo il sito, deposito e l'interlocuore
				if ($found==False) {
					//						echo "<br>Tentativo senza sito! PARAMETRO:$parametro";
					$res_prz = $db->execute($stmt_prz, array($parametro, $societa, "", "", ""));
					if($row_rpz = $db->fetch_array($stmt_prz)) {
						//				echo "ROW PRZ:<pre>"; print_r($row_rpz); echo "</pre>";
						$parametri_array[$chiave] = $row_rpz;
						$found = True;
					}
				}
				// Tentativo senza società
				// Cerco togliendo tutto e lasciando solo il nome del parametro
				if ($found==False) {
					//						echo "<br>Tentativo senza sito!";
					$res_prz = $db->execute($stmt_prz, array($parametro, "", "", "", ""));
					if($row_rpz = $db->fetch_array($stmt_prz)) {
						//				echo "ROW PRZ:<pre>"; print_r($row_rpz); echo "</pre>";
						$parametri_array[$chiave] = $row_rpz;
						$found = True;
					}
				}
			}
		}
		// SCrittura file serializzato
		put_serialized_file($cache_file, $parametri_array);
	}
	//		die("fsdfafas");
	//		echo "PREZZI ARRAY:<pre>"; print_r($prezzi_array); echo "</pre>";
	if (isset($parametri_array[$chiave])) {
		return $parametri_array[$chiave];
	} else {
		return false;
	}
}

function wi400GetCssButton($width = "auto", $topColor = "#FFFFFF", $bottomColor = "#A6A6A6", $color = "black", $border = "#A8A8A8") {
	if(!$color) {
		$color = "black";
	}

	$cssButton = "border: solid 1px $border;
	color: $color;
	text-shadow: none;
	width: $width;
	background: -webkit-linear-gradient($topColor, $bottomColor);
	background: linear-gradient($topColor, $bottomColor);
	background: -o-linear-gradient($topColor, $bottomColor);
	background: -moz-linear-gradient($topColor, $bottomColor);";

	return $cssButton;
}

function get_history_steps($off, $steps=null) {
	global $history;

	if(!isset($steps) || empty($steps))
		$steps = $history->getSteps();

	$step_types = array(
		"CURRENT" => count($steps)-1,
		"FIRST" => 0,
		"LAST" => count($steps)-$off,
		"PREV" => count($steps)-$off-1
	);

	$array_steps = array();
	foreach($step_types as $type => $index) {
		$array_steps[$type."_STEP"] = "";
		$array_steps[$type."_ACTION"] = "";
		$array_steps[$type."_FORM"] = "";
	}

	if(!empty($steps)) {
		foreach($step_types as $type => $index) {
			$step_array = get_history_step($index, $steps);
//			echo "STEP ARRAY:<pre>"; print_r($current_array); echo "</pre>";

			foreach($step_array as $key => $val) {
				$campo = $type."_".$key;

				$array_steps[$campo] = $val;
			}
		}
	}
//	echo "ARRAY STEPS:<pre>"; print_r($array_steps); echo "</pre>";

	return $array_steps;
}

function get_history_step($index, $steps=null) {
	global $history;

	if(!isset($steps) || empty($steps))
		$steps = $history->getSteps();

//	echo "INDEX - FIRST: $index<br>";
	if($index<0)
		$index = 0;
//	echo "INDEX - LAST: $index<br>";

	$step = $steps[$index];
//	echo "STEP: $step<br>";

	$action_obj = $history->getAction($step);
	if(isset($action_obj)) {
		$action = $action_obj->getAction();
		$form = $action_obj->getForm();
	}

	$array_step = array(
		"STEP" => $step,
		"ACTION" => $action,
		"FORM" => $form,
	);
//	echo "ARRAY STEP:<pre>"; print_r($array_step); echo "</pre>";

	return $array_step;
}

function get_list_keys_num_to_campi($wi400List, $keyArray) {
	$list_keys = array();
	
	$keys = $wi400List->getKeys();
	
	$i=0;
	foreach($keys as $k => $v) {
		$list_keys[$k] = $keyArray[$i];
		
		$i++;
	}
	
	return $list_keys;
}
function disableInputFocusStyle() {
	echo "<script>var disabledInputFocusStyle = true;</script>";
}

function get_image_base64($filename, $h="", $w="") {
	// Read image path, convert to base64 encoding
	$imageData = base64_encode(file_get_contents($filename));
	$img_html = '<img src="data:image/jpeg;base64,'.$imageData.'"';
	if($h!="" || $w!="") {
		$img_html .= ' style="';
		
		if($h!="")
			$img_html .= 'height:'.$h.'px ';
		
		if($w!="")
			$img_html .= 'width:'.$w.'px';
		
		$img_html .= '"';
	}

	$img_html .= '>';
	
	return $img_html;
}

/*function setWindowSizeCookie($row, $name = 'wi400WindowSize') {
	$wi400WindowSize = json_decode($_COOKIE['wi400WindowSize'], true);
	
	$chiave = $row['WIDAZI']."|".$row['WIDID']."|".$row['WIDREQ'];
	if(!isset($wi400WindowSizeSave[$chiave])) {
		$wi400WindowSize[$chiave] = $row['WIDDFV'];
		setcookie($name, json_encode($wi400WindowSize), time()*3600*24*30*12); //Un anno
	}
}*/

function wi400GetWindowSizeKeyByRequest($request) {
	$chiave = '';
	if(isset($_REQUEST['FROM_LIST'])) {
		$chiave = explode("-", $_REQUEST['FIELD_ID']);
		$chiave = $chiave[0]."_".$chiave[2];
	}else {
		$chiave = array();
		if(isset($_REQUEST['DETAIL_ID'])) $chiave[] = $_REQUEST['DETAIL_ID'];
		if(isset($_REQUEST['FIELD_ID'])) $chiave[] = $_REQUEST['FIELD_ID'];
		$chiave = implode("_", $chiave);
	}
	return $chiave;
}

function wi400SaveWindowSize($wi400WindowSizeSave) {
	global $db;
	
	$file = "ZWIDETPA";
	
	$error = false;
	
	foreach($wi400WindowSizeSave as $key => $size) {
		$key = explode("|", $key);
		if(!isset($key[2])) $key[2] = '';
		
		//$size = explode("|", $size);
		if(wi400ExistWindowSize($key[0], $key[1], $key[2])) {
			$rs = wi400UpdateWindowSize($key[0], $key[1], $key[2], $size);
		}else {
			$rs = wi400InsertWindowSize($key[0], $key[1], $key[2], $size);
		}
		
		if(!$rs) {
			$error = true;
		}
	}
	
	return $error;
}

function wi400ExistWindowSizeCookie($azione, $form, $key, $name = 'wi400WindowSize') {
	$wi400WindowSize = json_decode($_COOKIE[$name], true);
	
	$size = '';

	$chiave = $azione."|".$form."|".$key;
	if(!$wi400WindowSize || !isset($wi400WindowSize[$chiave])) {
		if(!$wi400WindowSize) $wi400WindowSize = array();
		$row = wi400ExistWindowSize($azione, $form, $key);
		if($row) {
			$wi400WindowSize[$chiave] = $row['WIDDFV']."|".$row['WIDTYP'];
			$size = $wi400WindowSize[$chiave];
			
			setcookie($name, json_encode($wi400WindowSize)); //Un anno
		}
	}
	
	return $size;
}

function wi400ExistWindowSize($azione, $form, $key) {
	global $db;

	$file = "ZWIDETPA";
	
	static $stmt_exist_win_size;
	
	if(!isset($stmt_exist_win_size)) {
		$sql = "SELECT WIDAZI, WIDID, WIDREQ, WIDDFV, WIDTYP FROM $file WHERE WIDAZI=? AND WIDDOL='W' AND WIDID=? AND WIDREQ=?";
		$stmt_exist_win_size = $db->singlePrepare($sql);
	}
	
	$rs = $db->execute($stmt_exist_win_size, array($azione, $form, $key));
	$row = $db->fetch_array($stmt_exist_win_size);
	
	if($row) return $row;
	else return false;
}

function wi400UpdateWindowSize($azione, $form, $key, $size) {
	global $db;
	
	$file = "ZWIDETPA";
	
	static $stmt_update_win_size;
	
	if(!isset($stmt_update_win_size)) {
		$sql = "SELECT WIDREQ FROM $file WHERE WIDAZI=? AND WIDDOL='W' AND WIDID=? AND WIDREQ=?";
		$where = array(
			"WIDAZI" => "?",
			"WIDDOL" => 'W',
			"WIDID" => "?",
			"WIDREQ" => "?"
		);
		$stmt_update_win_size = $db->prepare('UPDATE', $file, $where, array('WIDDFV', 'WIDTYP'));
	}
	
	$size = explode("|", $size);
	
	$rs = $db->execute($stmt_update_win_size, array($size[0].'|'.$size[1], $size[2].'|'.$size[3],  $azione, $form, $key));
	if($rs) return true;
	else return false;
}

function wi400InsertWindowSize($azione, $form, $key, $size) {
	global $db;

	$file = "ZWIDETPA";
	
	static $stmt_insert_win_size;
	
	$field = getDs($file);
	
	if(!isset($stmt_insert_win_size)) {
		$stmt_insert_win_size = $db->prepare("INSERT", $file, null, array_keys($field));
	}
	
	$size = explode("|", $size);
	
	$field['WIDAZI'] = $azione;
	$field['WIDDOL'] = "W";
	$field['WIDID'] = $form;
	$field['WIDKEY'] = '*ALL';
	$field['WIDREQ'] = $key;
	$field['WIDDFV'] = $size[0].'|'.$size[1];
	$field['WIDTYP'] = $size[2].'|'.$size[3];
	$field['WIDSTA'] = "1";
	
	$rs = $db->execute($stmt_insert_win_size, $field);
	if($rs) return true;
	else return false;
}
