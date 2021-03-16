<?php

/**
 * @name wi400Input 
 * @desc Classe per la creazione di elementi di input (bottoni, campi, ecc.)
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 21/07/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Input {

	private $id;
	private $name;
	private $label;
	private $type;
	private $list;
	private $value;
	private $source;
	private $accept_file = "";
    
    private $readonly;
    
    private $styleClass = "";
    private $style = "";
    
    // Validazioni ed informazioni
    private $validations = array();
    private $info = "";
    private $mask = "";
    private $onChange = "";
    private $onClick = "";
    private $onBlur = "";
    private $onFocus = "";
    private $onKeyDown = "";
    private $onKeyUp="";
    
    private $fromArray = array();
    
    // Specializzazioni
    private $idDetail;
    private $idList;
    private $rowNumber = 0;
    
    // Strumenti decodifica
    private $decode = "";
    
    private $tabIndex = -1;
    private $autoFocus = false;
    
    private $disabled = false;
    
    private $saveFile = true;
    private $saveSession = true;
    
    private $checkUpdate = false;
    
    private $title = "";
    
    private $automatic_field_add = "";
    
    private $customHTML="";
    private $height;
    private $idTab;
    private $toolTip="";
    private $forceLabel=False;
    
    /**
	 * @return the $toolTip
	 */
	public function getToolTip() {
		return $this->toolTip;
	}

	/**
	 * @param field_type $toolTip
	 */
	public function setToolTip($toolTip) {
		$this->toolTip = $toolTip;
	}

	/**
     * @desc getHeight() : Setta l'eventuale altezza del campo (Solo Custom HTML)
     * @param string: height Altezza della cella
     */
    public function getHeight() {
    	return $this->height;
    }
    public function setHeight($height) {
    	$this->height = $height;
    }   
    /**
	 * @return the $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param field_type $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return the $style
	 */
	public function getStyle($id=null,$key=null) {
		return $this->style;
	}

	/**
	 * @param field_type $style
	 */
	public function setStyle($style, $key=null, $value=null) {
		$this->style = $style;
	}

	public function setSaveFile($saveFile){
    	$this->saveFile = $saveFile;
    }
    
    public function getSaveFile(){
    	return $this->saveFile;
    }
    /**
     * Settare se il campo sarà salvato in sessione. 
     * @param boolean $saveSession
     */
	public function setSaveSession($saveSession){
    	$this->saveSession = $saveSession;
    }
    
    public function getSaveSession(){
    	return $this->saveSession;
    }
    
    public function getTabIndex(){
    	return $this->tabIndex;
    }
    /**
     * @desc setIdTab: Setta l'id del TAB per il campo
     * @param string $idTab: Codice del tab
     */
    public function setIdTab($idTab) {
    	$this->idTab=$idTab;
    }
    /**
     * @desc getIdTab(): Recupera l'id del tab dove compare il campo
     * @return string idTab: Codice del tab
     */
    public function getIdTab() {
    	return $this->idTab;
    }

    public function setTabIndex($tabIndex = -1){
    	if ($tabIndex == -1){
			global $tab_index, $idNumList;
			if($tab_index == 0 && $idNumList) $tab_index = intval($idNumList."0000");
			$tab_index = $tab_index + 1;
			$this->tabIndex = $tab_index;		
    	}else{
    		$this->tabIndex = $tabIndex;	
    	}
    }
    
    public function setAutoFocus($autoFocus){
    	$this->autoFocus = $autoFocus;
    }
    
    public function getAutoFocus(){
    	return $this->autoFocus;
    }
    
    public function setDisabled($disabled){
    	$this->disabled = $disabled;
    }
    
    public function getDisabled(){
    	if ($this->disabled === true){
    		return "disabled";
    	}
    }
    
    public function setDecode($decode){
    	$this->decode = $decode;
    }
    
    public function getDecode(){
    	return $this->decode;
    }
    
    public function setOnClick($onClick){
    	$this->onClick = $onClick;
    }
    
    public function getOnClick(){
    	return $this->onClick;
    }
    
    public function setIdDetail($idDetail){
    	$this->idDetail = $idDetail;
    }
    
	public function getIdDetail(){
    	return $this->idDetail;
    }
    
    public function setIdList($idList){
    	$this->idList = $idList;
    }

	public function getIdList(){
    	return $this->idList;
    }
    /**
     * @desc setChekcUpdate() Imposta il messaggio di warning su eventuali modifiche presenti sula pagina alla pressione del bottone
     * @param string $checkUpdate true o false per abilitare, default false. Attenzione che è stringa passare valori in minuscolo
     */
    public function setCheckUpdate($checkUpdate){
    	$this->checkUpdate = $checkUpdate;
    }

	public function getCheckUpdate(){
    	return $this->checkUpdate;
    }
    
	public function getCheckUpdateText(){
    	if ($this->getCheckUpdate() === true || strtolower($this->getCheckUpdate()) === "true"){
    		return "true";
    	}else if ($this->getCheckUpdate() === false || strtolower($this->getCheckUpdate()) === "false"){
    		return "false";
    	}else{
    		return $this->getCheckUpdate();	
    	}
    }
    /**
     * @Desc setCustomHTML() Passa l'HTML da utilizzare per visualizzare il campo
     * @param string $customHTML
     */
    public function setCustomHTML($customHTML){
    	$this->customHTML =$customHTML;
    }
    public function getCustomHTML(){
    	return $this->customHTML;
    }
    public function setAutomaticFieldAdd($automatic) {
    	$this->automatic_field_add = $automatic;
    }
    
    public function getAutomaticFieldAdd() {
    	return $this->automatic_field_add;
    }
    
    public function setOnChange($onChange){
    	$this->onChange = $onChange;
/*    	
    	if (in_array("date", $this->validations)) {
    		// Se presente validazione di tipo data
    		$onChange = str_replace("()","(this)", $onChange);
    		$this->addValidation("custom|doOnChange(this,'".$onChange."')");
    	}
*/
    }
    
	public function getOnChange(){
    	return $this->onChange;
    }
    
    public function setOnFocus($onFocus){
    	$this->onFocus = $onFocus;
    }
    
    public function getOnFocus(){
    	return $this->onFocus;
    }
    
    public function setOnBlur($onBlur){
    	$this->onBlur = $onBlur;
    }
    
    public function getOnBlur(){
    	return $this->onBlur;
    }

    public function setOnKeyUp($onKeyUp){
    	$this->onKeyUp = $onKeyUp;
    }
    
    public function getOnKeyUp(){
    	return $this->onKeyUp;
    }
    /**
     * Setta l'evento onKeyDown con la stringa passata
     * 
     * @param string
     */
    public function setOnKeyDown($onKeyDown) {
		$this->onKeyDown = $onKeyDown;
	}
    
    /**
     * Ritorna la funzione dell'evento onKeyDown
     * 
     * @param void
     * @return string
     */
    public function getOnKeyDown() {
		return $this->onKeyDown;
    }
    
    public function setRowNumber($rowNumber){
    	$this->rowNumber = $rowNumber;
    }

	public function getRowNumber(){
    	return $this->rowNumber;
    }
    
    public function setMask($mask){
    	$this->mask = $mask;
    }

	public function getMask(){
    	return $this->mask;
    }
    
    public function setId($id){
    	$this->id = $id;
    }

	public function getId(){
    	return $this->id;
    }
    
    public function setName($name){
    	$this->name = $name;
    }
    
    public function getName(){
    	return $this->name;
    }
    
	public function setInfo($info){
    	$this->info = $info;
    }
    
    public function getInfo(){
    	return $this->info;
    }   
    
    public function setLabel($label){
    	$this->label = $label;
    }
    public function setForceLabel($forceLabel) {
    	$this->forceLabel=$forceLabel;
    }
    public function getForceLabel() {
    	return $this->forceLabel;
    }
    public function getLabel(){
    	return $this->label;
    }    
    
    public function setType($type){
    	$this->type = $type;
    }
    
    public function getType(){
    	return $this->type;
    } 

    /**
     * @desc Funzione per l'indicazione delle estenzioni di file accettate dal tool di acquisizione di files
     * L'elenco dei formati da accettare deve essere scritto come una stringa con i diversi elementi separati da ,
     * I singoli formati inclusi nella stringa devono avere un . davanti al nome del formato
     * (es: ".xls, .pdf")
     * 
     * @param string $accept
     */
    public function setAcceptFile($accept){
    	$this->accept_file = $accept;
    }
    
    /**
     * @desc Recupero delle estensioni di file accettate dal tool di acquisizione di files
     * 
     * @return La stringa delle estensioni di file (scritte con il . davanti ogni estensione) separate da ,
     */
    public function getAcceptFile(){
    	return $this->accept_file;
    }
    
    public function setValue($value=""){
    	if (isset($value)) {
    		$this->value = $value;
    	}
    }
    
    public function getValue(){
    	return $this->value;
    }  

    public function addValidation($validation){
    	$this->validations[] = $validation;
    }
    /**
     * @desc Rimozione di una regola di validazione settata
     * @param unknown $validation
     */
    
    public function removeValidation($validation)  {
    	foreach ($this->validations as $key => $value) {
    		  if ($value==$validation) {
    		  	unset($this->validations[$key]);
    		  }
    	}
    }
    public function setValidations($validations){
    	$this->validations = $validations;
    }    
    
    public function getValidations(){
    	return $this->validations;
    }    
    
    public function setReadonly($readonly, $cleanable=false){
    	$this->readonly = $readonly;
    }
    
    public function getReadonly(){
    	return $this->readonly;
    }

    public function setStyleClass($styleClass){
    	$this->styleClass = $styleClass;
    }
    
    public function getStyleClass(){
    	return $this->styleClass;
    }
    
    public function setSource($source){
    	if (isset($source)) {
    		$this->source = $source;
    	}
    }
    
    public function getSource(){
    	return $this->source;
    }
    
    public function setFromArray($resultArray){
    	if (isset($resultArray[$this->getId()])){
    		$this->fromArray = $resultArray[$this->getId()];
    	}
    }
    
	public function getFromArray(){
    	return $this->fromArray;
    }
    
}
?>