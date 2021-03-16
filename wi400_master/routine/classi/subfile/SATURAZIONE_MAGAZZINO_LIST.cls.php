<?php 

class SATURAZIONE_MAGAZZINO_LIST extends wi400CustomSubfile {
	
	private $rtlart;
	private $rtlgdi;
	
	private $stmt_ord;
	
	private $deposito;
//	private $num_rows;
//	private $perc;
	
	private static $percCounter=0;
	private static $rowCounter=0;
	
	public function __construct($parameters){
		global $db, $connzend;
		
		$this->deposito = $parameters['DEPOSITO'];
//		$this->num_rows = $parameters['NUM_ROWS'];
//		$this->perc = round(5*($this->num_rows/100));
//		echo "NUM:".$this->num_rows."_PERC:".$this->perc."<br>";
		
		// Routine RTLART
		$this->rtlart = new wi400Routine('RTLART', $connzend);
	    $this->rtlart->load_description();
	    
	    // Routine RTLGDI
	    $this->rtlgdi = new wi400Routine('RTLGDI', $connzend);
	    $this->rtlgdi->load_description();
	    
	    $sql_ord = "select * from FODGRIGO where ODGCDA=? and ODGCDE='".$this->deposito."' and ODGSTA='1' 
	    	and ODGCAU in ('0872','0873')";
//	    $this->stmt_ord = $db->singlePrepare($sql_ord, true);
		$this->stmt_ord = $db->prepareStatement($sql_ord,0,true);
	}
	
	public function getArrayCampi() {
		global $db;
		
		$array = array();
		$array['ARTICOLO']=$db->singleColumns("1", "7", "", "Codice articolo");
		$array['DES_ART']=$db->singleColumns("1", "30", "", "Descrizione articolo");
		$array['ZONA']=$db->singleColumns("1", "2", "", "Zona");
		$array['CORRIDOIO']=$db->singleColumns("1", "2", "", "Corridoio");
		$array['BAY']=$db->singleColumns("1", "3", "", "Bay");
		$array['POST']=$db->singleColumns("1", "2", "", "Post");
		
		return $array;
	}
	
	public function init($parameters){
		global $db;
		
		$this->setCols($this->getArrayCampi());
	}
	
	public function body($campi, $parameters){
		global $db, $connzend;
/*		
		self::$percCounter++;
		self::$rowCounter++;
		
		if (self::$percCounter == $this->perc){
			self::$percCounter = 0;
			$progress = self::$rowCounter*100/$this->num_rows;
//			echo "PROGRESS:$progress<br>";
			wi400ProgressBar::setPercentage("SATURAZIONE_MAGAZZINO_PROGRESS",$progress);
		}
*/		
		// Giacenza
		$this->rtlgdi->prepare();
		$this->rtlgdi->set('DEPOSITO',$this->deposito);
		$this->rtlgdi->set('ARTICOLO',$campi['MHPCDA']);
		$this->rtlgdi->set('ANNO',date('Y'));
		$this->rtlgdi->call();
		
		$giacenza = $this->rtlgdi->get("GIACOL");
		
		if($giacenza!=0) {
			return false;
		}
			
		// Ordini
	    $result = $db->execute($this->stmt_ord, array($campi['MHPCDA']));

	    if($row = $db->fetch_array($this->stmt_ord))
	    	return false;
		
		$this->rtlart->prepare();
	    $this->rtlart->set('NUMRIC',1);
	    $this->rtlart->set('DATINV', date("Ymd"));
		$this->rtlart->set('ARTICOLO',$campi['MHPCDA']);
	    $this->rtlart->call();
	    
	    $des_art = $this->rtlart->getDSParm("ARTI", "MDADSA");
	    
	    $writeRow = array(
	    	$campi['MHPCDA'],
	    	$des_art,
	    	$campi['MHPZOD'],
	    	$campi['MHPCOR'],
	    	$campi['MHPBAY'],
	    	$campi['MHPPOS']
	    );
/*	 	
	    if(self::$rowCounter==$this->num_rows) {
//	    	echo "ROW:".self::$rowCounter."<br>";
	    	wi400ProgressBar::setPercentage("SATURAZIONE_MAGAZZINO_PROGRESS",100);
	    }
*/	    
	    return $writeRow;
	}
	
}

?>