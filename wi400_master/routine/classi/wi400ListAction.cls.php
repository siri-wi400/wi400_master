<?php
require_once $routine_path.'/classi/wi400Action.cls.php';
class wi400ListAction extends wi400Action {

    private $confirmMessage = "";
    private $script			= "";
    private $selection      = "MULTIPLE";
    private $parameters;
    private $target;
    private $modale = "undefined";
    private $canClose = "true";
    private $closeFunction = "undefined";
    
    private $show = true;
    
    private $width;
    private $height;
    
    private $serialize;
    
    private $idList;
    private $id;
    private $shortcutKeys="";
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
    
    public function __construct($actionName = "", $formName = "", $parameters = array()){
		$this->parameters = $parameters;
		$this->setAction($actionName);
		$this->setForm($formName);
		$this->target = "";
		$this->idList = "";
		$this->id = "";
		$this->serialize = true;
    }
    
    public function addParameter($parameterKey, $parameterValue){
    	$this->parameters[$parameterKey] = $parameterValue;
    }
    
    public function getUrlParameters(){
    	$parameterString = "";
    	foreach ($this->parameters as $parameterKey => $parameterValue){
    		$parameterString = $parameterString."&".$parameterKey."=".$parameterValue;
    	}
    	return $parameterString;
    }
    
    public function setScript($script){
    	$this->script = $script;
    }
    
    public function getScript(){
    	return $this->script;
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
	public function setTarget($target, $width = "", $height = "", $canClose = "true", $closeFunction = "undefined"){
    	$this->target = $target;
    	$this->width = $width;
    	$this->height = $height;
    	$this->canClose = $canClose ? "true" : "false";
    	$this->closeFunction = $closeFunction;
    }
    public function getCanClose(){
    	return $this->canClose;
    }
    
    /**
     * Recupero la funzione che viene eseguita nel momento in cui la finestra viene chiusa
     * 
     * @param string 
     */
    public function getCloseFunction() {
    	return $this->closeFunction;
    }
    
    public function getTarget(){
    	return $this->target;
    }
    
    /**
     * Settare se l'azione di lista deve aprire una finestra modale o meno
     * 
     * default no
     * 
     */
    public function setModale($val) {
    	$this->modale = $val ? 'true' : 'false';
    }
    
    public function getModale() {
		return $this->modale;
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
    
}

?>