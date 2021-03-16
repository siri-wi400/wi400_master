<?php
/**
 * Classe di conversione tra varie code page .. quando iconv non ce la fa proprio
 * @author luca
 *
 */
class wi400Convert {
	
	private $to="";
	private $from="";
	private $data="";
	private $utf8=False;
	
	public function __construct($to="",$from="",$data="",$utf8=True){
		$this->to = $to;
		$this->from=$from;
		$this->data=$data;
		$this->utf8=$utf8;
	}
	public function setTo($to) {
		$this->to=$to;
	}
	public function getTo() {
		return $this->to;
	}
	public function setFrom($from) {
		$this->from=$from;
	}
	public function getFrom() {
		return $this->from;
	}
	public function setData($data) {
		$this->data=$data;
	}
	public function getData() {
		return $this->data;
	}
	public function setUtf8($utf8) {
		$this->utf8=$utf8;
	}
	public function getUtf8() {
		return $this->utf8;
	}
	public function convert() {
		global $settings, $routine_path;
		static $char, $oldFrom;
		$from = $this->getFrom();
		if (!isset($char) || (isset($oldFrom) && $from!=$oldFrom)) {
			$oldFrom=$from;
			$path = $routine_path."/conversion/ucstable/$from.map";
			$mappa = file_get_contents($path);
			$char = unserialize($mappa);
		}
		for ($i = 0 ; $i < strlen($e) ; $i++) { $a .= chr($char[ord(substr($e,$i,1))]); }
		if ($this->utf8()==True) {
			$a = utf8_encode($a);
		}
		return $a;
	}
}