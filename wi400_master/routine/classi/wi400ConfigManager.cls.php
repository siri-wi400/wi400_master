<?php

/**
 * @name wi400ConfigManager
 * @desc Classe per la gestione delle configurazioni
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author  
 * @version 1.0 01/01/2019
 * @link www.siri-informatica.it
 * @info info@siri-informatica.it
 * 
 * Al momento la classe viene usata per gestire preferiti, configurazione filtri 
 * di dettaglio e liste, configurazioni liste e filtri master
 * @todo Gestire tutto l'accesso ai file tramite questa classe
 */

class wi400ConfigManager {

	private $config;
	// @ TODO metodo generico per cancellare tutte le configurazioni di un id/key
 	public static function readConfig($key, $id, $user="", $file="", $argo="") {
 		global $settings, $db;
 		if ($user=="") {
 			$user = $_SESSION['user'];
 		}
 		$preferitiArray=False;
 		//$settings['configuration_db']=True;
 		if (isset($settings['configuration_db']) && $settings['configuration_db']==True) {
 		    // Controllo se esiste su DB
 		    $sql ="SELECT CONFTX FROM ZWICONFP WHERE CONFKEY=? AND CONFID=? AND CONFUS=?";
 		    $stmt = $db->singlePrepare($sql);
 		    $do = $db->execute($stmt, array($key, $id, $user));
 		    $row = $db->fetch_array($stmt);
 		    if ($row) {
 		    	$preferitiArray = unserialize(base64_decode($row['CONFTX']));
 		    	if (file_exists($file)) {
 		    		rename($file, $file.".mig");
 		    	}
 		    } else {
 		    	if (file_exists($file)) {
 		    		$handle = fopen($file, "r");
 		    		$contents = fread($handle, filesize($file));
 		    		fclose($handle);
 		    		//$prereritiArray = base64_decode($contents); //commentato per testare il giro normale
 		    		$preferitiArray = unserialize($contents);
 		    		wi400ConfigManager::saveConfig($key,$id,$user,$file,$preferitiArray);
 		    		rename($file, $file.".mig");
 		    	}
 		    }
 		} else {
 			// Recupero contenuto del file
 			if (file_exists($file)) {
	 			$handle = fopen($file, "r");
	 			//$contents = fread($handle, filesize($file));
	 			//fclose($handle);
	 			$contents = "";
	 			while (!feof($handle)) {
	 				$contents.= fgets($handle, 4096);
	 			}
	 			fclose($handle);
	 			//$prereritiArray = base64_decode($contents); //commentato per testare il giro normale
	 			$preferitiArray = unserialize($contents);
 			}

 		}
 		return $preferitiArray;
 	}
 	
 	public static function saveConfig($key, $id, $user="", $file="", $dati=array(), $argo="") {
 		global $settings, $db;
 		//$settings['configuration_db']=True;
 		if ($user=="") {
 			$user = $_SESSION['user'];
 		}
 		if (isset($settings['configuration_db']) && $settings['configuration_db']==True) {
 			 // DELETE
 			$sql ="DELETE FROM ZWICONFP WHERE CONFKEY=? AND CONFID=? AND CONFUS=?";
 			$stmt = $db->singlePrepare($sql, 0, true);
 			$do = $db->execute($stmt, array($key, $id, $user));
 			if(!$do) {
 				developer_debug("Errore DELETE wi400ConfigManager $key, $id, $user");
 			}else {
	 			
	 			// INSERT
	 			$fieldConf = getDS("ZWICONFP");
	 			$fieldConf['CONFKEY']=$key;
	 			$fieldConf['CONFID']=$id;
	 			$fieldConf['CONFUS']=$user;
	 			$fieldConf['CONFFI']=$file;
	 			$fieldConf['CONFTX']= base64_encode(serialize($dati));
	 			$fieldConf['CONTTM']=getDb2Timestamp();
	 			$stmtConf = $db->prepare("INSERT", "ZWICONFP", null, array_keys($fieldConf));
	 			$result = $db->execute($stmtConf, $fieldConf);
	 			if(!$result) {
	 				developer_debug("Errore INSERT wi400ConfigManager $key, $id, $user");
	 			}
 			}
 		}else {
 			 /*$handle = fopen($preferitiFile, "w");
 			 $contents = serialize($preferitiArray);
 			 $contents = base64_encode($contents);
 			 fwrite($handle, $contents);
 			 fclose($handle);*/
 			// Creo la directory se non esiste
 			$dir = dirname($file);
 			if(!file_exists($dir)) {
 				wi400_mkdir($dir, 777, true);
 			}
 			 $handle = fopen($file, "w");
 			 if (flock($handle, LOCK_EX)){
 			 	$putfile = True;
 			 } else {
 			 	$putfile = False;
 			 	fclose($handle);
 			 }
 			 if ($putfile){
 			 	$contents = serialize($dati);
 			 	fwrite($handle, $contents);
 			 	flock($handle, LOCK_UN);
 			 	fclose($handle);
 			 }else{
 			 	echo "GRAVE: Errore durante il salvataggio.";
 			 	exit();
 			 }
 		}
 	}
 	public static function deleteConfig($key, $id, $user="", $file="", $argo="") {
 		global $settings, $db;
 		if ($user=="") {
 			$user = $_SESSION['user'];
 		}
 		//$settings['configuration_db']=True;
 		if (isset($settings['configuration_db']) && $settings['configuration_db']==True) {
 			// DELETE
 			$sql ="DELETE FROM ZWICONFP WHERE CONFKEY=? AND CONFID=? AND CONFUS=?";
 			$stmt = $db->singlePrepare($sql);
 			$do = $db->execute($stmt, array($key, $id, $user));
 		} else {
 			// Cancello il file
 			if (file_exists($file)){
 				unlink($file);
 			}
 		}
 	}
 	public static function deleteMultiConfig($key, $id, $file="", $argo="") {
 		global $settings, $db, $data_path;
 		if ($user=="") {
 			$user = $_SESSION['user'];
 		} 		
 		//$settings['configuration_db']=True;
 		if (isset($settings['configuration_db']) && $settings['configuration_db']==True) {
 			// DELETE
 			$sql ="DELETE FROM ZWICONFP WHERE CONFKEY=? AND CONFID=?";
 			$stmt = $db->singlePrepare($sql);
 			$do = $db->execute($stmt, array($key, $id));
 		} else {
 			// Cancello il file
 			$dir_handle = opendir($data_path);
 			// Recupero dei file della directory
 			while(($fileread = readdir($dir_handle))!==false) {
 				//		    		echo "FILE: $file<br>";
 				$path = $data_path.$fileread;
 				
 				if($fileread!="." && $fileread!=".." && $fileread!="CVS" && !is_file($path)) {
 					//						echo "FILE: $file<br>";
 					//$nome = basename($fileread);
 					$file_path = $path."/list/".$id.".lst";
 					if(file_exists($file_path)) {
 						//							echo "REMOVE FILE: $file_path<br>";
 						unlink($file_path);
 					}
 				}
 			}
 			closedir($dir_handle);
 		}
 	}
}
?>
