<?php
class PARAMETRI_CLIENT extends wi400CustomSubfile {
	
	private $legame;
	private $form;
	private $field;
	private $type;
	
	public function __construct($parameters){
		global $db, $connzend;
		
		$this->legame = $parameters['LEGAME'];
		$this->form = $parameters['FORM'];
		$this->field = $parameters['FIELD'];
		$this->type = $parameters['TYPE'];
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['OT5PRM']=$db->singleColumns("1", "50", "", "Chiave");
		$array['OT5VAL']=$db->singleColumns("1", "100", "", "Valore");
		$array['OT5STA']=$db->singleColumns("1", "1", "", "Stato");
		
		return $array;
	}
	
	public function init($parameters){
		global $db, $connzend;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function start($subfile) {
		global $db, $settings, $moduli_path;
		
		$writeRow = $this->getColsInz();
		
		$writeRow['NREL'] = "";
		$stmt_ins = $db->prepare("INSERT", $subfile->getTable(), null, array_keys($writeRow));
		
		$cont = 1;
		
		$dati = getParametriClient($this->legame, $this->form, $this->field, $this->type);
		
		
		
		foreach($dati as $row) {
			$writeRow['OT5PRM'] = $row['OT5PRM'];
			$writeRow['OT5VAL'] = $row['OT5VAL'];
			$writeRow['OT5STA'] = $row['OT5STA'];
			$writeRow['NREL'] = $cont;
			//showArray($writeRow);
			
			$rs = $db->execute($stmt_ins, $writeRow);
			if($rs) $cont++;
		}
		
		
		for($i = $cont; $i<10; $i++) {
			$writeRow = array(
				"OT5PRM" => "",
				"OT5VAL" => "",
				"OT5STA" => "1",
				"NREL" => $i
			);
						
			if(!empty($writeRow)) {
				// Inserimento della riga nel subfile
				$db->execute($stmt_ins, $writeRow);
			}
		}
	}
	
	public function body($campi, $parameters) {
		return false;
	}
	
	public function end($subfile){
		
	}
	
}

?>