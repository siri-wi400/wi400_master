<?php

/**
 * @name wi400Action 
 * @desc Classe per la gestione delle azioni
 * @copyright S.I.R.I. Informatica s.r.l.
 * @author 
 * @version 1.01 08/09/2009
 * @link www.siri.informatica.it
 * @info info@siri-informatica.it
 */

class wi400Action {

	private $module;

    private $action;  // t=
    private $form;    // f=
    private $gateway; // g=
    private $validation = "false";
        
    private $label;
    private $url;

    private $model;
    private $view;
    private $help;
    
    private $type;
    private $ico;
    private $confirmMessage = "";
        
    // Navigation controller
    private $next = array();
    private $error;
    private $success;
    
	// Ricarica la pagina automaticamente
	private $timer = 0;

	// Azione da richiamare dopo il redirect (POST ACTION
	private $postAction ="";
	/**
	 * @desc getPostAction: Recupera Azione da richiamare dopo avere rieseguito il model ed il view classoci
	 * @param unknown_type $timer
	 */
	public function getPostAction(){
		return $this->postAction;
	}
	/**
	 * @desc setPostAction: Setta Azione da richiamare dopo avere rieseguito il model ed il view classoci
	 * @param unknown_type $timer
	 */
	public function setPostAction($postAction){
		$this->postAction = $postAction;
	}
	
	public function getTimer(){
    	return $this->timer;
    }
    
    public function setTimer($timer){
    	$this->timer = $timer; 
    }
    
    public function getHelp($param) {
    	return $this->help[$param];
	}

	public function setHelp($url = false, $width = false, $height = false) {
		if($url) 	$this->help['url'] 	  = $url;
		if($width) 	$this->help['width']  = $width;
		if($height) $this->help['height'] = $height;
	}

	/**
	 * Costruttore della classe
	 *
	 * @param string $actionName	: ID dell'azione da creare
	 */
	public function __construct($actionName = ""){
		global $settings;
		
    	$this->action = $actionName;
    	$this->form   = "DEFAULT";
    	$this->gateway   = "";
    	
    	$this->model = "";
     	$this->module = "announce";
    	$this->label  = _t("WELCOME");
    	$this->view = "announce_view.php";
    	$this->type = "";
    	$this->ico = "";
    	
    	$this->help = array();
    	if (isset($settings["wi400_help"])){
    		$this->help = $settings["wi400_help"];
    	}else{
    		$this->help = array('url' => '',
								'width' => 900,
								'height' => 500);
    	}
    	
	    $this->next = array();
	    $this->error = array();
	    $this->success = array();
    }
    
    /**
	 * Azione da eseguire in caso di severity di tipo "ERROR"
	 *
	 * @param string $a	: ID dell'azione da eseguire
	 * @param string $f	: ID del form dell'azione da eseguire
	 * @param string $s	: 
	 * @param string $g : ID del gateway
	 */
    public function onError($a,$f="",$s="",$g="", $redirect = false, $isFromHistory=true){
    	$this->error = array($a,$f,$s,$g,$redirect,$isFromHistory);
    }
    
    /**
	 * Azione da eseguire in caso di severity di tipo "SUCCESS"
	 *
	 * @param string $a	: ID dell'azione da eseguire
	 * @param string $f	: ID del form dell'azione da eseguire
	 * @param string $s	: 
	 * @param string $g : ID del gateway
	 */
    public function onSuccess($a,$f="",$s="",$g="", $isFromHistory=true){
    	$this->success = array($a,$f,$s,$g,$isFromHistory);
    }
    
    /**
	 * Azione da eseguire
	 *
	 * @param string $a	: ID dell'azione da eseguire
	 * @param string $f	: ID del form dell'azione da eseguire
	 * @param string $g	: ID del Gateway
	 * @param boolean $now	: indica se eseguire subito il redirect
	 * @param boolean $request	: indica se deve essere passata all'azione successiva tutta la request
	 */
    public function gotoAction($a,$f="",$g="", $now = false, $request= true, $isFromHistory=true){
    	global $appBase;
    	$this->next = array($a,$f,$g);
    	if ($now){
    		$nextUrl =  $appBase."index.php?t=".$a;
    		if ($f != ""){
    			$nextUrl.= "&f=".$f;
    		}
    		if ($g != ""){
    			$nextUrl.= "&g=".$g;
    		}
    		if ($this->postAction!="") {
    			$nextUrl .="&postAction=".urlencode(serialize($this->postAction));
    		}
    		if (isset($_REQUEST['DECORATION'])) {
    			$nextUrl.="&DECORATION=".$_REQUEST['DECORATION'];
    		}
    		
    		if($isFromHistory===true) {
    			$nextUrl .= "&HST_NAV=true";
    			unset($_REQUEST["LCK_DLT"]);
    		}
    		
    		$nextUrl2="";
    	if ($request){
//	    	$nextUrl2="";
	    	foreach($_REQUEST as $key => $value) {
	             if (strpos($nextUrl, $key."=")===False) {
			             	if (is_array($value)) {
	//		             		$value = implode(",",$value);
			             		$value = serialize($value);
			             	}
	               			$nextUrl2.="&$key=".urlencode($value);
	           		}
	       		}
	   		}
	   		if (strlen($nextUrl2)>1500) {
	   				//$do = parse_str($nextUrl2, $datiPost);
					$unique_id = serializeAndGetUinqueID($_REQUEST);
					$nextUrl2="&ID_FILE=".$unique_id;
	   		}
	   		$nextUrl .=$nextUrl2;
 //   		echo "NEXT URL: $nextUrl<br>";
			//header("Location:".$nextUrl);
			goHeader($nextUrl);
			exit();
    	}
    }

    public function checkPost($a,$f=""){
   		/*global $messageContext;
   		if (!isset($_POST) || sizeof($_POST) == 0){
   			$messageContext->addMessage("ERROR", "Azione non consentita! Operazione annullata.");
			$this->onError($a,$f);
			$this->getNext("ERROR","","");
   		}*/
    }
    
    public function getNext($severity=0,$currentAction="",$currentForm="DEFAULT"){
    	global $appBase;
    	    	
    	$nextAction = array();
		$nextUrl = "";
		//header("Location:".$appBase.$nextAction);
		if (sizeof($this->next)>0){
			$this->form = $this->next[1];
		}
		else{
			if ($severity == "ERROR"){
				// Errori
				if (sizeof($this->error)>0){
					//OnError definitio
					$nextAction = $this->error;
					if ($nextAction[0] == $currentAction && !$nextAction[4]){
						$this->form = $nextAction[1];
					}
					else{
						$nextUrl = $appBase."index.php?t=".$nextAction[0]."&f=".$nextAction[1];
						if(sizeof($this->error)>3 && $nextAction[3]!="")
							$nextUrl .= "&g=".$nextAction[3];
						if(isset($nextAction[5]))
							$nextUrl .= "&HST_NAV=true";
						if ($this->postAction!="") {
							$nextUrl .="&postAction=".urlencode(serialize($this->postAction));
						}
						if (isset($_REQUEST['DECORATION'])) {
							$nextUrl.="&DECORATION=".$_REQUEST['DECORATION'];
						}	
//						echo "ON ERROR - NEXT URL: $nextUrl<br>";
						//header("Location:".$nextUrl);
						goHeader($nextUrl);
						exit();
					}
				}
			}
			else{
				// Nessun errore
				if (sizeof($this->success)>0){
					//OnSuccess definito
					$nextAction = $this->success;
					$nextUrl =  $appBase."index.php?t=".$nextAction[0]."&f=".$nextAction[1];
					if(sizeof($this->success)>3 && $nextAction[3]!="")
						$nextUrl .= "&g=".$nextAction[3];
					if(isset($nextAction[4]))
						$nextUrl .= "&HST_NAV=true";
					if ($this->postAction!="") {
						$nextUrl .="&postAction=".urlencode(serialize($this->postAction));
					}
					if (isset($_REQUEST['DECORATION'])) {
						$nextUrl.="&DECORATION=".$_REQUEST['DECORATION'];
					}
					//header("Location:".$nextUrl);
					goHeader($nextUrl);
					exit();
				}
			}
		}
    }
    
    public function setConfirmMessage($cm){
    	$this->confirmMessage = $cm;
    }
    
    public function getConfirmMessage(){
    	return $this->confirmMessage;
    }

	public function setIco($ico){
		$this->ico = $ico;
    }
    
    public function getIco(){
    	return $this->ico;
    }
    
	public function setType($type){
    	$this->type = $type;
    }
    
    public function getType(){
    	return $this->type;
    }
    
	public function setView($view, $module = ""){
		if ($module != ""){
			$this->module = $module;
		}
    	$this->view = $view;
    }
    
    public function getView(){
    	return $this->view;
    }
    
    /**
	 * Impostazione del titolo dell'azione da eseguire
	 * 
	 * @param string $label	: il titolo dell'azione
	 */
    public function setLabel($label){
    	$this->label = $label;
    }
    
    /**
	 * Ritorna il titolo dell'azione corrente
	 * 
	 * @return string
	 */
    public function getLabel(){
    	return $this->label;
    }
    
    public function getModelUrl(){
		return trim("modules/".$this->module."/".$this->model);
    }
    
    public function getViewUrl(){
     	return trim("modules/".$this->module."/".$this->view);
    }
    
    public function getValidationUrl($validationFile){
     	return trim("modules/".$this->module."/".$validationFile);
    }
    
    /**
     * Recupero dell'indirizzo del file di gateway
     *
     * @param string $gatwayFile
     * 
     * @return string
     */
    public function getGatewayUrl($gatwayFile){
     	return trim("modules/".$this->module."/".$gatwayFile);
    }
    
    /**
     * Impostazione del nome del gateway da utilizzare
     *
     * @param string $gateway	: Nome del gateway da utilizzare
     */
    public function setGateway($gateway){
    	$this->gateway = $gateway;
    }
    
 
    /**
     * Recupero del nome del file di gateway
     * 
     * @return string
     */
    public function getGateway(){
    	return $this->gateway;
    }
    
    /**
     * true se l'azione richiede di passare per il validation
     * altrimenti false
     *
     * @param boolean $val
     */
    public function setValidation($val){
    	$this->validation = $val ? "true" : "false";
    }
    
    /**
     * Ritorna true se l'azione passa il validation
     *
     * @return boolean
     */
    public function getValidation(){
    	return $this->validation;
    }
    
    public function setModule($module){
    	$this->module = $module;
    }
    
    public function getModule(){
    	return $this->module;
    }
    
    public function setModel($model){
    	$this->model = $model;
    }
    
    public function getModel(){
    	return trim($this->model);
    }
    
    /**
	 * Impostazione del nome dell'azione da eseguire
	 * 
	 * @param string $action	: il nome dell'azione
	 */
    public function setAction($action){
    	$this->action = $action;
    }
    
	/**
	 * Ritorna il nome dell'azione corrente
	 * 
	 * @return string
	 */
    public function getAction(){
    	return $this->action;
    }
    
    /**
	 * Impostazione del nome del form dell'azione da eseguire
	 * 
	 * @param string $form	: il nome del form
	 */
	public function setForm($form){
    	$this->form = $form;
    }
    
    /**
	 * Ritorna il nome del form dell'azione corrente
	 * 
	 * @return string
	 */
    public function getForm(){
    	return $this->form;
    }
}
?>
