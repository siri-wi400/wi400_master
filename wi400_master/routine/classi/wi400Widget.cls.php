<?php
class wi400Widget {
	protected $progressivo;
	protected $parameters=array(
		"INTERVAL" => "NONE",
		"TITLE" => "GENERICO",
		"BODY" => "BODY",
		"RELOAD" => false,
		"MINIMIZED" => false,
		"ONCLICK" => false
	);
	protected $userParameters = array();
	
	protected $enableColor = false;
	protected $removeColor = false;
	protected $color = array(
		'TESTATA' => 'black',
		'TESTO_TESTATA' => 'red',
		'BORDO_TESTATA' => '1px solid red',
		'BODY' => 'blue',
		'BORDO' => "1px solid red"
	);
	private $status = array(
		"ERROR" => array(
			'TESTATA' => 'red',
			'TESTO_TESTATA' => 'white',
			'BODY' => 'white'
		),
		"WARNING" => array(
			'TESTATA' => 'yellow',
			'BORDO' => "1px solid #d7d700",
			'TESTO_TESTATA' => 'black'
		),
		"INFO" => array(
			'TESTATA' => '#0095ff',
			'TESTO_TESTATA' => 'white',
			'BORDO' => "1px solid #0095ff"
		),
	);
	
	public function __construct($progressivo) {

	}
	public function getParameters() {
		return $this->parameters;
	}
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}
	public function getUserParameters() {
		return $this->userParameters;
	}
	public function setUserParameters($parameters) {
		$this->userParameters = $parameters;
	}
	public function getHtmlBody() {
		return $this->htmlBody;
	}
	public function getHtmlTitle() {
		return $this->htmlTitle;
	}
	public function getResult() {
		return $this->result;
	}
	
	/*
	 * @desc Rimuove tutti gli stili base settati al widget riportandolo al colore originario
	 * 
	 */
	public function setRemoveColor($val) {
		$this->removeColor = $val;
	}
	
	public function getDetailParam($progressivo, $user) {
		$detail = new wi400Detail("PARAM_WIDGET", true);

		return $detail;
	}
	
	public function saveParams($progressivo, $user) {
		return true;
	}
	
	public function getColor($result) {
		if($result != "SUCCESS") {
			return $this->status[$result];
		}else if($this->removeColor) {
			return "remove";
		}else if($this->enableColor) {
			return $this->color;
		}else {
			return false;
		}
	}
	public function run() {
		//
	}
}