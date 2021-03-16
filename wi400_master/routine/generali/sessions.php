<?php
    global $settings;
	// Attiva i cookie di sessione
	ini_set("session.use_cookies", 1); 
	//ini_set('session.cookie_secure',1);
	//ini_set('session.cookie_httponly',1);
	// Accetta ID solo da cookie, rifiuta se vengono propagati da URL
	ini_set("session.use_only_cookies", 1);

	// Informa PHP che dovrà usare dei gestori definiti da noi
	//ini_set("session.save_handler", "user");

	// Impostiamo la cartella del server che dovrà contenere i file di sessione
	ini_set("session.save_path", $settings['sess_path']);
	if (!file_exists($settings['sess_path'])) {
		wi400_mkdir($settings['sess_path'], 777, True);
	}
	$life = 60*24;
	if (isset($settings['session_life']) && $settings['session_life']!="") {
		$life = $settings['session_life'];
	}
	define("FILE_PREFISSO", "WI400_"); // Inizio nome dei file di sessione
	define("FILE_ESTENSIONE", ".txt"); // Estensione dei file di sessione
	if (isset($settings['session_life']) && $settings['session_life']!="") {
		session_cache_expire($settings['session_life']);
	} else {
		session_cache_expire(60 * 24); // I file di sessione sono validi per 24 ore
	}
	//session_set_cookie_params(0, "/","", False, True); // Il cookie di sessione dura 24 ore
	/*if (!isSecure()) {
		session_set_cookie_params($life, "/","", False, True); // Il cookie di sessione dura 24 ore
	} else {
		session_set_cookie_params($life, "/","", True , True); // Il cookie di sessione dura 24 ore
	}*/
	$session_file = NULL;

	/*function ss_apertura($path, $nome)
	{
		session_cache_expire(60 * 24); // I file di sessione sono validi per 24 ore
		session_set_cookie_params(0); // Il cookie di sessione dura 24 ore
		return true;
	}

	function ss_chiusura()
	{
		global $session_file;
		if (isset($session_file)) {
		return @fclose($session_file); // Chiude il puntatore al file di sessione aperto
		}
		return false;
	}

	function ss_lettura($id)
	{
		$id = session_id();
		$filename = session_save_path() . FILE_PREFISSO . $id . FILE_ESTENSIONE;

		// Tenta di leggere il contenuto del file. Se il file non esiste lo crea
		$session_data = @file_get_contents($filename);

		if (strlen($session_data))
		{ return $session_data; }
		else
		{ return ""; }
	}

	function ss_scrittura($id, $dati)
	{
		global $session_file;

		$id = session_id();
		$filename = session_save_path() . FILE_PREFISSO . $id . FILE_ESTENSIONE;

		try
		{
			$session_file = @fopen($filename, "w+");
			@fwrite($session_file, $dati); // Scriviamo nel file i nuovi dati di sessione

			return true;
		}
		catch (Exception $e)
		{ return false; }
	}

	function ss_distruzione($id)
	{
		$filename = session_save_path() . FILE_PREFISSO . $id . FILE_ESTENSIONE;
		return @unlink($filename); // Cancella il file

	}

	function ss_spazzino($max_lifetime) { return true; }*/

	//session_set_save_handler("ss_apertura", "ss_chiusura", "ss_lettura", "ss_scrittura", "ss_distruzione", "ss_spazzino");
	//session_id("NEW_".uniqid());
	//setcookie("PORTA:", "pippo");
	if (isset($settings['enable_multi_session_byport'])) {
		if (isset($_COOKIE[$_SERVER['SERVER_PORT']])) {
			$id_sessione = $_COOKIE[$_SERVER['SERVER_PORT']];
		} else {
			$id_sessione = uniqid($_SERVER['SERVER_PORT']);
			setcookie($_SERVER['SERVER_PORT'], $id_sessione);
		}
		session_id($id_sessione);
	}
	if (isset($settings['enable_multi_session_byappbase'])) {
		$tmpapp = str_replace(array("/","_","\\") ,"", $appBase);
		if (isset($_COOKIE[$tmpapp])) {
			$id_sessione = $_COOKIE[$tmpapp];
		} else {
			$id_sessione = uniqid($tmpapp);
			setcookie($tmpapp, $id_sessione);
		}
		session_id($id_sessione);
	}
	session_start();
	//echo "</pre>";
	//print_r(session_get_cookie_params());
	//die();
?>