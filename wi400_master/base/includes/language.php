<?php
	global $wi400Lang;
	/*$lang_to_include='';

	if (!isset($_SESSION['user']) || $_SESSION['user'] == ''){
		//$language = $settings['default_language'];
		$language = getDefaultLanguage();
		$lang_to_include=$base_path.'/lang/'.$language.'.lang.php';
		require_once "$lang_to_include";
		//$wi400Lang = parse_ini_file("$base_path/lang/".$settings['default_language'].".lang.php");
	}else{
		//$language = getLanguage($_SESSION['user']);
		$language = $_SESSION['USER_LANGUAGE'];
		if (isset($_SESSION['CUSTOM_LANGUAGE']) && $_SESSION['CUSTOM_LANGUAGE']!="") {
			$language = $_SESSION['CUSTOM_LANGUAGE'];
		}
		if ($language == '') {
		    //$language = $settings['default_language'];
		    $language = getDefaultLanguage();
		    $lang_to_include=$base_path.'/lang/'.$language.'.lang.php';
		    //$wi400Lang = parse_ini_file("$base_path/lang/".$settings['default_language'].".lang.php");
		} else {
		    $lang_to_include=$base_path.'/lang/'.$language.'.lang.php';
			//$wi400Lang = parse_ini_file("$base_path/lang/$language.lang.php");		    
		}
		require_once "$lang_to_include";
		// Verifico la profondità del recupero Vocabolario
		if (isset($settings['language_tree'])) {
			// Package language
			if ($settings['language_tree']>=2) {
				$path = "/base/package/".strtolower($settings['package'])."/lang/$language.lang.php";
				$lang_package=p13n($path);
				if ($lang_package) {
						require_once "$lang_package";
						$wi400Lang = array_merge($wi400Lang, $wi400PackageLang);
				}
			}
		}
	}*/
	$wi400Lang = get_language_array("", "");
	$language = $wi400Lang['__LANGUAGE'];
	// Costruisco il codice linguaggio da 2, metodo da sistemare per eventuali altre lingua che non si risolvono con i pirmi 2 byte
	$language_2 = strtolower(substr($language,0,2));
	/*$_SESSION["DEBUG"]=true;
	getMicroTimeStep("INIZIO");
	echo "<pre>";
	print_r(get_language_array("","resi_vuoti"));
	echo "</pre>";	
	getMicroTimeStep("INIZIO 2");
	echo "<pre>";
	print_r(get_language_array("","resi_vuoti"));
	echo "</pre>";
	getMicroTimeStep("FINE 2");*/
	/**
	 * @desc get_language_array: Recupero array stringhe di linguaggio data una lingua ed una eventuale azione
	 * @param string $language: codice linguaggio, se non passato viene reperito quello dell'utente
	 * @param unknown $modulo
	 */
	function get_language_array($language="", $module="") {
		global $base_path, $settings, $root_path;
		$wi400Lang = array();
		$wi400PackageLang = array();
		$wi400ModuleLang = array();
		$lang_to_include='';
		// Verifico se è stato passato un linguaggio
		if ($language =="") {
			if (!isset($_SESSION['user']) || $_SESSION['user'] == ''){
				$language = getDefaultLanguage();
			}else {
				$language = $_SESSION['USER_LANGUAGE'];
				if (isset($_SESSION['CUSTOM_LANGUAGE']) && $_SESSION['CUSTOM_LANGUAGE']!="") {
					$language = $_SESSION['CUSTOM_LANGUAGE'];
				}
				if ($language == '') {
					$language = getDefaultLanguage();
				}
			}
		}			
		$lang_to_include=$base_path.'/lang/'.$language.'.lang.php';
		require_once "$lang_to_include";
		// Verifico la profondità del recupero Vocabolario
		if (isset($settings['language_tree'])) {
			// Package language
			if ($settings['language_tree']>=2) {
				$path = "/base/package/".strtolower($settings['package'])."/lang/$language.lang.php";
				$lang_package=p13n($path);
				if ($lang_package) {
					require "$lang_package";
					$wi400Lang = array_merge($wi400Lang, $wi400PackageLang);
				}
			}
			// Package language
			if ($settings['language_tree']>=3 && $module!="") {
				$path = "/modules/$module/lang/$language.lang.php";
				$lang_package=p13n($path);
				if ($lang_package) {
					require "$lang_package";
					$wi400Lang = array_merge($wi400Lang, $wi400ModuleLang);
				}
			}
		}
		$wi400Al = array_merge($wi400Lang,$wi400PackageLang,$wi400ModuleLang);
		$wi400Al['__LANGUAGE']=$language;
		//showArray($wi400Al);
		return $wi400Al;
	}