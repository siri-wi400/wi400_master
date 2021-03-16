<?php

/**
 * @name wi400InputButton 
 * @desc Classe per la creazione di un bottone
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.00 22/07/2008
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

//require_once $routine_path.'/classi/wi400Input.cls.php';

class wi400InputButton extends wi400Input {

	private $action  = "";
	private $form    = "";
	private $gateway = "";
	    
	private $script     = "";
    private $validation = false;
	private $show_loading = false;
    private $type="";
    
    private $confirmMessage = "";
    
    private $target;
    private $width;
    private $height;
    private $image="";
    private $event="";
	private $closeFunction="";
	private $canClose="";
	
	private $button_class = "detail-button";
	private $button_style = "";
	
	private $parameters = array();
	private $addToContext = true;
    
    /**
	 * Costruttore della classe
	 *
	 * @param string $id	: ID del bottone da creare
	 */
    public function __construct($id){
    	$this->setId($id);
    	$this->setType("BUTTON");
    	
    	$this->target = "";
    }
    /**
     * @desc setTarget: Setto il target dell'azione PAGE o WINDOW(Apertura in finestra)
     * @param string $target  PAGE o WINDOW, default WINDOW
     * @param string $width Larghezza finestra
     * @param string $height Aletezza finestra
     * @param boolean $canClose True o False per indicare che la finestra può essere chiusa
     * @param string $closeFunction Funziona da richiamare per la chiusura della finestra (Tasto X)
     */
	public function setTarget($target, $width = "", $height = "", $canClose=True, $closeFunction="closeLookUp();"){
    	$this->target = $target;
    	$this->width = $width;
    	$this->height = $height;
		$this->canClose = $canClose;
		
		if(strpos($closeFunction, "closeLookUp") === false) {
			$closeFunction .= "closeLookUp();";
		}
		$this->closeFunction = $closeFunction;
    }
    
    public function getTarget(){
    	return $this->target;
    }
    public function setType($type) {
    	return $this->type = $type;
    }
 	public function getType() {
    	return $this->type;
    }
    public function getWidth() {
    	return $this->width;
    }
    public function getHeight() {
    	return $this->height;
    }
    public function setImage($image) {
    	return $this->image = $image;
    }
 	public function getImage() {
    	return $this->image;
    }
    public function setEvent($event) {
    	return $this->event = $event;
    }
 	public function getEvent() {
    	return $this->event;
    }       
    /**
	 * Associazione di uno script al bottone
	 *
	 * @param string $script	: script da associare al bottone
	 */
    public function setScript($script){
    	$this->script = $script;
    }
    
    /**
     * Recupero dello script associato al bottone
     *
     * @return string
     */
    public function getScript(){
    	return $this->script;
    }
    
    /**
     * @desc :Impostazione del controllo della validazione dei dati del form a cui è associato il bottone (True, False, GLOBAL)
     *
     * @param mixed $which	: impostato a true per eseguire il controllo della validazione, false altrimenti. 
     *            E' previsto il valore speciale GLOBAL per controllare tutti gli errori, anche a livello di liste
     */
    public function setValidation($which){
    	$this->validation = $which;
    }
    
    /**
     * Recupero dello stato del controllo della validazione
     *
     * @return boolean	Se ritorna true esegue il controllo della validazione, false altrimenti 
     */
    public function getValidation(){
    	return $this->validation;
    }
    
	
	    public function setShowLoading($which){
    	$this->show_loading = $which;
    }
	 public function getShowLoading(){
    	return $this->show_loading;
    }
    /**
     * Aggiunta di un messaggio da visualizzare una volta premuto il bottone 
     * per chiedere la conferma della decisione di eseguire l'azione associata al bottone
     *
     * @param stirng $cm	: messaggio di conferma
     */
    public function setConfirmMessage($cm){
    	$this->confirmMessage = $cm;
    }
    
    /**
     * Recupero del messaggio di conferma associato al bottone
     *
     * @return string
     */
    public function getConfirmMessage(){
    	return $this->confirmMessage;
    }
    
    /**
     * Impostazione del gateway da associare all'azione legata al bottone
     *
     * @param string $gateway	: nome del gateway
     */
    public function setGateway($gateway){
    	$this->gateway = $gateway;
    }
    
    /**
     * Recupero del nome del gateway associato all'azione legata al bottone
     *
     * @return string
     */
    public function getGateway(){
    	return $this->gateway;
    }
    
    /**
     * Impostazione del form dell'azione legata al bottone
     *
     * @param string $form	: nome del form dell'azione
     */
    public function setForm($form){
    	$this->form = $form;
    }
    
    /**
     * Recupero del form dell'azione legata al bottone
     *
     * @return string
     */
    public function getForm(){
    	return $this->form;
    }
    
    /**
     * Impostazione dell'azione legata al bottone
     *
     * @param string $action	: nome dell'azione legata al bottone
     */
    public function setAction($action){
    	$this->action = $action;
    }
    
    /**
     * Recupero dell'azione legata al bottone
     *
     * @return string
     */
    public function getAction(){
    	return $this->action;
    }
    
    /**
     * Impostazione dello stile CSS del bottone
     * 
     * @param string $class		: id dello stile CSS del bottone
     */
    public function setButtonClass($class) {
    	$this->button_class = $class;
     }
    
     /**
      * Recupero lo stile CSS del bottone
      * 
      * @return string
      */
    public function getButtonClass() {
    	return $this->button_class;
    }
    
    /**
     * Impostazione del tag di style del bottone
     * 
     * @param string $style		: contenuto del tag style del bottone
     */
    public function setButtonStyle($style) {
   		$this->button_style = $style;
    }
    
    /**
     * Recupero del tag di style del bottone
     * 
     * @return string
     */
    public function getButtonStyle() {
    	return $this->button_style;
    }
    
    public function addParameter($parameterKey, $parameterValue){
    	$this->parameters[$parameterKey] = $parameterValue;
    }
    
    public function getParameters(){
    	return $this->parameters;
    }
    
    /**
     * Recupero dei parametri da passare nell'url
     * @return multitype:
     */
    public function getUrlParameters(){
    	$parameterString = "";
    	foreach ($this->parameters as $parameterKey => $parameterValue){
    		$parameterString = $parameterString."&".$parameterKey."=".$parameterValue;
    	}
    	return $parameterString;
    }
    
    /**
     * Impostazione del parametro addToContext che indica se aggiungere l'azione alla lista che si apre con il tasto destro del mouse
     * 
     * @return boolean
     */
    public function setAddToContext($addToContext) {
    	$this->addToContext = $addToContext;
    }
    
    /**
     * Recupero del parametro addToContext che indica se aggiungere l'azione alla lista che si apre con il tasto destro del mouse
     * 
     * @return boolean
     */
    public function getAddToContext() {
    	return $this->addToContext;
    }
    
    /**
     * Recupero della funzione onClick da applicare al bottone
     * 
     * @return mixed
     */
    public function getOnClickFunction() {
    	$checkValidation = "false";
    	if ($this->getValidation() === "SERVER"){
    		$checkValidation = "\"SERVER\"";
    	}else if ($this->getValidation() === "GLOBAL"){
    			$checkValidation = "\"GLOBAL\"";
    	}else if ($this->getValidation()){
    		$checkValidation = "true";
    	}
    	 
    	$message = "";
    	if ($this->getConfirmMessage()!=""){
    		$message = addslashes($this->getConfirmMessage());
    	}
    	 
    	// Aggiunta gateway
    	$gatewayUrl = "";
    	if ($this->gateway != ""){
    		$gatewayUrl = "&g=".$this->gateway;
    	}
    	
    	$onClick = '';
    	$showLoading = 'true';
    	$close = 'true';
    	if (!$this->canClose) {
    		$close = 'false';
    	}
    	
    	$params = "";
    	$array_params = $this->getParameters();
    	if(!empty($array_params)) {
    		$params = $this->getUrlParameters();
    	}
    	
    	if ($this->getShowLoading()) $showLoading = 'true';
    	
    	if (in_array($this->target, array("WINDOW", "TAB"))) {
    		//    		$onClick = 'openWindow(_APP_BASE + APP_SCRIPT + "?t='.$this->action.$gatewayUrl.'&f='.$this->form.'&" + $(APP_FORM).serialize(), "buttonAction", "'.$this->width.'", "'.$this->height.'");';
    		//$onClick = 'openWindow(_APP_BASE + APP_SCRIPT + "?t='.$this->action.$gatewayUrl.$params.'&f='.$this->form.'&" + jQuery("#"+APP_FORM).serialize(), "buttonAction", "'.$this->width.'", "'.$this->height.'", true, '.$close.', '.$checkValidation.', "'.$this->closeFunction.'");';
    		$clickUrl = '_APP_BASE + APP_SCRIPT + "?t='.$this->action.$gatewayUrl.$params.'&f='.$this->form.'"';
    		if($this->target == 'WINDOW') {
    			$onClick = 'openWindow('.$clickUrl.', "buttonAction", "'.$this->width.'", "'.$this->height.'", true, '.$close.', '.$checkValidation.', "'.$this->closeFunction.'",  jQuery("#"+APP_FORM).serialize());';
    		}else {
    			$onClick = 'window.open('.$clickUrl.')';
    		}
    	}else{
    		$onClick = 'doSubmit("'.$this->action.$gatewayUrl.$params.'","'.$this->form.'", '.$checkValidation.', '.$this->getCheckUpdateText().', "'.$message.'", '.$showLoading.')';
    	}
    	 
    	if ($this->getScript()!=""){
    		$onClick = $this->getScript();
    	}
    	
    	// Correzione apici
    	$onClick = str_replace("'", "\"", $onClick);
    	
    	return $onClick;
    }
    
    /**
     * Visualizzazione del bottone
     *
     * @param boolean $addToContext	: impostato a true se si vuole aggiungere il bottone al menu contestuale, false altrimenti
     */
    public function getHtml(){
    	global $menuContext;
    	
    	if(!$this->getOnClick()) { 
    		$onClick = $this->getOnClickFunction();
    	}else {
    		$onClick = $this->getOnClick();
    	}
    	
    	//echo $this->getOnClick()."<br/>";
    	
    	if ($this->getAddToContext() === true){
	    	// Aggiunta azione a menu contestuale
	    	$listAction = new wi400ListAction();
	    	$listAction->setLabel($this->getLabel());
	    	$listAction->setScript(str_replace("\"", "'", $onClick));
	    	$menuContext->addAction($listAction, "BUTTONS");
    	}
    	
    	$disabled = "";
    	if ($this->getDisabled()){
    		$disabled = "disabled";
    	}
    	
    	// Tipo Botton
    	if ($this->getType()=="") {
	    	$type = "submit";
	    	if ($this->getScript() != "" && $this->getAction() == ""){
	    		$type = "button";
	    	}
    	} else {
    		$type = $this->getType();
    	}
    	
        // Se tipo immagine carico l'immagine
        $img ="";
        if ($type=='image') {
        	$img = " src='".$this->getImage()."' ";
        }
        
        $event="";
        if ($this->getEvent()!="") {
        	$event = $this->getEvent();
        }
        
        $style = "";
        if($this->getButtonStyle()!="") {
        	$style = " style='".$this->getButtonStyle()."' ";
        }

		$html = "<input id='".$this->getId()."' name='".$this->getId()."' $disabled class='".$this->getButtonClass()." ".$this->getStyleClass()."' type='".$type."' onClick='".$onClick."' value='".str_replace("'", "&apos;", $this->getLabel())."' $img $event $style>";
		
		return $html;
    }
    
    public function dispose() {
    	echo $this->getHtml();
    }
    
}

?>