<?php

class EXPORT_GERARCHIE_LIST extends wi400CustomSubfile {

	private $pdv_array = array();
	private $ger_array = array();
	
	public function __construct($parameters){
		global $db, $connzend, $moduli_path, $root_path, $doc_root, $data_path, $settings;
		
		$this->pdv_array = $parameters['PDV_ARRAY'];
//		echo "PDV_ARRAY:<pre>"; print_r($this->pdv_array); echo "</pre>";

		$this->ger_array = $parameters['GER_ARRAY'];
//		echo "GER_ARRAY:<pre>"; print_r($this->ger_array); echo "</pre>";		
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['MAACDE']=$db->singleColumns("1", "4", "", "Pdv");
		$array['DES_PDV']=$db->singleColumns("1", "100", "", "Descrizione Pdv");
		$array['CONT']=$db->singleColumns("1", "4", "", "Contingentamento");
		$array['GER_RIF']=$db->singleColumns("1", "4", "", "Gerarchia rifornibilità costi prezzi");
		
		foreach($this->ger_array as $k => $ger) {
			$array["G_".$k]=$db->singleColumns("1", "4", "", $ger);
		}
		
//		echo "COLS:<pre>"; print_r(array_keys($array)); echo "</pre>";
	
		return $array;
	}
	
	public function init($parameters){
		global $db, $connzend;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function start($subfile) {
		global $db, $connzend;
		global $settings;
		
		// Prepare della query di inserimento
		$fields = array_keys($this->getArrayCampi());
		
		$stmtinsert = $db->prepare("INSERT", $subfile->getTable(), null, $fields);
		
		foreach($this->pdv_array as $pdv => $vals) {
			$des_pdv = get_campo_ente($pdv, date("Ymd"), "MAFDSE");
			$cont = get_campo_ente($pdv, date("Ymd"), "MAFGER");
			$ger_rif = get_campo_ente($pdv, date("Ymd"), "MAFPRZ");
			
			$writeRow = array(
				$pdv,
				$des_pdv,
				$cont,
				$ger_rif
			);
			
			foreach($this->ger_array as $k => $ger) {
				if(array_key_exists($ger, $vals)) {
					$writeRow["G_".$k] = $vals[$ger];
				}
				else {
					$writeRow["G_".$k] = "";
				}
			}
			
//			echo "WRITE:<pre>"; print_r($writeRow); echo "</pre>";
			
			// Inserimento della riga nel subfile
			$db->execute($stmtinsert, $writeRow);						
		}
	}
	
	public function body($campi, $parameters) {
		global $db, $connzend;
		
		return false;
	}
	
}