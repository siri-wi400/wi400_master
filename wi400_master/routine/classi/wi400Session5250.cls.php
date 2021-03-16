<?php

class wi400Session5250 {

	private $type;
	private $parameters;
	private $path;
	private $fileName;
	private $id;
	private $fileKbd;
	private $program="";
	private $kpjbu="";
	private $azione="";
	private $hasinsertdata=false;
	private $sendkeys;
	private $loginKeys;
	
	public function __construct(){
		$this->parameters = array();
		$this->fileName = "macro.zip";
		$this->id = str_pad(getSequence("OPEN5250"), 10, "0", STR_PAD_LEFT);
		// Setto di default il login Keys .. ma potrebbe essere sovrascritto in caso di necessita
		$this->addLoginKey("SENDKEY", str_pad($_SESSION['user'], 10));
		$this->addLoginKey("SENDKEY", str_pad(getUserPassword(), 10));
		$this->addLoginKey("SENDKEY", str_pad("zgetidc", 10));
		$this->addLoginKey("SENDKEY", "{ENTER}");
		$this->addLoginKey("SENDKEY", "{ENTER}");
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
	public function addKey($type, $value) {
		$this->sendKeys[] = array($type, $value);
	}
	public function addLoginKey($type, $value) {
		$this->loginKeys[] = array($type, $value);
	}
	public function clearLoginKeys() {
		unset($this->loginKeys);
	}
	public function getSendKeys() {
		$chiavi = "";
		$sepa = "";
		foreach ($this->sendKeys as $key => $value) {
			$chiavi .=$sepa.$value[0].";".$value[1];
			$sepa = "|";
		}
		return $chiavi;
	}
	public function getLoginKeys() {
		$chiavi = "";
		$sepa = "";
		foreach ($this->loginKeys as $key => $value) {
			$chiavi .=$sepa.$value[0].";".$value[1];
			$sepa = "|";
		}
		return $chiavi;
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
	public function setProgram($program) {
		$this->program = $program;
	}
	public function getProgram() {
		return $this->program;
	}
	public function setKpjbu($kpjbu) {
		$this->kpjbu = $kpjbu;
	}
	public function getKpjbu() {
		return $this->kpjbu;
	}
	public function setAzione($azione) {
		$this->azione = $azione;
	}
	public function getAzione() {
		return $this->azione;
	}
	public function open() {
		global $moduli_path, $routine_path;
		if ($this->hasinsertdata==false) {
			$this->insertData();
		}
		require_once $routine_path."/classi/wi400Websocket.cls.php";
		$ws = new WebsocketClient('127.0.0.1','8080');
		require_once $moduli_path."/socket_server/rachet_server_function.php";
		$id =webs_create_token();
		$connection_string=array("action"=>"INIT", "token"=>"$id", "ip"=>"10.0.40.1", "user"=>"PHPMAN", "request"=>"CLIENT", "id"=>"dfsljflksj", "sender"=>"CLIENT");
		$connection_string=base64_encode(json_encode($connection_string));
		$result = $ws->sendData($connection_string);
		//$result = json_decode(base64_decode($result));
		// Devo Reperire il tipo di emulatore presente sul PC
		$contents = file_get_contents($moduli_path."/open_5250/template.ws");
		$mydati = array("id"=>"WI*=","file"=>$contents, "loginkeys" =>$this->getLoginKeys(), "sendkeys" => $this->getSendKeys(), "user"=>$_SESSION['user']);
		$mydati = base64_encode(json_encode($mydati));
		$messaggio = array("operazione"=>"OPEN5250", "dati"=>$mydati);
		$connection_string2=array("sender"=>"CONSOLE", 'action'=>"MSG", "from" => array("connid" =>"me"), "to"=>array("session"=>session_id()), "msg"=>array("0"=>$messaggio));
		//echo base64_encode(json_encode($connection_string2));
		//die();
		$connection_string2=base64_encode(json_encode($connection_string2));
		$result = $ws->sendData($connection_string2);
		
		// Da verificare eventuali errori per dare un risposta diversa
		return true;
	}
	public function insertData() {
		global $db;
		$field = array("IDOPEN","AZIONE","PGM", "ONLYJBU", "SYSINF","IDFL01","IDFL02","IDFL03","IDFL04","IDFL05","STATO","OKPJBU");
		$stmtInsert = $db->prepare("INSERT", "ZID5250O", null, $field);
		$haskpjbu="N";
		if ($this->getKpjbu()<>"") $haskpjbu='S';
		$campi = array($this->getId(), $this->getAzione(), $this->getProgram(), $haskpjbu, $_SESSION['sysinfname'], "", "", "", "", "", 'V',$this->getKpjbu());
		// Scrittura del record
		$db->execute($stmtInsert, $campi);
		$this->hasinsertdata=True;
	}
}	