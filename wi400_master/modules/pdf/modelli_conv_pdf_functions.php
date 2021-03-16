<?php

	function check_file_exists($file_path, $file_name) {
		$sep = "/";
		if(substr($file_path, -1, 1)=="/")
			$sep = "";
//		echo "SEP:$sep<br>";
		
		$file = $file_path.$sep.$file_name;
//		echo "FILE:$file<br>";
		
		$exists = "N";
		if(file_exists($file)) {
			$exists = "S";
		}

		return $exists;
	}