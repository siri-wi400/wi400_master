<?php

class IMAGE_LIST_VIEWER extends wi400CustomSubfile {
	
	private $file_paths = array();
	private $exclude_types = array();
	private $include_only_types = array();
	private $remote_path = "";
	
	public function __construct($parameters){
		global $db, $connzend, $moduli_path, $root_path, $doc_root, $data_path, $settings;
		
		$this->file_paths = $parameters['IMAGE_PATHS'];
//		echo "FILE_PATHS:<pre>"; print_r($this->file_paths); echo "</pre>";

		$this->remote_path = $parameters['REMOTE_PATH'];

		$this->exclude_types = $parameters['EXCLUDE_TYPES'];
//		echo "EXCLUDE_TYPES:<pre>"; print_r($this->exclude_types); echo "</pre>";
		
		$this->include_only_types = $parameters['INCLUDE_ONLY_TYPES'];
//		echo "INCLUDE_ONLY_TYPES:<pre>"; print_r($this->include_only_types); echo "</pre>";
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['FILE']=$db->singleColumns("1", "300", "", "File");
	    $array['TIPO']=$db->singleColumns("1", "20", "", "Tipo file");
	    $array['DIMENSIONE']=$db->singleColumns("3", "9", 0, "Dimensione");
	    $array['IMG_PATH']=$db->singleColumns("1", "300", "", "Immagine");
	
		return $array;
	}
	
	public function init($parameters){
		global $db, $connzend;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function start($subfile) {
		global $db, $connzend, $doc_root;
		
		// Prepare della query di inserimento
		$fields = array_keys($this->getArrayCampi());
		
		$stmtinsert = $db->prepare("INSERT", $subfile->getTable(), null, $fields);
		
		foreach($this->file_paths as $key => $img_path) {
			$file_path = $doc_root.$img_path;
			
			if(file_exists($file_path)) {
				$dir_handle = opendir($file_path);
			
				// Recupero dei file della directory
				while(($file_name = readdir($dir_handle))!==false) {
//					echo "FILE:$file_name<br>";

//					chmod($file_path.$file_name, 777);
					
					if($file_name!="." && $file_name!=".." && $file_name!="CVS") {
//						echo "FILE:$file_name<br>";

						$file_parts = pathinfo($file_name);
//						echo "FILE PARTS:<pre>"; print_r($file_parts); echo "</pre>";
						
						$extension = "";
						if(array_key_exists('extension', $file_parts))
							$extension = strtoupper($file_parts['extension']);

						if(!empty($this->exclude_types)) {
							if(in_array($extension, $this->exclude_types))
								continue;
						}
						
						if(!empty($this->include_only_types)) {
							if(!in_array($extension, $this->include_only_types))
								continue;
						}
					
						$file = $file_path.$file_name;
						
						$img_file_path = $file;
						if($this->remote_path!="")
							$img_file_path = $this->remote_path.$img_path.$file_name;
			
						$writeRow = array(
							$file,
							$extension,
							filesize($file),
							$img_file_path
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