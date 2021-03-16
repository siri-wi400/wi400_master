<?php

	/**
	 * @name wi400File
	 * @desc Funzioni per la gestione dei files
	 * @copyright S.I.R.I. Informatica s.r.l.
	 * @author Massimiliano Consigli
	 * @link www.wi400.com
	 */

	class wi400File {

		/**
		 * @desc Restituisce il percorso completo di un file utente
		 *
		 * @param string $context contesto del file
		 * @param string $fileName nome del file completo di estensione
		 * @param string $userForced utente forzato per reperimento nome file
		 * …
		 * @return string percorso completo dalla root del file
		 **/
	    public static function getUserFile($context, $fileName = "", $userForced="") {
	
			global $data_path;
			
			if (isset ( $_SESSION ['user'] ))
				$user = $_SESSION ['user'];
			elseif ($userForced!="") {
				$user = $userForced;
			} else 
				$user = 'BATCH';
			// Patch per macchine LINUX, altrimenti tutto maiuscolo ..
			$user = strtoupper($user);
			if (! file_exists ( $data_path . $user )) {
				wi400_mkdir ( $data_path . $user, 777 );
			}
			if (! file_exists ( $data_path . $user . "/" . $context )) {
				wi400_mkdir ( $data_path . $user . "/" . $context, 777 );
			}
			return $data_path . $user . "/" . $context . "/" . $fileName;
		}

			/**
		 * @desc Restituisce il percorso completo di un file sessione
		 *
		 * @param string $idSession sessione
		 * @param string $fileName nome del file completo di estensione
		 * …
		 * @return string percorso completo dalla root del file
		 **/
	    public static function getSessionFile($idSession, $fileName = "") {
	
			global $data_path;
				
	    	if (! file_exists ( $data_path . "_SESSION" )) {
				wi400_mkdir ( $data_path . "_SESSION" );
			}
			if (! file_exists ( $data_path . "_SESSION/" . $idSession )) {
				wi400_mkdir ( $data_path . "_SESSION/" . $idSession );
			}
			return $data_path . "_SESSION/" . $idSession . "/" . $fileName;
		}
		/**
		 * @desc Restituisce il percorso completo di un file di log
		 *
		 * @param string $context contesto del file
		 * @param string $fileName nome del file completo di estensione
		 * …
		 * @return string percorso completo dalla root del file
		 **/
		public static function getLogFile($context, $fileName = "") {
		
			global $log_path;
		
			if (! file_exists ( $log_path . "logs" )) {
				wi400_mkdir ( $log_path . "logs" );
			}
			if (! file_exists ( $log_path . "logs/" . $context )) {
				wi400_mkdir ( $log_path . "logs/" . $context );
			}
			return wi400File::normalizePath($log_path . "logs/" . $context . "/" . $fileName);
		}
		public static function normalizePath($path) {
			global $settings;
			if ($settings['platform']=="WINDOWS") {
				while( strpos($path, "\\\\") !== false ) {
					$path = str_replace("\\\\","\\",$path);
				}				
			} else {
				while( strpos($path, '//') !== false ) {
					$path = str_replace('//','/',$path);
				}
			}
			return $path;
		}
		/**
		 * @desc Restituisce il percorso completo di un file comune a tutti gli utenti
		 *
		 * @param string $context contesto del file
		 * @param string $fileName nome del file completo di estensione
		 * …
		 * @return string percorso completo dalla root del file
		 **/
		public static function getCommonFile($context, $fileName = "") {
			global $data_path;
			if (! file_exists ( $data_path . "COMMON" )) {
				wi400_mkdir ( $data_path . "COMMON" );
			}
			if (! file_exists ( $data_path . "COMMON" . "/" . $context )) {
				wi400_mkdir ( $data_path . "COMMON" . "/" . $context );
			}
			return $data_path . "COMMON" . "/" . $context . "/" . $fileName;
		}
		
		/**
		 * @desc Controlla se esiste un determinato file comune a tutti gli utenti
		 *
		 * @param string $context contesto del file
		 * @param string $fileName nome del file completo di estensione
		 * …
		 * @return boolean esistenza del file stesso
		 **/

		public static function checkCommonFile($context, $fileName) {
			$fileName = wi400File::cleanFileName ( $fileName );
			$fileName = wi400File::getCommonFile( $context, $fileName );
			if (file_exists ( $fileName )) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * @desc Elimina dal nome di un file eventuali caratteri non supportati
		 *
		 * @param string $fileName nome del file
		 * …
		 * @return string nome del file con escape caratteri
		 **/
		public static function cleanFileName($fileName) {
			$fileName = str_replace ( "/", "_", $fileName );
			$fileName = str_replace ( " ", "_", $fileName );
			$fileName = str_replace ( "'", "_", $fileName );
			$fileName = str_replace ( "\\", "_", $fileName );
			$fileName = str_replace ( "!", "_", $fileName );
			$fileName = str_replace ( "$", "S", $fileName );		
			return $fileName;
		}
		
		/**
		 * @desc Elimina tutti i file sessione di un utente
		 *
		 * @param string $context contesto del file
		 * …
		 * @return void
		 **/
		public static function deleteSessionFiles($idSession) {
		   $dir = wi400File::getSessionFile($idSession);
		   if (stripos($dir, "data")>0){
/*		   	
		   	   $mydir = $dir."*";
			   if($objs = @glob($mydir)){
			        foreach($objs as $obj) {
			 			@is_dir($obj)? rmdir($obj) : @unlink($obj);
			 		    //apc_delete($obj);
			  		}
			 	}
				@rmdir($dir);
*/
		   		delete_dir_files($dir);
		   }
		}
		
		/**
	 	* @desc Rotazione file contenenti nel nome $filePart sopra i $maxNumber
	 	* Esempio  * fileRotation ("/www/zendsvr/htdocs/upload/sync/INVENTARI/", "_mysqlitedb.zip", 4);
	 	*/
		public static function fileRotation($baseDir, $filePart, $maxNumber = 999){
			$results = scandir($baseDir, 1);
			$fileCounter = 0;
			foreach ($results as $file){
				if (strpos($file, $filePart) !== false){
					$fileCounter++;
					if ($fileCounter > $maxNumber){
						unlink($baseDir. $file);
					}
				}
				
			}
		}
		
		/**
		 * @desc Elimina tutti i file utente di un contesto
		 *
		 * @param string $context contesto del file
		 * …
		 * @return void
		 **/
		public static function deleteUserFiles($context) {
		   $dir = wi400File::getUserFile($context);
		   if (stripos($dir, "data")>0){
/*		   	
		   	   $mydir = $dir."*";
			   if($objs = @glob($mydir)){
			        foreach($objs as $obj) {
			 			@is_dir($obj)? rmdir($obj) : @unlink($obj);
			  		}
			 	}
				@rmdir($dir);
*/
		   	delete_dir_files($dir);
		   }
		}
		
		// CANCELLAZIONE FILE DI ESPORTAZIONE
	public static function deleteExportFiles() {
	   $temp1='export';	
	   $dir = wi400File::getUserFile($temp1);
	   if (stripos($dir, "data")>0){/*	   	
		   	   $mydir = $dir."*";
			   if($objs = @glob($mydir)){
			        foreach($objs as $obj) {
			 			@is_dir($obj)? rmdirr($obj) : @unlink($obj);
			  		}
			 	}
				@rmdir($dir);
*/
	   			delete_dir_files($dir);
		   }
		}   
	}
?>