<?php
/**
 * @desc Scrive sulla cache un contenuto
 * @param $filename string: Nome del file
 * @param $dati mixed:dati da scrivere sul file in modalita' serializzata
 */
function put_serialized_file ($filename, $dati, $time_offset=0 ) {

	   global $settings;
	   	
	   if ($settings['platform']=='AS400') {
	   $fp = fopen($filename, "w+");
	   if (flock($fp, LOCK_EX| LOCK_NB) && (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') ) {
	       file_put_contents($filename, serialize ( $dati ));
		   flock($fp, LOCK_UN);
    	   fclose($fp);
	   }  
	   } else {
	       file_put_contents($filename, serialize ( $dati ),LOCK_EX);
    	   //fclose($fp);	 
	   }   
		
}
/**
 * @desc Controllo se un file e' stato gia' serializzato in cache
 * @param $filename
 */
function fileSerialized($filename) {
	
	global $settings;
	$exist = False;
	if (file_exists ( $filename )) {
		if (isset($settings['caching_persistent']) && $settings['caching_persistent']==True) {
                $exist = True;
		} else {
		$last_modified = filemtime ( $filename );
		if ($last_modified) {
			if (date ( "Ymd", $last_modified ) == date ( "Ymd" ))
				$exist = True;
		}
		}
	}
	// Se esiste carico dal file il descrittore, lo unserializzo e se va tutto ben lo ritorno al chiamante
	if ($exist) {
		$handle = fopen ( $filename, "r" );
		$size = filesize ( $filename );
		if ($handle && $size > 0) {
			$contents = fread ( $handle, $size );
			fclose ( $handle );
			$desc = unserialize ( $contents );
			if (is_array($desc) && count($desc)> 0) {
				// Nulla da fare
			} else {
				return null;
			}
			if ($desc) {
				return $desc;
			}
		}
	}
	return null;
}

/**
 * Pulizia della directory serialize
 *
 */
function clean_dir_serialize() {
	global $settings, $messageContext;
	
	$directory = $settings['data_path']."COMMON/serialize/";
			
	$dir_handle = opendir($directory);
	
	while(($file = readdir($dir_handle))!==false) {
		if($file!="." && $file!="..") {
			$file_path = $directory.$file;
			unlink($file_path);
		}
	}
	
	closedir($dir_handle);
}