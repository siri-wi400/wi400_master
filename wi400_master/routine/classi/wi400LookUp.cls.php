<?php
class wi400LookUp {

	private $id;
	private $action;
	private $form;
	private $fields = array();
	private $cpyFields = array();
	private $parameters = array();
    private $jsParameters = array();
    
    public function __construct($action, $id = "", $form = ""){
		if ($id == "") $id = $action;
		$this->action = $action;
		$this->form = $form;
		$this->id = $id;
    }
    
    public function getAction(){
    	
    	$retAction = $this->action;
    	if ($this->form != ""){
    		$retAction.= "&f=".$this->form;
    	}
    	return $retAction;
    }
    
    public function getId(){
    	return $this->id;
    }

    public function setFields($fields){
    	$this->fields = $fields;
    	$this->cpyFields = $fields;
    }
      
    public function getFields(){
    	return $this->fields;
    }
    
    public function getCpyFields(){
    	return $this->cpyFields;
    }
    
    public function addField($field){
    	$this->fields[] = $field;
    	$this->cpyFields[] = $field; 
    }
    public function replace_key($old, $new) {
    	foreach( $this->fields as $key => $value) {
    		if ($value==$old) {
    			$this->fields[$key]=$new;
    		}
    	}
    	foreach( $this->cpyFields as $key => $value) {
    		if ($value==$old) {
    			$this->cpyFields[$key]=$new;
    		}
    	}
    }
    public function getParameters(){
    	return $this->parameters;
    }
    
    public function setParameters($parameters){
    	$this->parameters = $parameters;
    }
    
    public function addParameter($key, $parameter){
    	$this->parameters[$key] = $parameter; 
    }
    
    public function getJsParameters(){
    	return $this->jsParameters;
    }
    
    public function addJsParameter($jsParameter){
    	$this->jsParameters[] = $jsParameter; 
    }

}
?>