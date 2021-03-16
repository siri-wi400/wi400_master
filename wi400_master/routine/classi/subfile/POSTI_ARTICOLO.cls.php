<?php
class POSTI_ARTICOLO extends wi400CustomSubfile {
	
	private $rtlart;
	
	private $des_art = array();
	
	private $stmt_posto;

	public function __construct($parameters){
		
		global $db, $connzend;
		
		$this->rtlart = new wi400Routine('RTLART', $connzend);
	    $this->rtlart->load_description();
	    
	    // Caratteristiche posto
		$sql_posto = "SELECT * FROM LMADSTOC 
			WHERE MADCDE=? AND MADZOD=? AND MADCOR=? 
			AND MADBAY=? AND MADCDP=? AND MADSTA = '1'";
		$this->stmt_posto = $db->singlePrepare($sql_posto);
	}
	
	public function init($parameters){
		
		global $db;
		
		$array = array();
		$array['POSTO']=$db->singleColumns("1", "20");
		$array['TIPO_POSTO']=$db->singleColumns("1", "2");
		$array['PIANO']=$db->singleColumns("1", "2");
		$array['DIMENSIONI_POSTO']=$db->singleColumns("1", "20");
		$array['CLASSE_POSTO']=$db->singleColumns("1", "2");
		$array['ARTICOLO']=$db->singleColumns("1", "7");
		$array['DES_ART']=$db->singleColumns("1", "50");
		$array['PALLET']=$db->singleColumns("3", "2", "0" );
		$array['COLDIS']=$db->singleColumns("3", "4", "0" );
		$array['COLGIA']=$db->singleColumns("3", "4", "0" );
		$array['PEZDIS']=$db->singleColumns("3", "6", "2" );
		$array['PEZGIA']=$db->singleColumns("3", "6", "2" );
		
		$this->setCols($array);
	}
	
	public function body($campi, $parameters){
		global $db;
		
		if(!isset($this->des_art[$campi['CAPCDA']])) {
		    $this->rtlart->prepare();
		    $this->rtlart->set('NUMRIC',1);
		    $this->rtlart->set('DATINV', date("Ymd"));
		    $this->rtlart->set('ARTICOLO',$campi['CAPCDA']);
		    $this->rtlart->call();
		    $arti = $this->rtlart->get('ARTI');
		    $this->des_art[$campi['CAPCDA']] = $arti['MDADSA'];
		}
		
		$result = $db->execute($this->stmt_posto, array($campi['CAPCDE'],$campi['CAPZO1'],$campi['CAPCO1'],
			$campi['CAPBA1'],$campi['CAPCP1']));
	    $row_posto = $db->fetch_array($this->stmt_posto);
		
		$dim_posto = $row_posto['MADALT']." x ".$row_posto['MADLAR']." x ".$row_posto['MADPRF'];

		$writeRow = array(
//			$campi['CAPZO1']."-".$campi['CAPCO1']."-".$campi['CAPBA1']."-".$campi['CAPCP1'],
			$campi['CAPZO1']." . ".$campi['CAPCO1']." . ".$campi['CAPBA1']." . ".$campi['CAPCP1'],
			$campi['CAPTP1'],
			$row_posto['MADPPA'],
			$dim_posto,
			$row_posto['MADCLP'],
			$campi['CAPCDA'],
			$this->des_art[$campi['CAPCDA']],
			$campi['NUMERO'],
			$campi['COLDIS'],
			$campi['COLGIA'],
			$campi['PEZDIS'],
			$campi['PEZGIA']
		);
			
		return $writeRow;

	}
}
?>