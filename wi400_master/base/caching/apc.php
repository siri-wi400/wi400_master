<?php
/**
 * @desc Scrive sulla cache un contenuto
 * @param $filename string: Nome del file
 * @param $dati mixed:dati da scrivere sul file in modalita' serializzata
 */
function put_serialized_file ($filename, $dati,$time_offset=0 ) {
		
	  $do = apc_store($filename, $dati, $time_offset);
	  if (!$do) {
	  	die('Failed to store into APC. Check Configuration');
	  }
}
/**
 * @desc Controllo se un file e' stato gia' serializzato in cache
 * @param $filename
 */
function fileSerialized($filename) {
	
	// Se esiste carico dal file il descrittore, lo unserializzo e se va tutto ben lo ritorno al chiamante
	$desc =apc_fetch($filename); 
	if ($desc) {
				return $desc;
	}
	return null;
}

/**
 * Pulizia della directory serialize
 *
 */
function clean_dir_serialize() {

	apc_clear_cache();
}