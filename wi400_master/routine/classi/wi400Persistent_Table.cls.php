<?php
/*
 * wi400Persistent_Table
 * La classe permette di memorizzare in modo persistente sulla sessione le informazioni
 * decodificate in precedenza.
 */
class wi400Persistent_Table {

	private $errore;
	private $tabella;
	private $saveSession;

	/**
	 * Costruttore della classe
	 * 
	 * @param: boolean $saveSessione : se a True salva la persistenza su sessione
	 * 
	**/	
		public function __construct($saveSession=False){

		$this->errrore = '0';
		$this->saveSession = $saveSession;
		if ($saveSession==True) { 
		    if (isset($_SESSION['PERS_TABLE'])) {
		    	$this->tabella = $_SESSION['PERS_TABLE'];
			} else {
				// Cerco i dati dall'area di memoria condivisa e persistente
				//$seg = shm_attach (1);
				//$valori=shm_get_var($seg , 1 );
				//shm_detach($seg);
				//if (isset($valori)) $this->tabella = $valori;
				//echo "<br>Tabelle!!";
				//print_r($this->tabella);
			}		
	    }
		}
	/**
	 * La funzione fa il prepare sulla tabella se non è stato fatto in precedenza
	 * 
	 * @param: string $sigla : sigla della tabela a cui aggiungere il filtro
	 * 
	**/	
    public function prepareStmt($sigla)
	{
		global $db; 
		
		if (!isset($this->tabella[$sigla]['STATEMENT']))
		{
		$sqlWhere="";	
		//echo "prepare".$sigla;
		// Verifico se c'è un filtro aggiuntivo
		if (isset($this->tabella[$sigla]['WHERE'])) $sqlWhere=$this->tabella[$sigla]['WHERE'];
		// Cerco la configurazione della tabella
		if (isset($this->tabella[$sigla]['CONFIGURATION']))
		{
			$tabella = $this->tabella[$sigla]['CONFIGURATION'];
		} else {
			$tabella = $this->getTabella($sigla);
			$this->tabella[$sigla]['CONFIGURATION']=$tabella;
		}
		if (!isset($tabella))
		$this->errore = '2';
		else
		{
			$what = "A.*";
			if (isset($tabella['ISTABGEN']) && $tabella['ISTABGEN']==True)
			{
				$what.=", SUBSTR(TABREC, ".$tabella['STAPOS'] ." , 1) AS STAGEN ,
		    		    SUBSTR(TABREC, ".$tabella['DESPOS'] ." , ".$tabella['DESLEN']. ") AS DESGEN";
			}
		  
			$sql = "SELECT ". $what ." FROM ".$tabella['FILE']." A WHERE ".$tabella['SIGLA']. "='"
			.$sigla."' AND ".$tabella['CODICE']."=?". $sqlWhere;
		}
	   $this->tabella[$sigla]['STATEMENT'] = $db->singlePrepare($sql);
	}
	}
	/**
	 * La funzione permette decodificare un elemento, se non già codificato e persistente viene
	 * ricercato sul dataBase
	 * 
	 * @param: string $sigla : sigla della tabela a cui aggiungere il filtro
	 * @param: string $elemento : elemento da decodificare
	 * 
	**/
	public function decodifica($sigla, $elemento)
	{
		global $db;
		
//		echo "SIGLA: $sigla - ELEMENTO: $elemento<br>";
		
        $dati = array();
        $dati['FOUND']=False;
        $dati['DESCRIZIONE']="";
		$campi = array($elemento);
		// Se non passo un elemento non lo decodifico
		if ($elemento=="") return $dati;
		
//		echo "TABELLA:<pre>"; print_r($this->tabella[$sigla]['ELEMENTO'][$elemento]); echo "</pre>";
		
        if (!isset($this->tabella[$sigla]['ELEMENTO'][$elemento])) {
//        	echo "SIGLA:$sigla<br>";
		// Eseguo il prepare se non è già stato fatto
		$this->prepareStmt($sigla);
 		$result = $db->execute($this->tabella[$sigla]['STATEMENT'], $campi);
		$row = $db->fetch_array($this->tabella[$sigla]['STATEMENT']);

		if (!$row)
			{
				$this->errore = '1';
				return $dati;
			}
			else
			{
				//echo "Decodificato:".$elemento;
				$this->errore = '0';
				if (isset($this->tabella[$sigla]['CONFIGURATION']['ISTABGEN']) && $this->tabella[$sigla]['CONFIGURATION']['ISTABGEN']==True) {
					$dati['DESCRIZIONE'] = trim($row['DESGEN']);
					$dati['STATO'] = $row['STAGEN'];
				} else {
					if (isset($row[$this->tabella[$sigla]['CONFIGURATION']['DESCRIZIONE']])) {
						$dati['DESCRIZIONE'] = trim($row[$this->tabella[$sigla]['CONFIGURATION']['DESCRIZIONE']]);
					} else {
						$dati['DESCRIZIONE'] = "";
					}
					$dati['STATO'] = $row[$this->tabella[$sigla]['CONFIGURATION']['STATO']];
				}
				$dati['TABELLA'] = $row;
				$this->tabella[$sigla]['ELEMENTO'][$elemento]['DESCRIZIONE']= $dati['DESCRIZIONE'];
				$this->tabella[$sigla]['ELEMENTO'][$elemento]['STATO']= $dati['STATO'];
				$this->tabella[$sigla]['ELEMENTO'][$elemento]['TABELLA']= $dati['TABELLA'];
			}
        } else {
        		//echo "Trovato:".$elemento;
				$dati['DESCRIZIONE'] = $this->tabella[$sigla]['ELEMENTO'][$elemento]['DESCRIZIONE'];
				$dati['STATO'] = $this->tabella[$sigla]['ELEMENTO'][$elemento]['STATO'];
				$dati['TABELLA'] = $this->tabella[$sigla]['ELEMENTO'][$elemento]['TABELLA'];
        }
        $dati['FOUND']=True;
//		echo "DATI:<pre>"; print_r($dati); echo "</pre>";
        return $dati;
	}
	/**
	 * La funzione permette di aggiungere un filtro SQL all'interrogazione
	 * 
	 * @param: string $where : filtro sql
	 * @param: string $sigla : sigla della tabela a cui aggiungere il filtro
	 * 
	**/
	public function setWhere($where, $sigla){

       $this->tabella[$sigla]['WHERE'] = $where;

	}	
	/**
	 * La funzione permette di rimuovere dalla persistenza una tabella per la successiva ricarica
	 * 
	 * @param: string $sigla : sigla della tabela a cui aggiungere il filtro
	 * 
	**/
	public function removeTabellla($sigla){

       unset($this->tabella[$sigla]);

	}	
	// Cerca caratteristiche tabella
	// @todo: spostare la funzione in common.php
	function getTabella($sigla) {
		global $db;
		
		static $stmtTabgen;
		
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
		if(is_numeric($sigla)) {
			$number = 0;
			$number = $sigla;

			// Se sigla minore di 99
			if ($sigla <100) {
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
			if (($sigla >99) && ($sigla < 1000)) {
				$sfx = substr($sigla, 1, 3);
				$arrayTabelle[$sigla] = array(
						    'FILE'=>'FTAB'.$sfx,
							'SIGLA'=>'T'.$sfx.'SG',
						    'CODICE'=>'T'.$sfx.'CD',
						    'DESCRIZIONE'=>'T'.$sfx.'DE',
						    'STATO'=>'T'.$sfx.'ST'
						    );
		    
			}
			// Sigla oltre 1000
			if ($sigla >1000 && $sigla<=9000) {
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
		
		// Forzatura FTABGEN
		$forceTabGen = False;
		if ($sigla == '0168') {
			$forceTabGen = True;
		}
		
		// Verifico se per caso la tabella è in FTABGEN
		if (!isset($stmtTabgen)) {
			$sql="SELECT COUNT(*) AS COUNTROW FROM FTABGEN WHERE TABSIG=?";
		    $stmtTabgen = $db->prepareStatement ($sql, 1 );
		}
		
		//$result = $db->singleQuery($sql);
		$result = $db->execute ($stmtTabgen, array($sigla));
		$row = $db->fetch_array($stmtTabgen);
		
		// Se ho trovato record significa che la sigla è in FTABGEN
		if($row['COUNTROW']>0 || $forceTabGen) {
			$arrayTabelle[$sigla] = array(
				       'FILE'=>'FTABGEN',
					   'SIGLA'=>'TABSIG',
					   'CODICE'=>'TABCOD',
			           'ISTABGEN'=>True
			);
			
			switch ($sigla) {
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
				case "0462":
					$arrayTabelle[$sigla]['STAPOS']= '105';
					$arrayTabelle[$sigla]['DESPOS']= '16';
					$arrayTabelle[$sigla]['DESLEN']= '15';
				    break;				    
				case "0114":
					$arrayTabelle[$sigla]['STAPOS']= '105';
					$arrayTabelle[$sigla]['DESPOS']= '1';
					$arrayTabelle[$sigla]['DESLEN']= '30';
				    break;
			    case "0112":
			    	$arrayTabelle[$sigla]['STAPOS']= '105';
			    	$arrayTabelle[$sigla]['DESPOS']= '1';
			    	$arrayTabelle[$sigla]['DESLEN']= '40';
			    	break;				    	
				case "0192":
					$arrayTabelle[$sigla]['STAPOS']= '105';
					$arrayTabelle[$sigla]['DESPOS']= '1';
					$arrayTabelle[$sigla]['DESLEN']= '30';
				    break;	
				case "0153":
					$arrayTabelle[$sigla]['STAPOS']= '105';
					$arrayTabelle[$sigla]['DESPOS']= '1';
					$arrayTabelle[$sigla]['DESLEN']= '30';
					break;
				default:
					$arrayTabelle[$sigla]['STAPOS']= '104';
					$arrayTabelle[$sigla]['DESPOS']= '1';
					$arrayTabelle[$sigla]['DESLEN']= '14';
					break;
			}

		} else {
			$prefix = substr($sigla, 0, 1);
			$prefix2= substr($sigla, 0, 2);
			$number = substr($sigla, 1, 3);
//			echo "PREFIX: $prefix - NUMBER: $number<br>";

			if ($prefix =="V") {
				if(in_array($number, array("00P", "00C", "00A"))) {
					$codtab = substr($sigla, 1, 3);
					$codcmp = substr($sigla, -1, 1);
					
					$arrayTabelle[$sigla] = array(
						'FILE'=>'FTABV'.$codtab,
						'SIGLA'=>'TV'.$codcmp.'SIG',
						'CODICE'=>'TV'.$codcmp.'COD',
						'DESCRIZIONE'=>'TV'.$codcmp.'DEL',
						'STATO'=>'TV'.$codcmp.'STA');
				}
				else if ($number == 170) {
					$codtab = substr($sigla, 1, 3);
					$arrayTabelle[$sigla] = array(
						'FILE'=>'FTAB'.$sigla,
						'SIGLA'=>'TV'.$codtab.'SG',
						'CODICE'=>'TV'.$codtab.'CD',
						'DESCRIZIONE'=>'TV'.$codtab.'DE',
						'STATO'=>'TV'.$codtab.'ST');
				}
				elseif ($number == 200) {
					$codtab = substr($sigla, 1, 1);
					$arrayTabelle[$sigla] = array(
							'FILE'=>'FTABV020',
							'SIGLA'=>'TV'.$codtab.'SIG',
							'CODICE'=>'TV'.$codtab.'COD',
							'DESCRIZIONE'=>'TV'.$codtab.'DEL',
							'STATO'=>'TV'.$codtab.'STA');
				}
				elseif ($number == 40) {
					$codtab = substr($sigla, 2, 1);
//					echo "COD TAB: $codtab<br>";
					$arrayTabelle[$sigla] = array(
							'FILE'=>'FTABV040',
							'SIGLA'=>'TV'.$codtab.'SIG',
							'CODICE'=>'TV'.$codtab.'COD',
							'DESCRIZIONE'=>'TV'.$codtab.'DEL',
							'STATO'=>'TV'.$codtab.'STA');
				} 
				elseif ($number > 1 && $number < 10) {
					$codtab = substr($sigla, 3, 1);
					$arrayTabelle[$sigla] = array(
							'FILE'=>'FTABV00'.$codtab,
							'SIGLA'=>'TV'.$codtab.'SIG',
							'CODICE'=>'TV'.$codtab.'COD',
							'DESCRIZIONE'=>'TV'.$codtab.'DEL',
							'STATO'=>'TV'.$codtab.'STA');
				} 
				else {
					if ($number < 100) {
						$codtab = substr($sigla, 2, 2);
						$arrayTabelle[$sigla] = array(
					       'FILE'=>'FTAB'.$sigla,
						   'SIGLA'=>'TV'.$codtab.'SG',
						   'CODICE'=>'TV'.$codtab.'CD',
	               		   'DESCRIZIONE'=>'TV'.$codtab.'DE',
						   'STATO'=>'TV'.$codtab.'ST');
					}
					else {
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
			
			if ($prefix2 =="RE") {
				$number2 = substr($sigla, 2, 2);
				
				if ($number2 == 1){
					$arrayTabelle[$sigla] = array(
		 		        'FILE'=>'FTBR0001',
					    'SIGLA'=>'TBRESG',
					    'CODICE'=>'TBRECD',
		           	    'DESCRIZIONE'=>'TBREDE',
					    'STATO'=>'TBRSSA');
				} elseif ($number2 == 2) {
					$arrayTabelle[$sigla] = array(
		 		        'FILE'=>'FTBR0002',
					    'SIGLA'=>'TBRSSG',
					    'CODICE'=>'TBRSCD',
		           	    'DESCRIZIONE'=>'TBRSDE',
					    'STATO'=>'TBRSSA');					
				}
			}
			
			if ($prefix =="S") {
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
			
			// Prefisso T per tabelle SD
			if ($prefix =="T") {
				$number2 = substr($sigla, 2, 2);
					$arrayTabelle[$sigla] = array(
					'FILE'=>'FTAB'.$prefix.$number2,
					'SIGLA'=>'TT'.$number2.'SG',
					'CODICE'=>'TT'.$number2.'CD',
	               	'DESCRIZIONE'=>'TT'.$number2.'DE',
					'STATO'=>'TT'.$number2.'ST');
			}
			
			// Prefisso L per tabelle LGM
			if ($prefix =="L") {
				$number2 = substr($sigla, 2, 2);
				$arrayTabelle[$sigla] = array(
						'FILE'=>'FTAB'.$prefix."0".$number2,
						'SIGLA'=>'TL'.$number2.'SG',
						'CODICE'=>'TL'.$number2.'CD',
						'DESCRIZIONE'=>'TL'.$number2.'DE',
						'STATO'=>'TL'.$number2.'ST');
			}			
			
		}
		
		// Eccezioni alle regole sopra
//		echo "PRIMA:<pre>"; print_r($arrayTabelle[$sigla]); echo "</pre>";
		if ($sigla == '0170') $arrayTabelle[$sigla]["DESCRIZIONE"] = 'T170DS';
		if ($sigla == 'V170') $arrayTabelle[$sigla]["DESCRIZIONE"] = 'TV170DS';
		if ($sigla == '0147') $arrayTabelle[$sigla]["DESCRIZIONE"] = 'T147DS';
		if ($sigla == '0924') $arrayTabelle[$sigla]["FILE"] = 'FTAB0924';
		if ($sigla == '0941') $arrayTabelle[$sigla]["FILE"] = 'FTAB0941';
//		echo "DOPO:<pre>"; print_r($arrayTabelle[$sigla]); echo "</pre>";

		return $arrayTabelle[$sigla];
	}
	/**
	 * Distruttore della classe
	 * 
	**/
	function __destruct()
	{
		// Distruggo l'array degli statemente perchè la prossima connessione DB sarà diversa
		if ($this->saveSession == True and isset($this->tabella))
		{
		foreach ($this->tabella as $key=>$valore) {
			unset($this->tabella[$key]['STATEMENT']);//=null;
		}
		$_SESSION['PERS_TABLE']=$this->tabella;
		//$seg = shm_attach (1);
		//shm_put_var($seg , 1, $this->tabella );
		//shm_detach($seg);		
		}
		//print_r($this->tabella);
		//exit();
	}
	
}
?>