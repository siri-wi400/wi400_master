<?php
class SERIALIZED_FILE extends wi400CustomSubfile {
	
	private $file_paths;
	private $exclude_types = array();
	private $include_only_types = array();
	
	public function __construct($parameters){
		global $db, $connzend, $moduli_path, $root_path, $doc_root, $data_path, $settings;
		
		$this->file_paths=$parameters['LOG_FILES_PATHS'];
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['FILE_NAME']=$db->singleColumns("1", "120", "", "File");
		$array['FILE_PATH']=$db->singleColumns("1", "300", "", "Path");
		$array['HAS_SUBFILE']=$db->singleColumns("1", "1", "", "Subfile");
	    $array['TIPO']=$db->singleColumns("1", "20", "", "Tipo file");
	    $array['DIMENSIONE']=$db->singleColumns("3", "9", 0, "Dimensione");
	    $array['ATIME']=$db->singleColumns("1", "20", 0, "Ultimo Accesso");
	    $array['CTIME']=$db->singleColumns("1", "20", 0, "Ultimo Cambiamento");
	    $array['MTIME']=$db->singleColumns("1", "20", 0, "Ultima Modifica");
	
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
			if(file_exists($this->file_paths)) {
				$dir_handle = opendir($this->file_paths);
			
				// Recupero dei file della directory
				while(($file_name = readdir($dir_handle))!==false) {
					if($file_name!="." && $file_name!=".." && $file_name!="CVS") {
//						echo "FILE:$file_name<br>";
						$file_parts = pathinfo($file_name);
						// Li carico tutti
						if($file_parts['extension']=="list") {
							//
						} else {
							//continue;
						}
					
						$file = $this->file_paths."/".$file_name;
						$writeRow = $this->getColsInz();

						$subfile = "N";
						// Carico la lista
						$contents = file_get_contents($file);
						$wi400List= unserialize($contents);
						//
						// Se è una lista controllo se ha un SUBFILE attaccato
						if ($file_parts['extension']=="list") {
							if ($wi400List->getSubfile() != null){
								$subfile="S";
							}
						}	
						$dati = stat($file);
						$writeRow['FILE_NAME']=$file_name;
						$writeRow['FILE_PATH']=$file;
						$writeRow['HAS_SUBFILE']=$subfile;
						$writeRow['TIPO']="Array";
						$writeRow['DIMENSIONE']=filesize($file);
						$writeRow['ATIME']=date("Y-m-d H:i:s", $dati['atime']);
						$writeRow['CTIME']=date("Y-m-d H:i:s", $dati['ctime']);
						$writeRow['MTIME']=date("Y-m-d H:i:s", $dati['mtime']);

						// Inserimento della riga nel subfile
						$db->execute($stmtinsert, $writeRow);
					}
				}
				closedir($dir_handle);
			}
		}
	
	public function body($campi, $parameters) {
		global $db, $connzend;
		
		return false;
	}
	
}

?>