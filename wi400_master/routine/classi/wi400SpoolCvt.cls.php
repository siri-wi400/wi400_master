<?php

/**
 * @name wi400SpoolConvert 
 * @desc Classe per la conversione di un file di spool
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Luca Zovi
 * @version 1.00 05/01/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400SpoolConvert {

    private $idRecord;
    private $connZend;
    private $connDb;
    protected $modulo;
    public  $pdf;
    private $result;
    private $nome;
    protected $count;
    private $pagina;
    protected $salto;
    protected $pdf_out = array();
    protected $nome_file = array();
    private $pdf_key_url = array();
    private $file;
    private $len;
    protected $arrayContents;
    protected $numDocuments;
    protected $totalePagine;
    protected $currentDocument;
    private $fileGenerated;
    private $fileType;
    protected $dati = array();
    protected $dati_conv = array();
    protected $spec_file = array();
    
    private $user_keys = array();
    
    private $stampa = "S";
    private $archiviazione = "S";
    
    //  @todo Files PDF da concatenare
    protected $extraPDF = array();
    
    /**
     * Enter description here...
     *
     * @param unknown_type $idRecord	: Numero identificativo della conversione
     * @param unknown_type $connZend	: Connessione a Zend
     * @param unknown_type $connDb		: Connessione al DataBase
     * @param unknown_type $file		: indirizzo del file di spool da convertire
     * @param unknown_type $libl
     */
	public function __construct($idRecord, $connZend, $connDb, $file, $libl=null){    	
//		getMicroTimeStep("INIZIO CONVERSIONE");
		
		$this->idRecord = $idRecord;
    	$this->connZend = $connZend;
    	$this->connDb = $connDb;
    	$this->file = $file;
		ini_set("memory_limit","600M");
		error_reporting(E_ALL);
//		ini_set("display_errors", true);
		if (isset($libl)){
			$this->dati['FILE']=trim($libl).".".trim($file);		 
		} 
		else {
			$this->dati['FILE']=trim($file);
		}
		$this->totalePagine=0;
		$this->currentDocument=1;
		$this->fileType = "pdf";
	}
	
	/**
	 * @desc Impostazione dei dati dei modelli di conversione
	 *
	 * @param array $modulo	: Array dei dati del modello di conversione
	 */
	public function setDatiBySiriModuli($modulo) {
		global $settings;
//		echo "MODULO:<pre>"; print_r($modulo); echo "</pre><br>";
		
		$this->dati['NOME_MODULO']=$modulo['MODNAM'];
		$this->dati['RIGA_INIZIALE']=$modulo['MODRIN'];
		$this->dati['COLONNA_INIZIALE']=$modulo['MODCIN'];
		$this->dati['PATH_OUTPUT']=$modulo['MODPDP'];
		$this->dati['NOME']=$modulo['MODPDN'];
		$this->dati['PATH_PREFINCATO']=$modulo['MODPPA']; 
		$this->dati['NOME_PREFINCATO']=$modulo['MODPNA'];  
		$this->dati['NOME_FONT']=$modulo['MODFNA'];
		$this->dati['ALTEZZA_FONT']=$modulo['MODFAL'];
		$this->dati['ALTEZZA_CARATTERE']=$modulo['MODFAC'];            
		$this->dati['ALTEZZA_INTERLINEA']=$modulo['MODIAL'];
		$this->dati['ORIENTAMENTO']=$modulo['MODPPL'];
		$this->dati['FORMATO_PAGINA']=$modulo['MODPFO'];      
		$this->dati['UNITA_MISURA']=$modulo['MODUMI'];
		$this->dati['DESCRIZIONE']=$modulo['MODDES'];
		$this->dati['MODULO']=$modulo['MODNAM'];
		$this->dati['DAPAGINA']=$modulo['MODPDA'];
		$this->dati['APAGINA']=$modulo['MODPA'];
		$this->dati['NUMERO_COPIE']=$modulo['MODCPY'];
		$this->dati['COMPRESSIONE_FILE']=$modulo['MODZIP'];
		$this->dati['ABILITAZIONE_ARCHIVIAZIONE']=$modulo['MODABA'];
		$this->dati['ABILITAZIONE_EMAIL']=$modulo['MODABE'];
		$this->dati['ABILITAZIONE_PDF']=$modulo['MODABP'];
		$this->dati['REGOLE_ARCHIVIAZIONE']=$modulo['MODRUA'];
		for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
			if(isset($modulo['MODKY'.$i]))
				$this->dati['KEY'.$i]=$modulo['MODKY'.$i];
			else
				$this->dati['KEY'.$i]="";
		}
/*		
		if(isset($modulo['OUTQ'])){
			$this->dati['OUTQ']=$modulo['OUTQ'];
		}
*/
		$this->checkDati();
	}
	
	/**
	 * @desc Impostazione di un dato dei modelli di conversione 
	 *
	 * @param strin $element		: nome del dato da impostare
	 * @param unknown_type $value	: valore del dato da impostare
	 */
	public function setDati($element, $value) {
		$this->dati[$element]=$value;
	}
	
	public function get_file_rename() {
		if(isset($this->dati['MAINAM']))
			return $this->dati['MAINAM'];
		
		return "";
	}
	
	public function getFile() {
		$test =$this->dati['FILE'];
		if (file_exists($test)) {
			return true;
		}
		else {
			return false;	
		}
	}
	
	/**
	 * @desc Caricamento del contenuto del file di spool da convertire
	 *
	 */
	public function loadContents() {
	    $test = $this->dati['FILE'];
		$file = fopen($test, "r");
		$contents = fread($file, filesize($test));
		$contents = str_replace("\r\n", "=0D=0A=", $contents);
//		echo "CONTENTS: $contents<br>";
		$array = explode("=0D=0A=" , $contents);
// 		LZ: Tolgo l'ultima riga solo se vuota. Intervento per conversione stampe Al.Fi. ...
		if (substr($array[count($array)-1],4) == "") {
			unset($array[count($array)-1]);
		}
//		echo "LOAD:<pre>"; print_r($array); echo "</pre>";
		fclose($file);
		// Chiamata al metodo per reperire eventuali rotture sul PDF
		$this->callUserFunc($array);
	}
	
	/**
	 * @desc Recupero il numero massimo di colonne dello spool file
	 *
	 * @return integer
	 */
	public function spoolMaxLen(){
    	if (!isset($this->arrayContents)) {
        	$this->loadcontents();
    	}
    	$max=0;

    	foreach($this->arrayContents as $doc => $contents) {
    		foreach($contents as $key => $record) {
    			$len = strlen(trim($record));
				if($len>$max) 
					$max = $len;
    		}
    	}
		return $max;
	}

	/**
	 * @desc Creazione Header del PDF
	 *
	 */
	public function createPdf(){
	    global $routine_path;	
	 
	    require_once $routine_path."/FPDF/tcpdf/tcpdf.php";
	    require_once $routine_path."/FPDF/fpdi.php";

		$this->pdf=new FPDI($this->dati['ORIENTAMENTO'],trim($this->dati['UNITA_MISURA']),trim($this->dati['FORMATO_PAGINA']));
		$this->pdf->SetFont(trim($this->dati['NOME_FONT']),'',$this->modulo['ALTEZZA_FONT']);
		$this->pdf->SetRightMargin(0);
		$this->pdf->SetAutoPageBreak(False);
		$this->pdf->SetCreator($this->dati['CREATORE']);
		$this->pdf->SetAuthor($this->dati['AUTORE']);
		$this->pdf->SetSubject($this->dati['DESCRIZIONE']);
		$this->pdf->setPrintHeader(False);
		$this->pdf->setPrintFooter(False);
		// Performance Test
		$this->pdf->setFontSubsetting(False);		
	}
	
	/**
	 * @desc Controlla i parametri del PDF ed imposta i dati mancanti o errati con *DEFAULT
	 *
	 */
	function checkDati() {
		global $settings;
//	    static $len;
	    
//	    echo "DATI INIZIO:<pre>"; print_r($this->dati); echo "</pre><br>";
   
//	    if (!isset($len)) {
	        $len = $this->spoolMaxLen();
	        $this->len = $len;
//	    }
	    // Setto l'orientamento dello spool
	    if (!isset($this->dati['ORIENTAMENTO']) or $this->dati['ORIENTAMENTO']=="A") {
	    	if ($len<125) $this->dati['ORIENTAMENTO']="P";
	    	else $this->dati['ORIENTAMENTO']="L";
	    }
	    // Setto il carattere
	    if (!isset($this->dati['NOME_FONT']) or $this->dati['NOME_FONT']=="*DEFAULT") {
	    	$this->dati['NOME_FONT']="courier";
	    }
	    // Altezza font
	    if (!isset($this->dati['ALTEZZA_FONT']) or $this->dati['ALTEZZA_FONT']==0) {
	    	// La calcolo in base all'orientamento della pagina
	    	if ($len<=84) {
	    	    	$this->dati['ALTEZZA_FONT']="12.0";
    		}
	    	else {
		    	if ($len<=136) {
		    		$this->dati['ALTEZZA_FONT']="10.0";
		    		$this->dati['ALTEZZA_CARATTERE']="5.0";
		    		$this->dati['ALTEZZA_INTERLINEA']="3.1";
		    		$this->dati['COLONNA_INIZIALE']="6";
		    	}
		    	else {
		    		if ($len>136) {
			    		$this->dati['ALTEZZA_FONT']="6.0";
			    		$this->dati['ALTEZZA_CARATTERE']="6.0";
			    		$this->dati['ALTEZZA_INTERLINEA']="3.0";	    		
		    		}
		    	}
			}
	    }	    
	    // Colonna Iniziale
	    if (!isset($this->dati['COLONNA_INIZIALE']) or $this->dati['COLONNA_INIZIALE']==0) {
	    	$this->dati['COLONNA_INIZIALE']="1";
	    }
	    // Autore
	    if (!isset($this->dati['AUTORE']) or $this->dati['AUTORE']=="") {
	    	$this->dati['AUTORE']="WI400 By SIRI Informatica!";
	    }
	    // Creatore
	    if (!isset($this->dati['CRETORE']) or $this->dati['CREATORE']=="") {
	    	$this->dati['CREATORE']="WI400 By SIRI Informatica!";
	    }		    	    
	    // Riga Iniziale
	    if (!isset($this->dati['RIGA_INIZIALE']) or $this->dati['RIGA_INIZIALE']==0) {
	    	$this->dati['RIGA_INIZIALE']="1";
	    }
	    // Altezza Carattere
	    if (!isset($this->dati['ALTEZZA_CARATTERE']) or $this->dati['ALTEZZA_CARATTERE']==0) {
	    	$this->dati['ALTEZZA_CARATTERE']="4.0";
	    }
	    // Altezza Interlinea
	    if (!isset($this->dati['ALTEZZA_INTERLINEA']) or $this->dati['ALTEZZA_INTERLINEA']==0) {
	    	$this->dati['ALTEZZA_INTERLINEA']="4.0";
	    }
	    // Pagina DA
	    if (!isset($this->dati['DAPAGINA']) or $this->dati['DAPAGINA']=="" or $this->dati['DAPAGINA']==0) {
	    	$this->dati['DAPAGINA']=0;
	    }
	    // Pagina A
	    if (!isset($this->dati['APAGINA']) or $this->dati['APAGINA']=="" or $this->dati['APAGINA']==0) {
	    	$this->dati['APAGINA']=0;
	    }	    
	    // Unità di misura
	    if (!isset($this->dati['UNITA_MISURA']) or $this->dati['UNITA_MISURA']=="") {
	    	$this->dati['UNITA_MISURA']="mm";
	    }
	    // Unità di misura
	    if (!isset($this->dati['FORMATO_PAGINA']) or $this->dati['FORMATO_PAGINA']=="") {
	    	$this->dati['FORMATO_PAGINA']="A4";
	    }
	    // Unità di misura
	    if (!isset($this->dati['PATH_OUTPUT']) or $this->dati['PATH_OUTPUT']=="") {
	    	$this->dati['PATH_OUTPUT']="/Siri/Temp/";
	    }		    	    		    	    	    	    
        // NOME UTENTE
	    if (!isset($this->dati['UTENTE']) or $this->dati['UTENTE']=="") {
			$this->dati['UTENTE']="GENERICO";
		}
        // ARGOMENTO
	    if (!isset($this->dati['ARGOMENTO']) or $this->dati['ARGOMENTO']=="") {
			$this->dati['ARGOMENTO']="GENERICO";
		}
		// Composizione nome dello spool di output
		if (!isset($this->dati['NOME']) or $this->dati['NOME']=="") {
			  $this->dati['NOME'] = trim($this->dati['ARGOMENTO'])."_".date("d")."-".date("m")."-".date("Y")."-".date("H")."_".date("i")."_".date("s")."_".substr(microtime(), 2, 8);
		} 
		
		if($this->numDocuments > 1) {
			for($i=1; $i<=$this->numDocuments; $i++) {
				if(empty($this->spec_file))
					$this->nome_file[$i] = date("YmdHis").substr(microtime(), 2, 8)."_".$this->dati['NOME'].'_'.$i.'.pdf';
				else {
					$spec = str_replace(' ','_',$this->spec_file[$i]);
					$this->nome_file[$i] = date("YmdHis").substr(microtime(), 2, 8)."_".$this->dati['NOME'].'_'.$spec.'.pdf';
				}
				$filepath = wi400File::getUserFile('tmp', $this->nome_file[$i]);		
				$this->pdf_out[$i] = $filepath;
			}
//			echo "FILES:<pre>"; print_r($this->pdf_out); echo "</pre><br>";
		}
		else {
			$this->nome_file[$this->currentDocument] = date("YmdHis").substr(microtime(), 2, 8)."_".$this->dati['NOME'].'.pdf';	    
			$filepath = wi400File::getUserFile('tmp', $this->nome_file[$this->currentDocument]);		
			$this->pdf_out[$this->currentDocument] = $filepath;
		}
		
		// Numero di copie
		if(!isset($this->dati['NUMERO_COPIE']) || $this->dati['NUMERO_COPIE']=="" || $this->dati['NUMERO_COPIE']==0) {
			$this->dati['NUMERO_COPIE'] = 1;
		}

		// Numero di copie
		if(!isset($this->dati['COMPRESSIONE_FILE']) || $this->dati['COMPRESSIONE_FILE']=="") {
			$this->dati['COMPRESSIONE_FILE'] = 'N';
		}
		
		// Abilitazione archiviazione
		if(!isset($this->dati['ABILITAZIONE_ARCHIVIAZIONE']) || $this->dati['ABILITAZIONE_ARCHIVIAZIONE']=="") {
			$this->dati['ABILITAZIONE_ARCHIVIAZIONE'] = 'N';
		}
		
		// Abilitazione email
		if(!isset($this->dati['ABILITAZIONE_EMAIL']) || $this->dati['ABILITAZIONE_EMAIL']=="") {
			if($settings['mail_export']===false)
				$this->dati['ABILITAZIONE_EMAIL'] = 'N';
			else
				$this->dati['ABILITAZIONE_EMAIL'] = 'S';
		}
		
		// Abilitazione archiviazione
		if(!isset($this->dati['ABILITAZIONE_PDF']) || $this->dati['ABILITAZIONE_PDF']=="") {
			$this->dati['ABILITAZIONE_PDF'] = 'N';
		}
		
		if(!isset($this->dati['OUTQ']) || $this->dati['OUTQ']=="") {
			$this->dati['OUTQ'] = '';
		}
		
		if(!isset($this->dati['MAINAM']) || $this->dati['MAINAM']=="") {
			$this->dati['MAINAM'] = '';
		}

//		echo "DATI FINE:<pre>"; print_r($this->dati); echo "</pre><br>";	
//		die();
	}
	
	public function setStampa($stampa) {
		$this->stampa = $stampa;
	}
	
	public function setArchiviazione($archive) {
		$this->archiviazione = $archive;
	}
	
	public function setParm($dati) {
		$this->dati = $dati;
		$this->checkDati();
	}
	
	/**
	 *  @desc Esegue la conversione del PDF per tutti i sottodocumenti dello spool
	 *
	 */
	function convert() {
//		echo "FILES:<pre>"; print_r($this->pdf_out); echo "</pre><br>";
		if($this->dati['ABILITAZIONE_PDF']!="N") {
		    // Rottura Automatica delle chiavi!!
		    if ($this->numDocuments==1) {
		    	if (count($this->getKeyArray())>0) {
		    		$this->break_key($this->arrayContents[1]);
		    	}
		    }
		    
//		    echo "NUM DOC: ".$this->numDocuments."<br>";
//		    echo "ARRAY CONTENTS:<pre>"; print_r($this->arrayContents); echo "</pre>";
//		    echo "PDF OUT:<pre>"; print_r($this->pdf_out); echo "</pre>";
		    
		    for($i=1; $i<=$this->numDocuments; $i++) {
//		    	echo "<font color='red'>DOC: $i</font><br>";
				$this->currentDocument = $i;
				$this->stampa($this->arrayContents[$i]);
			}
//			echo "DATI CONV:<pre>"; print_r($this->dati_conv); echo "</pre><br>";
			// Operazioni Finali sul PDF
			$fin = $this->finalize();
//die();			
			if($fin===false)
				return false;
		}
		
		return true;
	}

	/**
	 * @desc Esegue il rendering di tutti i documenti PDF legati allo spool
	 *
	 * @param unknown_type $array
	 * @return unknown
	 */
	public function stampa($array) {
//		echo "ARRAY CONTENTS:<pre>"; print_r($array); echo "</pre>";
		$this->createPdf();
		$this->salto=False;
		$this->count=1;
		$this->pagina = 0;
		$chiaviArchiviazione = $this->getKeyArray();
		$old_skip = 9999;
		$riga = number_format($this->dati['RIGA_INIZIALE']);
		$riga_originale = 0;
		$colonna = number_format($this->dati['COLONNA_INIZIALE']);
		$oldBold="*";
		//ciclo tutti record
		foreach ($array as $key=>$record) {
			// Valorizzo i dati dello spool
			$isBold ="";
			$skip  = substr($record,0,3);
			$space = substr($record,3,1);
			$dati  = substr($record,4);
		    // Controllo il salto pagina da SKIP
//		    echo "<font color='orange'>SKIP: $skip - OLD SKIP: $old_skip</font><br>";
//		    echo "SPACE: "; var_dump($space); echo "<br>";
//			if ((trim($skip) <> "") and ($skip <= $old_skip))  {
			if ((trim($skip) <> "") && (($skip <= $old_skip) || ($skip <= $riga_originale)))  {
				$this->salto = True;
		        $old_skip = $skip;
				$this->pagina = $this->pagina+1;	
				$riga = number_format($this->dati['RIGA_INIZIALE']);
				$riga_originale = 0;
//				echo "<br><b>PAG: ".$this->pagina."</b><br>";
			}
			$paginaAttuale = $this->pagina;
//			echo "CONFRONTO: $paginaAttuale<".$this->dati['DAPAGINA']."<br>";
//			echo "RIGA $key: $dati<br>";
			// Verifico se devo stampare la pagina DA
			if ($this->dati['DAPAGINA']>0 && $paginaAttuale<$this->dati['DAPAGINA']) {
//				echo "<font color='red'>PAG SALTATA: ".$this->pagina."</font><br>";
				$this->salto = False;
				$old_skip = 9999;
				continue;		
			} 
			// Verifico se ho superato la pagina A
			if ($this->dati['APAGINA']>0 && $paginaAttuale>$this->dati['APAGINA']) {
//				echo "<font color='blue'>ULTIMA PAGINA: ".$this->pagina."</font><br>";
				$this->salto = False;
				break;		
			}
			$this->salto_pagina();		
		    // Calcolo dove mi devo posizionare con la riga di stampa
		    if (trim($skip) <> "")  {
		       $old_skip = $skip;
		       $riga = ($this->dati['ALTEZZA_INTERLINEA'] * $skip) + $this->dati['RIGA_INIZIALE'];
		       //$riga_originale = $riga_originale + $skip;
		       $riga_originale = $skip;
		    }
		    else {
		        $riga = $riga + ($this->dati['ALTEZZA_INTERLINEA'] * $space);
		        $riga_originale = $riga_originale + $space;	
		    }
		    if ($space == "0") 
		    	$isBold = "B";
		    if ($isBold != $oldBold) {	
				$this->pdf->SetFont(trim($this->dati['NOME_FONT']),$isBold,$this->dati['ALTEZZA_FONT']);
				$oldBold = $isBold;
		    }
		    // Controllo se ci sono delle chiavi di rottura
		    if($this->dati['ABILITAZIONE_ARCHIVIAZIONE']=="S") {
//		    	echo "CHIAVI:<pre>"; print_r($chiaviArchiviazione); echo "</pre>";
//		    	echo "DATI CONV:<pre>"; print_r($this->dati_conv); echo "</pre><br>";
			    foreach ($chiaviArchiviazione as $chiave=>$valore) {
//			    	echo "VAL RIGA: ".$valore['RIGA']." - RIG ORIG: $riga_originale<br>";
			    	  if ($valore['RIGA']==$riga_originale) {
			    	  	   $aggancio = 'LOGKY'.($chiave+1);
			    	  	   if (!isset($this->dati_conv[$this->currentDocument]) && $this->currentDocument>1) {
			    	  	   	     $this->dati_conv[$this->currentDocument]=$this->dati_conv[1];
//			    	  	   	    echo "CURRENT DOCUMENT: ".$this->currentDocument."<br>";
//		    					echo "DATI:<pre>"; print_r($this->dati_conv[$this->currentDocument]); echo "</pre>";
			    	  	   }
							$val = trim(substr($dati, $valore['COLONNA']-1, $valore['LEN']));
							$this->dati_conv[$this->currentDocument][$aggancio]=$val;
//							echo "<font color='purple'>CHIAVE $aggancio:</font> $val<br>";
/*			    	  	   
			    	  	   if(!array_key_exists($aggancio, $this->dati_conv[$this->currentDocument]) || 
			    	  	   		$this->dati_conv[$this->currentDocument][$aggancio]==""
			    	  	   	) {
			    	  	   		$this->dati_conv[$this->currentDocument][$aggancio]=substr($dati, $valore['COLONNA']-1, $valore['LEN']);
//			    	  	   		echo "<font color='purple'>CHIAVE $aggancio:</font> ".substr($dati, $valore['COLONNA']-1, $valore['LEN'])."<br>";
			    	  	   	}
*/
			    	  }
			    }
		    }
		   
//		    echo "<font color='green'>WRITE: $dati</font><br>";
//			$this->write($colonna,$riga,utf8_encode($dati));
			$this->write($colonna,$riga,prepare_string($dati, False));
//			$this->write($colonna,$riga,prepare_string($dati, False),$space);
			$isBold="";
		}
//		 echo "DATI CONV:<pre>"; print_r($this->dati_conv[$this->currentDocument]); echo "</pre>";
		// Generazione PDF
		$this->outputPdf();
//		getMicroTimeStep("FINE CONVERSIONE");
		return true;
	}
	
	/**
	 * @desc Scrittura di una riga del file di spool nel file PDF che si sta creando
	 *
	 * @param integer $colonna		: posizione x da cui cominciare a scrivere la riga
	 * @param integer $riga			: posizione y da cui cominciare a scrivere la riga
	 * @param unknown_type $dati
	 */
	function write($colonna,$riga,$dati) {
//	function write($colonna,$riga,$dati,$space="") {
		global $settings; 
		
		//$this->pdf->SetXY($colonna,$riga);
		//$this->pdf->Text($colonna+0.9, $riga+(($this->dati['ALTEZZA_INTERLINEA']+0.6)) , $dati);
		if ($settings['pdf_write_metod']=='text' or $this->dati['NOME_MODULO']=='*DEFAULT') {
			$this->pdf->Text($colonna, $riga , $dati);
		}
		else {
			$this->pdf->SetXY($colonna,$riga);
	   		$this->pdf->Write($this->dati['ALTEZZA_CARATTERE'], $dati);
		}
		//$this->pdf->Cell($this->len, 0 , $dati);
		//$this->pdf->SetXY($colonna,$riga);
		//$this->pdf->Write($this->dati['ALTEZZA_CARATTERE'], $dati);
	}
	
	/**
	 * @desc Generazione del file PDF
	 * 
	 */
	function outputPdf() {
//		echo "DOC: ".$this->currentDocument."<br>";
//		echo "FILE: ".$this->pdf_out[$this->currentDocument]."<br>";
		$this->pdf->Output($this->pdf_out[$this->currentDocument], 'F');
		$this->fileGenerated[]=$this->pdf_out[$this->currentDocument];		
	}
	
//	function update_logconv($file, $key, $j=1) {
	function update_logconv($file, $key) {
		global $connzend, $db;
		
//		echo "DATI CONV:<pre>"; print_r($this->dati_conv); echo "</pre><br>";
		
		$dati_conv = $this->dati_conv[$key];
		
//		echo "DATI CONV DOC:<pre>"; print_r($dati_conv); echo "</pre><br>";
//		echo "DATI:<pre>"; print_r($this->dati); echo "</pre><br>";
//		echo "STAMPA: {$this->stampa} - ARCHIVIAZIONE: $this->archiviazione<br>";
//die();		
		// Stampa diretta PDF
		if($this->dati['OUTQ']!="" && $this->stampa=="S") {
//			echo "STAMPA<br>"; die();
			$outq = substr($this->dati['OUTQ'],0, 10);
			$libl = substr($this->dati['OUTQ'],10, 10);
			
//			echo "FILE: $file<br>";
//			echo "OUTQ: $outq<br>";
//			echo "LIBL: $libl<br>";
//			echo "ID: {$this->idRecord}<br>";
//			echo "FILE: {$this->file}<br>";
//			echo "STAMPA<br>"; die();

			$sql_outq = "select * FROM FP2OPARM WHERE PROUTQ=?";
			$stmt_outq = $db->singlePrepare($sql_outq,0,true);
			
			$duplex = "N";
			$db->execute($stmt_outq, array($outq));
			if($row_outq = $db->fetch_array($stmt_outq)) {
				$duplex = $row_outq['PRDUPX'];
			}
			if ($row_outq) {
				$zp2oprt = new wi400Routine('ZP2OPRT', $connzend);
				$zp2oprt->load_description('ZP2OPRT');
				$zp2oprt->prepare();
				$zp2oprt->set("PDF", $file);
				$zp2oprt->set("OUTQ", $outq);
				$zp2oprt->set("LIBL", $libl);
				$zp2oprt->set("DUPLEX", $duplex);
				$zp2oprt->set("FLAG", "0");
				$zp2oprt->call();
				
				// Aggiorno lo stato di elaborazione
				$timeStamp = getDb2Timestamp();
				$sql = "UPDATE FEMAILAL SET MAISTO='S', MAISTT='".$timeStamp."' where ID='".$this->idRecord."' and CONV='S' and TPCONV='PDF' AND MAIATC='".$this->file."'";
				$db->query($sql);
			}

		}
		else {
//			echo "NON STAMPA<br>"; die();
		}

		if($this->dati['ABILITAZIONE_ARCHIVIAZIONE']=="S" && $this->archiviazione=="S") {
//			echo "ARCHIVIAZIONE<br>"; die();
			$regola = "";
			//$handle = fopen("/www/spool.txt", "a+");
			if ($this->dati['REGOLE_ARCHIVIAZIONE'] !="") {
				$regola = $this->dati['REGOLE_ARCHIVIAZIONE']; 
			}
			if ($regola=="1" && $this->get_file_rename()=="") {
				$regola ="";
			}
			if ($regola=="") {
				$filename = $this->archive_filename($file, $key, $dati_conv);
				$filepath = $this->archive_filepath($filename, $dati_conv);
				$filepath_dir = dirname($filepath);
				if(!file_exists($filepath_dir)) {
					wi400_mkdir($filepath_dir,777,true);
				}
				$dati_conv['LOGPTH'] = dirname($filepath);
				$dati_conv['LOGNOM'] = $filename;
				copy($file, $filepath);
				chmod($filepath, 777);
			}
			if ($regola=="1") {
				$path_parts = pathinfo($this->get_file_rename());
				$dati_conv['LOGPTH'] = $path_parts['dirname'];
				$dati_conv['LOGNOM'] = $path_parts['basename'];
			}
			//fwrite($handle, "LA REGOLA E':".$regola);
			//fclose($handle);
			// Aggiornamento del file di log delle conversioni
			if(isset($this->dati_conv) && !empty($this->dati_conv)) {
				$ret = $this->update_logconv_exe($dati_conv);	
				return $ret;
			}
		}
//echo "FINE UPDATE LOG CONV<br>"; die();
//		return false;
		return true;
	}
	
	function update_logconv_exe($dati_conv) {
		global $db, $dbTime, $dbUser, $settings; 

//		echo "UPDATE LOG CONV<br>";
//		echo "DATI CONV:<pre>"; print_r($dati_conv); echo "</pre><br>";

		$keys = array(
			"LOGUSR" => $dati_conv['LOGUSR'],			// @todo sicuri che non vada tolto LOGUSR dalle chiavi?
			"LOGJOB" => $dati_conv['LOGJOB'],			// @todo sicuri che non vada tolto LOGJOB dalle chiavi?
			"LOGNBR" => $dati_conv['LOGNBR'],			// @todo sicuri che non vada tolto LOGNBR dalle chiavi?
			"LOGDTA" => $dati_conv['LOGDTA'],			// @todo sicuri che non vada tolto LOGDTA dalle chiavi?
			"LOGNOM" => $dati_conv['LOGNOM'],			// @todo sicuri che non vada tolto LOGNOM dalle chiavi?
			"LOGMOD" => $dati_conv["LOGMOD"]
		);
		
		// @todo sicuri che vadano utilizzate le chiavi pdf come chiavi di ricerca nel file FLOGCONV?
		$sql_keys = array();
		for($i=1; $i<=$settings['modelli_pdf_keys']; $i++) {
			$keys["LOGKY".$i] = "";
			if(isset($dati_conv['LOGKY'.$i]))
				$keys["LOGKY".$i] = $dati_conv['LOGKY'.$i];
			
			$sql_keys[] = "LOGKY".$i."=?";
		}		
//		echo "KEYS:<pre>"; print_r($keys); echo "</pre><br>";
		
		$user_keys = $this->user_keys;
//		echo "USER KEYS:<pre>"; print_r($user_keys); echo "</pre><br>";

		$sql = "select * from FLOGCONV where LOGUSR=? and LOGJOB=? and LOGNBR=? and LOGDTA=? and LOGNOM=? and LOGMOD=? 
			and ".implode(" and ", $sql_keys);
		// @todo sicuri che non vadano tolti LOGNOM e LOGKY* dalle chiavi?
//		$sql = "select * from FLOGCONV where LOGUSR=? and LOGJOB=? and LOGNBR=? and LOGDTA=? and LOGMOD=?";
//		echo "SQL: $sql<br>";
//		if(!isset($stmt))
			$stmt = $db->singlePrepare($sql);
		$result_sel = $db->execute($stmt, $keys);
	    
	    if($array_conv = $db->fetch_array($stmt)) {
//	    	echo "UPDATE<br>";die();
	    	// Update
	    	unset($dati_conv['LOGINS']);
	    	
	    	if($array_conv['LOGNEL']=="")
	    		$dati_conv['LOGNEL'] = 1;
	    	else
	    		$dati_conv['LOGNEL'] = $array_conv['LOGNEL']+1;
	    	$stato_elaborazione =getASTimestamp();
	    	if (isset($dati_conv['LOGELA']) && $dati_conv['LOGELA']!="") {
	    		$stato_elaborazione = $dati_conv['LOGELA'];
	    	}
	    	$fieldValues = array(
	    		"LOGELA" => $stato_elaborazione,
//	    		"LOGPTH" => $dati_conv['LOGPTH'],
//	    		"LOGNOM" => $dati_conv['LOGNOM'],		// @todo sicuri che il LOGNOM non vada invece aggiornato invece che utilizzato come chiave?
	    		"LOGNEL" => $dati_conv['LOGNEL'],
	    		"LOGUSM" => $dati_conv['LOGUSM']
	    	);
	    	
	    	for($i=1; $i<=$settings['modelli_pdf_user_keys']; $i++) {
	    		$fieldValues["LOGKU".$i] = $dati_conv["LOGKU".$i];
	    	}
	    	
    		if($this->dati['OUTQ']!="") {
	    		$fieldValues["LOGSTP"] = "S";
	    		$fieldValues["LOGOUT"] = $dati_conv['LOGOUT'];
	    		$fieldValues["LOGSTT"] = getDb2Timestamp();
	    	}
	    	
	    	$fieldValues["LOGID"] = $dati_conv['LOGID']; 
	    	
//	    	if(!isset($stmtupdate))
	    		$stmtupdate  = $db->prepare("UPDATE", "FLOGCONV", $keys, array_keys($fieldValues));
		
			$result = $db->execute($stmtupdate, $fieldValues);
	    }
	    else {
//	    	echo "INSERT<br>";die();
	    	$fieldsValue = getDs("FLOGCONV");
	    	
	    	foreach($fieldsValue as $key => $val) {
	    		if(isset($dati_conv[$key]))
	    			$fieldsValue[$key] = $dati_conv[$key];
	    	}
	    	
	    	if (!isset($dati_conv['LOGELA'])) {
	    		$fieldsValue['LOGELA'] = getASTimestamp();
	    	}
/*	    	
	    	for($i=1; $i<=$settings['modelli_pdf_user_keys']; $i++) {
	    		$fieldsValue["LOGKU".$i] = $user_keys["LOGKU".$i];
	    	}
*/	    	
	    	if($this->dati['OUTQ']!="") {
	    		$fieldsValue["LOGSTP"] = "S";
	    		$fieldsValue["LOGOUT"] = $dati_conv['LOGOUT'];
	    		$fieldsValue["LOGSTT"] = getDb2Timestamp();
	    	}
//	    	echo "DATI:<pre>"; print_r($fieldsValue); echo "</pre>";
//die();
			// Insert
//			if(!isset($stmtinsert))
				$stmtinsert = $db->prepare("INSERT", "FLOGCONV", null, array_keys($fieldsValue));
	    	
			$result = $db->execute($stmtinsert, $fieldsValue);
	    }
		
		if(isset($result))
			return true;
		else
			return false;
	}
	
	function get_filepath($num_doc) {
		return $this->pdf_out[$num_doc];
	}
	
	function set_filename($num_doc) {
		$key_url = $this->get_file_key_url($num_doc);
		$key_url = cleanForShortURL($key_url);
		
		$filename = date("Ymd-Hms")."_".$key_url.'.pdf';
		
		return $filename;
	}
	
	function get_filename($num_doc) {
		return $this->nome_file[$num_doc];
	}
	
	function get_file_key_url($num_doc) {
		return $this->pdf_key_url[$num_doc];
	}
	
	function archive_filename($file, $num_doc, $dati_conv) {
		return basename($file);
	}
	
	function archive_filepath($filename, $dati_conv) {
		$filepath = wi400File::getUserFile('archivio', $filename);
	
		return $filepath;
	}
	
	function setDatiConv($dati_conv, $documento=1) {
		$this->dati_conv[$documento] = $dati_conv;
//		echo "SET DATI CONV:<pre>"; print_r($this->dati_conv[$documento]); echo "</pre>";
		
		$this->set_user_keys($documento);
	}
	
	function getDatiConv($documento=1) {
		return $this->dati_conv[$documento];
	}
	
	function getPdfName() {
	       return $this->nome_file[$this->currentDocument];
	}
	
	function getFullPdfName() {
	       return $this->pdf_out[$this->currentDocument];
	}
	
	/**
	 * @desc Ritorna il numero di pagine che compongono il PDF
	 *
	 * @return integer
	 */
	function getTotPag() {
		   return $this->pagina;
	}
	
	/**
	 * @desc Chiamata da definire dall'utente per personalizzare l'intero spool
	 *
	 * @return unknown
	 */
	function callUserFunc($array) {
		$this->numDocuments=1;
		$this->arrayContents[1] = $array;
	}
	
	function setFileType($fileType) {
		$this->fileType = $fileType;
	}
	
	function getFileType() {
		return $this->fileType;
	}
	
	function setEmail($email) {
		$this->dati['ABILITAZIONE_EMAIL'] = $email;
	}
	
	function getEmail() {
		return $this->dati['ABILITAZIONE_EMAIL'];
	}
	
	protected function get_dati_modulo() {
		return $this->modulo;
	}
	
	/**
	 * @desc Operazioni finali.
	 * @desc Eventuale generazione dello zip se ci sono più file
	 *
	 * @return unknown
	 */
	function finalize() {
		global $routine_path;
		
		require_once $routine_path.'/classi/wi400invioEmail.cls.php';
		
		$files = array();
		
		$return = true;
		
//		echo "DOCUMENTS:<pre>"; print_r($this->pdf_out); echo "</pre><br>";
		
		// Copia dei files
		if(isset($this->dati['NUMERO_COPIE']) && $this->dati['NUMERO_COPIE']>1) {
//			echo "COPIE<br>";
			foreach($this->pdf_out as $key => $doc) {
				$name = explode(".", $doc);
				for($c=1; $c<=$this->dati['NUMERO_COPIE']; $c++) {
//					echo "FILE $key: $doc - NAME: $name[0]<br>";
					$path = $name[0].'_'.$c.".pdf";
					$files[] = $path;
					copy($doc, $path);
				}
				
				// Archiviazione
				$ret = $this->update_logconv($doc, $key);
				if($ret===false)
					$return = false;
			}
		}
		else {
//			$j=1;
//			echo "<font color='green'>DATI CONV:<pre>"; print_r($this->dati_conv); echo "</pre></font><br>";
			foreach($this->pdf_out as $key => $doc) {
//				echo "DATI CONV:<pre>"; print_r($this->dati_conv); echo "</pre><br>";				
//				echo "<font color='blue'>KEY: $key - PDF OUT: $doc - NUM DOC: $j</font><br>";
//				echo "<font color='blue'>KEY: $key - PDF OUT: $doc</font><br>";
				$files[] = $doc;
				// Archiviazione
//				echo "J:$j<br>";
//				$ret = $this->update_logconv($doc, $key, $j);
				$ret = $this->update_logconv($doc, $key);
//				$j++;
				if($ret===false)
					$return = false;
			}
		}
		
//		echo "FILES:<pre>"; print_r($files); echo "</pre><br>";
		
		// Zip dei files
		if(
			(isset($this->dati['COMPRESSIONE_FILE']) && $this->dati['COMPRESSIONE_FILE']=="S") ||
			(isset($this->dati['NUMERO_COPIE']) && $this->dati['NUMERO_COPIE']>1) ||
			($this->numDocuments>1)
		) {
			$name = explode(".", $this->pdf_out[$this->currentDocument]);
			if($this->numDocuments>1) {
				if(!empty($this->spec_file))
					$zip = substr($name[0],0,-(strlen($this->spec_file[$this->currentDocument])+1)).'.zip';
				else
					$zip = substr($name[0],0,-2).'.zip';
				}
			else {
				$zip = $name[0].'.zip';
			}

			wi400invioEmail::compress($files,$zip);
//			echo "ZIP: $zip<br>";
			$this->nome_file[$this->currentDocument] = basename($zip);
			$this->fileType = "zip";
		}
				
		return $return;
	}
	
	/**
	 * @desc Recupero le chiavi
	 */
	function getKeyArray() {
		global $settings; 
		
		$array = array();
		$i=0;
		for($c=1; $c<=$settings['modelli_pdf_keys']; $c++) {
			if (isset($this->dati['KEY'.$c]) && $this->dati['KEY'.$c]!='') {
				$chiavi = explode(";", ($this->dati['KEY'.$c]));
				$array[$i]['RIGA']=		$chiavi[0];
				$array[$i]['COLONNA']=	$chiavi[1];
				$array[$i]['LEN']=		$chiavi[2];
				$i++;
			}
		}
		return $array;
	}	
	/**
	 * @desc Salto pagina
	 *
	 */
	protected function salto_pagina() {
		$this->count=$this->count + 1;
		
		if ($this->salto) {
			$this->pdf->AddPage();
			if ($this->dati['PATH_PREFINCATO']!="") {
				$this->pdf->SetFont('Courier', 10);
				$this->pdf->setSourceFile(trim($this->dati['PATH_PREFINCATO'])."/".trim($this->dati['NOME_PREFINCATO']));
				$tplIdx = $this->pdf->importPage(1);
				$this->pdf->useTemplate($tplIdx,0,0);	
//				$this->pdf->useTemplate($tplIdx, null, null, 0, 0, true);
			}
			
			$this->pdf->SetFont(trim($this->modulo['NOME_FONT']),'',$this->modulo['ALTEZZA_FONT']);
			$this->salto=False;	
			$this->count = 1;
			//$this->pagina = $this->pagina + 1;
			$this->totalePagine=$this->totalePagine+1;
	    }
	}
	/**
	 * @desc Spacchetta il documento se ci sono chiavi di rottura previste
	 *
	 * @param unknown_type $array
	 * @return array Documents
	 */
	public function break_key($array) {
		$pagina = 0;
		$old_skip = 9999;
		$i = 0;
		$doc = 0;
		$riga_originale = 0;
		$start = "";
		$contents = array();
		
//		$new_key_break = "";
//		$num_keys = 0;
		
		$old_key_break ="";
		$key_break="";
		$key_array = array();
		
		$chiaviArchiviazione = $this->getKeyArray();
//		echo "BREAK_KEY:<pre>"; print_r($chiaviArchiviazione); echo "</pre>";
			
		$this->arrayContents = array();
		$isFirst = true;
		
//		echo "ARRAY:<pre>"; print_r($array); echo "</pre>";
		
		//ciclo tutti record
		foreach ($array as $key => $record) {
//			echo "<font color='blue'>RIGA: $riga_originale -</font> RECORD: $record<br>";
			
			// Valorizzo i dati dello spool
			$skip  = substr($record,0,3);
			$space = substr($record,3,1);
			$dati  = substr($record,4);
			
		 	// Calcolo dove mi devo posizionare con la riga di stampa
		    if (trim($skip) <> "")  {
		       $old_skip = $skip;
//		       echo "RIGA ORIGINALE: $riga_originale - SKIP: $skip<br>";
		       if($riga_originale>trim($skip)) {
//		       		echo "<font color='pink'>SET RIGA ORIGINALE A 0</font><br>";
		       		$riga_originale = 0;
		       		$start = $i;
		       		if(!empty($contents))
						$this->arrayContents[$doc] = array_merge($this->arrayContents[$doc],$contents);
		       		$contents = array();
		       }
		       //$riga_originale = $riga_originale + $skip;
		       $riga_originale = $skip;
		    }
		    else {
		        $riga_originale = $riga_originale + $space;	
		    }
		    
			if($chiaviArchiviazione[0]['RIGA']>=$riga_originale) {
//				echo "<font color='purple'>RESET KEY BREAK</font><br>";
				$new_key_break = "";
				$num_keys = 0;
			}
			
			// Controllo se ci sono delle chiavi di rottura
			foreach ($chiaviArchiviazione as $chiave => $valore) {
//				echo "VAL RIGA: ".$valore['RIGA']." - RIGA ORIGINALE: $riga_originale<br>";
				if ($valore['RIGA']==$riga_originale) {
					$key_txt = substr($dati, $valore['COLONNA']-1, $valore['LEN']);
//					echo "<font color='orange'>KEY TXT:</font> $key_txt<br>";
					if(trim($key_txt)!="") {
						$key_array[$chiave+1] = $key_txt; 
						$new_key_break .= "_".trim($key_txt);
						$num_keys++;
//						echo "<font color='pink'>NEW KEY BREAK:</font> $new_key_break<br>";
					}
				}
/*				
				else if($valore['RIGA']>$riga_originale) {
					echo "<font color='purple'>RESET KEY BREAK</font><br>";
					$new_key_break = "";
					$num_keys = 0;
				}
*/
		    }
		     
		    if($new_key_break!="")
		    	$key_break = $new_key_break;
		    
//		    echo "KEY_BREAK: $key_break - SKIP: $skip - OLD SKIP: $old_skip<br>";
		    
			if((trim($skip)!="" && $skip<=$old_skip) || (trim($skip)=="" && trim($old_skip)!="")) {
//		    	echo "<font color='red'>KEY: $key_break - OLD: $old_key_break</font><br>";
		    	
		    	if ($chiaviArchiviazione[count($chiaviArchiviazione)-1]['RIGA']<=$riga_originale && 
		    		$key_break!=$old_key_break && $old_key_break!=""
		    	) {
		    		if($old_key_break!="") {
//						echo "<font color='green'>NEW DOCUMENT</font><br>";
						$doc++;
					}
					
					if($start!="") {
//						echo "<font color='orange'>CONTENTS</font><br>";
//						echo "<pre>"; print_r($contents); echo "</pre>";
						$this->arrayContents[$doc] = $contents;
						$contents = array();
						$start = "";
					}
					
					// Ripulire il nome del file da eventuali caratteri speciali (in particolare la &) perchè potrebbe interferire nell'URL in fase di download (funzione downloadDetail() in common.php)
//					$key_url = cleanForShortURL($key_break);
					ksort($key_array);
					$key_url = implode("_", $key_array);
//					$key_url = "_".cleanForShortURL($key_url);
//					echo "KEY_BREAK: $key_break<br>";
//					echo "KEY_ARRAY:<pre>"; print_r($key_url); echo "</pre>";
//					echo "KEY_URL: $key_url<br>";

					$this->pdf_key_url[$doc] = $key_url;

//					$filename = date("Ymd-Hms").$key_url.'.pdf';
					$filename = $this->set_filename($doc);
					$filepath = wi400File::getUserFile('tmp', $filename);
					$this->pdf_out[$doc] = $filepath;
					
					// Dato che viene cambiato il nome del file da convertire, bisogna modificarlo anche nell'elenco dei documenti che andiamo a creare, per poterlo recuperare in fase di download
					$this->nome_file[$doc] = $filename;
					
					$old_key_break = $key_break;
		    	}
		    	else {
		    		if($old_key_break=="") {
		    			if($isFirst===true) {
//							echo "<font color='green'>FIRST</font><br>";
							$doc++;
							$isFirst = false;
		    			}
		    			else if($num_keys==count($chiaviArchiviazione)) {
		    				$old_key_break = $key_break;
//		    				echo "<font color='yellow'>SET OLD KEY BREAK</font><br>";
		    			}
		    			
			    		if($start!="") {
//			    			echo "<font color='orange'>CONTENTS</font><br>";
//							echo "<pre>"; print_r($contents); echo "</pre>";
							$this->arrayContents[$doc] = $contents;
							$contents = array();
							$start = "";
						}
		    			
		    			// Ripulire il nome del file da eventuali caratteri speciali (in particolare la &) perchè potrebbe interferire nell'URL in fase di download (funzione downloadDetail() in common.php)
//						$key_url = cleanForShortURL($key_break);
						ksort($key_array);
						$key_url = implode("_", $key_array);
//						$key_url = "_".cleanForShortURL($key_url);
//						echo "KEY_BREAK: $key_break<br>";
//						echo "KEY_ARRAY:<pre>"; print_r($key_url); echo "</pre>";
//						echo "KEY_URL: $key_url<br>";

						$this->pdf_key_url[$doc] = $key_url;
						
//						$filename = date("Ymd-Hms").$key_url.'.pdf';
						$filename = $this->set_filename($doc);
						$filepath = wi400File::getUserFile('tmp', $filename);
						$this->pdf_out[$doc] = $filepath;
						
						// Dato che viene cambiato il nome del file da convertire, bisogna modificarlo anche nell'elenco dei documenti che andiamo a creare, per poterlo recuperare in fase di download
						$this->nome_file[$doc] = $filename;
					}
		    	}
		    	
		    }

		    if($start=="")
			   	$this->arrayContents[$doc][] = $array[$i];
			else
				$contents[] = $array[$i];
		    $i++;
		}
		
//		echo "CONTENTS:<pre>"; print_r($this->arrayContents); echo "</pre>";
		
		if(!empty($contents)) {
			$this->arrayContents[$doc] = array_merge($this->arrayContents[$doc],$contents);
			$contents = array();
		}
		
		$this->numDocuments = $doc;
	}
	
	private function set_user_keys($documento) {
		global $db, $settings;
		
		// Chiavi utente
		$sql_uk = "select * from FEMAILUD where ID='{$this->idRecord}' order by USNRIG";
		$res_uk = $db->query($sql_uk,0,false);
		
		$user_keys = array();
		$i = 1;
		while($row_uk = $db->fetch_array($res_uk)) {
			$this->dati_conv[$documento]["LOGKU".$i] = $row_uk['USNVAL'];
			$i++;
		}
		
		for($i; $i<=$settings['modelli_pdf_user_keys']; $i++) {
			$this->dati_conv[$documento]["LOGKU".$i] = "";
		}
	}
	
	public function get_pdf_out() {
		return $this->pdf_out;
	}
	
	public function set_currentDocument($num_doc) {
		$this->currentDocument = $num_doc;
	}
	
	public function get_currentDocument() {
		return $this->currentDocument;
	}
	
	//  @todo Files PDF da concatenare alla fine del file
	public function finalize_add_last_page() {
		global $routine_path, $settings, $appBase;
	
		if(!empty($this->extraPDF)) {
//			echo "EXTRA PDF:<pre>"; print_r($this->extraPDF); echo "</pre>";
	
			ini_set("memory_limit","200M");
			
//			echo "PDF OUT:<pre>"; print_r($this->pdf_out); echo "</pre>";
				
			foreach($this->pdf_out as $key => $doc) {
				$this->currentDocument = $key;
				
				$this->createPdf();
	
				$pagecount = $this->pdf->setSourceFile($doc);
	
				for($i=0; $i<$pagecount; $i++){
					$this->pdf->AddPage();
					$tplidx = $this->pdf->importPage($i+1, '/MediaBox');
					$this->pdf->useTemplate($tplidx, 10, 10, 200);
				}
	
				foreach($this->extraPDF as $extra_pdf) {
					$pagecount = $this->pdf->setSourceFile($extra_pdf);
	
					for($j=0; $j<$pagecount; $j++){
						$this->pdf->AddPage();
						$tplidx = $this->pdf->importPage($j+1, '/MediaBox');
						$this->pdf->useTemplate($tplidx, 10, 10, 200);
					}
				}
	
				$this->outputPdf();
			}
		}
	
		wi400SpoolConvert::finalize();
	}

	//  @todo Files PDF da concatenare dopo ogni pagina del file
	public function finalize_add_each_page() {
		global $routine_path, $settings, $appBase;
	
		if(!empty($this->extraPDF)) {
//			echo "EXTRA PDF:<pre>"; print_r($this->extraPDF); echo "</pre>";
	
			ini_set("memory_limit","200M");
			
//			echo "PDF OUT:<pre>"; print_r($this->pdf_out); echo "</pre>";
	
			foreach($this->pdf_out as $key => $doc) {
				$this->currentDocument = $key;
	
				$this->createPdf();
	
				$pagecount = $this->pdf->setSourceFile($doc);
	
				for($i=0; $i<$pagecount; $i++){
					$this->pdf->AddPage();
					
					$tplidx = $this->pdf->importPage($i+1, '/MediaBox');
					$this->pdf->useTemplate($tplidx);
					
					foreach($this->extraPDF as $extra_pdf) {
						if(file_exists($extra_pdf)) {
							$pagecount = $this->pdf->setSourceFile($extra_pdf);
						
							for($j=0; $j<$pagecount; $j++){
								$this->pdf->AddPage();
//								$this->pdf->SetAutoPageBreak(FALSE, 0);
								$tplidx = $this->pdf->importPage($j+1, '/MediaBox');
								$this->pdf->useTemplate($tplidx);
							}
						}
					}
					
					$pagecount = $this->pdf->setSourceFile($doc);
				}
	
				$this->outputPdf();
			}
		}
	
		wi400SpoolConvert::finalize();
	}
	
}

?>