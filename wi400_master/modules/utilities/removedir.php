<?php

set_time_limit(0);

$log_path = get_log_file_path("LOG_CLEAN");
$name_log = get_log_file_name("LOG_CLEAN");
$file_log = $log_path.$name_log;
echo "FILE LOG: $file_log<br>";

if(!file_exists($log_path)) {
	wi400_mkdir($log_path, 777, true);
}

//$clean_dir = $data_path."_SESSION";
$clean_dir = "/tmp/WI400/";

echo "<font color='blue'>INIZIO PULIZIA DI: $clean_dir</font><br>";
$log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] INIZIO PULIZIA DI: $clean_dir";
print_log($log_msg, $file_log, true);

rrmdir($clean_dir, $file_log);

echo "<font color='blue'>FINE PULIZIA DI: $clean_dir</font><br>";
$log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] FINE PULIZIA DI: $clean_dir";
print_log($log_msg, $file_log, true);
/*
function rrmdir($dir, $file_log, $giorni=1) {
	global $appBase;

	$clean = false;
	$num_files = 0;
	
    if (is_dir($dir))  {
        $objects = scandir($dir);
        
        foreach ($objects as $object)  {
            if ($object != "." && $object != "..")   {
            	$num_files++;
            	
            	$file = $dir."/".$object;
            	
//				if (filetype($file)=="dir") {
            	if (is_dir($file))  {
                	rrmdir($file, $file_log);
                }
                else {              
	                if (filemtime($file) <= time()-60*60*24*$giorni) {
	                	@unlink($file);
	                	
	                	echo "<font color='green'>FILE RIMOSSO: $file</font><br>";
	                	$log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] FILE RIMOSSO: $file";
	                	print_log($log_msg, $file_log, true);
	                	
	                	$clean = true;
	                }
	                else {
	                	echo "<font color='red'>FILE NON RIMOSSO: $file</font><br>";
	                	$log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] File $file NON rimosso";
	                	print_log($log_msg, $file_log, true);
	                }
                }
            }
        }
        
        if($num_files==0)
        	$clean = true;
        
        if($clean===true) {
	        reset($objects);
	        rmdir($dir);
	        
	        echo "<font color='orange'>RIMOSSA DIRECTORY: $dir</font><br>";
	        $log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] RIMOSSA DIRECTORY: $dir";
	        print_log($log_msg, $file_log, true);
        }
        else {        
        	echo "<font color='pink'>DIRECTORY NON RIMOSSA: $dir</font><br>";
	        $log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] Directory $dir NON rimossa";
	        print_log($log_msg, $file_log, true);
        }
    }   
}
*/
function rrmdir($dir, $file_log, $giorni=1) {
	global $appBase;

	$clean = false;
	$num_files = 0;
	
	if(file_exists($dir) && is_dir($dir)) {
		$dir_handle = opendir($dir);
	
		// Recupero dei file della directory
		while(($file_name = readdir($dir_handle))!==false) {
			if($file_name!="." && $file_name!=".." && $file_name!="CVS") {
//				echo "FILE:$file_name<br>";
	
				$file = $dir.$file_name;
				 
//				if (filetype($file)=="dir") {
				if (is_dir($file))  {
					rrmdir($file, $file_log);
				}
				else {
					$mod_time = filemtime($file);
					if ($mod_time <= time()-60*60*24*$giorni) {
						@unlink($file);

						echo "<font color='green'>FILE RIMOSSO: $file</font><br>";
						$log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] FILE RIMOSSO: $file (ultima modifica: $mod_time)";
						print_log($log_msg, $file_log, true);

						$clean = true;
					}
					else {
						echo "<font color='red'>FILE NON RIMOSSO: $file</font><br>";
						$log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] File $file NON rimosso (ultima modifica: $mod_time)";
						print_log($log_msg, $file_log, true);
					}
				}
			}
		}

		if($num_files==0)
			$clean = true;

		if($clean===true) {
			rmdir($dir);
					 
			echo "<font color='orange'>RIMOSSA DIRECTORY: $dir</font><br>";
			$log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] RIMOSSA DIRECTORY: $dir";
			print_log($log_msg, $file_log, true);
		}
		else {
			echo "<font color='pink'>DIRECTORY NON RIMOSSA: $dir</font><br>";
			$log_msg = "[".date("d-M-Y H:i:s")." ".date_default_timezone_get()."] Directory $dir NON rimossa";
			print_log($log_msg, $file_log, true);
		}
	}
}