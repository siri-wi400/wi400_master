<?php
class wi400WebSocketConnector {
	

	private $id;
	private $writeDB = True;
	private $sender = "";
	private $action = "";
	private $from ="";
	private $to="";
	private $ip="";
	private $operazioni = array();
	private $count_operazioni=0;
	
	public function __construct($action="", $sender=""){
		$this->action =$action;
		$this->sender = $sender;
	}
	public function setFrom($type, $id) {
		$this->from[$type][]=$id;
	}
	public function setTo($type, $id) {
		$this->top[$type][]=$id;
	}
	public function setAction($action) {
		$this->action=$action;
	}
	public function setSender($sender) {
		$this->sender=$sender;
	}
	public function addOperazione($operazione, $dati, $extra) {
		$this->operazioni[]=array($operazione, $dati, $extra);
		$this->count_operazioni++;
	}
	/**
	 * @return $id Univoco del messaggio
	 */
	private function _getId() {
		$this->id = uniqid("i_", True);
		return $this->id;
	}
	public function send() {
		$id = _getId();
	}
	private function _insertDB($messaggio) {
		global $db;
	}
	static function updateDB($messaggio, $fase="1") {
		global $db;
	}
	
	
}