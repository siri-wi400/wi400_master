<?php
/**
 * Operazioni da eseguire una sola volta per ogni utente
 * 
 * Aggiungere qui eventuali altre operazioni
 */  
if (!is_file('.install')) {
    	echo "<h1>Step performed for first time only!</h1>";
    	// controllo presenza folder per log
		$dir_check = array (
			'logs' . DIRECTORY_SEPARATOR . 'error', 
			'logs' . DIRECTORY_SEPARATOR . 'debug', 
			'logs' . DIRECTORY_SEPARATOR . 'email', 
			$settings['doc_root'],
			$settings['data_path'],
			$settings['log_sql'],
			$settings['sess_path'],
			$settings['template_path']
		);
	
		foreach ( $dir_check as $dir ) {
			if (! file_exists ( $dir )) {
				mkdir ( $dir, 0777, true );
				print "<br>";
				print "<p>//**---- directory \"$dir\" not exists. ----**\\</p>";
				print "<p>//**----    it's just been created.     ----**\\</p>";
				print "<p>//**----    Please, reload web page.    ----**\\</p>";
			}
		}
		$handle = fopen('.install', 'a');
		fwrite($handle, 'Do it : '.date("Y-m-d"));
		fclose($handle);
}