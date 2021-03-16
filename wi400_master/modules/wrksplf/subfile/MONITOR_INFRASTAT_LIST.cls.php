<?php

class MONITOR_INFRASTAT_LIST extends wi400CustomSubfile {
	
//	private $stmt_pes;
	private $rtlaa1;
	
	public function __construct($parameters){
		global $db, $connzend, $moduli_path;
		
		$this->data_rif = $parameters['DATA_RIF'];
/*		
		$sql_pes = "select MHLPES 
			from FMHLAADP o, LATERAL ( 
				SELECT rrn(i) AS NREL 
				FROM FMHLAADP i 
				WHERE o.mhlcda = i.mhlcda and o.mhlcde = i.mhlcde and digits(mhlava)!!digits(mhlmva)!!digits(mhlgva) <= {$this->data_rif} 
				ORDER BY mhlcda, digits(mhlava)!!digits(mhlmva)!!digits(mhlgva) desc 
				FETCH FIRST ROW ONLY ) AS x
			where rrn(o)=x.NREL and o.MHLCDA=? and o.MHLELI<>'9' and o.MHLSTA='1' and o.MHLCDE=?";
		$this->stmt_pes = $db->singlePrepare($sql_pes, 0, true);		
*/
		$this->rtlaa1 = new wi400Routine('RTLAA1', $connzend);
		$this->rtlaa1->load_description();
	}
	
	public function getArrayCampi() {
		global $db;
	
		$array = array();
	
		$array['FABCD1']=$db->singleColumns("1", "4", "", "Locale");
		$array['MAFDSE']=$db->singleColumns("1", "100", "", "Descrizione Locale");
		$array['FABNBL']=$db->singleColumns("1", "7", "", "Bolla");
		$array['DATA_BOL']=$db->singleColumns("1", "8", "", "Data bolla");
		$array['FATTURA']=$db->singleColumns("1", "9", "", "Fattura");
		$array['DATA_FAT']=$db->singleColumns("1", "8", "", "Data fattura");
		$array['FABCDA']=$db->singleColumns("1", "7", "", "Articolo");
		$array['MDADSA']=$db->singleColumns("1", "100", "", "Descrizione articolo");
		$array['MDACON']=$db->singleColumns("1", "2", "", "Confezione");
		$array['MDATPG']=$db->singleColumns("1", "2", "", "Tipo grammatura");
		$array['MDAGRA']=$db->singleColumns("3", "4", 0, "Grammatura");
		$array['MDAPEZ']=$db->singleColumns("3", "4", 0, "Pezzi per cartone");
		$array['MHLPES']=$db->singleColumns("3", "7", 2, "Peso medio cartone");
		$array['PESO_MEDIO']=$db->singleColumns("3", "9", 4, "Peso medio");
		$array['FABIVA']=$db->singleColumns("1", "2", "", "IVA");
		$array['VALORE']=$db->singleColumns("3", "15", 3, "Valore");
		$array['FABPPR']=$db->singleColumns("3", "9", 2, "Quantità");
	
		return $array;
	}
	
	public function init($parameters){
		global $db;
	
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){
		global $db, $connzend, $persTable, $moduli_path;
		
		$des_locale = get_campo_ente($campi['FABCD1'], $this->data_rif, "MAFDSE");
		
		$art_array = get_campo_articolo($campi['FABCDA'], $this->data_rif);
		
//		$peso_medio_cartone = $campi['MHLPES'];
/*		
		$res = $db->execute($this->stmt_pes, array($campi['FABCDA'], $deposito));
		
		$peso_medio_cartone = 0;
		if($row_pes = $db->fetch_array($this->stmt_pes)) {
			$peso_medio_cartone = $row_pes['MHLPES'];
		}
*/
		$this->rtlaa1->prepare();
		$this->rtlaa1->set('CODICE', $campi['FABCDE']);
		$this->rtlaa1->set('CODART', $campi['FABCDA']);
		$this->rtlaa1->set('DATARF', $this->data_rif);
		$this->rtlaa1->call();
		
		$aadp = $this->rtlaa1->get('AADP');
		
		$peso_medio_cartone = $aadp["MHLPES"];
		
		$peso_medio = 0;
		if($art_array['MDAPEZ']>0) {
			$peso_medio = $peso_medio_cartone/$art_array['MDAPEZ'];
		}
		
		$writeRow = array(
			$campi['FABCD1'],
			$des_locale,	
			$campi['FABNBL'],
			$campi['DATA_BOL'],
			$campi['FATTURA'],
			$campi['DATA_FAT'],
			$campi['FABCDA'],
			$art_array['MDADSA'],
			$art_array['MDACON'],
			$art_array['MDATPG'],
			$art_array['MDAGRA'],
			$art_array['MDAPEZ'],
			$peso_medio_cartone,
			$peso_medio,
			$campi['FABIVA'],
			$campi['VALORE'],
			$campi['FABPPR']				
		);
		
		return $writeRow;
	}
}