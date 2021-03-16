<?php
/**
 * @name wi400InputText 
 * @desc Classe per la creazione di un campo di inserimento di testo
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 21/07/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */
require_once $routine_path.'/classi/wi400Input.cls.php';

class wi400InputText extends wi400Input {

    private $size	   = "";
    private $maxLength = "";
	private $decimals  = 0;
    private $description = "";
    private $lookUp = "";
    private $qwerty;
    
    private $tree = "";
    
    private $align = "";
    
    private $fontStyle = "";
    private $fontSize = 0;
    
    private $case = "";
    
    // multiple text
    private $showMultiple;
    private $maxValues = 0;
    private $minValues = 0;
    // Sort multiple text
    private $sortMultiple;
    private $addBlankValue = false;
    private $checkDuplicate = true;
        
	private $userApplicationValue = "";
    
    // Strumenti decodifica
    private $decode = "";
    private $des_search = true;
	
    private $tools = array();
    private $customTools = array();
    
    private $selOption = false;
    private $selOption_select = "";
    private $isDateOfBirth = false;
//	private $options_array = array();
	private $cleanable = True;
	private $forceClean = false;
    private $removeAll = True;
    private $showLookUpEver = False;
    private $showKeys = false;
    private $autoSelection = false;
    private $virtualKeyboardType = "qwerty";
   	private $style5250 = false;
   	private $hideHeaderTable = false;
   	private $autocompleteBrowser = false;
   	private $droppable=False;
   	private $droppableCallback="";
   	/**
   	 * @desc Setta se nascondere la tabella header del campo (multi choice)
   	 * @param unknown $hideHeaderTable
   	 */
    public function setHideHeaderTable($hideHeaderTable) {
    	$this->hideHeaderTable = $hideHeaderTable;
    }
    public function getHideHeaderTable() {
    	return $this->hideHeaderTable;
    }
    public function setDroppable($droppable) {
    	$this->droppable = $droppable;
    }
    public function getDroppable() {
    	return $this->droppable;
    }
    public function setDroppableCallback($droppableCallback) {
    	$this->droppableCallback = $droppableCallback;
    }
    public function getDroppableCallback() {
    	return $this->droppableCallback;
    }
    /**
	 * @return the $virtualKeyboardType
	 */
	public function getVirtualKeyboardType() {
		return $this->virtualKeyboardType;
	}

	/**
	 * @param string $virtualKeyboardType
	 */
	public function setVirtualKeyboardType($virtualKeyboardType) {
		$this->virtualKeyboardType = $virtualKeyboardType;
	}

	/**
     * Costruttore della classe
     *
     * @param string $id	: ID del campo di inserimento di testo da creare
     * @param unknown_type $result
     */
    public function __construct($id, $result = null){
    	global $settings;
    	
    	$this->setId($id);
    	$this->setType("INPUT_TEXT");
    	$this->sortMultiple = false;
    	$this->qwerty = false;
    	if (isset($settings['cleanable_input'])) {
    		$this->cleanable = $settings['cleanable_input'];
    	}
    	
    }

    public function addTree($tree){
    	$tree->setIdField($this->getId());
    	wi400Session::save(wi400Session::$_TYPE_TREE, $tree->getId(), $tree);
    	$this->tree = $tree;
    }
    
    public function getTree(){
    	return $this->tree;
    }
    
    /**
     * Elimina ogni componente aggiuntivo per visualizzare solo input text
     *
     */
    public function cleanInputText(){
    	$this->tree = "";
    	$this->lookUp = "";
    	$this->description = "";
    	$this->showMultiple = "";
    	$this->sortMultiple = "";
    }
    /**
     * @desc setShowLookupEver() Imposta la visualizzazione del lookup anche se il campo è readOnly
     * @param unknown $showLookupEver
     */
    public function setShowLookUpEver($showLookUpEver) {
    	$this->showLookUpEver = $showLookUpEver;
    }
    /**
     * @desc getShowLookupEver() Recupera la visualizzazione del lookup anche se il campo è readOnly
     * @return unknown $showLookupEver
     */
    public function getShowLookUpEver() {
    	return $this->showLookUpEver;
    }
    /**
     * Aggiunge un tool al campo di testo. 
     *
     * @param string $tool	: tool da associare al campo di testo (REMOVE_TOOL | ADD_TOOL)
     */
    public function addTool($tool){
    	$this->tools[] = $tool;
    }
    
    /**
     * Recupero dei tools associati al campo di testo
     *
     * @return unknown
     */
    public function getTools(){
    	return $this->tools;
    }
    
	/** 
	 * Aggiunge un customTool al campo di testo
	 * 
	 * @param object wi400CustomTool
	 */
    public function addCustomTool($tool) {
		$this->customTools[] = $tool;
	}

     
    /**
     * Recupero i tool associati alla classe wi400CustomTool
     *
     * @return array di wi400CustomTool
     */
    public function getCustomTool() {
    	return $this->customTools;
    }
    
    
    /**
     * Aggiunta funzionalità qwerty
     *
     * @param boolean
     */
    public function setQwerty($qwerty){
    	$this->qwerty = $qwerty;
    }
    
    /**
     * Recupero della funzionalità qwerty
     *
     * @return boolean	
     */
    public function getQwerty(){
    	return $this->qwerty;
    }
    
   /**
   * Forzatura del testo in maiuscolo/minuscolo:
   *
   * @param string UPPER | LOWER
   */
    
    /**
     * Forzatura del testo in maiuscolo/minuscolo:
     *
     * @param string $case	: tipo di forzatura del testo (UPPER | LOWER)
     */
    public function setCase($case){
    	$this->case = $case;
    }
    
    /**
     * Recupero del tipo di forzatura del testo del campo
     *
     * @return string	(UPPER maiuscolo| LOWER minuscolo)
     */
    public function getCase(){
    	return $this->case;
    }
    /**
     * @desc setCleanable() : Setto se sul campo di input aggiungo l'icona di pulizia
     *
     * @param boolean $clean	: campo con scopino pulizia (True/False)
     */
    
    public function setCleanable($clean){
    	$this->cleanable = $clean;
    }
    
    /**
     * @desc getCleanable: Recupero se il campo può essere pulito
     *
     * @return boolean	     */
    
    public function getCleanable(){
    	return $this->cleanable;
    }
    
    public function setForceClean($clean){
    	$this->forceClean = $clean;
    }
    
    public function getForceClean(){
    	return $this->forceClean;
    }
    
    /**
     * Impostazione della decodifica del campo
     *
     */
    public function resetDecode() {
    	$this->decode ="";
    }
    public function setDecode($decode){
    	global $settings, $base_path, $root_path;
    	// Verifico se per caso il settings ho il parametro generale
    	if (isset($settings['ajax_decoding_default']) && $settings['ajax_decoding_default']==True) {
    		if(!isset($decode["AJAX"])) $decode["AJAX"] = true;
    	}
    	// Verifico se per caso c'è anche il complete automatico, verifico anch il metodo
    	if (isset($decode['TYPE'])) {
	   		$decode_path = p13nPackage($decode['TYPE']);
//   		if($decode_path!==false) {
		    	require_once $decode_path;
		    	$decodeClass = new $decode['TYPE'];
		    	if (method_exists($decodeClass, "complete")) {
			    	if (isset($settings['ajax_complete_default']) && $settings['ajax_complete_default']==True) {
			    		if(!isset($decode["COMPLETE"])) $decode["COMPLETE"] = true;
			    	}
			    	// Controllo che il complete sia completo con tutti i parametri @TODO
			    	// Verifico se è stato messo il minimo di caratteri
			    	if (isset($decode["COMPLETE"]) && !isset($decode["COMPLETE_MIN"]) && isset($settings['ajax_complete_min_char_default'])) {
			    		$decode["COMPLETE_MIN"]=$settings['ajax_complete_min_char_default'];
			    	}
			    	// Verifico se è stato messo il numero massimo di risultati
			    	if (isset($decode["COMPLETE"]) && !isset($decode["COMPLETE_MAX_RESULT"]) && isset($settings['ajax_complete_max_result_default'])) {
			    		$decode["COMPLETE_MAX_RESULT"]=$settings['ajax_complete_max_result_default'];
			    	}
		    	}
/*
    		}
    		else {
    			$decode = "";
    		}
*/
    	}
    	$this->decode = $decode;
    }
    
    public function getDecode(){
    	return $this->decode;
    }
    
    public function getAjaxDecode(){
    	if ($this->getDecode()){
    		$decodeArray = $this->getDecode();
    		if (isset($decodeArray["AJAX"]) && $decodeArray["AJAX"] == true){
    			return 'true';
    		}
    	}
    	return 'false';
    }
    
    /**
     * Impostazione della presenza della ricerca per descrizione dei valori suggeriti per il campo (indicata con icona T)
     * @param unknown $des_search
     */
    public function setDesSearch($des_search) {
    	$this->des_search = $des_search;
    }
    
    public function getDesSearch() {
    	return $this->des_search;
    }
    
    public function setDescription($description){
    	$this->description = $description;
    }
    
    public function getDescription(){
    	return $this->description;
    }
    
    /**
     * Impostazione dell'allineamento del testo del campo
     *
     * @param string $align	: tipo di allineamento del testo (left, right)
     */
	public function setAlign($align){
    	$this->align = $align;
    }
    
    /**
     * Recupero del tipo di allineamento del testo del campo
     *
     * @return unknown
     */
    public function getAlign(){
    	return $this->align;
    }
    
    public function setFontStyle($style,$size){
    	$this->fontStyle = $style;
    	$this->fontSize = $size;
    }
    
    public function setLookUp($lookUp){
    	if (isset($lookUp) && $lookUp != "" && sizeof($lookUp->getFields())==0){
    		$lookUp->addField($this->getId());
    	}
    	$this->lookUp = $lookUp;
    }
    
	public function getLookUp(){
    	return $this->lookUp;
    }
    
    public function setSize($size){
    	if (isIpad()) $size = $size + 2;
    	$this->size = $size;
    }
    
    public function getSize(){
    	return $this->size;
    }
    
	
	/** 
	 * Reperisce la larghezza in css rispetto al dato size
	 * 
	 * @param void
	 * @return number (width per css)
	 */
    /*public function getSizeIpad() {
    	$sizeWidth = array("1"=>36,"2"=>43,"3"=>50,"4"=>57,"5"=>64,"6"=>71,"7"=>78,"8"=>85,"9"=>92,"10"=>99);
    	$size = $this->size;
    	
    	if($size && !isset($sizeWidth[$size])) {
    		$cssWidth = number_format(((78*$size)/7), 2);
    	}else {
    		if($size) {
    			$cssWidth = $sizeWidth[$size];
    		}else {
    			return 0;
    		}
    	}
    	
    	return $cssWidth;
    }*/
    
    public function setDecimals($decimals){
    	$this->decimals = $decimals;
    }
    
    public function getDecimals(){
    	return $this->decimals;
    }
    
    /**
     * Impostazione della lunghezza massima del contenuto del campo
     *
     * @param integer $maxLength	: lunghezza massima del contenuto del campo
     */
    public function setMaxLength($maxLength){
    	$this->maxLength = $maxLength;
    }
    
    /**
     * Recupero della lunghezza massima del contenuto del campo
     *
     * @return integer
     */
    public function getMaxLength(){
    	return $this->maxLength;
    }
    
	public function setSelOption($sel) {
		$this->selOption = $sel;
	}
/*	
	public function getSelOption($sel) {
		return $this->selOption;
	}
	
	public function setOptions($sel_options) {
		$this->options_array = $sel_options;
	}
	
	public function getOptions() {
		$this->options_array;
	}
*/  
	public function setSelOption_select($sel) {
		$this->selOption_select = $sel;
	}

	public function getSelOption_select() {
		return $this->selOption_select;
	}
	
	/**
	 * Impostazione del campo di testo come un campo multiplo (cioè composto da più elementi)
	 *
	 * @param boolean $hasMultiple	: true per dare la possibilià di aggiungere più valori, false altrimenti
	 */
 	public function setShowMultiple($hasMultiple){
		$this->showMultiple = $hasMultiple;    	
    }
    
    public function getShowMultiple(){
    	return $this->showMultiple;
    }
    
    /**
	 * true: Possibilità di aggiungere il valore ""
	 *
	 * @param boolean $val
	 */
    public function setAddBlankValue($val) {
    	$this->addBlankValue = $val;
    }
    
    /**
     * ritorna true se c'è la possibilità di aggiungere il valore ""
     *
     * @return boolean
     */
    public function getAddBlankValue() {
    	return $this->addBlankValue;
    }
    
    /**
     * true: Possibilità di controllare se il valore è già stato inserito
     *
     * @param boolean $val
     */
    public function setCheckDuplicate($val) {
    	$this->checkDuplicate = $val;
    }
    
    /**
     * ritorna true se viene controllato che il valore è già stato inserito
     *
     * @return boolean
     */
    public function getCheckDuplicate() {
    	return $this->checkDuplicate;
    }
    
    /**
     * Setta il numero massimo di valori che il campo multiplo può avere
     * 
     * @param number $num
     */
    public function setMaxValues($num) {
    	$this->maxValues = $num;
    }
    
    /**
     * Ritorna il numero massimo di valori che il campo multiplo può avere
     * 
     * @return number
     */
    public function getMaxValues() {
    	return $this->maxValues;
    }
    
    /**
     * Setta il numero minimo di valori che il campo multiplo può avere
     * 
     * @param number $num
     */
    public function setMinValues($num) {
    	$this->minValues = $num;
    }
    
    /**
     * Ritorna il numero minimo di valori che il campo multiplo può avere
     * 
     * @return number
     */
    public function getMinValues() {
    	return $this->minValues;
    }
    
    /**
     * Impostazione per visualizzare le chiavi dell'array affianco al valore (solo con campo multiplo)
     *
     * @param boolean $show	: true per visualizzare le chiavi, false altrimenti
     */
    public function setShowKeys($show) {
    	$this->showKeys = $show;
    }
    
    public function getShowKeys() {
    	return $this->showKeys;
    }
    
    /**
     * Impostazione della possibilità di riordinare gli elemento di un campo multiplo 
     *
     * @param boolean $sortMultiple	: true per dare la possibilità di riordinare il campo multiplo
     */
    public function setSortMultiple($sortMultiple){
    	$this->sortMultiple = $sortMultiple;
    }
    /**
     * Impostazione della possibilità di riordinare gli elemento di un campo multiplo
     *
     * @param boolean $sortMultiple	: true per dare la possibilità di riordinare il campo multiplo
     */
    public function getSortMultiple(){
    	return $this->sortMultiple;
    }    
    /**
     * Impostazione del valore del campo da memorizzare in sessione per poter essere riutilizzato anche
     * in altre azioni in cui ci sia un campo con lo stesso ID
     *
     * @param unknown_type $userApplicationValue
     */
    public function setUserApplicationValue($userApplicationValue){
    	$this->userApplicationValue = $userApplicationValue;
    }
    
    public function getUserApplicationValue(){
    	return $this->userApplicationValue;
    }
    
    /**
     * Se a true i campi input text quando focussati si autoselezionano
     *
     * @param boolean
     */
    public function setAutoSelection($val) {
    	$this->autoSelection = $val;
    }
    
    /**
     * Ritorna il valore dell'attributo autoSelection
     *
     * Se a true i campi input text quando focussati si autoselezionano
     *
     * @return boolean
     */
    public function getAutoSelection() {
    	return $this->autoSelection;
    }
    
	/**
	 * Se a true la select degli anni inizierà  da -115 anni e finirà con -10 anni rispetto l'anno corrente
	 * 
	 * @param boolean
	 */
    public function setIsDateOfBirth($val) {
    	$this->isDateOfBirth = $val;
    }
 
    /**
     * Ritorna true se è un campo data di nascita altrimenti false
     * 
     * @return boolean
     */
    public function getIsDateOfBirth() {
    	return $this->isDateOfBirth;
    }
    
    /**
     * Settato a true il componente il dispose sarà dedicato per il 5250
     * 
     * @param boolean $val
     */
    public function setStyle5250($val) {
    	$this->style5250 = $val;
    } 
    
    /**
     * Ritorna true se lo style è settato per l'emulatore 5250
     * 
     * @return boolean
     * 
     */
    public function getStyle5250() {
    	return $this->style5250;
    }
    

    /**
     * Disabilita l'autocomplete del browser
     *
     * @param boolean
     */
    public function setAutocompleteBrowser($val){
    	$this->autocompleteBrowser = $val;
    }
    
    /**
     * Disabilita l'autocomplete del browser
     *
     * @return boolean
     */
    public function getAutocompleteBrowser(){
    	return $this->autocompleteBrowser;
    }
    
    /**
     * Recupero del codice html da utilizzare per visualizzare il campo di testo
     *
     * @return string
     */
    public function getHtml(){
    	global $temaDir, $settings;
    	
    	if ($this->getCustomHTML() !="") {
    		return $this->getCustomHTML();
    	}

    	$htmlOutput = "";
    	
    	$readonly = "";
    	$cssDisabled = "";

    	if ($this->getReadonly()){
    		$readonly = "readonly";
    		$cssDisabled = "Disabled";
    	}
		
    	$onChange = array();
    	$onFocus = array();
    	$onKeyUp = array();
    	$onBlur = array();
    	
    	$onFocus[] = "setFocusedField(this)";
    	$onBlur[]  = "setFocusedField()";
    	$onClickFunction = "";
    	if($this->autoSelection) {
	    	if(isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA'])) {
	    		$onFocus[] = "setSelectionRangeDevice(this)";
	    	}else {
	    		$onFocus[] = "this.select()";
	    	}
    	}
    	
    	if ($this->getCase() == "UPPER"){
    		$onChange[] = "this.value=this.value.toUpperCase();";
    	}else if ($this->getCase() == "LOWER"){
    		$onChange[] = "this.value=this.value.toLowerCase();";
    	}else if ($this->getCase() == "FIRST"){
    		$onChange[] = "this.value=capitaliseFirst(this.value);";
    	}
    	
    	$formatNumber = false;
    	if ($this->getDecimals()>0){
    		$formatNumber = true;
    		$onChange[] = "this.value=currencyFormatter(this.value," .$this->getDecimals().")";
    	}
    	
    	$showDate = false;
    	$showIp4 = false;
    	$acceptNegative = false;
		if (in_array("double",$this->getValidations())===True){
    		$this->setValue(doubleModelToView($this->getValue(), $this->getDecimals()));
    	}else if (in_array("date",$this->getValidations())===True){
			$showDate = true;
			if (!$this->style5250) {
				if ($this->getIdList() == "") {
					$this->setMask("wi400Date");
				}else{
					$onChange[] = "checkDateFormat(this)";
				}
			}
			
			if(!$this->style5250) {
				$this->setSize(10);
				$this->setMaxLength(10);
			}
    	}else if (in_array("time",$this->getValidations())===True){
			$this->setMask("wi400Time");
			$this->setSize(4);
			$this->setMaxLength(5);
    	}else if (in_array("ip",$this->getValidations())===True){
			$showIp4 = true;
    	}else if (in_array("numeric",$this->getValidations())===True){
    		$this->setMask("1234567890,.+-");
    		/*if(isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA'])) {
    			$this->setType("NUMERIC");
    		}*/
    	}else if (in_array("integer",$this->getValidations())===True){
    		$this->setMask("1234567890,.+-");
    		/*if(isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA'])) {
    			$this->setType("NUMERIC");
    		}*/
    	}
    	
    	
    	if($this->getType() != "NUMERIC" && $this->getMask() && isset($_SESSION['NAVIGAZIONE_TABLET_ATTIVA'])) {
    		$mask = $this->getMask();
    		$char_array = array("0","1","2","3","4","5","6","7","8","9", "0", "+", ",");
    		$isNumber = true;
    		for($i = 0; $i < strlen($mask); $i++) {
    			if(!in_array($mask[$i], $char_array)) {
    				$isNumber = false;
    				break;
    			}
    		}
    		if($isNumber)
    			$this->setType("NUMERIC");
    	}
    	
        if ($this->getType() == "FILE"){
    		$onChange[] = "setMultipartFormEncoding()";
    	}
    	
    	if ($this->getCheckUpdate()){
    		$onChange[] = "setUpdateStatus('ON')";
    	}
    	
    	if ($this->getOnFocus() != ""){
    		$onFocus[] = $this->getOnFocus();
    	}
    	
    	if ($this->getOnBlur() != ""){
    		$onBlur[] = $this->getOnBlur();
    	}
    	
    	$showLookUp = false;
    	$fromList = "";
    	$detailId = "";
    	if (($this->lookUp != "" && !$this->getReadonly()) || ($this->lookUp != "" && $this->getShowLookUpEver())){
    		$showLookUp = true;
    		$lu = $this->getLookUp();
    		
    		
    		if ($this->getIdList() != ""){
	        	$fromList = ",'".$this->getIdList()."',".$this->getRowNumber();
	        }
	        
	        // Serve per le funzioni wi400_lookUp, wi400_decode, wi400_complete
	        $detailId = "detailid=\"{$this->getIdDetail()}\"";
    		
    		$onChange[] = "checkLookUp(this,'".$this->getId()."_".$lu->getId()."'".$fromList.")";
    	}
    	
    	$showTree = false;
    	if ($this->tree != ""){
    		$showTree = true;
    		$tr = $this->getTree();
    		$onChange[] = "checkTree(this,'".$tr->getId()."')";
    	}
    	
    	if ($this->getDecode()){
    		$decodeArray = $this->getDecode();
    		if (isset($decodeArray["AJAX"]) && $decodeArray["AJAX"] == true){
    			$decodeArray['LABEL']=$this->getLabel();
    			$decodeKey = base64_encode(md5(serialize($decodeArray)));
    			$decodeArray['KEYID']=$decodeKey;
    			if ($this->showMultiple && !isset($decodeArray['NODECODE'])) {
    				/*$htmlOutput = $htmlOutput."<script>";
					$htmlOutput = $htmlOutput."window[\"".$this->getId()."_DECODE_PARAMETERS\"] = \"".urlencode(serialize($decodeArray))."\"";
					$htmlOutput = $htmlOutput."</script>";
					$htmlOutput .= '<input type="hidden" id="'.$this->getId().'_DECODE_PARAMETERS" name="'.$this->getId().'_DECODE_PARAMETERS" value="'.urlencode(serialize($decodeArray)).'">';*/
    			}else if ($this->getIdList() != "" && !isset($decodeArray['NODECODE'])){
    				$onBlur[] = "wi400_list_decode(this)";
    			}else {
    				if (!isset($decodeArray['NODECODE'])) {
    					$onChange[] = "wi400_decode(this)";
    				}
    			}
    			if(!$this->getIdDetail() && $this->getStyle5250()) $this->setIdDetail("DISPOSE_5250");
    			$detailId = "detailid=\"{$this->getIdDetail()}\"";
    		}
  		
    	}
    	
    	if ($this->getIdList() != ""){
    		$onChange[] = "checkGridRow('".$this->getIdList()."',".$this->getRowNumber().",true)";
    		if ($this->getMask() != ""){
    			$htmlOutput = $htmlOutput."<script>rules[rules.length]=\"".$this->getId()."|mask|".$this->getMask()."\";</script>";
			}
			
			$onFocus[] = "overGridRow('".$this->getIdList()."',".$this->getRowNumber().")";
			$onBlur[]  = "outGridRow('".$this->getIdList()."',".$this->getRowNumber().")";
			
    	}
    	
    	if ($this->getMask() != "" && strpos($this->getMask(), "-") !== false){
    		    $acceptNegative = true;
    	}
    	
    	if (!$this->getReadonly() && in_array("REMOVE_TOOL",$this->getTools())){
    		$onChange[] = "inputAddRemove(null, '".$this->getId()."', ".($acceptNegative ? 'true':'false').")";
    	}
    	
 //   	echo "CHECK_DUP:"; var_dump($this->getCheckDuplicate()); echo "<br>";
    	
    	if($this->getShowMultiple() && (!isset($settings['automatic_field_add']) || $settings['automatic_field_add']===true) && $this->getAutomaticFieldAdd()!==false) {
    		$onChange[] = "multiFieldAddRemove('ADD','".$this->getId()."', null, ".$this->getAjaxDecode().", '".$this->getSortMultiple()."', '".$this->getCheckDuplicate()."')";
    	}
    	
    	if ($this->getOnChange() != ""){
    		$onChange[] = $this->getOnChange();
    	}
    	if ($this->getOnKeyUp() != ""){
    		$onKeyUp[] = $this->getOnKeyUp();
    	}
    	
//    	echo "ONCHANGE:<pre>"; print_r($onChange); echo "</pre>";
		$maxValues = "";
		if($this->getMaxValues() > 0) {
			$maxValues = "max='".$this->getMaxValues()."'";
		}
		
		$minValues = "";
		if($this->getMinValues() > 0) {
			$minValues = "min='".$this->getMinValues()."'";
		}
    	
    	// ONCHANGE
    	$onChangeFunction = join(";", $onChange);
    	$onChangeFunction = "onChange=\"".$onChangeFunction."\"";
		
    	// ONFOCUS
    	$onFocusFunction = join(";", $onFocus);
    	$onFocusFunction = "onFocus=\"".$onFocusFunction."\"";
    	
    	// ONBLUR
    	$onBlurFunction = join(";", $onBlur);
    	$onBlurFunction = "onBlur=\"".$onBlurFunction."\"";
    	
		// ONKEYDOWN
		$onKeyDownFunction = "";
		if($this->getOnKeyDown()) {
			$onKeyDownFunction = "onkeyDown=\"".$this->getOnKeyDown()."\"";
		}
    	// ONKEYUP
    	if ($onKeyUp!="") {
			$onKeyUpFunction = join(";", $onKeyUp);
			$onKeyUpFunction = "onKeyUp=\"".$onKeyUpFunction."\"";
    	}
		
		
    	// Ricaricamento della pagina in caso di modifica di un campo data
    	if ($this->getOnChange() && $showDate){
    		//$readonly = "readonly";
    		//$cssDisabled = "Disabled";
    	}	
    	
   		// Mi trovo all'interno di un dettaglio	
    	if ($this->getIdDetail()!="" && !$this->getStyle5250()) {
    		$htmlOutput = $htmlOutput."<td>";
    	}
    		
    	// Mi trovo all'interno di una lista
    	if ($this->getIdList()!="") {
    		$htmlOutput = $htmlOutput."<table border=0 cellpadding=0 cellspacing=0><tr><td>";
    	}

		// Tool di rimozione numerica
		if (!$this->getReadonly() && in_array("REMOVE_TOOL",$this->getTools())){
			$disabled = "";
			if (!$acceptNegative && ($this->getValue() == "" || $this->getValue() == 0)) {
				$disabled = "_disabled";
			}
			$htmlOutput = $htmlOutput."<img id=\"".$this->getId()."_REMOVE_TOOL\" class=\"wi400-pointer\" hspace=\"5\" style=\"cursor:pointer\" title=\""._t('REMOVE')."\" onClick=\"inputAddRemove('-','".$this->getId()."',".($acceptNegative ? 'true':'false').")\" src=\"".$temaDir."images/grid/remove".$disabled.".png\">";
			if ($this->getIdList()!="") {
				$htmlOutput = $htmlOutput."</td><td>";
			}
		}
	
		// AutoFocus field
		if ($this->getAutoFocus() == true){
			$htmlOutput = $htmlOutput."<script>";
			$htmlOutput = $htmlOutput."window[\"AUTO_FOCUS_FIELD_ID\"] = \"".$this->getId()."\"";
			$htmlOutput = $htmlOutput."</script>";
		}
		
		//Variabile style
		$cssStyle = "";
		
		// Tipo input
		$inputType = "text";
		$acceptFile = "";
		if ($this->getType() == "FILE") {
			$inputType = "file";
			
			// Elenco delle estensioni di file accettate
			if($this->getAcceptFile()!="") {
				$acceptFile = "accept=\"".$this->getAcceptFile()."\"";
			}
		}
		else if($this->getType() == "PASSWORD") {
			$inputType = "password";
		}else if($this->getType() == "NUMERIC") {
			$inputType = "tel";
			/*if($larghez = $this->getSizeIpad()) {
				$cssStyle .= "width: ".$larghez."px;";
			}*/
		}
		
		// Allineamento testo
		if ($this->getAlign() != ""){
			$cssStyle .= "text-align:".$this->getAlign().";";
		}

		// MaxLength
		$maxLen = "";
		if (!$formatNumber){
			$maxLen = "maxlength=\"".$this->getMaxLength()."\"";
		}
		
		//Blank value
		$blankValue = "";
		if($this->getAddBlankValue()) {
			$blankValue = "blankValue='on'";
		}
		
    	// Font Style
		$fontStyle = "";
		if($this->fontStyle!="") {
			$fontStyle = "style=\"font-size:".$this->fontSize.";font-family:".$this->fontStyle."\"";
		}
		
		if ($this->getStyleClass() != ""){
			$className = $this->getStyleClass().$cssDisabled;
		}else{
			$className = "inputtext".$cssDisabled;
		}
		
		$tabIndexAttribute = "";
		if ($this->getTabIndex() != -1){
			$tabIndexAttribute = "tabindex=\"".$this->getTabIndex()."\"";
		}
		

		if ($this->getName() == ""){
			$this->setName($this->getId());
		}
		
		$autocompleteBrowser = '';
		if($this->getAutocompleteBrowser() == false) {
			$autocompleteBrowser = 'autocomplete="off"';
		}
		
		if ($this->getType() == "TEXT_AREA"){
			if ($this->getReadonly()){
//    			$readonly = "disabled";
				$readonly = "readonly";
    		}
    		
    		$wrap = "";
    		if ($this->getWrap()) {
    			$wrap = "wrap='hard'";
    		}
    		
    		$stile_textarea = "";
    		if ($this->getStyle()) {
    			$stile_textarea = "style='".$this->getStyle()."'";
    		}
    		
    		$htmlOutput = $htmlOutput."<textarea id=\"".$this->getId()."\"  name=\"".$this->getName()."\" ".$readonly." ".$onChangeFunction." ".$onFocusFunction." ".$onBlurFunction." class=\"".$className."\" rows=\"".$this->getRows()."\" cols=\"".$this->size."\" $detailId $stile_textarea $wrap ".$fontStyle." ";
			if($this->maxLength!="") {
//				$htmlOutput .= "onkeypress=\"return maxLength(this, 300);\" onpaste=\"return maxLengthPaste(this, 300);\" ";
				$htmlOutput .= "onkeyup=\"return textLimit(this, ".$this->maxLength.");\" ";
			}
			$htmlOutput .= $this->getDisabled().">".$this->getValue()."</textarea>";
/*			
			$htmlOutput = $htmlOutput."<textarea id=\"".$this->getId()."\"  name=\"".$this->getName()."\" hidden ".$onChangeFunction." ".$onFocusFunction." ".$onBlurFunction." class=\"".$className."\" rows=\"".$this->getRows()."\" cols=\"".$this->size."\" ".$fontStyle." ";
			if($this->maxLength!="") {
//				$htmlOutput .= "onkeypress=\"return maxLength(this, 300);\" onpaste=\"return maxLengthPaste(this, 300);\" ";
				$htmlOutput .= "onkeyup=\"return textLimit(this, ".$this->maxLength.");\" ";
			}
			$htmlOutput .= $this->getDisabled().">".$this->getValue()."</textarea>";
*/			
		}else{
			if($this->selOption==true) {
/*				
				$arraySelect = array(
								 "INCLUDE"=>_t("CONTIENE"),
    				 			 "START"=>_t("INIZIA_PER"),
    							 "EQUAL"=>_t("UGUALE_A")
				);
*/
				$arraySelect = get_text_condition_array();
/*				
				if(!empty($this->options_array)) {
					$arraySelect = $this->options_array;
				}
*/				
				$htmlOutput = '<td><select class="select-field" name="'.$this->getId().'_OPTION">';
				
				foreach($arraySelect as $key => $description){
					$selected = "";
//					if ($key == $this->getOption()) 
					if ($key == $this->getSelOption_select())
						$selected = "selected";
					$htmlOutput .= '<option '.$selected.' value="'.$key.'">'.$description.'</option>';
				}
			
				$htmlOutput .= '</select>';
			}
			
			$inputValue = $this->getValue();
			
			$inputName = $this->getName();
			if ($this->showMultiple){
				//$inputName = $inputName."_ADD";
				$inputValue = "";
			}
			
			// Implementazione validazione ip
			if ($showIp4){
				$ipArray = array();
				if ($this->getValue() != ""){
					$ipArray = explode(".",$this->getValue());
				}
				for ($ipCounter = 0; $ipCounter < 4; $ipCounter ++){
					$ipValue = "";
					if ($ipCounter > 0) $htmlOutput.=" . ";
					$htmlOutput = $htmlOutput."<script>rules[rules.length]=\"".$this->getId()."_".$ipCounter."|mask|0123456789\";</script>";
					if (isset($ipArray[$ipCounter])) $ipValue = $ipArray[$ipCounter];
					$htmlOutput = $htmlOutput."<input value=\"".$ipValue."\" onBlur=\"updateIpValue('".$this->getId()."', ".$ipCounter.")\" type=\"text\" size=\"2\" maxlength=\"3\" id=\"".$this->getId()."_".$ipCounter."\">";
				}
				$inputType = "hidden";
			}
//			echo "ONCHANGE FUNCTION: $onChangeFunction<br>";
			$divWidth = "";
			if ($showDate && !$this->getReadonly() && !$this->getStyle5250()) $divWidth = "<div style=\"width: 103px;\"></div>";
			$htmlOutput = $htmlOutput."$divWidth<input ".$tabIndexAttribute." type=\"".$inputType."\" $autocompleteBrowser $acceptFile title=\"".$this->getTitle()."\" id=\"".$this->getId()."\"  name=\"".$inputName."\" ".($cssStyle ? "style=\"$cssStyle\"" : "")." ".$readonly." ".$onChangeFunction." ".$onFocusFunction." ".$onBlurFunction." ".$onKeyUpFunction." ".$onKeyDownFunction." ".$onClickFunction." ".$maxLen." $minValues $maxValues $detailId class=\"".$className."\" value=\"".htmlspecialchars($inputValue)."\" size=\"".$this->size."\" ".$this->getDisabled()." $blankValue>";
		}
		// Tool di aggiunta numerica
		if (!$this->getReadonly() && in_array("ADD_TOOL",$this->getTools())){
			if ($this->getIdList()!="") {
				$htmlOutput = $htmlOutput."</td><td>";
			}
			$htmlOutput = $htmlOutput."<img id=\"".$this->getId()."_ADD_TOOL\" hspace=\"5\" class=\"wi400-pointer\" title=\""._t('ADD')."\" onClick=\"inputAddRemove('+','".$this->getId()."',".($acceptNegative ? 'true':'false').")\" src=\"".$temaDir."images/grid/add.png\">";
		}

		if ($this->getIdDetail()!="" || $this->getIdList()!=""){
			if(!$this->getStyle5250()) {
				$htmlOutput = $htmlOutput."</td>";
			}
		}
		
        if ($this->showMultiple && !$this->getReadonly() && $this->getDisabled() != 'disabled') {

        	$htmlOutput = $htmlOutput."<td>";
        	
        	if((!isset($settings['automatic_field_add']) || $settings['automatic_field_add']===true)  && $this->getAutomaticFieldAdd()!==false && $this->getAddBlankValue() == false) {
        		$multi_field_add_func = "";
        	}
        	else {
        		$multi_field_add_func = "multiFieldAddRemove('ADD','".$this->getId()."', null, ".$this->getAjaxDecode().", '".$this->getSortMultiple()."', '".$this->getCheckDuplicate()."')";
        	}
        	
//			$htmlOutput = $htmlOutput."<img id=\"".$this->getId()."_MULTIPLE\" onClick=\"multiFieldAddRemove('ADD','".$this->getId()."', null, ".$this->getAjaxDecode().")\" hspace=\"5\" class=\"wi400-pointer\" src=\"".$temaDir."images/add.png\" title=\""._t('ADD')."\">";
        	$htmlOutput = $htmlOutput."<img id=\"".$this->getId()."_MULTIPLE\" onClick=\"".$multi_field_add_func."\" hspace=\"5\" class=\"wi400-pointer\" src=\"".$temaDir."images/add.png\" title=\""._t('ADD')."\">";
			$htmlOutput = $htmlOutput."</td>";
			if ($this->removeAll) {
				$htmlOutput = $htmlOutput."<td>";
				$multi_field_add_func = "multiFieldAddRemove('REMOVEALL','".$this->getId()."', null, ".$this->getAjaxDecode().", '".$this->getSortMultiple()."', '".$this->getCheckDuplicate()."')";
				$htmlOutput = $htmlOutput."<img id=\"".$this->getId()."_MULTIPLE\" onClick=\"".$multi_field_add_func."\" hspace=\"5\" class=\"wi400-pointer\" src=\"".$temaDir."images/remove.png\" title=\""._t('REMOVE_ALL')."\">";
				$htmlOutput = $htmlOutput."</td>";				
			}
			if ($this->sortMultiple){
//				if ($this->sortMultiple){
					$htmlOutput .= "<script>jQuery(document).ready(function(){startReorderList('".$this->getId()."');});</script>";
//				}
				//$htmlOutput = $htmlOutput."<td>";
				//$htmlOutput = $htmlOutput."<img onClick=\"startReorderList('".$this->getId()."');\" hspace=\"5\" class=\"wi400-pointer\" src=\"".$temaDir."images/grid/reorder.gif\" title=\""._t('ORDER')."\">";
				//$htmlOutput = $htmlOutput."</td>";
			}
        }
		
		// CALENDARIO
		if ($showDate && !$this->getReadonly()){
			$versioneIE = "true";
			if(preg_match('/(?i)msie [2-7]/', $_SERVER['HTTP_USER_AGENT'])) {
				$versioneIE = "false";
			}
			//$htmlOutput = $htmlOutput."<td><img id=\"".$this->getId()."_CALENDAR\" hspace=\"5\" class=\"wi400-pointer\" src=\"".$temaDir."images/calendar.gif\" title=\""._t('DATE_SELECT')."\">";
			$htmlOutput .= "<script type=\"text/javascript\">";
			
			$htmlOutput .= 'jQuery(document).ready(function() {
								jQuery.datepicker.setDefaults(jQuery.datepicker.regional[(_USER_LANG_2 == "en" ? "en-AU" : _USER_LANG_2)]);
								jQuery( "#'.$this->getId().'").datepicker({';
									if($this->isDateOfBirth) {
			$htmlOutput .=  '			yearRange: "-115:-10",
										defaultDate: "-40y",';
									}
			$htmlOutput .=  '		showOn: "button",
									buttonImage: "'.$temaDir.'images/calendar.gif",
									buttonImageOnly: true,
									buttonText: "'._t("CALENDARIO").'",
									beforeShow: createButtonYears,
									onClose: closeDatePicker,
									onChangeMonthYear: changeMonthYear,
									changeMonth: true,
	      							changeYear: true,
									showButtonPanel: '.$versioneIE.',
									dateFormat: "'.($this->getStyle5250() ? 'd/mm/y' : 'dd/mm/yy').'"
								});
								
								jQuery( ".ui-datepicker-trigger" ).css({"left":"2px", "top":"1px"}).addClass("wi400-pointer");
							});';

			$htmlOutput .= "</script>";
		}

		// LOOKUP
		if ($showLookUp){ 
	        $lu = $this->getLookUp();
	        
	        if($this->getShowMultiple() && (!isset($settings['automatic_field_add']) || $settings['automatic_field_add']===true) && $this->getAutomaticFieldAdd()!==false) {
	        	$lu->addParameter("ONCHANGE","multiFieldAddRemove('ADD','".$this->getId()."', null, ".$this->getAjaxDecode().", '".$this->getSortMultiple()."', '".$this->getCheckDuplicate()."')");
	        	// @todo SISTEMARE il multiFieldAddRemove nel lookup 
	        	/*
	        	 * nel caso in cui il lookup venga aperto dalla finestra dei filtri avanzati di una lista NON SI CHIUDE LA FINESTRA una volta selezionato un elemento
	        	 * invece da lookup aperto in pagina (e non in finestra) funziona
	        	 * 
	        	 * sembra che in wi400-core.js la funzione multiFieldAddRemove() si interrompa per un errore
	        	 * quando si fa fieldObj = document.getElementById(id); e il lookup proviene dalla finestra dei filtri avanzati
	        	 * mentre trova fieldObj = document.getElementById(id); se il lookup proviene dalla pagina
	        	 * 
	        	 */
	        }
	        
	        /*$lookUpParameters = "";
	        foreach ($lu->getParameters() as $key => $value){
	        	$lookUpParameters = $lookUpParameters."&".$key."=".$value;
	        }*/
	        
			if(!$this->getStyle5250()) $htmlOutput = $htmlOutput."<td>";
			$htmlOutput = $htmlOutput."<script>";
			$htmlOutput = $htmlOutput."addLookUpConfig('".$this->getId()."_".$lu->getId()."_LOOKUP','".$lu->getAction()."', '".join("|",$lu->getJsParameters())."', '".$this->getId()."');";
			$htmlOutput = $htmlOutput."</script>";
			$htmlOutput = $htmlOutput."<img id=\"".$this->getId()."_LOOKUP\" onClick=\"lookUp('".$this->getId()."_".$lu->getId()."'".$fromList.")\" hspace=\"5\" class=\"wi400-pointer\" src=\"".$temaDir."images/lookup.png\" title=\""._t('SEARCH')."\">";
			if(!$this->getStyle5250()) $htmlOutput = $htmlOutput."</td>";
		}
		if($this->customTools) {
			foreach($this->customTools as $key => $customTool) {
				$htmlOutput = $htmlOutput."<td><img id=\"{$this->getId()}_custom_tool_{$key}\" title=\"{$customTool->getToolTip()}\" onClick=\"{$customTool->getOnClick($this->getId())}\" hspace=\"5\" class=\"wi400-pointer\" src=\"{$customTool->getIco()}\" title=\"".$customTool->getLabel()."\" style=\"{$customTool->getStyle()}\"></td>";
			}
		}
		if ((($this->getCleanable() && !$this->getReadonly()) || $this->getForceClean()) && !$this->getShowMultiple() && $this->getType()!="FILE") {
			$htmlOutput = $htmlOutput."<td>";			
			if($this->getType()=="NUMERIC")
				$htmlOutput = $htmlOutput."<img id=\"".$this->getId()."_CLEAN\" onClick=\"cleanNumericField('".$this->getId()."')\" hspace=\"5\" class=\"wi400-pointer\" src=\"".$temaDir."images/clean.png\" title=\""._t('CLEAN')."\">";
			else
				$htmlOutput = $htmlOutput."<img id=\"".$this->getId()."_CLEAN\" onClick=\"cleanField('".$this->getId()."')\" hspace=\"5\" class=\"wi400-pointer\" src=\"".$temaDir."images/clean.png\" title=\""._t('CLEAN')."\">";
			$htmlOutput = $htmlOutput."</td>";			
		}
		if ($showTree){
			$tr = $this->getTree();
			$htmlOutput = $htmlOutput."<td>";
			$htmlOutput = $htmlOutput."<img id=\"".$this->getId()."_TREE\"  onClick='showTree(\"".$tr->getId()."\")' hspace=\"5\" class=\"wi400-pointer\" src='".$temaDir."images/tree.gif' id='".$this->getId()."_TREE' title='".$tr->getDescription()."'>";
			$htmlOutput = $htmlOutput."</td>";
		}
		
		if ($this->getQwerty()){
			$htmlOutput = $htmlOutput."<td>";
			$htmlOutput = $htmlOutput."<img  hspace=\"5\" class=\"wi400-pointer\" onclick=\"jQuery('#".$this->getId()."').getkeyboard().reveal();\" src='".$temaDir."images/qwerty.png' id='".$this->getId()."_QWERTY'>";
			$htmlOutput = $htmlOutput."<script>jQuery(function($) {";
			if ($this->getVirtualKeyboardType()!="numPad") {
				$htmlOutput = $htmlOutput."jQuery('#".$this->getId()."').keyboard({layout:'".$this->getVirtualKeyboardType()."',lockInput: false, openOn: null});";
			} else {
				$htmlOutput = $htmlOutput."jQuery('#".$this->getId()."').keyboard({
				layout: 'custom',
				customLayout: {
					'default' : [
					'1 2 3',
					'4 5 6',
					'7 8 9',
					'0 ,{bksp}',
					' {a} {c}'
							]
				},
				maxLength : 6,
				restrictInput : true, // Prevent keys not in the displayed keyboard from being typed in
				useCombos : false // don't want A+E to become a ligature
				,lockInput: false, openOn: null});";
			}	
			$htmlOutput = $htmlOutput."});</script>";
			$htmlOutput = $htmlOutput."</td>";
		}
		// Verifico se è anche un autocompletamento
		if ($this->getDecode() && !$readonly) {
			if (isset($decodeArray["COMPLETE"]) && $decodeArray["COMPLETE"] == true && isset($decodeArray['COLUMN']) && $decodeArray['COLUMN']!="") {
				if(preg_match('/(?i)msie [2-7]/',$_SERVER['HTTP_USER_AGENT'])) {
					// if IE<=6 non metto l'autocompletamento
				}
				else {
					// if IE>6
					$decodeArray2 = $decodeArray;
					if (isset($decodeArray2['COLUMN'])) {
						$decodeArray2['KEY_COLUMN']=$decodeArray2['COLUMN'];
					}
					$decodeArray2['QUERY_MASK']='%##FIELD##%';
					$decodeArray2['BYDESC']='YES';
					unset($decodeArray2['KEYID']);
					$decodeKey = base64_encode(md5(serialize($decodeArray2)));
					$decodeArray2['KEYID']=$decodeKey;
						
					$newSrcImageEffect = "themes/common/images/map/leftArrow.gif";
					//$oldSrcImageEffect = $temaDir."images/text.png";
					$oldSrcImageEffect = "themes/common/images/text.png";
					//Per verificare le checkBox
					//jQuery(\'#startBox\').is(\':checked\'), jQuery("#caseSensitiveBox").is(\':checked\')
					if($this->getDesSearch()) {
						if($this->getStyle5250()) {
							$imgT = '<i class="fa fa-text-width" id="image_effect_input_'.$this->getId().'" onclick="openCloseInputSearchStyle5250(\''.$this->getId().'\')" ></i>';
							$htmlOutput .= $imgT;
						}else {
							$imgT = '<img width="12" height="12" id="image_effect_input_'.$this->getId().'" title="Ricerca per descrizione" hspace="5" class="wi400-pointer" onclick="openCloseInputSearch(\''.$this->getId().'\')" src="'.$oldSrcImageEffect.'">';
							$htmlOutput .='<td>'.$imgT.'</td>';
						}
					}
					$custom = "";
					if (isset($decodeArray['COMPLETE_CUSTOM_HIGHLIGHT'])) {
						$custom = "*CUSTOM";
					}
					$sendRequest = "";
					foreach($decodeArray2 as $string) {
						if(!is_array($string) && strpos($string,"<@REQUEST") !== false) {
							$sendRequest = "yes";
							break;
						}
					}
					$caseDES="checked";
					if (isset($settings['ajax_complete_case_des'])) {
						$caseIS=$settings['ajax_complete_case_des'];
						if ($caseIS!=True) {
							$caseDES="";
						}
					}
					if(!$this->style5250) $htmlOutput .='<td>';
					$htmlOutput .= '<div class="toggler" style="position: relative; top: 1px;">
						  <div id="DES_'.$this->getId().'_DIV" style="display: none;"><!-- per fare il bordo al div class="ui-widget-content ui-corner-all" -->
						      &nbsp;<label>Ricerca Descrizione</label> <input id="DES_'.$this->getId().'" value=""><br/>
								<center>
									<input type="checkbox" id="DES_START_'.$this->getId().'" name="start" value="1">&nbsp;<font size="2">'._t("INIZIA_PER").'</font>
									<input type="checkbox" id="DES_CASE_'.$this->getId().'" name="caseSensitive" value="1" '.$caseDES.'>&nbsp;<font size="2">'._t("CASE_SENSITIVE").'</font>
								</center>
						  </div>
						</div>';
					if(!$this->style5250) $htmlOutput .= '</td>';
					$htmlOutput .= '<script>
						setTimeout(function() {
							wi400_complete("'.$this->getId().'", "", '.$decodeArray2["COMPLETE_MIN"].', '.$decodeArray2["COMPLETE_MAX_RESULT"].', "'.$custom.'", "'.$sendRequest.'");
							wi400_complete("'.$this->getId().'", "DES", '.$decodeArray2["COMPLETE_MIN"].', '.$decodeArray2["COMPLETE_MAX_RESULT"].', "'.$custom.'", "'.$sendRequest.'");
						}, 0);
					</script>';
				}
			}
		}	
		if ($this->getIdDetail() != "" && !$this->getStyle5250()) {
			$htmlOutput = $htmlOutput.="<td class=\"detail-message-cell\" id=\"".$this->getId()."_DESCRIPTION\">&nbsp;".$this->getDescription()."</td>";
		}
		// Lookup sul campo con F1
		if ($showLookUp){
			/*$htmlOutput.="<script>jQuery(\"#".$this->getId()."\").keypress(function(e) {
				var code = e.keyCode || e.which;
	 			if(code == 112) {
					eval(jQuery(\"img#".$this->getId()."_LOOKUP\").attr('onclick'));
				}	
			});</script>";*/
			$htmlOutput.="<script>shortcut.add('ALT+F1',function() { eval(jQuery(\"img#".$this->getId()."_LOOKUP\").attr('onclick'));}, {
							'type':'keydown',
							'propagate':false,
							'disable_in_input':false,
							'target':'".$this->getId()."',
							'keycode':false
						});</script>";
		}
		
        // Mi trovo all'interno di una lista
    	if ($this->getIdList()!="") {
    		$htmlOutput = $htmlOutput."</td></tr></table>";
    	}
    	// Verifico se è anche un autocompletamento
    	/*if ($this->getDecode()){
	    	if (isset($decodeArray["COMPLETE"]) && $decodeArray["COMPLETE"] == true){
	    		if(preg_match('/(?i)msie [2-7]/',$_SERVER['HTTP_USER_AGENT'])) {
	    			// if IE<=6 non metto l'autocompletamento
	    		}
	    		else {
	    			$custom ="";
	    			if (isset($decodeArray['COMPLETE_CUSTOM_HIGHLIGHT'])) {
	    				$custom = "*CUSTOM"; 
	    			}
	    			$htmlOutput .="<script>wi400_complete('".$this->getId()."', '', ".$decodeArray["COMPLETE_MIN"].", ".$decodeArray["COMPLETE_MAX_RESULT"].", '$custom');</script>";
	    		}
	    	}
    	}*/
    	// INIZIO TEST DROPPABILE
    	if ($this->getDroppable()==True) {
    	//echo $this->getId().var_dump($this->getDroppable());
    	$htmlOutput .='<script type="text/javascript">
    	jQuery(function (){
    		jQuery("#'.$this->getId().'").droppable({
    			drop: function( event, ui ) {
					itemCode = ui.draggable.html();
					jQuery(this).val(itemCode);
     				'.$this->getDroppableCallback().'
				}
				});
			});
			</script>';
    	}
    	
    	// TEST DROPPABILE
		return $htmlOutput;
    }
    
    public function getDispose() {
    	return $this->getHtml();
    }
    
    /**
     * Visualizzazione del campo di testo
     *
     */
    public function dispose(){
    	echo $this->getHtml();
    }
   
}
?>