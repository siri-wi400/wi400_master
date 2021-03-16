<?php

class MONITOR_LAVORI_SISTEMA extends wi400CustomSubfile {
	
	private $medie = array();
	private $num_rig = 0;

	public function __construct($parameters){
		global $db, $connzend;
		
//		echo "TIPO DATI:<pre>"; prnt_r($parameters['TIPO_DATI']); echo "</pre>";
		
	}
	
	public function init($parameters){
		global $db, $connzend;
		
		$array = array();
		$array['DATA'] = $db->singleColumns("1", 50);
		
		foreach($parameters['SUBSYS'] as $val) {
			foreach($parameters['TIPO_DATI'] as $tipo) {
				$array[$tipo."_".$val] = $db->singleColumns("3", 9, 2);
			}
		}
		
		$this->setCols($array);
	}
	
	public function body($campi, $parameters){
		global $db, $connzend;
		
		$this->num_rig++;
		
		$writeRow = $campi;
//		echo "ROW:<pre>"; print_r($writeRow); echo "</pre>";
		
		foreach($writeRow as $key => $val) {
			if($key=="DATA") {
				$data = $writeRow['DATA'];
				switch($parameters['TIPO_INT']) {
					case "1H":
						$data .= ".00.000000";
						break;
					case "1G":
						$data .= "-00.00.00.000000";
						break;
					case "1M":
						$data .= "-00-00.00.00.000000";
						break;
				};
				
				$writeRow['DATA'] = $data;
			}
			else if($key!="DATA" && $val==null) {
				foreach($parameters['TIPO_DATI'] as $tipo) {
					$writeRow[$key] = 0;
				}
			}
		}
//		echo "ROW:<pre>"; print_r($writeRow); echo "</pre>";
		
		foreach($parameters['SUBSYS'] as $val) {
			foreach($parameters['TIPO_DATI'] as $tipo) {
				if(!isset($this->medie[$tipo."_".$val]))
					$this->medie[$tipo."_".$val] = 0;
					
				$this->medie[$tipo."_".$val] += $campi[$tipo."_".$val];
			}
		}
		
		return $writeRow;
	}
	
	public function extraRow($extraDesc, $parameters){	
		if($this->num_rig!=0) {
			foreach($parameters['SUBSYS'] as $val) {
				foreach($parameters['TIPO_DATI'] as $tipo) {
					$medie[$tipo."_".$val] = wi400_format_DOUBLE_2($this->medie[$tipo."_".$val]/$this->num_rig);
				}
			}

			return $medie;
		}
		else {
			return $this->medie;
		}
	}
}

?>