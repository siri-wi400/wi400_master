<?php 

class SOAP_ACTION extends wi400CustomSubfile {
	
	private $azioni;
	
	public function SOAP_ACTION($parameters){
		$this->azioni = $parameters['AZIONI'];
		
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		
		$array['AZIONE'] = $db->singleColumns("1", "60", "", "Azione");
		//$array['PARAMETRI'] = $db->singleColumns("1", "", "", "Parametri");
		
		return $array;
	}
	
	public function init($parameters){
		global $db;
				
		$this->setCols($this->getArrayCampi());
	}
	
	public function start($subfile) {
		global $db;
	
		// Prepare della query di inserimento
		$field = array(
			'AZIONE'
		);
		
		//showArray($this->azioni);
		
		$stmtinsert = $db->prepare("INSERT", $subfile->getTable(), null, $field);
		
		//$nrel = 0;
		
		foreach($this->azioni as $val) {
			$b = explode(" ", $val)[1];
			$azione = explode("(", $b)[0];
			
			//echo $azione."<br/>";
			// creazione riga
			$writeRow = array(
				$azione
			);
				
			// Inserimento della riga nel subfile
			$res = $db->execute($stmtinsert, $writeRow);
			//if($res) $nrel++;
		}
	}
	
	public function body($campi, $parameters){
		
	}
	
	public function end($subfile){
		
	}
}
?>