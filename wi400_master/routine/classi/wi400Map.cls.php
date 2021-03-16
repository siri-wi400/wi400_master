<?php
/**
 * @name wi400Map.php Classe visualizzazione mappa
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author Luca Zovi
 * @versione 1.00 10/22/2009
 * @link www.siri.informatica.it
 */
class wi400Map
{
	private $mapFile;
	private $codice;
	private $mapArg;
	private $point;
	private $isClickable;
	private $dati;
	private $error;

	/**
	 * Il costruttore può ricevere i seguenti parametri
	 *
	 * @param string $mapCodice      Codice della mappa
	 * @param string $mapArg         Argomento della mappa  (es. DEPO, CELLA, NEGOZIO)
	 * 
	 *  
	 */
	function __construct($mapCodice=null, $mapArg=null)
	{
		global $appBase, $messageContext;
		
		$this->point = array();
		if (isset($mapCodice)){
		    $this->codice = $mapCodice;
		}
		if (isset($mapArg)){
		    $this->mapArg = $mapArg;
		}
		$this->isClickable=False;
		$this->errore= False;
        require_once $_SERVER['DOCUMENT_ROOT'].$appBase."modules/mappa/map_commons.php";		
		$this->dati = $mappa;
		if (!isset($mapCodice) || !isset($this->dati[$mapCodice])) {
			$messageContext->addMessage("ERROR", "Per il deposito $mapCodice non è stata codificata la mappa", "");
			$this->errore= True;
		}
	}
	/**
	 * Setto il codice relativa alla mappa
	 *
	 * @param string $codice        Codice della mappa
	*/
	function setCodice($codice) {
       $this->codice = $codice;
    }
	/**
	 * Ritorno il codice mappa della classe
	 *
	 * @param string $codice        Codice della mappa
	*/
	function getCodice() {
       return $this->codice;
    }
    /**
	 * Carica il descrittore della mappa di deposito
	 *
	 * @param array $descrittore      Coordinate di deposito
	*/
	function loadDati() {
       $this->dati;
    }
    
	/**
	 * Setto l'argomento del codice associato alla mappa
	 *
	 * @param string $codice        Argomento della mappa
	*/
	function setArgomento($argomento) {
       $this->mapArg = $argomento;
    }
	/**
	 * Ritorno il codice mappa della classe
	 *
	 * @param string $codice        Codice della mappa
	*/
	function getArgomento() {
       return $this->mapArg;
    }
	/**
	 * Setto se la mappa è cliccabile sui vari posti per reperire il dettaglio della bay
	 *
	 * @param boolean $clickable        Setto se la mappa è cliccabile
	*/
	function setIsClickable($clickable=True) {
       $this->isClickable = $clickable;
    }
	/**
	 * Ritorno se la mappa è cliccabile sui vari posti per reperire il dettaglio della bay
	 *
	 * @param boolean $clickable        Reperisco se la mappa è cliccabile
	*/
	function getIsClickable() {
       return $this->mapIsClickable;
    }
	/**
	 * Aggiungo un punto di interesse sulla mappa
	 *
	 * @param wi400MapPoint $mapPoint    Setto un punto sulla mappa
	*/
	function addPoint($mapPoint) {
       $this->point[] = $mapPoint;
    }
	/**
	 * Mette a video la mappa
	 *
	 */
	function dispose()
	{
	global $appBase, $settings;
	
	if ($this->errore) {
		return false;
	}
	// Per prima cosa carico la mappa
	$fileName="..".$settings['uploadPath']."maps/".$this->dati[$this->codice]['FILE_NAME'];
	$info = pathinfo($fileName);
	if (strtolower($info['extension'])=="jpg" || strtolower($info['extension'])=="jpeg") {
		$im = imagecreatefromjpeg($fileName); 
	} elseif (strtolower($info['extension'])=="png") {
		$im = imagecreatefrompng($fileName); 
	}
    $color = imagecolorallocate ($im, 0,255, 0);
    $red = imagecolorallocate ($im, 0,0, 255);
	// Reperisco la grandezza dell'immagine per un eventuale rescaling
	$scale_x = imagesx($im);
	$scale_y = imagesy($im);
	$area = array(); 
    // Scrivo i punti sulla mappa
    foreach ($this->point as $key=>$point){
            $msgCliccabile="";
            if ($point->getPosto() !=""){
             // Reperisco il dettaglio del posto
             $detail = $this->getPostoDetail($point->getPosto());
             // Cerco il posto sulla mappa attualmente caricate
             $pt = $this->getCoordinate($detail);
             
             $c = $point->getCustomColor();
             if (!isset($area[$pt["x"]][$pt["y"]])){             
             if (!empty($c)) {
             	$c = $point->getCustomColor();
             	$color = imagecolorallocate ($im, $c['RED'],$c['GREEN'], $c['BLU']);
             }
             if ($point->getTipo()=="PALLET"){
             	 imagefilledellipse($im, $pt["x"],$pt["y"], 10, 10, $color);
             } elseif ($point->getTipo() =="PICKER") {
	             imagefilledrectangle ($im, $pt["x"]-3,$pt["y"]-3, $pt["x"]+3,$pt["y"]+3, $red);
             }
             }
             // Verifico se cliccabile
             if ($point->getIsClickable()) {
                $msgCliccabile="HREF=\"scaffale.php\" onclick=\"window.open(this.href, 'child', 'scrollbars,width=650,height=600'); return false\"";
             }
            $testo =$point->getText(); 
            if ($testo==""){ 
	            $testo = $point->getPosto();
            } 
            if (!isset($area[$pt["x"]][$pt["y"]])){
            	$area[$pt["x"]][$pt["y"]]=$testo;
            } else {
            	$area[$pt["x"]][$pt["y"]]=$area[$pt["x"]][$pt["y"]]."\r\n".$testo;
            
            }
			//$area[] ="<AREA $msgCliccabile title='".$testo."' SHAPE=circle COORDS=".$pt['x'].",".$pt['y'].',10">';              
            }               
    }
	$nomeFile = "newmappa".time("YmdHis").".jpg";
	$fileMappa = wi400File::getUserFile('tmp', $nomeFile);
	imagejpeg($im, $fileMappa);  
	//echo "<img src=/WI400_LZOVI/modules/mappa/newmappa.png BORDER=0 USEMAP='#map1'>";
//	echo "<img src='".$appBase."index.php?DECORATION=clean&t=FILEDWN&CONTEST=tmp&FILE_NAME=".$nomeFile."' BORDER=0 USEMAP='#map1'>";
	$link = create_file_download_link($nomeFile, "tmp");
	echo "<img src='".$link."' BORDER=0 USEMAP='#map1'>";
	echo "<MAP NAME='map1'>";
	// Area Cliccabile sulla mappa
	foreach ($area as $x=>$value) {
		foreach ($value as $y=>$value2) {
			$pos = substr($value2,0,12);
			//echo '<AREA HREF="scaffale.php" onclick="window.open(this.href, \'child\', \'scrollbars,width=650,height=600\'); return false" title="'.$value2.'" SHAPE=circle COORDS="'.
	        echo '<AREA HREF="javascript:openWindow(\'index.php?t=BAY&DETAIL_KEY=1&POSITION='.$pos.'&DEPOSITO='.$this->codice.'&PARENT_ID=MAP\', \'showDetail\', 640, 600, true
	        );" title="'.$value2.'" SHAPE=circle COORDS="'.
	   	$x.",".$y.',6">';		
		}
	}
	// Gestione mappa navigabile
	$this->setIsClickable(True);
	if ($this->isClickable) {
		foreach ($this->dati[$this->codice] as $zona=>$zonaArray){
		   if (is_array($zonaArray)) {
		   foreach ($zonaArray as $corridoio=>$corridoioArray) {		   
		   if (is_array($corridoioArray)) {
		   foreach ($corridoioArray as $lato=>$coordinate) {
            if (is_array($coordinate)) {
			     $datiCorridoio = $this->getBayCount($this->codice, $zona, $corridoio, $lato);
			     //echo "<br>Bay trovate per $zona-$corridoio-$lato:".$datiCorridoio['COUNT'];
			     if ($datiCorridoio['COUNT']>0) {
			     //echo "step1";
			     // Controllo orientamento mappa
			     if((isset($this->dati[$this->codice][$corridoio]['ORIENTATION']) && $this->dati[$this->codice][$corridoio]['ORIENTATION']=='PORTRAIT')
			      || $this->dati[$this->codice]['ORIENTATION']=='PORTRAIT') {
			         $X= $coordinate['X'];
			         $XP= $coordinate['X+'];
			         $START = $coordinate['Y'];
			         $step1 = $coordinate['Y+']-$coordinate['Y'];
			         $STEP = $step1/$datiCorridoio['COUNT'];
			         if (isset($this->dati[$this->codice][$zona][$corridoio]['DIRECTION']) && $this->dati[$this->codice][$zona][$corridoio]['DIRECTION']=='ASC') {
						$STEP = ($STEP * -1);
						$START = $coordinate['Y+'];		         	
			         }
			     } else {
			         $X= $coordinate['X'];
			         $XP= $coordinate['X+'];
			         $START = $coordinate['Y'];
			         $step1 = $coordinate['Y+']-$coordinate['Y'];
			         $STEP = $step1/$datiCorridoio['COUNT'];
			     	 if (isset($this->dati[$this->codice][$zona][$corridoio]['DIRECTION']) && $this->dati[$this->codice][$zona][$corridoio]['DIRECTION']=='ASC') {
						$STEP = ($STEP * -1);
						$START = $coordinate['Y+'];		         	
			         }			         			     
			     }
			     $POS= $zona."-".$corridoio;
			       foreach ($datiCorridoio['BAY'] as $key) {
			         $STEPFIN = $START + $STEP;
					 echo '<AREA HREF="javascript:openWindow(\'index.php?t=BAY&DETAIL_KEY=1&POSITION='.$POS."-".$key.'&DEPOSITO='.$this->codice.'&PARENT_ID=MAP\', \'showDetail\', 640, 600, true
	        		);" title="'.$POS."-".$key.'" SHAPE=RECT COORDS="'.
					 $X.",".$START.",".$XP,",".$STEPFIN.'">';
					 $START = $START + $STEP;
			       }	 
		     }
             }
             }
		     }
		   }	 
		   }
		}
	}
	//echo '<AREA HREF="scaffale.php" onclick="window.open(this.href, \'child\', \'scrollbars,width=650,height=600\'); return false" title="POSTO_PROVA" SHAPE=circle COORDS="'.
	//$pt['x'].",".$pt['y'].',20">';
	echo "</map>";
	// Distruggo l'immagine per liberare memoria
	imagedestroy($im);
	}
	/**
	 * Reperisco il dettaglio del posto passato come parametro
	 *
	 */
	function getPostoDetail($posto)
	{
	global $db;
	static $stmt;
	
	if (!isset($stmt)) {
		$sql ="SELECT * FROM FMADSTOC WHERE MADZOD=? AND MADCOR=? AND MADBAY=? AND MADCDP=? AND MADSTA='1'";
		$stmt = $db->singlePrepare($sql);
	}
	// Esplodo il posto - il separatore deve essere "-"
	$array = explode(".", $posto);
	$do = $db->execute($stmt, array(trim($array[0]),trim($array[1]),trim($array[2]),trim($array[3])));
	$row = $db->fetch_array($stmt);
	return $row;
	}
	function getCoordinate($row) {
	global $db;
	
	static $stmt;
	$pt = array();
	$pt['x']=0;
	$pt['y']=0;
	// Controllo dove trovo il posto
	// Posto preciso
	if (isset($this->dati[$this->codice][$row['MADZOD']][$row['MADCOR']][$row['MADLAT']][$row['MADBAY']][$row['MADCDP']])){
	}
	//  Zona/Corridoio/Lato/Bay 
	elseif (isset($this->dati[$this->codice][$row['MADZOD']][$row['MADCOR']][$row['MADLAT']][$row['MADBAY']])) {
	}
	// Zona/Corridoio/Lato 
	elseif (isset($this->dati[$this->codice][$row['MADZOD']][$row['MADCOR']][$row['MADLAT']])) {
	  $c = $this->dati[$this->codice][$row['MADZOD']][$row['MADCOR']][$row['MADLAT']];
	  // Trovo il numero di bay all'interno del corridoi per trovare una media per la posizione
	$filterSql =""; 
	if (isset($this->dati[$this->codice]['SQL_FILTER'])) {
	    $filterSql = " AND ".$this->dati[$this->codice]['SQL_FILTER'];
	} 
	if (!isset($stmt)) {  
		$sql ="SELECT MADBAY, MADCDP FROM FMADSTOC WHERE MADZOD=? AND MADCOR=? AND MADLAT=? AND MADPPA=? AND MADSTA='1' GROUP BY MADBAY, MADCDP ORDER BY MADBAY, MADCDP";
	    $stmt = $db->singlePrepare($sql);	
	}
	$do = $db->execute($stmt, array($row['MADZOD'], $row['MADCOR'],$row['MADLAT'], $row['MADPPA']));	
	$count = 0;
	$dove = 0;
	$posti = 0;
	while ($row1= $db->fetch_array($stmt)){
		$count++;
		if ($row1['MADBAY']==$row['MADBAY']) {
	          if ($row1['MADCDP']==$row['MADCDP']) {
				  $dove = $count;
	          }
	    $posti++;   	  
		}
	}
	//echo "numero di posti:".$posti. " count:".$count;
	// Verifico l'orientamento mappa
	// Ordinamento corridoio, in base alla direzione del prelievo giro la posizione della bay rispetto a count
	if (isset($this->dati[$this->codice][$row['MADZOD']][$row['MADCOR']]['DIRECTION'])) {
	       if ($this->dati[$this->codice][$row['MADZOD']][$row['MADCOR']]['DIRECTION']='ASC') {
	          $gira = $count - $dove;
	          $dove = $gira;
	       }
	}
	// ORDER BY MADBAY ".$this->dati[$this->codice][$row['MADZOD']][$row['MADCOR']]['DIRECTION']
	if ($this->dati[$this->codice]['ORIENTATION']=='PORTRAIT' || (isset($this->dati[$this->codice][$row['MADZOD']]['ORIENTATION']) && $this->dati[$this->codice][$row['MADZOD']]['ORIENTATION']=='PORTRAIT' )) {
		$diff = $c['Y+']-$c['Y'];
		$pt['x']= $c['X']+(($c['X+']-$c['X'])/2);
		$pt['y']= $diff/$count*$dove+$c['Y'];
	} else {
		$diff = $c['X+']-$c['X'];
		$pt['y']= $c['Y']+(($c['Y+']-$c['Y'])/2);
		$pt['x']= $diff/$count*$dove+$c['X'];
	}
	} 
	// Ultimo caso in cui è stata censita solamente la zona
	elseif (isset($this->dati[$this->codice][$row['MADZOD']][$row['MADCOR']])) {
	}
	// Verifico se punti x e y a zero vuol dire che non è stato trovato il posto 
	if ($pt['x']==0 && $pt['y']==0) {
		$pt['x']= 10;
		$pt['y']= 10;
		$pt['NOTE']="PUNTI NON TROVATI";
	}
	return $pt;
	}
	/**
	 * Recupero il numero di bay di un corridoio
	 *
	 */	
	function getBayCount ($deposito, $zona, $corridoio, $lato) {
	global $db;
	static $stmt;
	$datiCorridoio = array();
	if (!isset($stmt)) {  
		$sql ="SELECT MADBAY FROM FMADSTOC WHERE MADCDE=? AND MADZOD=? AND MADCOR=? AND MADLAT=? AND MADSTA='1' GROUP BY MADBAY ORDER BY MADBAY";
	    $stmt = $db->singlePrepare($sql);	
	}
	$do = $db->execute($stmt, array($deposito, $zona,$corridoio, $lato));
	$count = 0;
	while ($row1= $db->fetch_array($stmt)) {
	   $count++;
	   $datiCorridoio['BAY'][$row1['MADBAY']]=$row1['MADBAY'];
	}
	$datiCorridoio['COUNT']= $count;
	return $datiCorridoio;
	}
	/**
	 * Distruttore della classe. Solo se la connessione non è persistente
	 *
	 */
	function __destruct()
	{
	}
}
class wi400MapPoint {
	
    private $posto;
	private $tipo;
	private $color;
	private $isClickable;
	private $text;
	private $customColor;

	/**
	 * Il costruttore può ricevere i seguenti parametri
	 *
	 * @param string $mapCodice      Codice della mappa
	 * @param string $mapArg         Argomento della mappa  (es. DEPO, CELLA, NEGOZIO)
	 * 
	 *  
	 */
	function __construct($posto, $tipo="PALLET", $color="DEFAULT", $isClickable=True, $text="")
	{
		$this->posto=$posto;
		$this->tipo=$tipo;
		$this->color=$color;
		$this->isClickable=$isClickable;
		$this->text=$text;
	}
	function getPosto() {
	    return $this->posto;
	}
	function getTipo() {
	    return $this->tipo;
	}
	function getColor() {
	    return $this->color;
	}
	function getIsClickable() {
	    return $this->isClickable;
	}
	function getText() {
	    return $this->text;
	}
	function getCustomColor() {
	    return $this->customColor;
	}	
	function setPosto($posto) {
	    $this->posto=$posto;
	}
	function setTipo($tipo) {
	    $this->tipo =$tipo;
	}
	function setColor($color) {
	    $this->color=$colot;
	}
	function setIsClickable($bool) {
	    $this->isClickable=$bool;
	}
	function setText($text) {
	    $this->text = $text;
	}	
	function setCustomColor($red, $green, $blue) {
	    $this->customColor['RED']=$red;
	    $this->customColor['GREEN']=$green;
	    $this->customColor['BLU']=$blue;	    
	}
}
?>