<?php
	function get_session_data($session_id) {
		
		global $settings;
		
		$dati = array();
		$lines = "";
		
		$directory = $settings['sess_path'];
			
		$dir_handle = opendir($directory);
		
		$file_path = "";
		while(($file = readdir($dir_handle))!==false) {
			if(strcmp($file, "WI400_".$session_id.".txt")==0) {
				$file_path = $directory.$file;
//				echo "FILE: $file_path<br>";
				break;
			}
		}
		
		closedir($dir_handle);
		
		if(file_exists($file_path)) {
			$path_parts = pathinfo($file_path);
			
			if(isset($path_parts['extension']) && $path_parts['extension']=="txt")
				$lines = file_get_contents($file_path);
		}
		
		$dati['FILE_PATH'] = $file_path; 
		$dati['LINES'] = $lines;
		
		return $dati;
	}
?>