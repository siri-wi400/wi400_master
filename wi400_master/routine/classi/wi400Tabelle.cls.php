<?php
class wi400Tabelle {

	private $siglaTabella;
	private $codiceElemento;
	private $descrizioneElemento;
	private $statoElemento;
	private $recordTabella;
	private $errore;
	private $db;
	private $result;
	private $arrayTabelle;
	private $tabella;
	private $isTabGen;
	private $sqlWhere;
	private $prepared;

	// Costruttore
	// Se passo la sigla e il codice viene effettuata anche la ricerca
	//
	public function __construct($sigla="", $codice="", $db=null){

		$this->siglaTabella = $sigla;
		$this->codiceElemento = $codice;
		$this->db = $db;
		$this->errrore = '0';
		// Se ho passato nel costruttore sigla e codice tabella faccio anche la ricerca
		if ($sigla!="" AND $codice!="")
		{
			$this->cercaElemento($this->siglaTabella, $this->codiceElemento);
		}

	}
    public function prepareStmt($sigla=null)
	{
		// Test caricamento descrittore tabelle
		if (isset($sigla)) {
		$this->siglaTabella = $sigla;
		} else {
			$sigla=$this->siglaTabella;
		}
		
		$tabella = $this->getTabella($sigla);
		if (!isset($tabella))
		$this->errore = '2';
		else
		{
			$what = "A.*";
			if ($this->isTabGen)
			{
				$what.=", SUBSTR(TABREC, ".$tabella['STAPOS'] ." , 1) AS STAGEN ,
		    		    SUBSTR(TABREC, ".$tabella['DESPOS'] ." , ".$tabella['DESLEN']. ") AS DESGEN";
			}
		  
			$sql = "SELECT ". $what ." FROM ".$tabella['FILE']." A WHERE ".$tabella['SIGLA']. "='"
			.$this->siglaTabella."' AND ".$tabella['CODICE']."=?". $this->sqlWhere;
		}
	   $this->prepared = $this->db->singlePrepare($sql);
	   $this->tabella = $tabella;
	   
	}
	public function decodifica($elemento)
	{
        $campi = array($elemento);
		$result = $this->db->execute($this->prepared, $campi);
		$row = $this->db->fetch_array($this->prepared);
        
		if (!$row)
			{
				$this->errore = '1';
			}
			else
			{
				$this->errore = '0';
				if ($this->isTabGen) {
					$this->descrizioneElemento = $row['DESGEN'];
					$this->statoElemento = $row['STAGEN'];

				} else {
					$this->descrizioneElemento = $row[$this->tabella['DESCRIZIONE']];
					$this->statoElemento = $row[$this->tabella['STATO']];
				}
				$this->recordTabella = $row;
			}
		
	}
	public function setWhere($where){

       $this->sqlWhere = $where;

	}	
	
	// Cerca Elemento
	// Viene ricercato l'elemento della tabella passati come parametro
	public function cercaElemento($sigla, $codice)
	{
		// Test caricamento descrittore tabelle
		$this->siglaTabella = $sigla;
		$this->codiceElemento = $codice;
		$tabella = $this->getTabella($sigla);
		if (!isset($tabella))
		$this->errore = '2';
		else
		{
			$what = "A.*";
			if ($this->isTabGen)
			{
				$what.=", SUBSTR(TABREC, ".$tabella['STAPOS'] ." , 1) AS STAGEN ,
		    		    SUBSTR(TABREC, ".$tabella['DESPOS'] ." , ".$tabella['DESLEN']. ") AS DESGEN";
			}
		  
			$sql = "SELECT ". $what ." FROM ".$tabella['FILE']." A WHERE ".$tabella['SIGLA']. "='"
			.$this->siglaTabella."' AND ".$tabella['CODICE']."='".$this->codiceElemento."'";
			$result = $this->db->singleQuery($sql);
			$row = $this->db->fetch_array($result);
			if (!$row)
			{
				$this->errore = '1';
			}
			else
			{
				$this->errore = '0';
				if ($this->isTabGen) {
					$this->descrizioneElemento = $row['DESGEN'];
					$this->statoElemento = $row['STAGEN'];

				} else {
					$this->descrizioneElemento = $row[$tabella['DESCRIZIONE']];
					$this->statoElemento = $row[$tabella['STATO']];
				}
				$this->recordTabella = $row;
			}
		}
	}
	// Prepara caricamento tabella
	public function preparaTabella($sigla)
	{
		$this->siglaTabella = $sigla;
		// Cerco la tabella
		$this->tabella = $this->getTabella($sigla);
		if (!isset($this->tabella))
		$this->errore = '2';
		else
		{
			$what = "A.*";
			if ($this->isTabGen)
			{
				$what.=", SUBSTR(TABREC, ".$this->tabella['STAPOS'] ." , 1) AS STAGEN ,
		    		    SUBSTR(TABREC, ".$this->tabella['DESPOS'] ." , ".$this->tabella['DESLEN']. ") AS DESGEN";
			}
		  
			$sql = "SELECT ". $what ." FROM ".$this->tabella['FILE']." A WHERE ".$this->tabella['SIGLA']. "='"
			.$this->siglaTabella."'". $this->sqlWhere;
			$result = $this->db->query($sql);
			if (!$result)
			{
				$this->errore = '1';
			}
			else
			{
				$this->errore = '0';
				$this->result = $result;
			}
		}
	}
	// Caricamento elementi tabella
	public function caricaTabella()
	{
		$row = $this->db->fetch_array($this->result);
		if (!$row) return false;
		if (!$this->isTabGen)
		{
			$this->statoElemento = $row[$this->tabella['STATO']];
			$this->descrizioneElemento = $row[$this->tabella['DESCRIZIONE']];
		}
		else {
			$this->statoElemento = $row['STAGEN'];
			$this->descrizioneElemento = $row['DESGEN'];
		}
		$this->codiceElemento = $row[$this->tabella['CODICE']];
		$this->recordTabella = $row;
		return true;
	}
	// Ritorna la descrizione dell'elemento della tabella
	function getDescrizione()
	{
		return $this->descrizioneElemento;
	}
	// Ritorna la descrizione dell'elemento della tabella
	function getElemento()
	{
		return $this->codiceElemento;
	}
	// Ritorna lo statodell'elemento della tabella
	function getStato()
	{
		return $this->statoElemento;
	}
	// Ritorna il record dell'elemento della tabella
	function getRecord()
	{
		return $this->recordTabella;
	}
	// Ritorna l'eventuale errore
	function getErrore()
	{
		return $this->errore;
	}
	// Cerca caratteristiche tabella
	function getTabella($sigla)
	{
		
		$arrayTabelle = array();
		
		$number = 0;
		// Tabella inserite in altre tabelle
		if ($sigla=="0014") $sigla="0005";
		if ($sigla=="0013") $sigla="0005";
		if ($sigla=="0033") $sigla="0005";
		if ($sigla=="0060") $sigla="0005";
		if ($sigla=="0011") $sigla="0005";
		if ($sigla=="0049") $sigla="0005";
		if ($sigla=="0051") $sigla="0005";
		if ($sigla=="0052") $sigla="0005";
		if ($sigla=="0021") $sigla="0005";			
		if ($sigla=="0095") $sigla="0003";
		
		$number = $sigla;
		$this->isTabGen = False;
		if(is_numeric($sigla))
		{
			$number = 0;
			$number = $sigla;

			// Se sigla minore di 99
			if ($sigla <100)
			{
				$sfx = substr($sigla, 2, 2);
				$arrayTabelle[$sigla] = array(
						    'FILE'=>'FTAB0'.$sfx,
						    'SIGLA'=>'T'.$sfx.'SIG',
						    'CODICE'=>'T'.$sfx.'COD',
						    'DESCRIZIONE'=>'T'.$sfx.'DEL',
						    'STATO'=>'T'.$sfx.'STA'
						    );
			}
			// Sigla tra 100 e 999
			if (($sigla >99) && ($sigla < 1000))
			{
				$sfx = substr($sigla, 1, 3);
				$arrayTabelle[$sigla] = array(
						    'FILE'=>'FTAB'.$sfx,
							   'SIGLA'=>'T'.$sfx.'SG',
						    'CODICE'=>'T'.$sfx.'CD',
						    'DESCRIZIONE'=>'T'.$sfx.'DE',
						    'STATO'=>'T'.$sfx.'ST'
						    );
			}
			// Sigla tra 100 e 999
			if ($sigla >1000 && $sigla<=9000)
			{
				$sfx = substr($sigla, 2, 2);
				$arrayTabelle[$sigla] = array(
						    'FILE'=>'FTB'.$sigla,
							'SIGLA'=>'TB'.$sfx.'SG',
						    'CODICE'=>'TB'.$sfx.'CD',
						    'DESCRIZIONE'=>'TB'.$sfx.'DS',
						    'STATO'=>'TB'.$sfx.'ST'
						    );
			}
			else if($sigla>9000) {
				if($sigla>9009) {
					$sfx = substr($sigla, 2, 2);
					$arrayTabelle[$sigla] = array(
							'FILE'=>'FTB'.$sigla,
							'SIGLA'=>'TB'.$sfx.'SG',
							'CODICE'=>'TB'.$sfx.'CD',
							'DESCRIZIONE'=>'TB'.$sfx.'DE',
							'STATO'=>'TB'.$sfx.'ST'
					);
						
					switch ($sigla) {
						case "9071":
							$arrayTabelle[$sigla]['DESCRIZIONE']= 'TB'.$sfx.'DS';
							break;
					}
				}
				else {
					$sfx = substr($sigla, 3, 1);
					$arrayTabelle[$sigla] = array(
							'FILE'=>'FTB'.$sigla,
							'SIGLA'=>'TB'.$sfx.'SIG',
							'CODICE'=>'TB'.$sfx.'COD',
							'DESCRIZIONE'=>'TB'.$sfx.'DES',
							'STATO'=>'TB'.$sfx.'STA'
					);
				}
			}
		}
		// Verifico se per caso la tabella è in FTABGEN
		$sql="SELECT COUNT(*) AS COUNTROW FROM FTABGEN WHERE TABSIG='$sigla'";
		$result = $this->db->query($sql);
		$row = $this->db->fetch_array($result);

		// Se ho trovato record significa che la sigla è in FTABGEN
		if($row['COUNTROW']>0)
		{
			$this->isTabGen = True;
			$arrayTabelle[$sigla] = array(
				       'FILE'=>'FTABGEN',
					   'SIGLA'=>'TABSIG',
					   'CODICE'=>'TABCOD',
			);
			switch ( $this->siglaTabella )
			{
				case "0063":
					$arrayTabelle[$sigla]['STAPOS']= '33';
					$arrayTabelle[$sigla]['DESPOS']= '1';
					$arrayTabelle[$sigla]['DESLEN']= '20';
					break;
				case "0069":
					$arrayTabelle[$sigla]['STAPOS']= '105';
					$arrayTabelle[$sigla]['DESPOS']= '1';
					$arrayTabelle[$sigla]['DESLEN']= '20';
				    break;
				case "0167":
					$arrayTabelle[$sigla]['STAPOS']= '105';
					$arrayTabelle[$sigla]['DESPOS']= '1';
					$arrayTabelle[$sigla]['DESLEN']= '30';
				    break;						    
				case "0462":
					$arrayTabelle[$sigla]['STAPOS']= '105';
					$arrayTabelle[$sigla]['DESPOS']= '16';
					$arrayTabelle[$sigla]['DESLEN']= '15';
				    break;
			    case "0112":
			    	$arrayTabelle[$sigla]['STAPOS']= '105';
			    	$arrayTabelle[$sigla]['DESPOS']= '1';
			    	$arrayTabelle[$sigla]['DESLEN']= '40';
			    	break;
				default:
					$arrayTabelle[$sigla]['STAPOS']= '104';
					$arrayTabelle[$sigla]['DESPOS']= '1';
					$arrayTabelle[$sigla]['DESLEN']= '14';
					break;
			}

		} else {
			$prefix = substr($sigla, 0, 1);
			$number = substr($sigla, 1, 3);
			if ($prefix =="V")
			{
				if ($number == 170) {
					$codtab = substr($sigla, 1, 3);
					$arrayTabelle[$sigla] = array(
						'FILE'=>'FTAB'.$sigla,
						'SIGLA'=>'TV'.$codtab.'SG',
						'CODICE'=>'TV'.$codtab.'CD',
						'DESCRIZIONE'=>'TV'.$codtab.'DE',
						'STATO'=>'TV'.$codtab.'ST');
				} elseif ($number == 200) {
					$codtab = substr($sigla, 1, 1);
					$arrayTabelle[$sigla] = array(
							'FILE'=>'FTABV020',
							'SIGLA'=>'TV'.$codtab.'SIG',
							'CODICE'=>'TV'.$codtab.'COD',
							'DESCRIZIONE'=>'TV'.$codtab.'DEL',
							'STATO'=>'TV'.$codtab.'STA');
				} elseif ($number > 1 && $number < 10) {
					$codtab = substr($sigla, 3, 1);
					$arrayTabelle[$sigla] = array(
							'FILE'=>'FTABV00'.$codtab,
							'SIGLA'=>'TV'.$codtab.'SIG',
							'CODICE'=>'TV'.$codtab.'COD',
							'DESCRIZIONE'=>'TV'.$codtab.'DEL',
							'STATO'=>'TV'.$codtab.'STA');
				}  else {
					if ($number < 100)
					{
						$codtab = substr($sigla, 2, 2);
						$arrayTabelle[$sigla] = array(
					       'FILE'=>'FTAB'.$sigla,
						   'SIGLA'=>'TV'.$codtab.'SG',
						   'CODICE'=>'TV'.$codtab.'CD',
	               		   'DESCRIZIONE'=>'TV'.$codtab.'DE',
						   'STATO'=>'TV'.$codtab.'ST');
					} else {
						$codtab = substr($sigla, 1, 3);
						$arrayTabelle[$sigla] = array(
					       'FILE'=>'FTAB'.$sigla,
						   'SIGLA'=>'T'.$codtab.'SG',
						   'CODICE'=>'T'.$codtab.'CD',
	               		   'DESCRIZIONE'=>'T'.$codtab.'DE',
						   'STATO'=>'T'.$codtab.'ST');
					}
				}
			}
			if ($prefix =="S")
			{
				$number2 = substr($sigla, 2, 2);
				if ($number2 < 9){
					$number2 = substr($sigla, 3, 1);
					$arrayTabelle[$sigla] = array(
					'FILE'=>'FTAB'.$prefix.'0'.$number2,
					'SIGLA'=>'TS'.$number2.'SIG',
					'CODICE'=>'TS'.$number2.'COD',
	               	'DESCRIZIONE'=>'TS'.$number2.'DEL',
					'STATO'=>'TS2'.$number.'STA');
				} elseif ($number < 999) {
					$arrayTabelle[$sigla] = array(
					'FILE'=>'FTAB'.$prefix.$number2,
					'SIGLA'=>'TS'.$number2.'SG',
					'CODICE'=>'TS'.$number2.'CD',
	               	'DESCRIZIONE'=>'S'.$number2.'DE',
					'STATO'=>'S'.$number2.'ST');
				}
			}
			if ($prefix =="R")
			{
				$number2 = substr($sigla, 2, 2);
				if ($number2 < 9){
					$arrayTabelle[$sigla] = array(
					'FILE'=>'FTBR00'.$number2,
					'SIGLA'=>'TBRSSG',
					'CODICE'=>'TBRSCD',
	               	'DESCRIZIONE'=>'TBRSDE',
					'STATO'=>'TBRSSA');
				} else {
					$arrayTabelle[$sigla] = array(
					'FILE'=>'FTAB'.$prefix.$number2,
					'SIGLA'=>'TS'.$number2.'SG',
					'CODICE'=>'TS'.$number2.'CD',
	               	'DESCRIZIONE'=>'TS'.$number2.'DE',
					'STATO'=>'TS'.$number2.'ST');
				}
			}
			// Prefisso L per tabelle LGM
			if ($prefix =="L")
			{
				$number2 = substr($sigla, 2, 2);
				$arrayTabelle[$sigla] = array(
						'FILE'=>'FTAB'.$prefix."0".$number2,
						'SIGLA'=>'TL'.$number2.'SG',
						'CODICE'=>'TL'.$number2.'CD',
						'DESCRIZIONE'=>'TL'.$number2.'DE',
						'STATO'=>'TL'.$number2.'ST');
			}
		}
		// Eccezzioni alle regole sopra
		if ($sigla == '0170') $arrayTabelle[$sigla]["DESCRIZIONE"] = 'T170DS';
		if ($sigla == '0147') $arrayTabelle[$sigla]["DESCRIZIONE"] = 'T147DS';
		if ($sigla == '0924') $arrayTabelle[$sigla]["FILE"] = 'FTAB0924';		
		if ($sigla == '0941') $arrayTabelle[$sigla]["FILE"] = 'FTAB0941';
		return $arrayTabelle[$sigla];
	}
		function __destruct()
	{
		    global $db;

			$db->freestmt($this->prepared);
	}
	
}
?>
