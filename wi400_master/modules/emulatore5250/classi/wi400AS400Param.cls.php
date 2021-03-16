<?php
class wi400AS400Param {
	private $id="";
	private $azione="";
	private $pgm="";
	private $sysinf="";
	private $onlyjbu="";
	private $stato="";
	private $kpjbu="";
	private $file="ZID5250N";
	
	public function __construct($id="",$azione="",$pgm="",$sysinf="",$onlyjbu="N",$stato="V",$kpjbu="") {
		global $db;
		// Recupero un ID univoco
		if ($id=="") {
			$id = str_pad(getSequence("OPEN5250"), 10, "0", STR_PAD_LEFT);
		}
		$this->id=$id;
		$this->azione=$azione;
		$this->pgm=$pgm;
		$this->sysinf=$sysinf;
		if ($this->sysinf=="") {
			$this->sysinf=$_SESSION['sysinfname'];
		}
		$this->onlyjbu=$onlyjbu;
		$this->stato=$stato;
		$this->kpjbu=$kpjbu;
	}
	public function getId() {
		return $this->id;
	}
	public function write() {
		global $db;
		// prepare dello statement
		// Scrittura record di innesco
		$dati = getDS($this->file);
		$stmtDoc = $db->prepare("INSERT", $this->file, null, array_keys($dati));
		// Cancello un eventuale record presente
		$sql = "DELETE FROM $this->file WHERE IDOPEN='".$this->id."'";
		$db->query($sql);
		// Scrittura
		$timeStamp = getDb2Timestamp();
		$dati['IDOPEN']=$this->id;
		$dati['AZIONE']=$this->azione;
		$dati['PGM']=$this->pgm;
		$dati['SYSINF']=$this->sysinf;
		$dati['ONLYJBU']=$this->onlyjbu;
		$dati['IDFL01']="";
		$dati['IDFL02']="";
		$dati['IDFL03']="";
		$dati['IDFL04']="";
		$dati['IDFL05']="";
		$dati['STATO']=$this->stato;
		$dati['OKPJBU']=$this->kpjbu;
		$dati['TMSINS']= $timeStamp;
		
		$result = $db->execute($stmtDoc, $dati);
	}
}