<?php
/**
 * @name wi400SpoolConvert Classe per accesso al DB2 AS400
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
    private $modulo;
    public  $pdf;
    private $result;
    private $nome;
    private $count;
    private $pagina;
    private $salto;
    private $pdf_out;
    private $nome_file;
    
	/**
	 * Costrutture Classi
	 *
	 * @param $idRecord     Numero identificativo della conversione
	 * @param $connZend     Connessione a Zend
	 * @param $db           Connessione al DataBase
	 * */
public function __construct($idRecord, $connZend, $connDb, $file, $libl=null){
	    	
    $this->idRecord = $idRecord;
    $this->connZend = $connZend;
    $this->connDb = $connDb;
	ini_set("memory_limit","20M");
	error_reporting(E_ALL);
//	ini_set("display_errors", true);
	if (isset($libl)){
		$this->dati['FILE']=trim($libl).".".trim($file);		 
	} else {
	$this->dati['FILE']="{".trim($file)."}";
	}
	
}
public function setDatiBySiriModuli($modulo)
{
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
      $this->checkDati();
      
}
public function setDati($element, $value)
{
	$this->dati[$element]=$value;
}
public function getFile(){

	$sql="select * from ".$this->dati['FILE'];
	$this->result = $this->connDb->query($sql); 
	if (!$this->result) 
	{
		return false;
	}
	return true;
}
// Recupero il numero massimo di colonne dello spool file
public function spoolMaxLen(){

	$sql="select MAX(ZDT_LEN(RECORD)) AS MAXLEN from ".$this->dati['FILE'];
	$result = $this->connDb->query($sql);
	$row = $this->connDb->fetch_array($result); 
	return $row['MAXLEN'];
}

public function createPdf(){

	    global $routine_path;	
	 
	    require_once $routine_path."/FPDF/fpdi.php";

		$this->pdf=new FPDI($this->dati['ORIENTAMENTO'],trim($this->dati['UNITA_MISURA']),trim($this->dati['FORMATO_PAGINA']));
		$this->pdf->SetFont(trim($this->dati['NOME_FONT']),'',$this->modulo['ALTEZZA_FONT']);
		$this->pdf->SetRightMargin(0);
		$this->pdf->SetAutoPageBreak(False);
		$this->pdf->SetCreator($this->dati['CREATORE']);
		$this->pdf->SetAuthor($this->dati['AUTORE']);
		$this->pdf->SetSubject($this->dati['DESCRIZIONE']);
}
// Funzione che sostituisce i dati *DEFAULT, *BLANK, *NAME
function checkDati()
{
        $len = $this->spoolMaxLen();
	    // Setto l'orientamento dello spool
	    if (!isset($this->dati['ORIENTAMENTO']) or $this->dati['ORIENTAMENTO']=="A")
	    {
	    	if ($len<132) $this->dati['ORIENTAMENTO']="P";
	    	else $this->dati['ORIENTAMENTO']="L";
	    }
	    // Setto il carattere
	    if (!isset($this->dati['NOME_FONT']) or $this->dati['NOME_FONT']=="*DEFAULT")
	    {
	    	$this->dati['NOME_FONT']="courier";
	    }
	    // Altezza font
	    if (!isset($this->dati['ALTEZZA_FONT']) or $this->dati['ALTEZZA_FONT']==0)
	    {
	    	// La calcolo in base all'orientamento della pagina
	    	if ($len<84) { 
	    	    	$this->dati['ALTEZZA_FONT']="12.0";
	    		}
	    	else {
	    	if ($len<136) {
	    		$this->dati['ALTEZZA_FONT']="6.0";
	    		$this->dati['ALTEZZA_CARATTERE']="6.0";
	    		$this->dati['ALTEZZA_INTERLINEA']="3.0";
	    	}
	    	else {
	    	if ($len>136)  {
	    		$this->dati['ALTEZZA_FONT']="6.0";
	    		$this->dati['ALTEZZA_CARATTERE']="6.0";
	    		$this->dati['ALTEZZA_INTERLINEA']="3.0";	    		
	    		}
	    	}
	    }
	    }	    
	    // Colonna Iniziale
	    if (!isset($this->dati['COLONNA_INIZIALE']) or $this->dati['COLONNA_INIZIALE']==0)
	    {
	    	$this->dati['COLONNA_INIZIALE']="1";
	    }
	    // Autore
	    if (!isset($this->dati['AUTORE']) or $this->dati['AUTORE']=="")
	    {
	    	$this->dati['AUTORE']="WI400 By SIRI Informatica!";
	    }
	    // Creatore
	    if (!isset($this->dati['CRETORE']) or $this->dati['CREATORE']=="")
	    {
	    	$this->dati['CREATORE']="WI400 By SIRI Informatica!";
	    }		    	    
	    // Riga Iniziale
	    if (!isset($this->dati['RIGA_INIZIALE']) or $this->dati['RIGA_INIZIALE']==0)
	    {
	    	$this->dati['RIGA_INIZIALE']="1";
	    }
	    // Altezza Carattere
	    if (!isset($this->dati['ALTEZZA_CARATTERE']) or $this->dati['ALTEZZA_CARATTERE']==0)
	    {
	    	$this->dati['ALTEZZA_CARATTERE']="4.0";
	    }
	    // Altezza Interlinea
	    if (!isset($this->dati['ALTEZZA_INTERLINEA']) or $this->dati['ALTEZZA_INTERLINEA']==0)
	    {
	    	$this->dati['ALTEZZA_INTERLINEA']="4.0";
	    }
	    // Unità di misura
	    if (!isset($this->dati['UNITA_MISURA']) or $this->dati['UNITA_MISURA']=="")
	    {
	    	$this->dati['UNITA_MISURA']="mm";
	    }
	    // Unità di misura
	    if (!isset($this->dati['FORMATO_PAGINA']) or $this->dati['FORMATO_PAGINA']=="")
	    {
	    	$this->dati['FORMATO_PAGINA']="A4";
	    }
	    // Unità di misura
	    if (!isset($this->dati['PATH_OUTPUT']) or $this->dati['PATH_OUTPUT']=="")
	    {
	    	$this->dati['PATH_OUTPUT']="/Siri/Temp/";
	    }		    	    		    	    	    	    
        // NOME UTENTE
	    if (!isset($this->dati['UTENTE']) or $this->dati['UTENTE']==""){
			$this->dati['UTENTE']="GENERICO";
		}
        // ARGOMENTO
	    if (!isset($this->dati['ARGOMENTO']) or $this->dati['ARGOMENTO']==""){
			$this->dati['ARGOMENTO']="GENERICO";
		}
		// Composizione nome dello spool di output
		if (!isset($this->dati['NOME']) or $this->dati['NOME']==""){
			  $this->dati['NOME']=	trim($this->dati['ARGOMENTO'])."_".date("d")."-".date("m")."-".date("Y")."-".date("H")."_".date("i")."_".date("s");
		} 

		$this->nome_file = date("YmdHis")."_".$this->dati['NOME'].'.pdf';	    
		$filepath = wi400File::getUserFile('tmp', $this->nome_file);		
		$this->pdf_out = $filepath;
}
public function setParm($dati) {
	$this->dati = $dati;
	$this->checkDati();
}
// Impostazione variabili iniziali
function convert()
{
$this->salto=False;
$this->count=1;
$this->pagina = 1;
$old_skip = 9999;
$riga = number_format($this->dati['RIGA_INIZIALE']);
$colonna = number_format($this->dati['COLONNA_INIZIALE']);
//ciclo tutti record
while ($row=$this->connDb->fetch_array($this->result, Null, False)) {

	// Valorizzo i dati dello spool
	$isBold ="";
	$skip  = substr($row['RECORD'],0,3);
	$space = substr($row['RECORD'],3,1);
	$dati  = substr($row['RECORD'],4);
    // Controllo il salto pagina da SKIP
	if ((trim($skip) <> "") and ($skip <= $old_skip))  {
	$this->salto = True;	
	$this->salto_pagina();		
	$riga = number_format($this->dati['RIGA_INIZIALE']);
	}	
    // Calcolo dove mi devo posizionare con la riga di stampa
    if (trim($skip) <> "")  {
       $old_skip = $skip;
       $riga = ($this->dati['ALTEZZA_INTERLINEA'] * $skip) + $this->dati['RIGA_INIZIALE'];
    }
    else {
        $riga = $riga + ($this->dati['ALTEZZA_INTERLINEA'] * $space);	
    }
    if ($space = "0") $isBold = "B";	
	$this->pdf->SetFont(trim($this->dati['NOME_FONT']),$isBold,$this->dati['ALTEZZA_FONT']);
	$this->write($colonna,$riga,$dati);
	$isBold="";
}
// Generazione PDF
$this->pdf->Output($this->pdf_out, 'F');
return true;
}
function write($colonna,$riga,$dati)
{
	$this->pdf->SetXY($colonna,$riga);
	$this->pdf->Write($this->dati['ALTEZZA_CARATTERE'], $dati);
}
function getPdfName()
{
       return $this->nome_file;
}
function getFullPdfName()
{
       return $this->pdf_out;
}
// Gestione salto pagina
function salto_pagina()
{
	$this->count=$this->count + 1;
	
	if ($this->salto)
	{
	$this->pdf->AddPage();
	if ($this->dati['PATH_PREFINCATO']!="")
	{
	$this->pdf->setSourceFile(trim($this->dati['PATH_PREFINCATO'])."/".trim($this->dati['NOME_PREFINCATO']));
	$tplIdx = $this->pdf->importPage(1);
	$this->pdf->useTemplate($tplIdx,0,0);	
	}
		
	$this->pdf->SetFont(trim($this->modulo['NOME_FONT']),'',$this->modulo['ALTEZZA_FONT']);
	$this->salto=False;	
	$this->count = 1;
	$this->pagina = $this->pagina + 1;
    }
}

}
?>

