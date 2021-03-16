<?php
class wi400AS400Param {
	private $id="";
	private $azione="";
	private $pgm="";
	private $sysinf="";
	private $onlyjbu="";
	private $stato="";
	private $kpjbu="";
	private $file="ZID5250O";
	
	public function __construct($id="",$azione="",$pgm="",$sysinf="",$onlyjbu="",$stato="V",$kpjbu="") {
		global $db;
		// Recupero un ID univoco
		if ($id=="") {
			$id = str_pad(getSequence("OPEN5250"), 10, "0", STR_PAD_LEFT);
		}
		$this->id=$id;
		$this->azione=$azione;
		$this->pgm=$pgm;
		$this->sysinf=$sysinf;
		$this->onlyjbu=$oblyjbu;
		$this->stato=$stato;
		$this->kpjbu=$kpjbu;
	}
	public function getId() {
		return $this->id;
	}
	public function write() {
		// prepare dello statement
		// Scrittura record di innesco
		$field = array("IDOPEN","AZIONE","PGM", "ONLYJBU", "SYSINF","IDFL01","IDFL02","IDFL03","IDFL04","IDFL05","STATO","OKPJBU");
		$stmtInsert = $db->prepare("INSERT", "ZID5250O", null, $field);
		// Scrittura
		$campi = array($this->id, $this->azione, $this->pgm, $this->onlyjbu ,$this->sysinf, "", "", "", "", "", $this->stato,$this->kpjby);
		$db->execute($stmtInsert, $campi);
	}
}