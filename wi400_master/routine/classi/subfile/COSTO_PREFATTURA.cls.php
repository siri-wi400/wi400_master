<?php

class COSTO_PREFATTURA extends wi400CustomSubfile {
	
//	private $tab0114;
//	private $array_segni;
	private $rtlent;
	private $stmt;
	
	public function __construct($parameters){
		global $db, $connzend;
		
//		$this->tab0114 = new wi400Tabelle ( "0114", Null, $db );
//		$this->tab0114->prepareStmt();

		$this->rtlent = new wi400Routine('RTLENT', $connzend);
	    $this->rtlent->load_description();
	    /*$this->rtlent->prepare();
	    $this->rtlent->set('NUMRIC',1);
	    $this->rtlent->set('DATINV', date("Ymd"));*/
		
		$sql_row = "SELECT sum(CALIMP) as COSTO, sum(CALVIM) as IVA FROM FCALPRER";
		$sql_row .= " WHERE CALNRO=? AND CALSCP=? AND CALACC=?";
		$this->stmt = $db->singlePrepare($sql_row, 0);
	}
	
	public function getArrayCampi() {	
		global $db;
		
		$array = array();
		$array['PREFATTURA']=$db->singleColumns("1", "10", "", "Prefattura");
		$array['DATA_COMPETENZA']=$db->singleColumns("1", "10", "", "Data competenza");
		$array['CAUSALE']=$db->singleColumns("1", "4", "", "Causale");
		$array['DES_CAU']=$db->singleColumns("1", "30", "", "Descrizione causale");
		$array['SEGNO']=$db->singleColumns("1", "2", "", "Segno");
		$array['COSTO']=$db->singleColumns("3", "13", 2, "Costo");
		$array['IVA']=$db->singleColumns("3", "9", 2, "IVA");
		$array['TOT_COSTO']=$db->singleColumns("3", "13", 2, "Totale Prefattura");
		$array['STORE']=$db->singleColumns("1", "4", "", "Store");
		$array['DES_STORE']=$db->singleColumns("1", "100", "", "Descrizione store");
		$array['CAMACC']=$db->singleColumns("1", "4");
		$array['CAMSCP']=$db->singleColumns("1", "4");
		
		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}

	public function body($campi, $parameters){
		global $db,$connzend, $persTable;

/*		
		if(!isset($this->array_segni[$campi['CAUSALE']])) {
			$this->tab0114->decodifica($campi['CAUSALE']);
			$tabella =$this->tab0114->getRecord();
	
			$cod_segno = substr($tabella['TABREC'],78,1);
			
			if($cod_segno==1 || $cod_segno==3 || $cod_segno==5) {
				$this->array_segni[$campi['CAUSALE']]['SEGNO'] = "+";
			}
			else if($cod_segno==2 || $cod_segno==4 || $cod_segno==6) {
				$this->array_segni[$campi['CAUSALE']]['SEGNO'] = "-";
			}
			
			$this->array_segni[$campi['CAUSALE']]['DES_CAU'] = substr($tabella['TABREC'],0,30);
		}
*/		
		if(isset($campi['CAUSALE']) && !empty($campi['CAUSALE'])) {
			$causali = $persTable->decodifica('0114', $campi['CAUSALE']);
			$cod_segno = substr($causali['TABELLA']['TABREC'],78,1);
			
			if($cod_segno==1 || $cod_segno==3 || $cod_segno==5) {
				$segno = "+";
			}
			else if($cod_segno==2 || $cod_segno==4 || $cod_segno==6) {
				$segno = "-";
			}
				
			$descr = $causali['DESCRIZIONE']; 
		}
		
		$data_competenza = dateString($campi['GIORNO'],$campi['MESE'],$campi['ANNO']);
		
		$this->rtlent->prepare();
	    $this->rtlent->set('NUMRIC',1);
	    $this->rtlent->set('DATINV', date("Ymd"));
		$this->rtlent->set('CODICE', $campi['CAMCDE']);
		$this->rtlent->call();
		
		$ente = $this->rtlent->get('ENTI');
		
		$result = $db->execute($this->stmt, array($campi['PREFATTURA'], $campi['CAMSCP'], $campi['CAMACC']));
	    $row = $db->fetch_array($this->stmt);		
		
		$writeRow = array(
			$campi['PREFATTURA'],
			$data_competenza,
			$campi['CAUSALE'],
			$descr,
			$segno,
			$row['COSTO'],
			$row['IVA'],
			$campi['TOT_COSTO'],
			$campi['CAMCDE'],
			$ente['MAFDSE'],
			$campi['CAMACC'],
			$campi['CAMSCP']
		);

		return $writeRow;
	}
	
}

?>