<?php

class LOG_FILES_LIST extends wi400CustomSubfile {
	
	private $file_paths;
	private $exclude_types = array();
	private $include_only_types = array();
	
	public function __construct($parameters){
		global $db, $connzend, $moduli_path, $root_path, $doc_root, $data_path, $settings;
		
		$file_type = $parameters['FILE_TYPE'];
//		echo "FILE_TYPE:$file_type<br>";
/*		
		$this->file_paths = array();
		if($file_type!="ALL") {
			$this->file_paths[$file_type] = $log_files_paths[$file_type];
		}
		else {
			$this->file_paths = $log_files_paths;
		}
*/
		$this->file_paths = array();
		if($file_type!="ALL") {
			$this->file_paths[$file_type] = $parameters['LOG_FILES_PATHS'][$file_type];
		}
		else {
			$this->file_paths = $parameters['LOG_FILES_PATHS'];
		}		
//		echo "FILE_PATHS:<pre>"; print_r($this->file_paths); echo "</pre>";

		$this->exclude_types = $parameters['EXCLUDE_TYPES'];
		$this->include_only_types = $parameters['INCLUDE_ONLY_TYPES'];
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['FILE_NAME']=$db->singleColumns("1", "300", "", "File");
	    $array['TIPO']=$db->singleColumns("1", "20", "", "Tipo file");
	    $array['DIMENSIONE']=$db->singleColumns("3", "9", 0, "Dimensione");
	
		return $array;
	}
	
	public function init($parameters){
		global $db, $connzend;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function start($subfile) {
		global $db, $connzend;
		
		// Prepare della query di inserimento
		$fields = array_keys($this->getArrayCampi());
		
		$stmtinsert = $db->prepare("INSERT", $subfile->getTable(), null, $fields);
		
		foreach($this->file_paths as $key => $file_path) {
			if(file_exists($file_path)) {
				$dir_handle = opendir($file_path);
			
				// Recupero dei file della directory
				while(($file_name = readdir($dir_handle))!==false) {
					if($file_name!="." && $file_name!=".." && $file_name!="CVS") {
//						echo "FILE:$file_name<br>";

						if(!empty($this->exclude_types)) {
							$file_parts = pathinfo($file_name);
//							echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
					
							$extension = "";
							if(array_key_exists('extension', $file_parts))
								$extension = $file_parts['extension'];
							
							if(in_array(strtoupper($extension), $this->exclude_types))
								continue;
						}
						
						if(!empty($this->include_only_types)) {
							$file_parts = pathinfo($file_name);
//							echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
								
							$extension = $file_parts['extension'];
								
							if(!in_array(strtoupper($extension), $this->include_only_types))
								continue;
						}
					
						$file = $file_path.$file_name;
			
						$writeRow = array(
							$file,
							substr($key,4),
							filesize($file)
						);
						
						// Inserimento della riga nel subfile
						$db->execute($stmtinsert, $writeRow);
					}
				}
			
				closedir($dir_handle);
			}
		}
	}
	
	public function body($campi, $parameters) {
		global $db, $connzend;
		
		return false;
	}
	
}

?>