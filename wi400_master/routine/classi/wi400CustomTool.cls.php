<?php
require_once $routine_path.'/classi/wi400Action.cls.php';
class wi400CustomTool extends wi400Action {

    private $confirmMessage = "";
    private $script			= "";
    private $selection      = "MULTIPLE";
    private $parameters;
    private $returnParameter = false;
    private $jsParameters = array();
    private $passFormParameters = false;
    private $target;
    private $campo;
    private $toolTip = "";
    private $validation = false;
    
    private $show = true;
    
    private $width = "";
    private $height = "";
    
    private $serialize;
    
    private $idList;
    private $id;
    private $shortcutKeys="";
    private $style="";
    
    public function __construct($actionName = "", $formName = "", $parameters = array()){
    	$this->parameters = $parameters;
    	$this->setAction($actionName);
    	$this->setForm($formName);
    	$this->target = "";
    	$this->idList = "";
    	$this->id = "";
    	$this->serialize = true;
    }
    
    /**
     * setShortcutKeys() Setta la scorciatoia con i caratteri per lanciare l'azione di lista
     * @param string $shortcutKeys Stringa scorciatoia es ."Alt+1"
     */
    public function setShortcutKeys($shortcutKeys){
    	$this->shortcutKeys = $shortcutKeys;
    }
    /**
     * getShortcutKeys() Ritorna la sequenza scorciatoia associata all'azione di lista
     * @return string Scorciatoia
     */
    public function getShortcutKeys(){
    	return $this->shortcutKeys;
    }    
    public function setIdList($idList){
    	$this->idList = $idList;
    }
    
    public function getIdList(){
    	return $this->idList;
    }
    
    /**
     * Setta l'attributo "title" nel tag img
     *
     * @param string $toolTip
     */
    public function setToolTip($toolTip){
    	$this->toolTip = $toolTip;
    }
    
    /**
     * Ritorna il valore che viene passato all'attributo title nel tag img
     * 
     * @return string
     */
    public function getToolTip(){
    	return $this->toolTip;
    }
    
    /**
     * Passare true se la funzione ritorna un valore
     * 
     * @param boolean 
     */
    public function setReturnParameter($val) {
		$this->returnParameter = $val;
	}

    
    /**
     * Ritorna true se il customTool ritorna un valore altrimenti false
     * 
     * @return boolean
     */
    public function getReturnParameter() {
		return $this->returnParameter;
    }
    
    /**
     * true se l'azione richiede di passare per il validation
     * altrimenti false
     *
     * @param boolean $val
     */
    public function setValidation($val){
    	$this->validation = $val;
    }
    
    /**
     * Ritorna true se l'azione passa il validation
     *
     * @return boolean
     */
    public function getValidation(){
    	return $this->validation;
    }
    
    /**
     * L'id del campo a cui passare il valore di ritorno
     * 
     * @param string $id_campo
     */
    public function setCampo($id_campo) {
    	$this->campo = $id_campo;
    }
    
    /**
     * Ritorna l'id del campo a cui viene passato il valore di ritorno
     * 
     * @return string
     */
    public function getCampo() {
    	return $this->campo;
    }
    
    public function addParameter($parameterKey, $parameterValue){
    	$this->parameters[$parameterKey] = $parameterValue;
    }
    
    public function getUrlParameters(){
    	$parameterString = "";
    	foreach ($this->parameters as $parameterKey => $parameterValue){
    		$parameterString .= "&".$parameterKey."=".$parameterValue;
    	}
    	return $parameterString;
    }
    
    public function addJsParameter($par) {
		$this->jsParameters[] = $par;
	}
	
	public function getJsParameters() {
		return $this->jsParameters;
	}

    
    public function setScript($script){
    	$this->script = $script;
    }
    
    public function getScript(){
    	return $this->script;
    }
    
    public function getOnClick($inputId) {
    	if($this->getScript()) {
    		$script = $this->getScript();
    	}else {
    		$campo = "";
    		if($this->returnParameter) $campo = "&CAMPO=".$inputId;
    		if($this->campo) $campo = "&CAMPO=".$this->campo;
    		$bigData = $this->passFormParameters ? "true" : "undefined";
    		$script = "customTool('{$this->getAction()}', '{$this->getForm()}{$this->getUrlParameters()}', '$campo', '".implode("|", $this->jsParameters)."', '{$this->width}', '{$this->height}', ".($this->validation ? "true" : "false").", '{$this->getGateway()}', $bigData)";
    		//$script = "openWindow(_APP_BASE + APP_SCRIPT + '?t={$this->getAction()}&f={$this->getForm()}$campo', 'customTool', '{$this->width}', '{$this->height}', true, true, false, 'closeLookUp()');";
    	}
    	
    	return $script;
	}

    
    /**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param field_type $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/*
		Serialize form data in WINDOW target mode. Settare a False se vengono trasmessi troppi campi dal video precednete (Campi Input)
		Se false non vengono passati in $_GET i dati di input. Explorer ha una lunghezza massima di 2000 byte
 	*/
    public function setSerialize($serialize){
    	$this->serialize = $serialize;
    }
    
    public function getSerialize(){
    	return $this->serialize;
    }
    
    /*
		target action default PAGE
		VALUES: PAGE, WINDOW, AJAX
 	*/
	public function setTarget($target, $width = "", $height = ""){
    	$this->target = $target;
    	$this->width = $width;
    	$this->height = $height;
    }
    
    public function getTarget(){
    	return $this->target;
    }
    
    public function getWidth(){
    	return $this->width;
    }
    
    public function getHeight(){
    	return $this->height;
    }
    
    public function setConfirmMessage($cm){
    	$this->confirmMessage = $cm;
    }
    
    public function getConfirmMessage(){
    	return $this->confirmMessage;
    }

    public function setSelection($selection){
    	$this->selection = $selection;
    }
    
    public function getSelection(){
    	return $this->selection;
    }
    
    public function setShow($show) {
    	$this->show = $show;
    }
    
    public function getShow() {
    	return $this->show;
    }
    
    /**
     * @return the $style
     */
    public function getStyle() {
    	return $this->style;
    }
    
    /**
     * Setta lo stile del customTool
     * 
     * @param field_type $style
     */
    public function setStyle($style) {
    	$this->style = $style;
    }
    
    /**
     * true: passa in request tutti i parametri del form
     * false: no
     * 
     * @param boolean $val
     */
    public function setPassFormParameter($val) {
    	$this->passFormParameters = $val;
    }
    
    /**
     * Ritorna il valore di passFormParameters
     * true: passa in request tutti i parametri del form
     * false: no
     *
     * @return boolean passFormParameters
     */
    public function getPassFormParameter() {
    	return $this->passFormParameters;
    }
}

?>