<?php

class MONITOR_EMAIL_ATC_LIST extends wi400CustomSubfile {
	
//	private $stmt_arc;
	
	public function __construct($parameters) {
		global $db, $connzend, $moduli_path, $persTable;
/*		
		$sql_arc = "select * from FLOGCONV where LOGID=? order by LOGELA desc";
		$this->stmt_arc = $db->singlePrepare($sql_arc, 0, true);
*/
	}
	
	public function getArrayCampi() {
		global $db;
	
		$array = array();
	
		$array['ID']=$db->singleColumns("1", "10", "", "ID");
		$array['MAIATC']=$db->singleColumns("1", "100", "", "Allegato");
		$array['EX_ATC']=$db->singleColumns("1", "1", 0, "Esistenza allegato");
		$array['ATC_DIM']=$db->singleColumns("3", "9", 0, "Dimensione allegato");
		$array['MAIPAT']=$db->singleColumns("1", "100", "", "Path file convertito");	
		$array['EX_PAT']=$db->singleColumns("1", "1", 0, "Esistenza file convertito");
		$array['PAT_DIM']=$db->singleColumns("3", "9", 0, "Dimensione file convertito");
		$array['CONV']=$db->singleColumns("1", "1", "", "Da convertire");
		$array['TPCONV']=$db->singleColumns("1", "10", "", "Tipo conversione");
		$array['MAIMOD']=$db->singleColumns("1", "10", "", "Modulo conversione");
		$array['MAIARG']=$db->singleColumns("1", "10", "", "Argomento conversione");
		$array['MAINAM']=$db->singleColumns("1", "200", "", "Ridenominazione allegato");
		$array['EX_NAM']=$db->singleColumns("1", "1", 0, "Esistenza allegato ridenominato");
		$array['NAM_DIM']=$db->singleColumns("3", "9", 0, "Dimensione allegato ridenominato");
		$array['FILZIP']=$db->singleColumns("1", "1", "", "Zippare allegato");
		
		$array['TO_ARC']=$db->singleColumns("1", "1", "", "Da archiviare");		
//		$array['ARCHIVED']=$db->singleColumns("1", "1", "", "Archiviato");		
/*		
		$array['FILEARC']=$db->singleColumns("1", "200", "", "File archiviato");
		$array['EX_ARC']=$db->singleColumns("1", "1", 0, "Esistenza file archiviato");
		$array['ARC_DIM']=$db->singleColumns("3", "9", 0, "Dimensione file archiviato");
*/		
		$array['MAISTO']=$db->singleColumns("1", "1", "", "Stampato");
		$array['MAIOUT']=$db->singleColumns("1", "20", "", "Coda di stampa");
		$array['MAISTT']=$db->singleColumns("1", "30", "", "Data e ora di stampa");
				
		return $array;
	}
	
	public function init($parameters){
		global $db, $settings;
	
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters) {
		global $db, $connzend, $persTable, $routine_path;
		
		require_once $routine_path.'/classi/wi400invioConvert.cls.php';
		
		$atc_exists = "N";
		$atc_dim = 0;
		if($campi['MAIATC']!="" && file_exists($campi['MAIATC'])) {
			$atc_exists = "S";
			$atc_dim = filesize($campi['MAIATC']);
		}
		else if($campi['MAIATC']=="")
			$atc_exists = "";
		
		$pat_exists = "N";
		$pat_dim = 0;
		if($campi['MAIPAT']!="" && file_exists($campi['MAIPAT'])) {
			$pat_exists = "S";
			$pat_dim = filesize($campi['MAIPAT']);
		}
		else if($campi['MAIPAT']=="")
			$pat_exists = "";
		
		$nam_exists = "N";
		$nam_dim = 0;
		$file = "";
		if($campi['MAINAM']!="") {
			$file = wi400invioConvert::get_file_rename($campi);
//			echo "FILE: $file<br>";
						
			if(file_exists($file)) {
				$nam_exists = "S";
				$nam_dim = filesize($file);
			}
			
			$file = $campi['MAINAM'];
		}
		else
			$nam_exists = "";
		
		$to_arc = "";		
//		$archived = "";
/*			
		$arc_exists = "N";
		$arc_dim = 0;
		$file_arc = "";
		
		$res_arc = $db->execute($this->stmt_arc, array($campi['ID']));
		if($row_arc = $db->fetch_array($this->stmt_arc)) {
			$file_arc = $row_arc['LOGPTH']."/".$row_arc['LOGNOM'];
			
			if(file_exists($file_arc)) {
				$arc_exists = "S";
				$arc_dim = filesize($file_arc);
			}
		}
		else
			$arc_exists = "";
*/			
		$writeRow = array(
			$campi['ID'],	
			$campi['MAIATC'],
			$atc_exists,
			$atc_dim,
			$campi['MAIPAT'],
			$pat_exists,
			$pat_dim,			
			$campi['CONV'],
			$campi['TPCONV'],
			$campi['MAIMOD'],
			$campi['MAIARG'],
			$file,
			$nam_exists,
			$nam_dim,
			$campi['FILZIP'],
				
			$to_arc,				
//			$archived,
/*			
			$file_arc,
			$arc_exists,
			$arc_dim,
*/
			$campi['MAISTO'],
			$campi['MAIOUT'],
			$campi['MAISTT']
		);
		
		if(!empty($writeRow))
			return $writeRow;
		else
			return false;
	}
	
}