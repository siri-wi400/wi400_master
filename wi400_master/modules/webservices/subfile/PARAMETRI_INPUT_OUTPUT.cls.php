<?php 

class PARAMETRI_INPUT_OUTPUT extends wi400CustomSubfile {
	
	private $max_rows;
	private $query;
	private $stati;
	
	public function PARAMETRI_INPUT_OUTPUT($parameters){
		$this->max_rows = $parameters['MAX_ROWS'];
		$this->query = $parameters['QUERY'];
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		
		$array['IS_MODIFY']=$db->singleColumns("1", "1");
		$array['ASEENT'] = $db->singleColumns("1", "4", "", "Ente");
		$array['ASECOD'] = $db->singleColumns("1", "4", "", "Segmento");
		$array['ASESEQ'] = $db->singleColumns("3", "4", 0, "Sequenza");
		$array['ASEMET'] = $db->singleColumns("1", "50", "", "Metodo ws");
		$array['ASETYP'] = $db->singleColumns("1", "6", "", "Tipo I/O");
		$array['ASENAM'] = $db->singleColumns("1", "30", "", "Nome origine");
		$array['ASENA2'] = $db->singleColumns("1", "30", "", "Nome destinazione");
		$array['ASEDES'] = $db->singleColumns("1", "30", "", "Descrizione parametro");
		$array['ASEORI'] = $db->singleColumns("1", "6", "", "Origine");
		$array['ASEGET'] = $db->singleColumns("1", "200", "", "Funzione rep parametri");
		$array['ASEDFT'] = $db->singleColumns("1", "50", "", "Default parametro");
	
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
			'NREL',
			'IS_MODIFY',
			'ASEENT',
			'ASECOD',
			'ASESEQ',
			'ASEMET',
			'ASETYP',
			'ASENAM',
			'ASENA2',
			'ASEDES',
			'ASEORI',
			'ASEGET',
			'ASEDFT'
		);
		
		/*echo $this->query."<br/>";
		echo $subfile->getTable()."<br/>";*/
	
		$stmtinsert = $db->prepare("INSERT", $subfile->getTable(), null, $field);
		
		$start = 1;
	
		$rs = $db->query($this->query);
		while($campi = $db->fetch_array($rs)) {
			// creazione riga
			$writeRow = array(
					$start,
					'X',
					$campi['ASEENT'],
					$campi['ASECOD'],
					$campi['ASESEQ'],
					$campi['ASEMET'],
					$campi['ASETYP'],
					$campi['ASENAM'],
					$campi['ASENA2'],
					$campi['ASEDES'],
					$campi['ASEORI'],
					$campi['ASEGET'],
					$campi['ASEDFT']
			);
				
			// Inserimento della riga nel subfile
			$res = $db->execute($stmtinsert, $writeRow);
			if($res) $start++; 
		}
	
		// Inserimento delle righe vuote extra
		for($rowNum = $start; $rowNum < $this->max_rows; $rowNum++){
			$writeRow = array(
					$rowNum,
					'',
					'',
					'',
					null,
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
			);
	
			$db->execute($stmtinsert, $writeRow);
		}
	}
	
	public function body($campi, $parameters){
		
	}
	
	public function end($subfile){
		
	}
}
?>