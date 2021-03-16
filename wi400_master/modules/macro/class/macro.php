<?php
					
class Macro {


	private $type;
	private $parameters;
	private $path;
	private $fileName;
	private $id;
	private $fileKbd;
	
	public function __construct(){
		$this->parameters = array();
		$this->fileName = "macro.zip";
		$this->id = str_pad(getSequence("OPEN5250"), 10, "0", STR_PAD_LEFT);
	}
	
	
	/**
	 * @return the $fileName
	 */
	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param $id the $id to set
	 */
	public function setId($id) {
		$this->id = $id;
	}

	public function getFileName() {
		return $this->fileName;
	}

	/**
	 * @param $fileName the $fileName to set
	 */
	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}

	public function addParameter($key, $value){
		$this->parameters[$key] = $value;
	}


	/**
	 * @return the $path
	 */
	public function getPath() {
		return $this->path;
	}



	/**
	 * @param $path the $path to set
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	public function getType() {
		return $this->type;
	}

	/**
	 * @desc getKeyboardFile(): Recupero il file da utilizzare per la tastiera
	 * @return the $parameters
	 */
	public function getKeyboardFile() {
		global $moduli_path, $settings, $root_path;
		$keyboardFile="";
		$keyboardFile= p13n('modules/macro/template/AS400.KMP');
		if ($keyboardFile=="") {
			$keyboardFile = $moduli_path.'/macro/template/AS400.KMP';
		}
		return $keyboardFile;
	}
	
	/**
	 * @return the $parameters
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * @return the $parameters
	 */
	public function getParameter($key) {
		if (!isset($this->parameters[$key])){
			return false;
		}
		return $this->parameters[$key];
	}

	/**
	 * @param $type the $type to set
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @param $parameters the $parameters to set
	 */
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}
	
}	


?>