<?php

class GRUPPI_WIDGET extends wi400CustomSubfile {

	private $gruppi = array();
	
	public function __construct($parameters){
		global $wi400_groups;
		
//		$this->gruppi = $_SESSION['WI400_GROUPS'];
		$this->gruppi = $wi400_groups;
	}

	public function getArrayCampi() {
		global $db;
		
		$array = array();
		
		$array['GRUPPO'] = $db->singleColumns("1", "50", "", "Gruppo wi400");

		return $array;
	}

	public function init($parameters){
		global $db;

		$this->setCols($this->getArrayCampi());
	}
	
	public function start($subfile) {
		global $db;
		
		$writeRow = $this->getColsInz();

		$stmtinsert = $db->prepare("INSERT", $subfile->getTable(), null, array("GRUPPO"));
		
		foreach($this->gruppi as $gruppo) {
			$db->execute($stmtinsert, array($gruppo));
		}

		return;
	}

	public function end($subfile) {
		
	}
}